<?php
class SpipController extends Zend_Controller_Action
{
	var $dbNom = "spip_citu";
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->view->data = "OK"; 					
			
	}

	/**
	 * controle pour l'importation d'un fichier csv
	 */
	public function importAction() {
		$this->view->data = "OK"; 					
			
	}

	/**
	 * controle pour l'affichage des publication dans HAL
	 */
	public function rsshalAction() {
		if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');
		$hal = new Flux_Hal($this->dbNom);
		$rss = $hal->getAuthorPubli($this->_getParam('auteur'));
		$this->view->canal = $rss;
			
	}

	/**
	 * controle pour la création automatique des rubrique et article d'auteur
	 */
	public function creaenvauteurAction() {
		//initialisation des objets
		if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');
		
		$s = new Flux_Site($this->dbNom);
		$arrRubStat = array("Membre"=>576,"Doctorant"=>578,"Associé"=>577, "Historique"=>579);
		$dbArt = new Model_DbTable_Spip_articles($s->db);
		$dbAut = new Model_DbTable_Spip_auteurs($s->db);
		$dbR = new Model_DbTable_Spip_rubriques($s->db);
		$dbAutR = new Model_DbTable_Spip_auteursrubriques($s->db);
		
		//charge la liste des membres
		$membres = $s->csvToArray("../data/paragraphe/membresCITU.csv",0,","); 					
		$nbM = count($membres);
		for ($i = 0; $i < $nbM; $i++) {
			//récupère l'identifiant de l'auteur
			$arrAuteur = $dbAut->findByLogin(utf8_encode($membres[$i][0]));
			$idAuteur = count($arrAuteur["id_auteur"]) ? $arrAuteur["id_auteur"] : 0; 
			if($idAuteur){
				$membres[$i]["idAuteur"] = $idAuteur;
				
				//construction du numéro d'ordre
				$num = $i."00";
				/*
				if($i<10)$num = "0".$i;
				else $num = "".$i;
				if(!isset($arrRubStat[$membres[3]]))
					$idRubStat = $dbR->findByTitre($membres[3]);
				*/ 
				$idRubStat = $arrRubStat[$membres[$i][1]];
				$membres[$i]["idRubStat"] = $idRubStat;
				//vérifie si la rubrique de l'auteur existe
				$titreRubAuteur = $num.". ".$arrAuteur["nom"];
				$membres[$i]["titreRubAuteur"] = $titreRubAuteur;
				$arrRubAut = $dbR->findByTitre($titreRubAuteur);
				$idRubAuteur = count($arrRubAut) ? $arrRubAut[0]["id_rubrique"] : 0;
				$membres[$i]["idRubAuteur"] = $idRubAuteur;
				if($idRubAuteur==0){
					//création de la rubrique de l'auteur
					$idRubAuteur = $dbR->ajouter(array("id_parent"=>$idRubStat, "titre"=>$titreRubAuteur, "statut"=>'publie'));
				}
				//vérifie le statut
				switch ($membres[$i][2]) {
					case "admin":
						$dbAut->edit($idAuteur, array("statut"=>"0minirezo"));
					break;
					case "restreint":
						$dbAut->edit($idAuteur, array("statut"=>"0minirezo"));
						$dbAutR->ajouter(array("id_auteur"=>$idAuteur, "id_rubrique"=>$idRubAuteur));
					break;
				}
				//création des articles
				$dbArt->ajouter(array("id_rubrique"=>$idRubAuteur, "titre"=>"01. Profil", "texte"=>utf8_encode("A compléter..."), "statut"=>"publie"));
				$dbArt->ajouter(array("id_rubrique"=>$idRubAuteur, "titre"=>utf8_encode("02. Enseignements et responsabilités administratives"), "texte"=>utf8_encode("A compléter..."), "statut"=>"publie"));
				$dbArt->ajouter(array("id_rubrique"=>$idRubAuteur, "titre"=>"03. Publications", "texte"=>utf8_encode("A compléter..."), "statut"=>"publie"));
				$dbArt->ajouter(array("id_rubrique"=>$idRubAuteur, "titre"=>utf8_encode("04. Conférences"), "texte"=>utf8_encode("A compléter..."), "statut"=>"publie"));
				
			}
		}
		
		$this->view->data = $membres;
					
	}
	
	/**
	 * controle pour pour l'envoie des mails de connection
	 */
	public function envoiemailauteurAction() {
			
		//initialisation des objets
		$s = new Flux_Site($this->dbNom);
		$dbA = new Model_DbTable_Spip_articles($s->db);
		$dbAut = new Model_DbTable_Spip_auteurs($s->db);
		$dbR = new Model_DbTable_Spip_rubriques($s->db);
		$gm = new Flux_Gmail("samszon","Juillet2014");
		
		//charge la liste des auteurs à prévenir
		$auteurs = $s->csvToArray("../data/paragraphe/auteursCITU.csv",0,","); 					
		
		$nbAut = count($auteurs);
		$this->view->data = array();
		for ($i = 0; $i < $nbAut; $i++) {
			//récupère l'identifiant de l'auteur
			$arrAuteur = $dbAut->findByLogin(utf8_encode($auteurs[$i][2]));
			$idAuteur = count($arrAuteur["id_auteur"]) ? $arrAuteur["id_auteur"] : 0; 
			if($idAuteur){
				//recherche la rubrique dédié à l'auteur
				$arrRubAut = $dbR->findByTitre($arrAuteur["nom"],true);
				$idRubAuteur = count($arrRubAut) ? $arrRubAut[0]["id_rubrique"] : 0;
				$auteurs[$i]["idRubAuteur"] = $idRubAuteur;
				//$this->view->data["auteurs"][]=$auteurs[$i];
				if($idRubAuteur){
					//récupère les articles de la rubrique
					$arrArt = $dbA->findById_rubrique($idRubAuteur);
					/*construction de la vue
					 * http://stackoverflow.com/questions/11076428/sending-newsletter-from-a-zend-framework-project
					 */
					$mailView = new Zend_View();
					$mailView->setScriptPath(APPLICATION_PATH.'/views/scripts/mail/');
			    	$mailView->assign('site',$auteurs[$i][4]);
			    	$mailView->assign('prenom',$auteurs[$i][0]);
			    	$mailView->assign('nom',$auteurs[$i][1]);
			    	$mailView->assign('login',$auteurs[$i][2]);
			    	$mailView->assign('mdp',$auteurs[$i][3]);
			    	$mailView->assign('arrAut',$arrAuteur);
			    	$mailView->assign('arrRub',$arrRubAut[0]);
			    	$mailView->assign('arrArt',$arrArt);
			    	$mailView->assign('urlVoir',$auteurs[$i][5]);
			    	$mailView->assign('urlEcrire',$auteurs[$i][6]);
			    	$mailView->assign('urlAuteur',$auteurs[$i][7]);
			    	
			    	$mailView->assign('mailContact',"samuel.szoniecky@univ-paris8.fr");
			    	$mailView->assign('nomContact',"Samuel Szoniecky");    	

					/*envoie du mail
					merci à http://stackoverflow.com/questions/18361233/gmail-sending-limits
					http://stackoverflow.com/questions/11076428/sending-newsletter-from-a-zend-framework-project
					*/
					try {
			    		$date = date("m/d/Y H:i:s");
						$gm->sendMail($arrAuteur['email'], "samszon@gmail.com", $auteurs[$i][4]." : nouveau site", $mailView->render('spipconnexion.phtml'));
						$this->view->data["OK"][] = "mail envoyé à ".$arrAuteur['email']." le :".$date;
						//$this->view->data["HTML"][] = $mailView->render('spipconnexion.phtml');
						//$this->view->data["arrRubAut"] = $arrRubAut;
						//$this->view->data["arrAuteur"] = $arrAuteur;
						//$this->view->data["arrArt"] = $arrArt;
						
						//ne pas envoyer les mail trop vite
						//https://support.google.com/a/answer/166852?hl=en
						sleep(2);
				       	continue;
					} catch (Zend_Mail_Transport_Exception $e) {
					    $this->view->data['Zend_Mail_Transport_Exception'][]="- Mails were not accepted for sending: ".$e->getMessage();
					} catch (Zend_Mail_Protocol_Exception $e) {
					    $this->view->data['Zend_Mail_Protocol_Exception'][]="- SMTP Sentmail Error: ".$e->getMessage();
					} catch (Exception $e) {
					    $this->view->data['Unknown Exception'][]="- SMTP Sentmail Error: ".$e->getMessage();
					}		
			    	
				}
				
			}
		}	
			
		
	}
	
	
}
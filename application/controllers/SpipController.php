<?php
/**
 * SpipController
 *
 * Pour gérer SPIP et ses objets
 * http://www.spip.net/fr_rubrique91.html
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

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
	 * controle pour l'importation des données
	 */
	public function importAction() {
		
		
		//initialisation des objets
		$sSpip = new Flux_Site($this->_getParam('idBaseSpip'));
    	$sSpip->bTrace = true;
    	$sSpip->bTraceFlush = true;
		
		switch ($this->_getParam('type')) {
			case "fluxSKOs":
			    $dbA = new Model_DbTable_Spip_mots($sSpip->db);
				$json = $sSpip->getUrlBodyContent("http://skos.um.es/unescothes/CS000/json");
				
				$arr = json_encode($json);
				
				foreach ($arr as $key => $value) {
					;
				}
		    		
				//ajouter un mot clef dans spip
				$dbA->ajouter(array("titre"=>"","descriptif"=>"","id_groupe"=>"","type"=>""
				,"profondeur"=>"", "id_mot_racine"=>"", "id_parent"=>""));
				break;
			case "toJDC":
				$s = new Flux_Spip($this->_getParam('idBaseSpip'));
				$json = $s->importToJDC($this->_getParam('idDocParent')
					,$this->_getParam('where')
					,$this->_getParam('idBaseSpip')
					,$this->_getParam('idBaseDst')
					);
				break;
		}
		$this->view->data = "OK"; 				
		
	}
	
	
	/**
	 * controle pour l'exportation des données
	 */
	public function exportAction() {
		
		//initialisation des objets
		$sSpip = new Flux_Site($this->_getParam('idBaseSpip'));
		$sFlux = new Flux_Site($this->_getParam('idBaseFlux'));
    		$sSpip->bTrace = true;
    		$sSpip->bTraceFlush = true;
		
		switch ($this->_getParam('type')) {
			case "fluxExi":
				$dbA = new Model_DbTable_Spip_auteurs($sSpip->db);
				$dbE = new Model_DbTable_Flux_Exi($sFlux->db);
				$dbFS = new Model_DbTable_Flux_Spip($sFlux->db);
				$dbD = new Model_DbTable_Flux_Doc($sFlux->db);
				$dbT = new Model_DbTable_Flux_Tag($sFlux->db);
		    		$dbETD = new Model_DbTable_Flux_ExiTagDoc($sFlux->db);
		    		$dbUE = new Model_DbTable_Flux_UtiExi($sFlux->db);
		    		
				//récupère les auteurs;
				$arrAuteurs = $dbA->getAll();
				//récupère les références
				$idDocRacine = $dbD->ajouter(array("titre"=>"Editeur de réseaux d'influences","url"=>"jdc/public/biolographes"));    		    		    		
				//on ajoute le document spip
				$idDoc = $dbD->ajouter(array("titre"=>"SPIP proverbes","url"=>"http://gapai.univ-paris8.fr/CreaTIC/E-education/Proverbes/","parent"=>$idDocRacine, ));    		
		    		$TagGlobal = $dbT->findByCode("Cribles biolographes");
		    		$idTagAct = $dbT->ajouter(array("code"=>"Acteur","parent"=>$TagGlobal["tag_id"]));
		    		
				//création des existences
				foreach ($arrAuteurs as $a) {
					$idE = $dbE->ajouter(array("nom"=>$a["nom"]));
					$dbFS->ajouter(array("id_flux"=>$idE,"id_spip"=>$a["id_auteur"],"obj_flux"=>"exi","obj_spip"=>"auteurs"));
					$sSpip->trace("existence ajoutée : ".$a["nom"]." = ".$a["id_auteur"]." => ".$idE);
					
					$dbETD->ajouter(array("exi_id"=>$idE,"tag_id"=>$idTagAct,"doc_id"=>$idDoc));
		    			//lie l'existence à l'utilisateur admin pour gérer les droits CRUD
					$dbUE->ajouter(array("uti_id"=>1, "exi_id"=>$idE)); 
					
				}
				break;
		}
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
		$idRub = $this->_getParam('idRub',6);
		$dbArt = new Model_DbTable_Spip_articles($s->db);
		$dbAut = new Model_DbTable_Spip_auteurs($s->db);
		$dbR = new Model_DbTable_Spip_rubriques($s->db);
		$dbAutL = new Model_DbTable_Spip_auteursliens($s->db);
		
		//récupère la liste des auteurs
		$membres = $dbAut->getAll();
		$nbM = count($membres);
		for ($i = 0; $i < $nbM; $i++) {
			$m = $membres[$i]; 				
			//construction du numéro d'ordre
			$num = $i."00";
			//vérifie si la rubrique de l'auteur existe
			$titreRubAuteur = $num.". ".$m["nom"];
			$arrRubAut = $dbR->findByTitre($titreRubAuteur);
			$idRubAuteur = count($arrRubAut) ? $arrRubAut[0]["id_rubrique"] : 0;
			if($idRubAuteur==0){
				//création de la rubrique de l'auteur
				$idRubAuteur = $dbR->ajouter(array("id_parent"=>$idRub, "titre"=>$titreRubAuteur
				,'lang'=>'fr','langue_choisie'=>'oui','texte'=>'','descriptif'=>'', "statut"=>'publie'));
			}
			//vérifie le statut
			if($m['statut']=='1comite') {
				$dbAut->edit($m["id_auteur"], array("statut"=>"0minirezo"));
				$dbAutL->ajouter(array("id_auteur"=>$m["id_auteur"], "id_objet"=>$idRubAuteur, "objet"=>'rubrique'));
			}
			//création des articles
			$idArt = $dbArt->ajouter(array("id_rubrique"=>$idRubAuteur, "titre"=>"01. Profil", "texte"=>"A compléter..."
				, "statut"=>"publie",'lang'=>'fr','langue_choisie'=>'oui','surtitre'=>'','soustitre'=>'','descriptif'=>''
				,'chapo'=>'','ps'=>'','nom_site'=>'','url_site'=>'','virtuel'=>''));
			$dbAutL->ajouter(array("id_auteur"=>$m["id_auteur"], "id_objet"=>$idArt, "objet"=>'article'));
			$idArt = $dbArt->ajouter(array("id_rubrique"=>$idRubAuteur, "titre"=>"02. Enseignements et responsabilités administratives", "texte"=>"A compléter..."
				, "statut"=>"publie",'lang'=>'fr','langue_choisie'=>'oui','surtitre'=>'','soustitre'=>''
				,'descriptif'=>'','chapo'=>'','ps'=>'','nom_site'=>'','url_site'=>'','virtuel'=>''));
			$dbAutL->ajouter(array("id_auteur"=>$m["id_auteur"], "id_objet"=>$idArt, "objet"=>'article'));
			$idArt = $dbArt->ajouter(array("id_rubrique"=>$idRubAuteur, "titre"=>"03. Publications", "texte"=>"A compléter..."
				, "statut"=>"publie",'lang'=>'fr','langue_choisie'=>'oui','surtitre'=>'','soustitre'=>'','descriptif'=>''
				,'chapo'=>'','ps'=>'','nom_site'=>'','url_site'=>'','virtuel'=>''));
			$dbAutL->ajouter(array("id_auteur"=>$m["id_auteur"], "id_objet"=>$idArt, "objet"=>'article'));
			$idArt = $dbArt->ajouter(array("id_rubrique"=>$idRubAuteur, "titre"=>"04. Conférences", "texte"=>"A compléter..."
				, "statut"=>"publie",'lang'=>'fr','langue_choisie'=>'oui','surtitre'=>'','soustitre'=>'','descriptif'=>''
				,'chapo'=>'','ps'=>'','nom_site'=>'','url_site'=>'','virtuel'=>''));
			$dbAutL->ajouter(array("id_auteur"=>$m["id_auteur"], "id_objet"=>$idArt, "objet"=>'article'));

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
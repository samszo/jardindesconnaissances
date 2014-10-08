<?php
class SpipController extends Zend_Controller_Action
{
	var $dbNom = "spip_crac";
	
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
		$s = new Flux_Site($this->dbNom);
		$arrRubStat = array("Membre"=>344,"Doctorant"=>366,"Associé"=>367);
		$db = new Model_DbTable_flux_acti();
		$dbArt = new Model_DbTable_Spip_articles($s->db);
		$dbAut = new Model_DbTable_Spip_auteurs($s->db);
		$dbR = new Model_DbTable_Spip_rubriques($s->db);
		$dbAutR = new Model_DbTable_Spip_auteursrubriques($s->db);
		
		if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');

		//charge la liste des membres
		$membres = $s->csvToArray("../data/paragraphe/membresCRAC.csv",0,","); 					
		$nbM = count($membres);
		for ($i = 0; $i < $nbM; $i++) {
			//récupère l'identifiant de l'auteur
			$idAuteur = $dbAut->findByLogin(utf8_encode($membres[$i][0]),true);
			if($idAuteur){
				$membres[$i]["idAuteur"] = $idAuteur;
				
				//construction du numéro d'ordre
				if($i<10)$num = "0".$i;
				else $num = "".$i;
				/*
				if(!isset($arrRubStat[$membres[3]]))
					$idRubStat = $dbR->findByTitre($membres[3]);
				*/ 
				$idRubStat = $arrRubStat[$membres[$i][1]];
				$membres[$i]["idRubStat"] = $idRubStat;
				//vérifie si la rubrique de l'auteur existe
				$titreRubAuteur = $num.". ".$membres[$i][3];
				$membres[$i]["titreRubAuteur"] = $titreRubAuteur;
				$arrRubAut = $dbR->findByTitre(utf8_encode($titreRubAuteur));
				$idRubAuteur = count($arrRubAut) ? $arrRubAut[0]["id_rubrique"] : 0;
				$membres[$i]["idRubAuteur"] = $idRubAuteur;
				if($idRubAuteur==0){
					//création de la rubrique de l'auteur
					$idRubAuteur = $dbR->ajouter(array("id_parent"=>$idRubStat, "titre"=>utf8_encode($titreRubAuteur), "statut"=>'new'));
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
	
	
}
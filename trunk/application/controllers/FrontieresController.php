<?php

/**
 * FrontieresController
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class FrontieresController extends Zend_Controller_Action {
	
	var $dbNom = "flux_frontieres";
	var $redir = '/auth/login?redir=frontieres&idBase=';
	var $fondTweetPalette = 1218;
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		try {

			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
			    // l'identité existe ; on la récupère
			    $this->view->identite = $auth->getIdentity();
			    $ssUti = new Zend_Session_Namespace('uti');
			    $this->view->idUti = $ssUti->idUti;
				//récupère les photos et les géolocalisations
			    $site = new Flux_Site();
			    $db = $site->getDb($this->dbNom);
				$dbUGD = new Model_DbTable_Flux_UtiGeoDoc($db);
		    	$where = null;
				if($this->_getParam('id', 0)){
			    	$where = "d.doc_id = ".$this->_getParam('id', 0);			
		    	}
				$this->view->arr = $dbUGD->getDataLiees("RAND()",$where); 
			    
			}else{
			    $this->_redirect($this->redir.$this->dbNom);
			}	    	

		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}

	
	/**
	 * The default action - show the home page
	 */
	public function etudiantsAction() {
		try {

			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
			    // l'identité existe ; on la récupère
			    $this->view->identite = $auth->getIdentity();
			    $ssUti = new Zend_Session_Namespace('uti');
			    $this->view->idUti = $ssUti->idUti;
				//récupère les photos et les géolocalisations
			    $site = new Flux_Site();
			    $db = $site->getDb($ssUti->dbNom);
			    /*
				$dbUGD = new Model_DbTable_Flux_UtiGeoDoc($db);
		    	$where = null;
				if($this->_getParam('id', 0)){
			    	$where = "d.doc_id = ".$this->_getParam('id', 0);			
		    	}
				$this->view->arr = $dbUGD->getDataLiees("RAND()",$where); 
			    */
			}else{
			    $this->_redirect('/auth/login?redir=frontieres&idBase='.$this->dbNom);
			}	    	

		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}
	
	public function ajoutAction() {
		try {
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {			
				$params = $this->getRequest()->getParams();
				$this->view->data = $params;
				if($params){
		    	   	//initialise les variables
					$d = new Zend_Date();		
					$site = new Flux_Site();
				    $db = $site->getDb($this->dbNom);
					$dbG = new Model_DbTable_Flux_Geos($db);
					$dbGUD = new Model_DbTable_Flux_UtiGeoDoc($db);
					//récupération de l'utilisateur	
					//$idU = $site->getUser(array("login"=>$_SERVER['REMOTE_ADDR'],"flux"=>"frontières","date_inscription"=>$d->get("c")));
				    $ssUti = new Zend_Session_Namespace('uti');
				    $idU = $ssUti->idUti;
					
					//enregistre la position
					$idGeo = $dbG->ajouter(array("lat"=>$params['lat'],"lng"=>$params['lng'],"zoom_max"=>$params['zoom'],"maj"=>$d->get("c")));
		    		$dbGUD->ajouter(array("doc_id"=>$params['idDoc'],"uti_id"=>$idU,"geo_id"=>$idGeo,"maj"=>$d->get("c"), "note"=>$params['note']));
		    		//calcul le tag cloud
					$dbUTD = new Model_DbTable_Flux_UtiTagDoc($db);		
					$tagDoc = $dbUTD->GetDocTags($params['idDoc'],"" , "", "RAND()", "10");
					for ($i = 0; $i < count($tagDoc); $i++) {
						$tagDoc[$i]['on'] = 1;
					}
					$tagRand = $dbUTD->GetUtiTags(1 ,"" , "", "RAND()", "10");
					for ($i = 0; $i < count($tagRand); $i++) {
						$tagRand[$i]['on'] = 0;
					}
					$tags = array_merge($tagDoc, $tagRand);
					$this->view->data = $tags;					
				}
			    
			}else{
			    $this->_redirect($this->redir.$this->dbNom);
			}	    	
			}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}
	
	public function tagcloudAction() {
		try {
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {			
				$params = $this->getRequest()->getParams();
				if($params){
		    	   	//initialise les variables
					$site = new Flux_Site();
				    $db = $site->getDb($this->dbNom);
		    		//calcul le tag cloud
					$this->view->data = $this->getTagcloud($db, $params['idDoc']);					
				}
			    
			}else{
			    $this->_redirect($this->redir.$this->dbNom);
			}	    	
			}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}

	public function classementAction() {
		try {
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {			
				$params = $this->getRequest()->getParams();
				//if($params){
		    	   	//initialise les variables
					$site = new Flux_Site();
				    $db = $site->getDb($this->dbNom);
				    $arr = array();
				    //calcul les indices géographiques
				    $dbGUD = new Model_DbTable_Flux_UtiGeoDoc($db);
				    $arr["indTerreForDoc"] = $dbGUD->calcIndTerreForDoc();
				    $arr["indTerreForUti"] = $dbGUD->calcIndTerreForUti();
					$dbUTD = new Model_DbTable_Flux_UtiTagDoc($db);

					//calcul les indices des tags cloud
				    //$arr["UtisNbTagNbDoc"] = $dbUTD->GetUtisNbTagNbDoc();
				    $arr["indTerreTagForUti"] = $dbUTD->calcIndTerreTagForUti();
				    $arr["indTerreTagForDoc"] = $dbUTD->calcIndTerreTagForDoc();
				    $arr["indTerreTagForTag"] = $dbUTD->calcIndTerreTagForTag();

				    //calcul les indices des palettes de concepts
				    $tp = new Flux_Tweetpalette($this->dbNom);
				    $arr["indTerreConcept"] = $tp->getFondStat($this->fondTweetPalette);
				    
				    //envoie les données
					$this->view->data = $arr;					
				//}
			    
			}else{
			    $this->_redirect($this->redir.$this->dbNom);
			}	    	
			}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}
	
	function getTagcloud($db, $idDoc) {
		//calcul le tag cloud
		//echo "idDoc=".$idDoc."<br/>";
		$dbUTD = new Model_DbTable_Flux_UtiTagDoc($db);		
		$tagDoc = $dbUTD->GetDocTags($idDoc,"" , "", "RAND()", "10");
		for ($i = 0; $i < count($tagDoc); $i++) {
			$tagDoc[$i]['on'] = 1;
		}
		$tagRand = $dbUTD->GetUtiTags(1 ,"" , "", "RAND()", "10");
		for ($i = 0; $i < count($tagRand); $i++) {
			$tagRand[$i]['on'] = 0;
		}
		$tags = array_merge($tagDoc, $tagRand);
		return $tags;					
	}
	
	
}

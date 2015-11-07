<?php

/**
 * DeleuzeController
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class DeleuzeController extends Zend_Controller_Action {
	
	var $dbNom = "flux_DeleuzeSpinoza";
	var $urlRedir = 'deleuze/navigation?idBase=';
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		try {
			$this->view->title = "Affichage des flux Deleuze";
		    $this->view->headTitle($this->view->title, 'PREPEND');
		    $site = new Flux_Site();
		    $db = $site->getDb("flux_DeleuzeSpinozaOld");
			$dbUTD = new Model_DbTable_Flux_UtiTagDoc($db);
		    if($this->_getParam('tag', 0)){
		    		$arr = $dbUTD->findByTag($this->_getParam('tag', 0));
		        $this->view->arr = $arr;
		    }elseif ($this->_getParam('url', 0)){
		    		$arr = $dbUTD->findByUrl($this->_getParam('url', 0));
		        $this->view->arr = $arr;
		    }else{
			    $arr = $dbUTD->getAllInfo();
		        $this->view->arr = $arr;
		    }
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}

	/**
	 * affiche un arbre pour gérer les mots clefs
	 */
	public function arbreAction() {
		try {
			$this->view->title = "Arbre de cours";
		    $this->view->headTitle($this->view->title, 'PREPEND');

		
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}

	/**
	 * affichage des positions d'un term
	 */
	public function positionAction() {
		
		
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $ssUti = new Zend_Session_Namespace('uti');
		    $this->view->idUti = $ssUti->uti["uti_id"];
		}else{
		    $this->_redirect('/auth/login');
		}
		$this->view->resultats = "";
	    	if($this->_getParam('term', 0)){
	    		$oD = new Flux_Deleuze($this->dbNom);
	    		$oD->user = $ssUti->uti["uti_id"];
	    		echo $this->_getParam('term', 0);
	    		$arrPosis = $oD->getTermPositions($this->_getParam('term', 0), true, true, "score DESC", $this->_getParam('idDoc', false));
	    		//echo $this->dbNom;
	    		$this->view->resultats = $arrPosis;
			$this->view->term = $this->_getParam('term', 0);
	    	}
		$this->view->ajax = $this->_getParam('ajax', 0);    	
	}	

	/**
	 * affichage des positions d'un term
	 */
	public function positionsAction() {
		
		
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $ssUti = new Zend_Session_Namespace('uti');
		    $this->view->idUti = $ssUti->uti["uti_id"];
		}else{
		    $this->_redirect('/auth/login');
		}
		$this->view->resultats = "";
	    	if($this->_getParam('term', 0)){
	    		$oD = new Flux_Deleuze($this->dbNom);
	    		$oD->user = $ssUti->uti["uti_id"];
	    		$arrPosis = $oD->getTermPositions($this->_getParam('term', 0), true, $this->dbNom, "tronc");
	    		echo $this->_getParam('term', 0);
	    		$this->view->resultats = $arrPosis;
				$this->view->term = $this->_getParam('term', 0);
	    	}
	    $this->view->ajax = false;
	    	if($this->_getParam('ajax', 0))$this->view->ajax = true;    	
	}	
	
	
	/**
	 * affichage d'un fragment
	 */
	public function fragmentAction() {
		
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $ssUti = new Zend_Session_Namespace('uti');
		    $this->view->idUti = $ssUti->uti["uti_id"];
		}else{
		    $this->view->identite = "aucun";
		    $this->view->idUti = -1;
		}
				
	    $this->view->resultats = "";
	    	if($this->_getParam('id', 0)){
			$oD = new Flux_Deleuze($this->dbNom);
	    		$arrPosis = $oD->getFragment($this->_getParam('id', 0));
			$this->view->resultats = $arrPosis;
			$this->view->term = $this->_getParam('term', 0);
	    	}
	    	
	}		

	/**
	 * affichage les fragments en json
	 */
	public function fragmentsAction() {
		
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $ssUti = new Zend_Session_Namespace('uti');
		    $this->view->idUti = $ssUti->uti["uti_id"];
		}else{
		    $this->view->identite = "aucun";
		    $this->view->idUti = -1;
		}
				
	    $this->view->resultats = "";
	    	if($this->_getParam('term', 0)){
			$oD = new Flux_Deleuze($this->dbNom);
			$oD->user = $ssUti->uti["uti_id"];
			$arrPosis = $oD->getTermPositions($this->_getParam('term', 0),true);
			$this->view->resultats = $arrPosis;
			$this->view->term = $this->_getParam('term', 0);
			$this->view->getJson = $this->_getParam('json', 0);
	    	}    	
	}		
	
	/**
	 * affichage les outils de navigation dans les cours
	 */
	public function navigationAction() {
		
		$this->view->title = "Navigation dans les cours de Deleuze";
		
    		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {						
			// l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $ssUti = new Zend_Session_Namespace('uti');
		    $ssUti->redir = $this->urlRedir.$this->dbNom;
		    $this->view->uti = $ssUti->uti["uti_id"];
		}else{			
		    $this->view->identite = false;
		}   
		$this->view->idBase = $this->dbNom;
		
		$oD = new Flux_Deleuze($this->dbNom);
		//récupère les utilisateurs avec des mots clefs
		$dbUTD = new Model_DbTable_Flux_UtiTagDoc($oD->db);
		$this->view->cribles = $dbUTD->GetUtisNbTagNbDoc("u.role = 'crible'");
		
	}	
	
	public function ajoutAction() {
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $ssUti = new Zend_Session_Namespace('uti');
			//enregistre les positions
			$oD = new Flux_Deleuze($this->dbNom);
			$this->view->data = $oD->saveTermPosition($ssUti->uti["uti_id"], $this->getRequest()->getParams());
		}else{
		    $this->_redirect('/auth/login');
		}
	}
		
	public function suppAction() {
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			//supprime la position
			$oD = new Flux_Deleuze($this->dbNom);
			$this->view->data = $this->getRequest()->getParams();
			$oD->suppTermPosition($this->_getParam('idDoc', 0));
		}else{
		    $this->_redirect('/auth/login');
		}
	}
	
	public function modifAction() {
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			//supprime la position
			$oD = new Flux_Deleuze($this->dbNom);
			$oD->modifTermPosition($this->getRequest()->getParams());
		}else{
		    $this->_redirect('/auth/login');
		}
	}

	public function chercheAction() {
	    if($this->_getParam('term', 0)){
			$oD = new Flux_Deleuze('deleuze');
			//echo $this->_getParam('term');
			$this->view->data = $oD->cherche($this->_getParam('term'));		
	    }
	}
	
}

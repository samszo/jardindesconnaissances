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

	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		try {
			$this->view->title = "Affichage des flux Deleuze";
		    $this->view->headTitle($this->view->title, 'PREPEND');
		    $site = new Flux_Site();
		    $db = $site->getDb($this->dbNom);
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
		    $this->view->idUti = $ssUti->idUti;
		}else{
		    $this->_redirect('/auth/login');
		}
		$this->view->resultats = "";
    	if($this->_getParam('term', 0)){
			$oD = new Flux_Deleuze($this->dbNom);
			$oD->user = $ssUti->idUti;
    		$arrPosis = $oD->getTermPositions($this->_getParam('term', 0));
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
		
		try {
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
			    // l'identité existe ; on la récupère
			    $this->view->identite = $auth->getIdentity();
			    $ssUti = new Zend_Session_Namespace('uti');
			    $this->view->idUti = $ssUti->idUti;
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
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	    	
	}		

	/**
	 * affichage les fragments en json
	 */
	public function fragmentsAction() {
		
		try {
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
			    // l'identité existe ; on la récupère
			    $this->view->identite = $auth->getIdentity();
			    $ssUti = new Zend_Session_Namespace('uti');
			    $this->view->idUti = $ssUti->idUti;
			}else{
			    $this->view->identite = "aucun";
			    $this->view->idUti = -1;
			}
					
		    $this->view->resultats = "";
	    	if($this->_getParam('term', 0)){
				$oD = new Flux_Deleuze($this->dbNom);
				$oD->user = $ssUti->idUti;
				$arrPosis = $oD->getTermPositions($this->_getParam('term', 0),true);
				$this->view->resultats = $arrPosis;
				$this->view->term = $this->_getParam('term', 0);
				$this->view->getJson = $this->_getParam('json', 0);
	    	}
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
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
		    $this->view->idUti = $ssUti->idUti;
		}else{
		    $this->_redirect('/auth/login');
		}
		$oD = new Flux_Deleuze($this->dbNom);
		//récupère les utilisateurs avec des mots clefs
		$dbUTD = new Model_DbTable_Flux_UtiTagDoc($oD->db);
		$this->view->cribles = $dbUTD->GetUtisNbTagNbDoc("u.role = 'crible'");
		
	}	
	
	public function ajoutAction() {
		try {
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
			    // l'identité existe ; on la récupère
			    $this->view->identite = $auth->getIdentity();
			    $ssUti = new Zend_Session_Namespace('uti');
				//enregistre les positions
				$oD = new Flux_Deleuze($this->dbNom);
				$this->view->data = $oD->saveTermPosition($ssUti->idUti, $this->getRequest()->getParams());
			}else{
			    $this->_redirect('/auth/login');
			}
			
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}
		
	public function suppAction() {
		try {
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
				//supprime la position
				$oD = new Flux_Deleuze($this->dbNom);
				$this->view->data = $this->getRequest()->getParams();
				$oD->suppTermPosition($this->_getParam('idDoc', 0));
			}else{
			    $this->_redirect('/auth/login');
			}
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}
	
	public function modifAction() {
		try {
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
				//supprime la position
				$oD = new Flux_Deleuze($this->dbNom);
				$oD->modifTermPosition($this->getRequest()->getParams());
			}else{
			    $this->_redirect('/auth/login');
			}
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}

	public function chercheAction() {
		$oD = new Flux_Deleuze($this->dbNom);
	}
	
}

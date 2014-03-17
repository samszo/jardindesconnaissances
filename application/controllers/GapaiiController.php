<?php

/**
 * GAPAII
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class GapaiiController extends Zend_Controller_Action {
	
	var $dbNom = "flux_gapaii";
	var $idOeu = 6;//37;//
	var $idUti = 2;
	var $idDoc = 1;
	var $idCpt = 157829;// poeme stein 158393;//158278 - test poème;//;

	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');
		if($this->_getParam('idOeu')) $this->idOeu = $this->_getParam('idOeu');
		if($this->_getParam('idDoc')) $this->idDoc = $this->_getParam('idDoc');
		if($this->_getParam('idCpt')) $this->idCpt = $this->_getParam('idCpt');
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $ssUti = new Zend_Session_Namespace('uti');
		    $this->view->idUti = $ssUti->idUti;
		}else{
			$this->view->idUti = $this->idUti;
		}
		$this->view->idBase = $this->dbNom;
		$this->view->idOeu = $this->idOeu;
		$this->view->idDoc = $this->idDoc;
		$this->view->idCpt = $this->idCpt;
	}

	public function savegenAction() {
		//enregistre le document généré
		if($this->_getParam('idBase', 0) && $this->_getParam('idUti', 0) && $this->_getParam('data', 0)){
			$g = new Flux_Gapaii($this->_getParam('idBase', 0));
			$idDoc = $g->saveGen($this->_getParam('idBase', 0), $this->_getParam('data'),$this->_getParam('idUti'));
			$this->view->idDoc = $idDoc;
		}
	}
		
	public function savesemevalAction() {
		//récupère les informations de la palette
		//print_r($this->getRequest()->getParams());
		if($this->_getParam('idBase', 0) && $this->_getParam('idDoc', 0) && $this->_getParam('idUti', 0) && $this->_getParam('sem', 0)){
			$g = new Flux_Gapaii($this->_getParam('idBase', 0));
			$g->saveSemEval($this->_getParam('idBase'), $this->_getParam('idDoc'), $this->_getParam('idUti'), $this->_getParam('sem'));
			//$this->view->sem = $this->_getParam('sem');
		}else echo "pas de donnée !";
	}
	
}

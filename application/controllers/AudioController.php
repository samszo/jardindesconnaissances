<?php
class AudioController extends Zend_Controller_Action
{
	var $idBase = "flux_proverbes";
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->view->data = "OK"; 					
			
	}

	/**
	 * controle pour l'enregistrment des sons
	 */
	public function recordAction() {
		 				
		
	}

	/**
	 * controle pour l'enregistrment des parole et la transcription
	 */
	public function dictaphoneAction() {
		$this->initInstance(); 				
		
	}
	
	/**
	 * controle pour la transcription des paroles
	 */
	public function paroleAction() {
		 				
		
	}
	
	function initInstance($action=""){
		if($this->_getParam('idBase')) $this->idBase = $this->_getParam('idBase');
		if($this->_getParam('idBaseCMS')) $this->idBaseCMS = $this->_getParam('idBaseCMS');
		if($this->_getParam('idUti')) $this->idUti = $this->_getParam('idUti');
		$this->idGeo = $this->_getParam('idGeo',-1);
		
		$this->view->idBase = $this->idBase;
		$this->view->idGeo = $this->idGeo;
		
		//pas d'authentification si idUti
		//echo "this->idUti=".$this->idUti."\n";
		if($this->idUti){
			$this->view->idUti = $this->idUti;
			return;
		}
		$auth = Zend_Auth::getInstance();
		$this->ssUti = new Zend_Session_Namespace('uti');
		if ($auth->hasIdentity()) {						
			// l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $this->view->uti = json_encode($this->ssUti->uti);
		    $this->view->idUti = $this->idUti = $this->ssUti->idUti;
		}else{
			$this->view->uti = 0;
		}
		
    }	
}
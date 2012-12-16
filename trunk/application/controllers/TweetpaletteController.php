<?php

/**
 * Tweetpalette
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class TweetpaletteController extends Zend_Controller_Action {
	
	var $dbNom = "flux_TweetPalette";
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		try {
			if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
			    // l'identité existe ; on la récupère
			    $this->view->identite = $auth->getIdentity();
			    $ssUti = new Zend_Session_Namespace('uti');
			    $this->view->idUti = $ssUti->idUti;
			    $this->view->tag = $this->_getParam('tag');
			    $this->view->url = $this->_getParam('url');
			    $this->view->idBase = $this->dbNom;
			    $this->view->iframe = $this->_getParam('iframe', false);
			    //récupère les palettes disponibles
			    $s = new Flux_Site($this->dbNom);
			    $dbDoc = new Model_DbTable_Flux_Doc($s->db);
			    $this->view->palettes = $dbDoc->findByType("palette");
			    //récupère les rôles disponibles
				$dbU = new Model_DbTable_Flux_Uti($s->db);
				$this->view->roles = $dbU->getRolesUtis("role");
			}else{
			    $this->_redirect('/auth/login?redir=tweetpalette&idBase='.$this->dbNom);
			}
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}

	/**
	 * The default action - show the home page
	 */
	public function litAction() {
		try {
			//récupère les informations de la palette
			if($this->_getParam('idBase', 0) && $this->_getParam('url', 0) && $this->_getParam('exi', 0) 
			&& $this->_getParam('urlFond', 0) && $this->_getParam('filtrer', 0) && $this->_getParam('event', 0)){
				$tp = new Flux_Tweetpalette($this->_getParam('idBase', 0));
				$this->view->json = $tp->getPaletteClics($this->_getParam('exi', 0), $this->_getParam('url', 0)
				, $this->_getParam('urlFond', 0), $this->_getParam('event', 0), $this->_getParam('filtrer', 0));
				//$s = new Flux_Stats($this->_getParam('idBase', 0));
				//$this->view->stats = $s->GetUtiTagDoc($this->_getParam('uti', 0), $this->_getParam('url', 0));
			}else{
				$this->view->json = "vide";
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
				//récupère les informations de la palette
				//print_r($this->getRequest()->getParams());
				if($this->_getParam('idBase', 0) && $this->_getParam('event', 0) && $this->_getParam('url', 0) && $this->_getParam('uti', 0) && $this->_getParam('exi', 0) && $this->_getParam('sem', 0)){
					$tp = new Flux_Tweetpalette($this->_getParam('idBase', 0));
					$tp->saveTweetSem($this->_getParam('uti', 0), $this->_getParam('exi', 0),$this->_getParam('url', 0), $this->_getParam('event', 0), $this->_getParam('sem'));
					//$this->view->sem = $this->_getParam('sem');
				}
			}else{
			    $this->_redirect('/auth/login?redir=tweetpalette&idBase='.$this->dbNom);
			}			
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}

	public function inputAction() {
		try {
			//récupère les informations pour les input
			$tp = new Flux_Tweetpalette($this->_getParam('idBase', 0));
			$this->view->data = $tp->getInput();			
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}
	
}

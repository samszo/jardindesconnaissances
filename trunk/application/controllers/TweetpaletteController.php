<?php

/**
 * Tweetpalette
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class TweetpaletteController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		try {
			$lit = "";
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
			if($this->_getParam('idBase', 0) && $this->_getParam('url', 0) && $this->_getParam('uti', 0) 
			&& $this->_getParam('urlFond', 0) && $this->_getParam('filtrer', 0) && $this->_getParam('event', 0)){
				$tp = new Flux_Tweetpalette($this->_getParam('idBase', 0));
				$this->view->json = $tp->getPaletteClics($this->_getParam('uti', 0), $this->_getParam('url', 0)
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
			//récupère les informations de la palette
			//print_r($this->getRequest()->getParams());
			if($this->_getParam('idBase', 0) && $this->_getParam('event', 0) && $this->_getParam('url', 0) && $this->_getParam('uti', 0) && $this->_getParam('sem', 0)){
				$tp = new Flux_Tweetpalette($this->_getParam('idBase', 0));
				$tp->saveTweetSem($this->_getParam('uti', 0), $this->_getParam('url', 0), $this->_getParam('event', 0), $this->_getParam('sem'));
				//$this->view->sem = $this->_getParam('sem');
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

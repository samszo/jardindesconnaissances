<?php

/**
 * GraphController
 * 
 * @author Samuel Szoniecky
 * @version 0.0
 */

require_once 'Zend/Controller/Action.php';

class GraphController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->view->title = "Graphiques disponibles";
	    $this->view->headTitle($this->view->title, 'PREPEND');
	    $this->view->stats = array("tags pour un utilisateur", "utilisateurs en relation");
	}

    public function bullesAction()
    {
	    $this->view->stats = "";
    	if($this->_getParam('tags')){
			$s = new Flux_Stats($this->_getParam('idBase', 0));
			$this->view->stats = $s->GetTagAssos($this->_getParam('tags'),$this->_getParam('racine', 0));	    
	    }
    }	
    
}

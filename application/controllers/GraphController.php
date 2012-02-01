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
	    
	    $request = $this->getRequest();
		$url = $request->getRequestUri();
		$arrUrl = explode("?",$url);
		
		$this->view->urlStats = "../stat/tagassos?".$arrUrl[1];	    
    }	
    
}

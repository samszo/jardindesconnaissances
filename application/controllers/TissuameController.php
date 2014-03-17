<?php

/**
 * TissuameController
 * 
 * @author Samuel Szoniecky
 * @version 0.0
 */

require_once 'Zend/Controller/Action.php';

class TissuameController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->view->title = "Graphiques 3D disponible";
	    $this->view->headTitle($this->view->title, 'PREPEND');
	}

    public function monadeAction()
    {
    }	

    public function fullereneAction()
    {
    }	
    
}

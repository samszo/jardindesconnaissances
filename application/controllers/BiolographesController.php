<?php

/**
 * BiolographesController
 * 
 * @author Samuel Szoniecky
 * @version 0.0
 */

require_once 'Zend/Controller/Action.php';

class BiolographesController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->view->title = "Visualisations disponibles";
	    $this->view->headTitle($this->view->title, 'PREPEND');
	}

    public function comparecatAction()
    {
    }	    

    public function keshifAction()
    {
    }	    

    public function editeurAction()
    {
		$this->view->connect =  $this->_getParam('connect', 0);
    		$this->view->idBase =  $this->_getParam('idBase', "flux_biolographes");
    }	    
    
    public function sauvecribleAction()
    {
    }	    

    
}

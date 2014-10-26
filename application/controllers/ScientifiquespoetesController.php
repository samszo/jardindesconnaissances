<?php

/**
 * ScientifiquespoetesController
 * 
 * @author Samuel Szoniecky
 * @version 0.0
 */

require_once 'Zend/Controller/Action.php';

class ScientifiquespoetesController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->view->title = "Visualisations disponibles";
	    $this->view->headTitle($this->view->title, 'PREPEND');
	}

    public function heatmapAction()
    {
    }	

    
}

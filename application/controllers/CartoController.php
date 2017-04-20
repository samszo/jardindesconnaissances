<?php

/**
 * CartoController
 * 
 * Pour la gestion des cartogrpahies
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
require_once 'Zend/Controller/Action.php';

class CartoController extends Zend_Controller_Action {

	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		
	}

    public function zoomifyAction()
    {
    }	

    public function iiifAction()
    {
	    	$config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
	    	$apikeys = $config->getOption('resources');

	    	$this->view->idBase = $this->_getParam('idBase',$apikeys['db']['params']['dbname']);
    		$this->view->idUti = $this->_getParam('idUti',0);
    	 
    }
    
    public function tempoAction()
    {
    	
    }

    public function mondeAction()
    {
    	 
    }
    
    public function hexagoneAction(){
    	
    }
}

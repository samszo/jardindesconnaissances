<?php

/**
 * Taghisto
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class TaghistoController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
	    $request = $this->getRequest();
		$idBase = "flux_sic";
		$s = new Flux_Site($idBase);		
		$this->view->idBase = $idBase;	    
		$this->view->urlStats = "stat/taghisto?idBase=".$idBase."&temps=Y";	    
	}
	
}

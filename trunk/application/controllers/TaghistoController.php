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
		$idBase = $this->_getParam('idBase', 0);
		$s = new Flux_Site($idBase);
		$this->view->idBase = $idBase;	    
		$tags = $this->_getParam('tags', 0);
		$pTags = "";
		if($tags){
			foreach ($tags as $t) {
				$pTags .= "&tags[]=".$t;
			}
		}
		$this->view->urlStats = "stat/taghisto?idBase=".$idBase."&temps=Y".$pTags."&q=".$this->_getParam('q', 0);	    
	}
	
}

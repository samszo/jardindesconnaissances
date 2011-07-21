<?php

/**
 * FluxController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class FluxController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->view->title = "Flux disponibles";
	    $this->view->headTitle($this->view->title, 'PREPEND');
	    $this->view->flux = array("tags pour un utilisateur", "utilisateurs en relation");
	}

    public function toustagsAction()
    {
	    if ($this->getRequest()->isPost()) {
	        $calculer = $this->getRequest()->getPost('calculer');
	    } else {
	        $dbTags = new Model_DbTable_Flux_Tag();
	        $this->view->tags = $dbTags->getAll();
	    }
    }	
    
    public function tagsAction()
    {
	    $dbTags = new Model_DbTable_Flux_UtiTag();
	    if($this->_getParam('exis', 0)){
		    $this->view->tags = $dbTags->findTagByUti($this->_getParam('exis', 0));	    	
	    }else{
		    $this->view->tags = $dbTags->findTagUti();	    	
	    }
    }	
    
}

<?php

class KeshifController extends Zend_Controller_Action
{

    public function indexAction()
    {
		$this->view->titre = $this->_getParam('titre', "Navigateur par facettes");
    }

    
}




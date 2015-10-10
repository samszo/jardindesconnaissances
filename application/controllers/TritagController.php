<?php

/**
 * TritagController
 * 
 * @author Samuel Szoniecky
 * @version 0.0
 */

require_once 'Zend/Controller/Action.php';

class TritagController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->view->title = "Tri Tag";
	    $this->view->headTitle($this->view->title, 'PREPEND');
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $ssUti = new Zend_Session_Namespace('uti');
		    $this->view->uti = $ssUti->uti;
		}else{
			
		}
	}
    
}

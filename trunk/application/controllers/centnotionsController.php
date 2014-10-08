<?php

/**
 * 100 notions
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class CentNotionsController extends Zend_Controller_Action {
	
	var $dbNom = "flux_100notions";
	var $idUti = 1;
	var $redir = '/auth/login?redir=centnotions&idBase=';
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');
		$this->view->idBase = $this->dbNom;
		$this->redir .= $this->dbNom;
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $ssUti = new Zend_Session_Namespace('uti');
		    $this->view->idUti = $ssUti->idUti;
		    $this->view->redir = $this->redir;
		    
		}else{
			$this->_redirect($this->redir);
		}
	}

	
}

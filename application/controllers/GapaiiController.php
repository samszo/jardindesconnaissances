<?php

/**
 * GAPAII
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class GapaiiController extends Zend_Controller_Action {
	
	var $dbNom = "flux_gapaii";
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		try {
			if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
			    // l'identité existe ; on la récupère
			    $this->view->identite = $auth->getIdentity();
			    $ssUti = new Zend_Session_Namespace('uti');
			    $this->view->idUti = $ssUti->idUti;
			    $this->view->idBase = $this->dbNom;
			}else{
			    $this->_redirect('/auth/login?redir=gapai&idBase='.$this->dbNom);
			}
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}
	
}

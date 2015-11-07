<?php

/**
 * ContactController
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class ContactsController extends Zend_Controller_Action {
	
	var $dbNom = "flux_etu";
		
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {

		$this->view->dbNom = $this->dbNom;
		$ssGoogle = new Zend_Session_Namespace('google');
		
		if(!$ssGoogle->token){
			$this->_redirect("../auth/google?redir=contacts&scope=Contacts");
		}
		
		$client = $ssGoogle->client;			
		$this->view->token = $ssGoogle->token; 				
		
		/*
		$gCtct = new Flux_Gcontacts($ssPlan->token);
		//vérifie si le calendrier est géré
		$arrCal = $gCtct->getListeContacts();
		$this->view->contacts = $arrCal; 				
		*/
		
	}
	
}

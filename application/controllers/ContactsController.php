<?php

/**
 * ContactController
 * 
 * Pour gérer les contacts
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
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

<?php

/**
 * PlanningController
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class PlanningController extends Zend_Controller_Action {
	
	var $dbNom = "flux_etu";
 	var $client_id = '77330937798-6h0j3fcu56mi3vt54mal0j5sg37ijkug.apps.googleusercontent.com';
 	var $client_secret = 'FlimbY2obkI3p6zUYtsMyfdx';
		
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {

		$this->view->dbNom = $this->dbNom;
		$ssPlan = new Zend_Session_Namespace('planning');
		
		if(!$ssPlan->client){
			$client = new Google_Client();
			$client->setClientId($this->client_id);
			$client->setClientSecret($this->client_secret);
			$client->setRedirectUri('http://' .$this->getRequest()->getHttpHost().$this->view->baseUrl()."/planning");
			$client->addScope("https://www.googleapis.com/auth/calendar");
			$ssPlan->client = $client;
		}else
			$client = $ssPlan->client;			
		
		/************************************************
		  If we're logging out we just need to clear our
		  local access token in this case
		************************************************/
		if ($this->_getParam('logout')) {
		  $ssPlan->token = false;
		}
		
		/************************************************
		  If we have a code back from the OAuth 2.0 flow,
		  we need to exchange that with the authenticate()
		  function. We store the resultant access token
		  bundle in the session, and redirect to ourself.
		 ************************************************/
		if ($this->_getParam('code')) {
		  $client->authenticate($this->_getParam('code'));
		  $ssPlan->token = $client->getAccessToken();
		  $this->_redirect('/planning');
		}
		
		if (isset($_SESSION['upload_token']) && $_SESSION['upload_token']) {
		  $client->setAccessToken($_SESSION['upload_token']);
		  if ($client->isAccessTokenExpired()) {
		    unset($_SESSION['upload_token']);
		  }
		} else {
		  $authUrl = $client->createAuthUrl();
		}
				
		/************************************************
		  If we have an access token, we can make
		  requests, else we generate la liste des plannings.
		 ************************************************/
		if ($ssPlan->token) {
		  	//echo "token=".$ssPlan->token;
		  	$this->verifExpireToken($ssPlan);
			/*
		  	$client->setAccessToken($ssPlan->token);
			if ($client->isAccessTokenExpired()) {
			    $ssPlan->token=false;
			    $this->_redirect('/planning');
			}else{
				$gCal = new Flux_Gcalendar($ssPlan->token);
				//vérifie si le calendrier est géré
				$arrCal = $gCal->getListeCalendar();
				foreach ($arrCal as $cal) {
					if($cal["access"]!="reader")$planning[]=$cal;
				}
				$this->view->plannings = $planning; 				
			}
			*/		  	
			$gCal = new Flux_Gcalendar($ssPlan->token);
			//vérifie si le calendrier est géré
			$arrCal = $gCal->getListeCalendar();
			foreach ($arrCal as $cal) {
				if($cal["access"]!="reader")$planning[]=$cal;
			}
			$this->view->plannings = $planning; 				

		}else{
		  $this->view->authUrl = $client->createAuthUrl();						
		}			
		
	}

	/**
	 * action permettant de recupérer les données d'un calendrier
	 */
	public function eventsAction() {
		$ssPlan = new Zend_Session_Namespace('planning');			
		if ($this->_getParam('idCal') && $ssPlan->token) {
			$gCal = new Flux_Gcalendar($ssPlan->token);
			$optParams = array();
			if($this->_getParam('timeMax')) $optParams["timeMax"]=$this->_getParam('timeMax');
			if($this->_getParam('timeMin')) $optParams["timeMin"]=$this->_getParam('timeMin');
			$this->view->data = $gCal->getListeEvents($this->_getParam('idCal'), $optParams);			
			
		}
		
	}

	private function verifExpireToken($ssPlan){
		$ssPlan->client->setAccessToken($ssPlan->token);
		if ($ssPlan->client->isAccessTokenExpired()) {
		    $ssPlan->token=false;
		    $this->_redirect('/planning');
		}
	}
	
}

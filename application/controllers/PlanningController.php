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
		
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {

		$this->view->dbNom = $this->dbNom;
		$ssPlan = new Zend_Session_Namespace('google');
		
		if(!$ssPlan->client){
			$client = new Google_Client();
			$client->setClientId(KEY_GOOGLE_CLIENT_ID);
			$client->setClientSecret(KEY_GOOGLE_CLIENT_SECRET);
			$client->setRedirectUri('http://' .$this->getRequest()->getHttpHost().$this->view->baseUrl()."/planning");
			$client->addScope(array("https://www.googleapis.com/auth/calendar","https://www.googleapis.com/auth/drive"));
			$ssPlan->client = $client;
		}else
			$client = $ssPlan->client;			
		
		/************************************************
		  If we're logging out we just need to clear our
		  local access token in this case
		************************************************/
		if ($this->_getParam('logout')) {
		  	$ssPlan->token = false;
			unset($_SESSION['upload_token']);		  	
		  	Zend_Session::namespaceUnset('google');		  	
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
		$ssPlan = new Zend_Session_Namespace('google');			
		if ($this->_getParam('idCal') && $ssPlan->token) {
			$gCal = new Flux_Gcalendar($ssPlan->token);
			$optParams = array();
			if($this->_getParam('timeMax')) $optParams["timeMax"]=$this->_getParam('timeMax');
			if($this->_getParam('timeMin')) $optParams["timeMin"]=$this->_getParam('timeMin');
			$this->view->data = $gCal->getListeEvents($this->_getParam('idCal'), $optParams);						
		}
		
	}

	/**
	 * action permettant d'afficher le tableau de bord
	 */
	public function uiAction() {

		$ssPlan = new Zend_Session_Namespace('google');
		$this->view->redirect = '/auth/google?scopes[]=Calendar&scopes[]=Drive&redir='.urlencode('/planning/ui');		
		if(!$ssPlan->client){
			$this->_redirect($this->view->redirect);			
		}else{
			$this->view->plannings = $this->getUserCalendar($ssPlan);
		}
		
	}	
	
	private function verifExpireToken($ssPlan){
		$ssPlan->client->setAccessToken($ssPlan->token);
		if ($ssPlan->client->isAccessTokenExpired()) {
		    $ssPlan->token=false;
		    $this->_redirect('/planning');
		}
	}
	
	private function getUserCalendar($ssPlan){
	  	$this->verifExpireToken($ssPlan);
	  	$s = new Flux_Site();
		$gCal = new Flux_Gcalendar($ssPlan->token);
		//vérifie si le calendrier est géré
		$arrCal = $gCal->getListeCalendar();
		foreach ($arrCal as $cal) {
			$p = array("text"=>$cal["summary"],"id"=>$cal["id"]);
			if($cal["access"]!="reader")$planning[$cal["summary"]]=$p;
		}
		//tri le résultat
		ksort($planning);
		foreach ($planning as $p) {
		    $sPlan[]=$p;
		}				
		return $sPlan;						
	}
}

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
			switch ($this->_getParam('action')) {
				case 'insert':
					$gCal->insertEventPresence($this->_getParam('idCal'), $this->_getParam('params'));
				break;				
				default:
					if($this->_getParam('timeMax')) $optParams["timeMax"]=$this->_getParam('timeMax');
					if($this->_getParam('timeMin')) $optParams["timeMin"]=$this->_getParam('timeMin');
					$this->view->data = $gCal->getListeEvents($this->_getParam('idCal'), $optParams);
				break;
			}
		}
		
	}

	/**
	 * action permettant d'afficher le tableau de bord
	 */
	public function uiAction() {

		$ssPlan = new Zend_Session_Namespace('google');
		$this->view->redirect = '/auth/google?scopes[]=Calendar&scopes[]=Profil&scopes[]=Drive&redir='.urlencode('/planning/ui');	
		echo "ssPlan->client->isAccessTokenExpired()=".$ssPlan->client->isAccessTokenExpired();
		if(!$ssPlan->token || $ssPlan->client->isAccessTokenExpired()){
			$this->_redirect($this->view->redirect);			
		}else{
			$plus = new Google_Service_Oauth2($ssPlan->client);
			$userinfo = $plus->userinfo->get();
			$this->view->UserInfos = array("link"=>$userinfo->getLink()
					,"picture"=>$userinfo->getPicture()
					,"name"=>$userinfo->getName()
					,"mail"=>$userinfo->getVerifiedEmail()
					,"id"=>$userinfo->getId()
			);
			$this->view->plannings = $this->getUserCalendar($ssPlan,"diplôme P8");
		}
		/*
			$accessToken = json_decode($ssPlan->client->getAccessToken());
			$this->view->AccessToken = $accessToken->access_token;
		*/
		
	}	

	/**
	 * action permettant d'afficher les compétences
	 */
	public function dashboardAction() {
		
		$this->view->docId = $this->_getParam('docId','1ayDcSA03grBSsh7Wcu65iVNPIUUWFoEGdL_UPa32RXI');
		$this->view->diplome = $this->_getParam('diplome','CDNL');
		$this->view->debut = $this->_getParam('debut',2016);
		$this->view->fin = $this->_getParam('fin',2017);
		$this->view->gid = $this->_getParam('gid','76288419');
		
	}
	
	private function verifExpireToken($ssPlan){
		$ssPlan->client->setAccessToken($ssPlan->token);
		if ($ssPlan->client->isAccessTokenExpired()) {
		    $ssPlan->token=false;
		    $this->_redirect('/planning');
		}
	}
	
	private function getUserCalendar($ssPlan, $type=""){
	  	$this->verifExpireToken($ssPlan);
	  	$s = new Flux_Site();
		$gCal = new Flux_Gcalendar($ssPlan->token);
		//vérifie si le calendrier est géré
		$arrCal = $gCal->getListeCalendar();
		foreach ($arrCal as $cal) {
			//print_r($cal);
			$p = array("text"=>$cal["summary"],"id"=>$cal["id"]);
			if($type!=""){
				if($type==$cal["description"])$planning[$cal["summary"]]=$p;
			}elseif (($cal["access"]!="reader")) $planning[$cal["summary"]]=$p;
		}
		//tri le résultat
		ksort($planning);
		foreach ($planning as $p) {
		    $sPlan[]=$p;
		}				
		return $sPlan;						
	}
}

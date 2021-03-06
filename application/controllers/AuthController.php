<?php
/**
 * AuthController
 *
 * Pour gérér l'authentification des utilisateurs
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class AuthController extends Zend_Controller_Action
{
	var $idBase = false;
	var $redir = "";
    public function loginAction()
    {    	
		$this->view->erreur = false;    		
		$this->view->code = 1;
		
		$ssExi = new Zend_Session_Namespace('uti');
		$this->view->idBase = $ssExi->dbNom;
		if($this->_getParam('idBase',0)){
		    $ssExi->dbNom = $this->_getParam('idBase');	
		    $this->view->idBase = $ssExi->dbNom;
		}		
		$this->view->redir = $ssExi->redir;
		if($this->_getParam('redir', 0)){
			$ssExi->redir='/'.$this->_getParam('redir', 0);
			$this->view->redir = $ssExi->redir;
		}
		
		//instanciation du site pour les bases
		$s = new Flux_Site($ssExi->dbNom);
		
    	// Obtention d'une référence de l'instance du Singleton de Zend_Auth
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();

		if($this->_getParam('ajax', 0)){
			$this->view->ajax = true;
			$adapter = new Zend_Auth_Adapter_DbTable(
				$s->db,
				'flux_uti',
				'login',
				'mdp'
				);		                
			$adapter->setIdentity($this->_getParam('login'));
			$adapter->setCredential($this->_getParam('mdp'));
			$login = $this->_getParam('login');
			// Tentative d'authentification et stockage du résultat
			if($login)$result = $auth->authenticate($adapter);
		} elseif ($this->_getParam('login')) {
			$adapter = new Zend_Auth_Adapter_DbTable(
				$s->db,
				'flux_uti',
				'login',
				'mdp'
				);		                
			$login = $this->_getParam('login');
			$adapter->setIdentity($login);
			$adapter->setCredential($this->_getParam('mdp'));
			// Tentative d'authentification et stockage du résultat
			$result = $auth->authenticate($adapter);			
			$this->view->erreur = "login calculé";			
		}else{
			return;
		}     		
    		
    	if ($result->isValid()) {		            	
			//met en sessions les informations de l'existence
			$dbUti = new Model_DbTable_Flux_Uti($s->db);
			$rs = $dbUti->findByLogin($login);
			$ssExi->uti = $rs;
			$ssExi->idUti = $rs["uti_id"];		            	
	        	
			if($this->view->ajax){
				$this->view->rs = $ssExi->uti;
	        }else{
				$this->redirect($ssExi->redir);
				return;
	        }
	    }else{
			$this->view->code = $result->getCode();
			switch ($result->getCode()) {
				case 0:
					$this->view->erreur = "Problème d'identification. Veuillez contacter le webmaster.";		            	
					break;		            		
				case -1:
					$this->view->erreur = "Le login n'a pas été trouvé. Veillez vous inscrire.";		            	
					break;		            		
				case -2:
					$this->view->erreur = "Le login est ambigue.";		            	
					break;		            		
				case -3:
					$this->view->erreur = "Le login et/ou le mot de passe ne sont pas bons.";		            	
					break;		            		
			}
	   	}
    }

    public function connexionAction()
    {
		$this->clearConnexion();		
        $this->view->erreur = false;
        $this->view->code = 1;
        
        $ssExi = new Zend_Session_Namespace('uti');
        $this->view->idBase = $ssExi->dbNom;
        if($this->_getParam('idBase',0)){
            $ssExi->dbNom = $this->_getParam('idBase');
            $this->view->idBase = $ssExi->dbNom;
        }
        $this->view->redir = $ssExi->redir;
        if($this->_getParam('redir', 0)){
            $ssExi->redir='/'.$this->_getParam('redir', 0);
            $this->view->redir = $ssExi->redir;
        }
        
        //instanciation du site pour les bases
        $s = new Flux_Site($ssExi->dbNom);
        
        
        // Obtention d'une référence de l'instance du Singleton de Zend_Auth
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
        
        if ($this->_getParam('login')) {
            $adapter = new Zend_Auth_Adapter_DbTable(
                $s->db,
                'flux_uti',
                'login',
                'mdp'
                );
            $login = $this->_getParam('login');
            $adapter->setIdentity($login);
            $adapter->setCredential($this->_getParam('mdp'));
            // Tentative d'authentification et stockage du résultat
            $result = $auth->authenticate($adapter);
            $this->view->erreur = "login calculé";
        }else{
            return;
        }
        
        if ($result->isValid()) {
            //met en sessions les informations de l'existence
            $dbUti = new Model_DbTable_Flux_Uti($s->db);
            $rs = $dbUti->findByLogin($login);
            $ssExi->uti = $rs;
            $ssExi->idUti = $rs["uti_id"];
            $this->redirect($ssExi->redir);
        }else{
            $this->view->code = $result->getCode();
            switch ($result->getCode()) {
                case 0:
                    $this->view->erreur = "Problème d'identification. Veuillez contacter le webmaster.";
                    break;
                case -1:
                    $this->view->erreur = "Le login n'a pas été trouvé. Veillez vous inscrire.";
                    break;
                case -2:
                    $this->view->erreur = "Le login est ambigue.";
                    break;
                case -3:
                    $this->view->erreur = "Le login et/ou le mot de passe ne sont pas bons.";
                    break;
            }
        }
    }
    
    public function inscriptionAction()
    {
        //paramètres par défaut
        $this->view->message = "";
        $this->view->redir = "";
        $this->view->rs = array();
        $this->view->ajax=$this->_getParam('ajax', false);
        if($this->_getParam('ajax', 0)){
			$formData['ip_inscription'] = $_SERVER['REMOTE_ADDR'];
			$formData['date_inscription'] = date('Y-m-d H:i:s');
			$formData['login'] = $this->_getParam('login');
			$formData['mdp'] = $this->_getParam('mdp');
			$formData['email'] = $this->_getParam('email');
			$ssExi = new Zend_Session_Namespace('uti');
			if($this->_getParam('idBase'))$ssExi->dbNom = $this->_getParam('idBase');				
			//echo "ssExi->dbNom = ".$ssExi->dbNom;
		    $s = new Flux_Site($ssExi->dbNom);
			$dbUti = new Model_DbTable_Flux_Uti($s->db);
			//vérifie la présence du même login
			 $rsLogin = $dbUti->findByLogin($formData['login']);
			if(count($rsLogin)>0){
			    $this->view->message = "Le login existe déjà. Merci de le changer.";
			    $this->view->erreur = "login existant";
			    $this->view->code = 1;
			}else{
			    $this->view->code = '';
			    $this->view->rs = $dbUti->ajouter($formData, true, true);
        			$this->view->message = "Votre compte est créer.";
    		  	   $this->view->redir = $ssExi->redir;			
    		  	   $this->view->ajax=true;
        		   $ssExi->uti = $this->view->rs;
    			   $ssExi->idUti = $this->view->rs["uti_id"];
			}
    		}else{    		
	    		$this->view->form = $form = new Form_Auth_Inscription();
	   	    	if($this->getRequest()->isPost()){
					$formData = $this->getRequest()->getPost();
					$this->view->ajax = isset($formData['ajax']);
					if(isset($formData['idBase']))$ssExi->dbNom=$formData['idBase'];						
					if($form->isValid($formData)){
						//supprime les données superflux
						unset($formData['envoyer']);					
						unset($formData['ajax']);					
						unset($formData['idBase']);					
						
						$formData['ip_inscription'] = $_SERVER['REMOTE_ADDR'];
						$formData['date_inscription'] = date('Y-m-d H:i:s');
						//print_r($formData);
						$ssExi = new Zend_Session_Namespace('uti');
						//echo "ssExi->dbNom = ".$ssExi->dbNom;
						if(isset($ssExi->dbNom)){
							$site = new Flux_Site();
							$db = $site->getDb($ssExi->dbNom);						
							$dbUti = new Model_DbTable_Flux_Uti($db);
						}else $dbUti = new Model_DbTable_Flux_Uti();					
						$idUti = $dbUti->ajouter($formData);
						if($this->view->ajax)
							$this->view->idUti = $idUti;
						else
							$this->redirect('auth/login');
					}else{
						if($this->view->ajax)
							$this->view->idUti = -1;
						else
							$form->populate($formData);
					}
					
			}    			        
    		}

    }
    
    public function deconnexionAction()
    {
		$redir = $this->_getParam('redir');
		$this->clearConnexion();
	    $this->redirect($redir."?idBase=".$this->idBase);            	
    }
    
    function clearConnexion(){
		$ssExi = new Zend_Session_Namespace('uti'); 		   	
		$this->redir = $ssExi->redir;
		$this->idBase = $ssExi->dbNom;
		Zend_Session::namespaceUnset('uti');
		Zend_Session::namespaceUnset('google');
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
    }

	/**
	 * Controller pour une authentification google
	 * 
	 * les scopes valides
	 * https://developers.google.com/gdata/faq#AuthScopes
	 */
    var $googleScopes = array(
		"Analytics"=>"https://www.google.com/analytics/feeds/",
		"Sites"=>"https://sites.google.com/feeds/",
		"Blogger"=>"http://www.blogger.com/feeds/",
		"Book"=>"http://www.google.com/books/feeds/",
		//"Calendar"=>"https://www.google.com/calendar/feeds/",
		"Calendar"=>"https://www.googleapis.com/auth/calendar",
		"Contacts"=>"https://www.google.com/m8/feeds/",
		"Shopping"=>"https://www.googleapis.com/auth/structuredcontent",
		"Documents"=>"https://docs.google.com/feeds/",
		"Finance"=>"http://finance.google.com/finance/feeds/",
		"Gmail"=>"https://mail.google.com/mail/feed/atom/",
		"Health"=>"https://www.google.com/health/feeds/",
		"Maps"=>"http://maps.google.com/maps/feeds/",
		"Picasa"=>"http://picasaweb.google.com/data/",
		"Portable"=>"http://www-opensocial.googleusercontent.com/api/people",
		"Sidewiki"=>"http://www.google.com/sidewiki/feeds/",
		"Spreadsheets"=>"https://spreadsheets.google.com/feeds/",
		"Webmaster"=>"http://www.google.com/webmasters/tools/feeds/",
		"YouTube"=>"http://gdata.youtube.com",
		"Drive"=>"https://www.googleapis.com/auth/drive",
		"Profil"=>"https://www.googleapis.com/auth/userinfo.profile"	
    );
    
    
    public function googleAction() {
        
        $ssGoogle = new Zend_Session_Namespace('google');
        //print_r($ssGoogle);
        //echo $ssGoogle->redir;
        $scopes = $this->_getParam('scopes');
        if(!$ssGoogle->client){
            $client = new Google_Client();
            $client->setClientId(KEY_GOOGLE_CLIENT_ID);
            $client->setClientSecret(KEY_GOOGLE_CLIENT_SECRET);
			if(empty($_SERVER['HTTPS']))
				$client->setRedirectUri('http://' .$this->getRequest()->getHttpHost().$this->view->baseUrl()."/auth/google");
			else
				$client->setRedirectUri('https://' .$this->getRequest()->getHttpHost().$this->view->baseUrl()."/auth/google");
            $client->addScope('https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/plus.login');            
            foreach ($scopes as $s) {
                $client->addScope($this->googleScopes[$s]);
            }
            $ssGoogle->client = $client;
            $ssGoogle->redir = $this->_getParam('redir',"/flux/google");
            //enregistre les information dans la base
            $ssGoogle->dbNom = $this->_getParam('idBase');            
        }else
            $client = $ssGoogle->client;
            
            /************************************************
             If we're logging out we just need to clear our
             local access token in this case
             pour zend suppression de la session
             ************************************************/
            if ($this->_getParam('logout')) {
                unset($_SESSION['upload_token']);
                $redir = 'http://' .$this->getRequest()->getHttpHost().$this->view->baseUrl().urldecode($ssGoogle->redir);
                Zend_Session::namespaceUnset('google');
                $this->redirect($redir);
            }else{
                
                /************************************************
                 If we have a code back from the OAuth 2.0 flow,
                 we need to exchange that with the authenticate()
                 function. We store the resultant access token
                 bundle in the session, and redirect to ourself.
                 ************************************************/
                if ($this->_getParam('code')) {
                    $client->authenticate($this->_getParam('code'));
                    $ssGoogle->token = $client->getAccessToken();
                    $this->redirect('/auth/google');
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
                if ($ssGoogle->token) {
                    //echo "token=".$ssPlan->token;
                    $this->verifExpireToken($ssGoogle);
                    $oauth2 = new Google_Service_Oauth2($ssGoogle->client);
                    $userInfo = $oauth2->userinfo->get();
                    //print_r($userInfo);
                    $s = new Flux_Site($ssGoogle->dbNom);
                    $json = json_encode($userInfo);
                    $dbUti = new Model_DbTable_Flux_Uti($s->db);
					$rs = $dbUti->ajouter(array("login"=>$userInfo['givenName'].' '.$userInfo['familyName'],"flux"=>"GOOGLE","data"=>$json,'email'=>$userInfo['email']),true,true);
					$ssExi = new Zend_Session_Namespace('uti');
					$ssExi->uti = $rs;
					$ssExi->idUti = $rs["uti_id"];		            								
                    //redirige l'utilisateur
                   $this->redirect($ssGoogle->redir."?idUti=".$$rs['uti_id']."&idBase=".$ssGoogle->dbNom);
                    //$this->redirect('http://' .$this->getRequest()->getHttpHost().$this->view->baseUrl().urldecode($ssGoogle->redir));
                }else{
                    $this->view->authUrl = $client->createAuthUrl();
                }
            }
            
            //print_r($ssGoogle);
            //echo $ssGoogle->redir;
            
    }

	private function verifExpireToken($ss){
		$ss->client->setAccessToken($ss->token);
		if ($ss->client->isAccessTokenExpired()) {
		    $ss->token=false;
		    $this->redirect('/auth/google');
		}
	}    

	public function googleuserinfoAction(){
		$ssPlan = new Zend_Session_Namespace('google');
		if(!$ssPlan->token || $ssPlan->client->isAccessTokenExpired()){
			$this->view->UserInfos = array();			
		}else{
			$plus = new Google_Service_Oauth2($ssPlan->client);
			$userinfo = $plus->userinfo->get();
			$this->view->UserInfos = array("link"=>$userinfo->getLink()
					,"picture"=>$userinfo->getPicture()
					,"name"=>$userinfo->getName()
					,"mail"=>$userinfo->getVerifiedEmail()
					,"id"=>$userinfo->getId()
			);
		}
	}    

    public function finsessionAction()
    {
		
    }

    public function casAction()
    {
    	/**
    	 * The purpose of this central config file is configuring all examples
    	 * in one place with minimal work for your working environment
    	 * Just configure all the items in this config according to your environment
    	 * and rename the file to config.php
    	 *
    	 * PHP Version 5
    	 *
    	 * @file     config.php
    	 * @category Authentication
    	 * @package  PhpCAS
    	 * @author   Joachim Fritschi <jfritschi@freenet.de>
    	 * @author   Adam Franco <afranco@middlebury.edu>
    	 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
    	 * @link     https://wiki.jasig.org/display/CASC/phpCAS
    	 */    	 
        
        $this->view->user = '';
        
            	///////////////////////////////////////
            	// Basic Config of the phpCAS client //
            	///////////////////////////////////////
            	
            	// Full Hostname of your CAS Server
            	$cas_host = 'cas.univ-paris8.fr';
            	
            	// Context of the CAS Server
            	$cas_context = '/cas';
            	
            	// Port of your CAS server. Normally for a https server it's 443
            	$cas_port = 443;
            	
            	// Path to the ca chain that issued the cas server certificate
            	$cas_server_ca_cert_path = '/path/to/cachain.pem';
            	    	
            	// Client config for cookie hardening
            	$client_domain = '127.0.0.1';
            	$client_path = 'phpcas';
            	$client_secure = true;
            	$client_httpOnly = true;
            	$client_lifetime = 0;
            	
            	
            	// Database config for PGT Storage
            	$db = 'pgsql:host=localhost;dbname=phpcas';
            	//$db = 'mysql:host=localhost;dbname=phpcas';
            	$db_user = 'phpcasuser';
            	$db_password = 'mysupersecretpass';
            	$db_table = 'phpcastabel';
            	$driver_options = '';
            	
            	///////////////////////////////////////////
            	// End Configuration -- Don't edit below //
            	///////////////////////////////////////////
            	
            	// Generating the URLS for the local cas example services for proxy testing
            	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            		$curbase = 'https://' . $_SERVER['SERVER_NAME'];
            	} else {
            		$curbase = 'http://' . $_SERVER['SERVER_NAME'];
            	}
            	if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
            		$curbase .= ':' . $_SERVER['SERVER_PORT'];
            	}
            	
            	$curdir = dirname($_SERVER['REQUEST_URI']) . "/";
            	
            	//CAS client nodes for rebroadcasting pgtIou/pgtId and logoutRequest
            	$rebroadcast_node_1 = 'https://cas.univ-paris8.fr/cas';
            	$rebroadcast_node_2 = 'https://cas.univ-paris8.fr/cas';
            	
            	// access to a single service
            	$serviceUrl = $curbase . $curdir . 'auth/cas';
            	// access to a second service
            	$serviceUrl2 = $curbase . $curdir . 'auth/cas';
            	 
            	
            	$pgtBase = preg_quote(preg_replace('/^http:/', 'https:', $curbase . $curdir), '/');
            	$pgtUrlRegexp = '/^' . $pgtBase . '.*$/';
            	
            	$cas_url = 'https://' . $cas_host;
            	if ($cas_port != '443') {
            		$cas_url = $cas_url . ':' . $cas_port;
            	}
            	$cas_url = $cas_url . $cas_context;
            	
            	// Set the session-name to be unique to the current script so that the client script
            	// doesn't share its session with a proxied script.
            	// This is just useful when running the example code, but not normally.
            	session_name(
            			'session_for:'
            			. preg_replace('/[^a-z0-9-]/i', '_', basename($_SERVER['SCRIPT_NAME']))
            			);
            	    	 
            	// Set an UTF-8 encoding header for internation characters (User attributes)
            	header('Content-Type: text/html; charset=utf-8');
            	
            	// Enable debugging
            	phpCAS::setDebug();
            	// Enable verbose error messages. Disable in production!
            	phpCAS::setVerbose(true);
            	
            	// Initialize phpCAS
            	phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
            	
            	// For production use set the CA certificate that is the issuer of the cert
            	// on the CAS server and uncomment the line below
            	// phpCAS::setCasServerCACert($cas_server_ca_cert_path);
            	
            	// For quick testing you can disable SSL validation of the CAS server.
            	// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
            	// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
            	phpCAS::setNoCasServerValidation();
            	
            	// force CAS authentication
            	phpCAS::forceAuthentication();
            	
            	// at this step, the user has been authenticated by the CAS server
            	// and the user's login name can be read with phpCAS::getUser().
            	
            	// logout if desired
            	if (isset($_REQUEST['logout'])) {
            		phpCAS::logout();
            	}    	    	
        	$this->view->user = phpCAS::getUser();	

        	
        	//enregistre les informations dans la base
        	$dbNom = $this->_getParam('idBase');
        	$s = new Flux_Site($dbNom);	        	
        	$dbUti = new Model_DbTable_Flux_Uti($s->db);
        	$uti = $dbUti->ajouter(array("login"=>$this->view->user,"flux"=>"CAS"),true,true);
        	$this->view->idUti =$uti['uti_id'];
        	
        //redirige l'utilisteur si besoin
        $redir = $this->_getParam('redir');
        if($redir)
            $this->redirect($redir."?idUti=".$uti['uti_id']."&idBase=".$dbNom);	
	
    }
}
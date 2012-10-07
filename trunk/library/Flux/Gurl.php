<?php
/**
 * Classe qui gère les flux venant du réducteur d'URL de Google
 * https://developers.google.com/url-shortener/v1/getting_started
 * 
 * merci à http://inchoo.net/tools-frameworks/zendframework-example-on-using-google-url-shortener-service/
 * 
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Gurl extends Flux_Site{

	var $client;
	var $token;
 	
    /**
     * Construction du gestionnaire de flux.
     *
     * @param string $login
     * @param string $pwd
     * @param string $idBase
     *
     * 
     */
	public function __construct($login=null, $pwd=null, $idBase=false)
    {
    	parent::__construct($idBase);
    	
    	$this->login = $login;
    	$this->pwd = $pwd;
    	$this->key = KEY_GOOGLE_URL;
    	if($login)$this->getUser(array("login"=>$this->login));
    	
    }
	
     /**
     * récupère l'authentification du service google
     *
     */
    public function getToken(){
    	
		$this->client = new Zend_Http_Client();
		 
		$this->client->setUri('https://www.google.com/accounts/ClientLogin');
		$this->client->setMethod(Zend_Http_Client::POST);
		$this->client->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'application/x-www-form-urlencoded');
		//$this->client->setParameterPost('accountType', 'GOOGLE');
		$this->client->setParameterPost('Email', $this->login);
		$this->client->setParameterPost('Passwd', $this->pwd);
		$this->client->setParameterPost('service', 'urlshortener');
		$this->client->setParameterPost('source', 'samso-jdc-0.2');
		 
		$responseLogin = $this->client->request();
		$responseLoginBody = $responseLogin->getBody();
		 
		$loginTokens = explode("\n", $responseLoginBody);
		$this->token = '';
		 
		foreach ($loginTokens as $r) {
		    $_r = explode("=", $r);
		    if (count($_r) > 1) {
		        if ($_r[0] == 'Auth') {
		            $this->token = $_r[1];
		        }
		    }
		}
		if (empty($this->token)) {
			throw new Exception("Connexion impossible : vérifier votre login et/ou votre ot de passe");
		}
		
    }
    
    /**
     * Récupère l'historique des urls d'un utilisateur.
     *
     * @param string $startToken
     *
     * @return string 
     */
    public function getUserHistory($startToken=""){

    	if(!$this->token) $this->getToken();
    	$responseHistoryBody = "";
	    
    	$clientHistory = new Zend_Http_Client();
	 	/**référence :
	 	 * https://developers.google.com/url-shortener/v1/url/list
	 	 */
	    $clientHistory->setUri('https://www.googleapis.com/urlshortener/v1/url/history');
	    $clientHistory->setMethod(Zend_Http_Client::GET);
	    $clientHistory->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'application/json');
	    $clientHistory->setHeaders('Authorization', 'GoogleLogin auth='.$this->token);
	    $clientHistory->setParameterGet('key', $this->key);
	    if($startToken)$clientHistory->setParameterGet('start-token', $startToken);
	 
	    $responseHistory = $clientHistory->request();
	    $responseHistoryBody = $responseHistory->getBody();

	    return $responseHistoryBody;    
    }	
    
    /**
     * Récupère les données analytics d'une url
     *
     * @param string $url
     *
     * @return string 
     */
    public function getUrlAnalytics($url){

    	$client = new Zend_Http_Client();
	 	/**référence :
	 	 * https://developers.google.com/url-shortener/v1/getting_started#url_analytics
	 	 */
	    $client->setUri('https://www.googleapis.com/urlshortener/v1/url');
	    $client->setMethod(Zend_Http_Client::GET);
	    $client->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'application/json');
	    $client->setHeaders('Authorization', 'GoogleLogin auth='.$this->token);
	    $client->setParameterGet('shortUrl', $url);
	    $client->setParameterGet('projection', "FULL");
	    
	    $response = $client->request();
	    $responseBody = $response->getBody();

	    return $responseBody;    
    }	    

    /**
     * Enregistre l'historique des urls d'un utilisateur.
     *
     * @param string $startToken
     * @param int $i
     * @param boolean $new
     *
     * @return string 
     */
    public function saveUserHistory($startToken="", $i=0, $new=false){

    	if(!$this->token) $this->getToken();

		//initialise les gestionnaires de base de données
    	if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		if(!$this->dbUU)$this->dbUU = new Model_DbTable_Flux_UtiUti($this->db);
    	    	
    	//récupère la première page de l'historique
		$json = $this->getUserHistory($startToken);
		//http://goo.gl/j3gFZ    	
    	$uh = json_decode($json);
    	$startToken = $uh->nextPageToken;
    	$nbItem = $uh->totalItems;
    	foreach ($uh->items as $url) {
    		
    		if($new){
    			//vérifie que l'url n'est pas déjà enregsitrée
    			$idD = $this->dbD->findByUrl($url->id);
    			if(count($idD)>0){
    				echo $i." - ".$idD." déjà enregistré : ".$url->longUrl."<br/>";
    				continue;	
    			}
    		}

    		//récupère les données analytics de l'url
    		$analytics = $this->getUrlAnalytics($url->id);
    		$ua = json_decode($analytics);
    		
    		echo $i." - ".$url->longUrl."<br/>";
    		
    		//récupère les données de l'url
    		try {
	    		$client = new Zend_Http_Client($url->longUrl);    		
		    	$response = $client->request();
				$heads = $response->getHeaders(); 
		    	//vérifie le type de document
				if(strrpos($heads['Content-type'],"html")){
			    	$html = $response->getBody();			
					//récupère le titre de la page
					$dom = new Zend_Dom_Query($html);
			    	foreach ($dom->query('/html/head/title') as $title)
					{
					   $titre =  $title->textContent;
					}
				}else{
					$html = "";
					$titre = "";				
				}    			
    		} catch (Exception $e) {
				$html = "";
				$titre = "";				
    		}
			//ajouter un document correspondant à l'url de base
			$idDT = $this->dbD->ajouter(array("url"=>$url->longUrl, "titre"=>$titre, "tronc"=>0, "note"=>$html, "type"=>$heads['Content-type']));
			//ajoute un lien entre l'utilisateur et le lien
			$this->dbUD->ajouter(array("uti_id"=>$this->user, "doc_id"=>$idDT, "poids"=>1));										    
			
			//ajouter un document correspondant au lien
			$idD = $this->dbD->ajouter(array("url"=>$url->id, "tronc"=>$idDT, "pubDate"=>$url->created, "note"=>$analytics, "type"=>39));		
			    		
    		//calcule la copie d'écran
    		try {
    			if($html!="")$this->getUrlBodyContent("http://localhost/exemples/php/webthumb/webthumb.php",array("url"=>$url->longUrl,"folder"=>ROOT_PATH."/public/img/screen/"),false);		
			}catch (Zend_Exception $e) {
				echo "Récupère exception: " . get_class($e) . "\n";
			    echo "Message: " . $e->getMessage() . "\n";
			}
				
			$img = WEB_ROOT."/public/img/screen/".md5($url->longUrl).".png";
			//ajouter un document correspondant au lien
			$idD = $this->dbD->ajouter(array("url"=>$img, "tronc"=>$idDT, "type"=>2));		
    		
			
    		$i++;
    	}
    	if($nbItem > $i)$this->saveUserHistory($startToken, $i);

    }	

    /**
     * Récupère les url enregistrées
     *
     * @return array
     */
    public function getUrlSave(){

		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	
        $query = $this->dbD->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("d" => "flux_doc") )                           
            ->joinInner(array('dc' => 'flux_doc'),'dc.tronc = d.doc_id AND dc.type = 39',array('dcurl'=>'dc.url'))
            ->joinInner(array('dp' => 'flux_doc'),'dp.tronc = d.doc_id AND dp.type = 2',array('dpurl'=>'dp.url'));
		
        return $this->dbD->fetchAll($query)->toArray(); 		
    }	    
    
    
}
<?php
/**
 * Flux_Okapi
 * Classe qui gère les flux du serveur Okapi
 *
 * REFERENCES
 * 
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\API
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Flux_Okapi extends Flux_Site{

	var $urlBase = "http://gapai.univ-paris8.fr:3010/";
	
	/**
	 * Zend_Service_Rest instance
	 *
	 * @var Zend_Service_Rest
	 */
	protected $rest;
	
	/**
	 * Constructeur de la classe
	 *
	 * @param  string $idBase
	 *
	 */
	public function __construct($login="", $pwd="", $idBase=false, $bTrace=false)
	{
		parent::__construct($idBase, $bTrace);
	
		//on récupère la racine des documents
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbM)$this->dbM = new Model_DbTable_Flux_Monade($this->db);
		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
	
		$this->login = $login;
		$this->pwd = $pwd;
		if($login && $pwd){
			$this->pwd = $pwd;
			$this->rest = new Zend_Rest_Client();
			$this->rest->getHttpClient()->setAuth($login, $pwd);
			$this->rest->setUri($this->urlBase);
		}
		
		
	}
	
	/**
	 * connexion au serveur 
	 *
	 * @param  $login
	 * @param  $pwd
	 * 
	 * @return Zend_Http_Client
	 */
	public function getLoginCookie($login="", $pwd="")
	{
		if(!$login)$login=$this->login;
		if(!$pwd)$pwd=$this->pwd;		
		
		$url = $this->urlBase."api/saphir/login?user=".$login."&password=".$pwd;
		$client = new Zend_Http_Client($url,array('timeout' => 30));
		$response = $client->request();
		$h = $response->getHeaders();
		return array("Cookie"=>$h['Set-cookie']);
	}
	
	
	/**
	 * Recherche un media sur le serveur
	*
	* @param  $q: query comprenant optionnellement les opérateurs AND, OR, NEAR,NOT ainsi que des patterns (expressions régulières) avec wilcard * ou ? 
	* @param  $lang: langage de recherche (ex: fr pour français)
	* @param  $limit : nombre max de résultats renvoyés
	* @param  $offset : offset dans la liste des résultats. Permet de gérer avec « limit » la fourniture de résultats par pages.
	* 	
	* @return array
	*/
	public function chercherMedia($q, $lang="fr", $limit=10, $offset=0)
	{
		$url = $this->urlBase."api/saphir/search_media?q=".$q;
		$json = $this->getUrlBodyContent($url,false,$this->bCache);
		
	    return $json;
	}

	/**
	 * Ajoute un media sur le serveur
	 *
	 * @param  $urlDown : Une url à partir de laquelle le fichier média peut être téléchargé
	 * @param  $urlStream : Une url à partir de laquelle le fichier média peut être diffusé par flux
	 * @param  $contributor : nombre max de résultats renvoyés
	 * @param  $creator : offset dans la liste des résultats. Permet de gérer avec « limit » la fourniture de résultats par pages.
	 * @param  $data : paramètres supplémentaires
	 *
	 * @return array
	 */
	public function ajouterMedia($urlDown,$urlStream,$contributor,$creator,$params=false)
	{
				
		$url = $this->urlBase."api/saphir/add_media?";
		$url .= "download_url=".$urlDown."&";
	    $url .= "streaming_url=".$urlStream."&";
	    $url .= "creator=".$contributor."&";
	    $url .= "contributor=".$contributor."&";
	    $url .= "title=".$params['title']."&";
	    $url .= "description=".$params['description'];
	    //$this->trace($url); 
	    
	    //création du client HTTP de login	    
	    $h = $this->getLoginCookie();
	    $client = new Zend_Http_Client($url);
	    $client->setHeaders($h);
	  
	    //envoie de la requête
	    	$response = $client->request();
	    	$json = $response->getBody();
	    	 	     
	    return $json;
	}

}

?>
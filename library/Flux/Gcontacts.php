<?php

/**
 * Classe qui gère les flux Google calendar
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Gcontacts extends Flux_Site{

	var $client;		
	var $token;
	var $version = "3.0";
	var $baseUrlParam = "";
	 	
	public function __construct($token, $idBase=false)
    {
    		parent::__construct($idBase);    	
		$this->client = new Google_Client();				
		$this->client->setAccessToken($token);		
	    	$this->token = $token;	
	    	$this->baseUrlParam = "v=".$this->version;    
    }
	
    public function getListeContacts()
    {
    	$this->trace("DEBUT ".__METHOD__);
		$c = str_replace("::", "_", __METHOD__)."_".md5($this->token); 
		$contacts = $this->cache->load($c);

		
    		if(!$contacts){
    			//merci à https://gist.github.com/lovasoa/9547629
			$req = new Google_Http_Request("https://www.google.com/m8/feeds/contacts/default/full");
		    //$req->setRequestMethod("POST");
		    $req->setRequestHeaders(array(
		        'GData-Version' => '3.0',
		        'content-type' => 'application/atom+xml; charset=UTF-8; type=feed',
		    		'max-results' => '10'
		    ));
		    
			$req = $this->client->getAuth()->sign($req);
			$submit = $this->client->getIo()->executeRequest($req);
		    $contacts = $submit->getResponseBody();			
	        	//$this->cache->save($contacts, $c);
    		}
    		$this->trace("FIN ".__METHOD__);
		return $contacts;
    }
    
}
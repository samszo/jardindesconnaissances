<?php

/**
 * Classe qui gère les flux Google Knowledge Graph
 *
 * https://developers.google.com/knowledge-graph/reference/rest/v1/
 * 
 * @copyright  2016 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Gknowledgegraph extends Flux_Site{

	var $service;		
	var $idsEvent;		
	var $events;		
	var $token;	
	var $langue = "fr";	
 	var $service_url = 'https://kgsearch.googleapis.com/v1/entities:search';
	 	
	/**
     * Constructeur de la classe
     *
     * @param  string $idBase
     * @param  boolean $bTrace
     * 
     */
 	public function __construct($idBase=false, $bTrace=false)
    {
    		parent::__construct($idBase, $bTrace);    	
    }
	
	/**
     * Execute uen requête sur le service
     *
     * @param  string $query
     * @param  boolean $bArray
     * 
     * @return objet
     * 
     */
    public function getQuery($query, $bArray=false)
    {
    		$this->trace("DEBUT ".__METHOD__);
		$params = array(
			  'query' => $query,
			  'limit' => 10,
			  'indent' => TRUE,
			  'languages' => $this->langue,
			  'key' => KEY_GOOGLE_SERVER);
		$url = $this->service_url . '?' . http_build_query($params);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		if($bArray) $response = json_decode($response, true);
		curl_close($ch);
		$this->trace("réponse ",$response);
	
	    	$this->trace("FIN ".__METHOD__);
		return $response;
    }
    
}
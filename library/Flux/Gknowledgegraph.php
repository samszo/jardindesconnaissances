<?php

/**
 * Flux_Gknowledgegraph
 * Classe qui gère les flux Google Knowledge Graph
 * https://developers.google.com/knowledge-graph/reference/rest/v1/
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\API
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
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
     * Execute une requête sur le service
     *
     * @param  array    $params
     * @param  boolean  $bArray
     * 
     * @return object
     * 
     */
    public function getReponse($params, $bArray=false)
    {
    		$this->trace("DEBUT ".__METHOD__);
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

    /**
     * Execute une recherche plein texte
     *
     * @param  string $query
     * @param  boolean $bArray
     *
     * @return object
     *
     */
    public function getQuery($query, $bArray=false)
    {
        $this->trace("DEBUT ".__METHOD__);
        return $this->getReponse(array(
            'query' => $query,
            'limit' => 10,
            'indent' => TRUE,
            'languages' => $this->langue,
            'key' => KEY_GOOGLE_SERVER),$bArray);
    }

    /**
     * Recherche un identifiant
     *
     * @param  string   $id
     * @param  boolean  $bArray
     *
     * @return object
     *
     */
    public function getId($id, $bArray=false)
    {
        $this->trace("DEBUT ".__METHOD__);
        // corrige l'id : kg:/m/02m0v = /m/02m0v
        $id = substr($id, 3);
        return $this->getReponse(array(
            'ids' => $id,
            'limit' => 10,
            'indent' => TRUE,
            'languages' => $this->langue,
            'key' => KEY_GOOGLE_SERVER),$bArray);
    }
    
}
<?php
/**
 * Classe qui gère les flux de l'API Ayliens
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Aylien extends Flux_Site{
	
	public function __construct($idBase=false)
    {
    	parent::__construct($idBase);
    	
    }
    

	public function call_api($endpoint, $parameters) {
	  $ch = curl_init('https://api.aylien.com/api/v1/' . $endpoint);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    'Accept: application/json',
	    'X-AYLIEN-TextAPI-Application-Key: ' . KEY_AYLIEN,
	    'X-AYLIEN-TextAPI-Application-ID: '. APPLI_AYLIEN
	  ));
	  
	  curl_setopt($ch, CURLOPT_POST, true);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
	  $response = curl_exec($ch);
	  return json_decode($response);
	}

	public function getAnalyses($text, $url=""){
		if($url)
			$this->params = array('url' =>$url);
		else
			$this->params = array('text' =>$text);
		$sentiment = $aylien->call_api('sentiment', $this->params);
		$concepts = $aylien->call_api('concepts', $this->params);
		$entities = $aylien->call_api('entities', $this->params);
	}
}
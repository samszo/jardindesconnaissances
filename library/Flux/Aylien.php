<?php
/**
 * Classe qui gère les flux de l'API Ayliens
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Aylien extends Flux_Site{
		
    /**
     * Constructeur de la classe
     *
     * @param  string $idBase
     * 
     */
	public function __construct($idBase=false)
    {
    		parent::__construct($idBase);    	
    }
    

    /**
     * appel l'api Aylien
     *
     * @param  string 	$endpoint
     * @param  array 	$parameters
     * 
     */
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
	  //echo $response;
	  
	  return json_decode($response);
	  
    }

    /**
     * renvoie les analyses de l'api Aylien
     *
     * @param  string 	$text
     * @param  string 	$url
     * @param  string 	$keyCache
     * 
     * @return array 	
     * 
     */
	public function getAnalyses($text, $url="", $keyCache=""){
		if($url)
			$this->params = array('url' =>$url);
		else
			$this->params = array('text' =>substr($text, 0, 10000));
		$this->params['language']="fr";
		if($keyCache){
			$cSentiments = str_replace("::", "_", __METHOD__)."_".$keyCache."_sentiments"; 
		   	$sentiments = $this->cache->load($cSentiments);
			$cConcepts = str_replace("::", "_", __METHOD__)."_".$keyCache."_concepts"; 
		   	$concepts = $this->cache->load($cConcepts);
			$cEntities = str_replace("::", "_", __METHOD__)."_".$keyCache."_entities"; 
		   	$entities = $this->cache->load($cEntities);
		}
        if(!$concepts){			
			$concepts = $this->call_api('concepts', $this->params);
			if($keyCache)$this->cache->save($concepts, $cConcepts);
        }
        /*problème avec l'extraction des sentiments			
		if(!$sentiments){			
			$sentiments = $this->call_api('sentiments', $this->params);
			if($keyCache)$this->cache->save($sentiments, $cSentiments);
        }
        */			
        if(!$entities){			
			$entities = $this->call_api('entities', $this->params);
			if($keyCache)$this->cache->save($entities, $cEntities);
        }			
		return array("sentiments"=>$sentiments, "concepts"=>$concepts, "entities"=>$entities);
	}
}
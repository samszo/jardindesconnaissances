<?php
/**
 * Classe qui gère les flux dbpedia
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 * THANKS
 * Author: John Wright
 * Website: http://johnwright.me/blog
 * http://johnwright.me/code-examples/sparql-query-in-code-rest-php-and-json-tutorial.php
 */
class Flux_Dbpedia{

	var $cache;
	var $forceCalcul = false;
	var $user;
	var $del;
	
	function GetTagLinks($tag) {
		
        $c = __CLASS__."_".__METHOD__."_".$tag;
        if($this->forceCalcul)$this->cache->remove($c);
        if(!$flux = $this->cache->load($c)) {
			$searchUrl = $this->getUrlKeyword($tag);
		    $flux = $this->request($searchUrl);
	    	$this->cache->save($flux,$c);
		}
		
		
		
		echo $flux;  
	    
	}
	
	function getUrlKeyword($QueryString, $QueryClass="", $MaxHits=""){
		//http://wiki.dbpedia.org/Lookup?v=1e13
		//class : http://www4.wiwiss.fu-berlin.de/dbpedia/dev/ontology.htm
		$url = "http://lookup.dbpedia.org/api/search.asmx/KeywordSearch?QueryString=".urlencode($QueryString);
		if($QueryClass!="")$url .= "&QueryClass=".$QueryClass;
		if($MaxHits!="")$url .= "&MaxHits=".$MaxHits;
		
		return $url;
	}
	
	function getUrlAbstract($term)
	{
	   	$format = 'json';
	 
	   	$query =
		"PREFIX dbp: <http://dbpedia.org/resource/>
			PREFIX dbp2: <http://dbpedia.org/ontology/>
			SELECT ?abstract
			WHERE {
			dbp:".$term." dbp2:abstract ?abstract .
			}";
	 
	   	$searchUrl = 'http://dbpedia.org/sparql?'
	    	.'query='.urlencode($query)
	      	.'&format='.$format;
	
		return $searchUrl;
	}
		
	function request($url){
	 
		if (!function_exists('curl_init')){
			die('CURL is not installed!');
		}
	 
		// get curl handle
		$ch= curl_init();
		curl_setopt($ch,
			CURLOPT_URL,
			$url);
		curl_setopt($ch,
	    	CURLOPT_RETURNTRANSFER,
			true);
	 
		$response = curl_exec($ch);
	 
		curl_close($ch);
	 
		return $response;
	}	
}
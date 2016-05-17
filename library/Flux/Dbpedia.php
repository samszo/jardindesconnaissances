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
class Flux_Dbpedia extends Flux_Site{

	var $forceCalcul = false;
	var $idUser;
	var $del;
	var $lang = "fr";
	var $formatResponse = "json";
	var $searchUrl = 'http://fr.dbpedia.org/sparql?';
    const IDEXI = 1;
    
    /**
     * Constructeur de la classe
     *
     * @param  string $idBase
     * 
     */
	public function __construct($idBase=false, $bTrace=false)
    {
    		parent::__construct($idBase, $bTrace);    	
    }

    /**
     * Execute une requète sur dbpedia
     *
     * @param  string $query
     *
     * @return string
     */
    public function query($query)
    {
	    $url = $this->searchUrl.'query='.urlencode($query)
	      	.'&format='.$this->formatResponse;
		return $this->getUrlBodyContent($url,false,$this->forceCalcul);
    }
    
	/**
     * Recherche une biographie à partir d'une ressource databnf
     *
     * @param  string $ressource
     *
     * @return string
     */
    public function getBio($ressource)
    {	   	
	   	$this->trace(__METHOD__." ".$ressource);
    		//récupère les infos de data bnf
	   	$query = "select * where {<http://fr.dbpedia.org/resource/".$ressource."> ?r ?p}";	   			   	
	   	$this->trace($query);
		$result = $this->query($query);
	   	$this->trace($result);
		$obj = json_decode($result);
						
		//construction de la réponse
		$objResult = new stdClass();
		$liens = array();
		foreach ($obj->results->bindings as $key => $v) {
	   		$this->trace($key,$v->r->value);
			switch ($v->r->value) {
				case "http://fr.dbpedia.org/property/bnf":
					$liens[] = array("value"=>"http://data.bnf.fr/"+$v->p->value,"recid"=>count($liens)+1,"type"=>"bnf");				
					break;
				case "http://fr.dbpedia.org/property/naissance":
					$objResult->nait = $v->p->value;				
					break;
				case "http://dbpedia.org/ontology/birthDate":
					$date = new DateTime($v->p->value);
					$objResult->nait = $date->format('Y-m-d');				
					break;
				case "http://fr.dbpedia.org/property/décès":
					$objResult->mort = $v->p->value;				
					break;
				case "http://dbpedia.org/ontology/deathDate":
					$date = new DateTime($v->p->value);
					$objResult->mort = $date->format('Y-m-d');				
					break;	
				case "http://fr.dbpedia.org/property/sudoc":
					$liens[] = array("value"=>"http://data.bnf.fr/"+$v->p->value,"recid"=>count($liens)+1,"type"=>"sudoc");														
					break;
				case "http://fr.dbpedia.org/property/viaf":
					$liens[] = array("value"=>"http://viaf.org/viaf/"+$v->p->value,"recid"=>count($liens)+1,"type"=>"viaf");																								
					break;
				case "http://xmlns.com/foaf/0.1/depiction":
					$liens[] = array("value"=>$v->p->value,"recid"=>count($liens)+1,"type"=>"img");																								
					break;
				case "http://dbpedia.org/ontology/wikiPageWikiLink":	
					$liens[] = array("value"=>$v->p->value,"recid"=>count($liens)+1,"type"=>"wiki");																								
					break;
			}
		}	
		$objResult->data->liens = $liens;
		
	   	$this->trace("result",$objResult);
	   	$js = json_encode($objResult); 
	   	$this->trace($js);
	   	
		return $js;
    }    
    	
	function SaveUserTagsLinks($user) {
		
		//récupère les tags d'un utilisateur
		if(!$this->dbUT) $this->dbUT = new Model_DbTable_Flux_UtiTag();
		$arrTags = $this->dbUT->findTagByUti($user);
		
		foreach ($arrTags as $tag){
			$this->SaveTagLinks($tag);
		}
	}
	
	function GetTagLinks($tag) {
		
        $c = str_replace("::", "_", __METHOD__)."_".md5($tag);
		if($this->forceCalcul)$this->cache->remove($c);
        if(!$flux = $this->cache->load($c)) {
			$searchUrl = $this->getUrlKeyword($tag);
		    $flux = $this->request($searchUrl);
	    		$this->cache->save($flux,$c);
		}
		
		return simplexml_load_string($flux);      
	}

	function SaveTagLinks($tag) {
		
		if(!$this->dbD) $this->dbD = new Model_DbTable_Flux_Doc();
		if(!$this->dbT) $this->dbT = new Model_DbTable_Flux_Tag();
		if(!$this->dbTD) $this->dbTD = new Model_DbTable_Flux_TagDoc();
		if(!$this->dbED) $this->dbED = new Model_DbTable_Flux_ExiDoc();
		if(!$this->dbET) $this->dbET = new Model_DbTable_Flux_ExiTag();
		if(!$this->dbTT) $this->dbTT = new Model_DbTable_Flux_TagTag();
		
		$links = $this->GetTagLinks($tag['code']);

		$date = new Zend_Date();
		foreach ($links->Result as $r){
			//enregistre le document dppedia
			$idD = $this->dbD->ajouter(array("url"=>$r->URI,"titre"=>$r->Label,"maj"=>$date->get("c")));
			//lie le document avec l'api
			$this->dbED->ajouter(array("exi_id"=>self::IDEXI, "doc_id"=>$idD));
			//enregistre les tags de class
			foreach ($r->Classes->Class as $t) {
				//ajoute le tag
				$idT = $this->dbT->ajouter(array("code"=>$t->Label,"desc"=>$t->URI,"parent"=>"Class"));
				//lie le tag au document
				$this->dbTD->ajouter(array("tag_id"=>$idT, "doc_id"=>$idD));
				//lie le tag à l'api
				$this->dbET->ajouter(array("tag_id"=>$idT, "exi_id"=>self::IDEXI));
				//lie le tag dbpedia au tag de l'utilisateur
				$this->dbTT->ajouter(array("tag_id_src"=>$tag['tag_id'], "tag_id_dst"=>$idT));
			}
			//enregistre les tags de category
			foreach ($r->Categories->Category as $t) {
				//ajoute le tag
				$idT = $this->dbT->ajouter(array("code"=>$t->Label,"desc"=>$t->URI,"parent"=>"Category"));
				//lie le tag au document
				$this->dbTD->ajouter(array("tag_id"=>$idT, "doc_id"=>$idD));
				//lie le tag à l'api
				$this->dbET->ajouter(array("tag_id"=>$idT, "exi_id"=>self::IDEXI));
				//lie le tag dbpedia au tag de l'utilisateur
				$this->dbTT->ajouter(array("tag_id_src"=>$tag['tag_id'], "tag_id_dst"=>$idT));
			}
			
		}
		
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

	   	/* pour récupérer les classification dewey lié à un mot clef
	   	 * http://dewey.info/sparql.php
PREFIX skos: <http://www.w3.org/2004/02/skos/core#> 
PREFIX dct: <http://purl.org/dc/terms/>

SELECT *
WHERE {
  ?x skos:prefLabel ?prefLabel ;
     skos:notation ?notation .
  FILTER regex(?prefLabel, "jazz", "i") 
}

PREFIX skos: <http://www.w3.org/2004/02/skos/core#> 
PREFIX dct: <http://purl.org/dc/terms/>

SELECT *
WHERE {
  ?x skos:prefLabel ?prefLabel ;
     skos:notation ?notation .
  FILTER ((?notation = 306) && langMatches( lang(?prefLabel), "fr" ))
}

	   	 *
	   	 *
	   	 */
	   	
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
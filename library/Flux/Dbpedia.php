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
	var $idUser;
	var $del;
	var $dbUT;
	var $dbD;
	var $dbT;
	var $dbUD;
	var $dbTD;
	var $dbED;
	var $dbET;
	var $dbTT;
	
    const IDEXI = 1;
	
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
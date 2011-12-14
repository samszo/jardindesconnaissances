<?php
/**
 * Classe qui gère les flux venant du site deleuze
 * http://www2.univ-paris8.fr/deleuze/
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Deleuze extends Flux_Site{
	
	var $root = "http://www2.univ-paris8.fr/deleuze/";
	
    function addInfoDocLucene($url, $doc) {
		$c = str_replace("::", "_", __METHOD__)."_".md5($url); 
	   	$dom = $this->cache->load($c);
        if(!$dom){
	    	$client = new Zend_Http_Client($url);
			$response = $client->request();
			$html = $response->getBody();
			$dom = new Zend_Dom_Query($html);	    
        	$this->cache->save($dom, $c);
        }    	
		//récupère le titre du document
		$results = $dom->query('/html/body/table[2]/tr[2]/td[2]/p[1]/strong');
		$titre = "";
		foreach ($results as $result) {
		    $titre = $result->nodeValue;
		}	    
		$doc->addField(Zend_Search_Lucene_Field::Keyword('titre',$titre));
		//récupère le mp3 du document 
		$results = $dom->query('/html/body/table[2]/tr[2]/td[2]/table/tr/td[1]/a');
		$mp3 = "";
		foreach ($results as $result) {
		    $mp3 = $result->getAttribute("onclick");
		    $mp3 = explode("'", $mp3);
		    $mp3 = $this->root.$mp3[1];
		}	    
		$doc->addField(Zend_Search_Lucene_Field::Keyword('mp3',$mp3));
		
		//ajoute l'url du document
		$doc->addField(Zend_Search_Lucene_Field::Keyword('url',$url));
		
		return $doc;		 
	}	
	
}
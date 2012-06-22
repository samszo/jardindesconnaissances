<?php

class Flux_Site{
    
    var $cache;
	var $idBase;
    var $idExi;
	var $login;
	var $pwd;
    var $user;
	var $graine;
	var $dbU;
	var $dbUU;
	var $dbUT;
	var $dbUD;
	var $dbUTD;
	var $dbT;
	var $dbTD;		
	var $dbD;
	var $dbDT;
	var $dbIEML;
	var $dbUIEML;
	var $dbTrad;
	var $dbE;
	var $dbED;
	var $dbET;
	var $dbG;
	var $dbGUD;
	var $db;
	var $lucene;
	
    function __construct($idBase=false){    	
    	
    	$this->getDb($idBase);
    	
        $frontendOptions = array(
            'lifetime' => 3000000000000000000000000000, // temps de vie du cache en seconde
            'automatic_serialization' => true,
        	'caching' => true //active ou desactive le cache
        );  
        $backendOptions = array(
            // Répertoire où stocker les fichiers de cache
            'cache_dir' => '../tmp/flux/'
        ); 
        // créer un objet Zend_Cache_Core
        $this->cache = Zend_Cache::factory('Core',
                                     'File',
                                     $frontendOptions,
                                     $backendOptions); 
    }

    /**
    * @param string $c
    */
    function removeCache($c){
        $res = $this->manager->remove($c);
    }
    
    /**
     * retourne une connexion à une base de donnée suivant son nom
    * @param string $idBase
    * @return Zend_Db_Table
    */
    public function getDb($idBase){
    	
 		$db = Zend_Db_Table::getDefaultAdapter();
    	if($idBase){
    		//change la connexion à la base
			$arr = $db->getConfig();
			$arr['dbname']=$idBase;
			$db = Zend_Db::factory('PDO_MYSQL', $arr);	
    	}
    	$this->db = $db;
    	$this->idBase = $idBase;
    	return $db;
    }
    
    /**
     * Récupère l'identifiant d'utilisateur ou le crée
     *
     * @param array $user
     * @param boolean $getId
     * 
     * return integer
     */
	function getUser($user, $getId=false) {

		//récupère ou enregistre l'utilisateur
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
		$idU = $this->dbU->ajouter($user);		
		if(!$getId)$this->user = $idU;
		
		return $idU;
	}

    /**
     * Récupère l'identifiant de la graine ou la crée
     *
     * @param array $graine
     * 
     */
	function getGraine($graine) {

		//TODO récupère ou enregistre la graine
		//if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti();
		//$this->graine = $this->dbU->ajouter($user);		

	}

    /**
     * Récupère le contenu d'une url
     *
     * @param string $url
     *   
     * @return string
     */
	function getUrlBodyContent($url) {
		if(substr($url, 0, 7)!="http://")$url = urldecode($url);
		$c = str_replace("::", "_", __METHOD__)."_".md5($url); 
	   	$html = $this->cache->load($c);
        if(!$html){
	    	$client = new Zend_Http_Client($url);
			$response = $client->request();
			$html = $response->getBody();
        	$this->cache->save($html, $c);
        }    	
		return $html;
	}

    /**
     * Ajoute des informations supplémentaire d'indexation
     *
     * @param string $url
     * @param Zend_Search_Lucene_Document_Html $doc
     *   
     * @return Zend_Search_Lucene_Document_Html
     */
	function addInfoDocLucene($url, $doc) {
	   	
    	//récupère le body de l'url
    	//$html = $this->getUrlBodyContent($url);
		//$dom = new Zend_Dom_Query($html);	    
    					
		//ajoute l'url du document
		$doc->addField(Zend_Search_Lucene_Field::Keyword('url',urlencode($url)));
				
		return $doc;		 
	}	
		
	function getParamString($params, $md5=false){
		$s="";
		foreach ($params as $k=>$v){
			if($md5) $s .= "_".md5($v);
			else $s .= "_".$v;
		}
		return $s;	
	}
	
    /**
     * Récupère les mots clefs d'une chaine
     *
     * @param string $chaine
     * @param string $class
     *   
     * @return array
     */
	function getKW($chaine, $class="autokeyword"){
		
		$params['content'] = $chaine; //page content
		//set the length of keywords you like
		$params['min_word_length'] = 4;  //minimum length of single words
		$params['min_word_occur'] = 1;  //minimum occur of single words
		
		$params['min_2words_length'] = 4;  //minimum length of words for 2 word phrases
		$params['min_2words_phrase_length'] = 10; //minimum length of 2 word phrases
		$params['min_2words_phrase_occur'] = 2; //minimum occur of 2 words phrase
		
		$params['min_3words_length'] = 4;  //minimum length of words for 3 word phrases
		$params['min_3words_phrase_length'] = 10; //minimum length of 3 word phrases
		$params['min_3words_phrase_occur'] = 2; //minimum occur of 3 words phrase
		
		if($class=="autokeyword")$keyword = new autokeyword($params, "UTF-8");
		
		//return $keyword->get_keywords();
		return $keyword->parse_words();
		
	}
	
    /**
     * Sauvegarde d'un tag
     *
     * @param string $tag
     * @param integer $idD
     * @param integer $poids
     * @param date $date
     *   
     * @return integer
     */
	function saveTag($tag, $idD, $poids, $date){

		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUT)$this->dbUT = new Model_DbTable_Flux_UtiTag($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
		//on ajoute le tag
		$idT = $this->dbT->ajouter(array("code"=>$tag));
		//on ajoute le lien entre le tag et le doc avec le poids
		$this->dbTD->ajouter(array("tag_id"=>$idT, "doc_id"=>$idD, "poids"=>$poids));
		//on ajoute le lien entre le tag et l'uti avec le poids
		$this->dbUT->ajouter(array("tag_id"=>$idT, "uti_id"=>$this->user, "poids"=>$poids));
		//on ajoute le lien entre le tag l'utilisateur et le doc
		$this->dbUTD->ajouter(array("uti_id"=>$this->user, "tag_id"=>$idT, "doc_id"=>$idD, "maj"=>$date));

		return $idT;
	}

	
    /**
     * Sauvegarde d'un tag sémantique IEML
     *
     * @param string $ieml
     * @param integer $idUti
     * @param integer $idTag
     *   
     * @return integer
     */
	function saveIEML($ieml, $idUti, $idTag){
		//on ajoute le tag ieml
		$idIeml = $this->dbIEML->ajouter(array("code"=>$ieml));
		//on ajoute le tag ieml à l'utilisateur
		$this->dbUIEML->ajouter(array("uti_id"=>$idUti, "ieml_id"=>$idIeml));
		//on ajoute la traduction ieml
		$this->dbTrad->ajouter(array("tag_id"=>$idTag, "ieml_id"=>$idIeml));			
		
		return $idIeml;
	}
	
}
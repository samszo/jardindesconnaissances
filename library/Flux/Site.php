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
	var $db;
	var $index;
	
    function __construct($idBase=false){    	
    	
    	$this->getDb($idBase);
    	
        $frontendOptions = array(
            'lifetime' => 3000000000000000000, // temps de vie du cache en seconde
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
    	return $db;
    }
    
    /**
     * Récupère l'identifiant d'utilisateur ou le crée
     *
     * @param array $user
     * 
     */
	function getUser($user) {

		//récupère ou enregistre l'utilisateur
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti();
		$this->user = $this->dbU->ajouter($user);		

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
		$doc->addField(Zend_Search_Lucene_Field::Keyword('url',$url));
				
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
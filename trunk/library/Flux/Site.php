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
	    
    function __construct($idBase=false){    	
    	
    	$this->getDb($idBase);
    	
        $frontendOptions = array(
            'lifetime' => 8640000000000000000000000000, // temps de vie du cache en seconde
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
	
}
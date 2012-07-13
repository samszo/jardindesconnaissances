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
	var $dbTT;
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
	//var $kwe = array("alchemy", "zemanta", "yahoo");
	var $kwe = array("zemanta", "yahoo");
	
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
     * Récupère le contenu body d'une url
     *
     * @param string $url
     * @param array $param
     * @param boolean $cache
     *   
     * @return string
     */
	function getUrlBodyContent($url, $param=false, $cache=true) {
		$html = false;
		if(substr($url, 0, 7)!="http://")$url = urldecode($url);
		if($cache){
			$c = str_replace("::", "_", __METHOD__)."_".md5($url)."_".$this->getParamString($param); 
		   	$html = $this->cache->load($c);
		}
        if(!$html){
	    	$client = new Zend_Http_Client($url);
	    	if($param)$client->setParameterGet($param);
	    	try {
				$response = $client->request();
				$html = $response->getBody();
			}catch (Zend_Exception $e) {
				echo "Récupère exception: " . get_class($e) . "\n";
			    echo "Message: " . $e->getMessage() . "\n";
			}				
        	if($cache)$this->cache->save($html, $c);
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
     * Sauvegarde d'un tag
     *
     * @param string $tag
     * @param integer $idD
     * @param integer $poids
     * @param date $date
     * @param int $idUser
     *   
     * @return integer
     */
	function saveTag($tag, $idD, $poids, $date, $idUser=-1){

		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUT)$this->dbUT = new Model_DbTable_Flux_UtiTag($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
		if($idUser==-1)$idUser=$this->user;
		
		//on ajoute le tag
		$idT = $this->dbT->ajouter(array("code"=>$tag));
		//on ajoute le lien entre le tag et le doc avec le poids
		$this->dbTD->ajouter(array("tag_id"=>$idT, "doc_id"=>$idD, "poids"=>$poids));
		//on ajoute le lien entre le tag et l'uti avec le poids
		$this->dbUT->ajouter(array("tag_id"=>$idT, "uti_id"=>$idUser, "poids"=>$poids));
		//on ajoute le lien entre le tag l'utilisateur et le doc
		$this->dbUTD->ajouter(array("uti_id"=>$idUser, "tag_id"=>$idT, "doc_id"=>$idD, "maj"=>$date, "poids"=>$poids));

		return $idT;
	}

	
    /**
     * Sauvegarde une relation enytre tag
     *
     * @param string $tagSrc
     * @param string $tagDst
     * @param integer $poids
     * @param date $date
     *   
     * @return integer
     */
	function saveTagTag($tagSrc, $tagDst, $poids, $date, $idSrc=-1, $idDst=-1){

		if(!$this->dbTT)$this->dbTT = new Model_DbTable_Flux_TagTag($this->db);
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);

		if($idSrc==-1){
			$idSrc = $this->dbT->ajouter(array("code"=>$tagSrc));			
		}
		if($idDst==-1){
			$idDst = $this->dbT->ajouter(array("code"=>$tagDst));			
		}
		
		//on ajoute la relation entre les tag
		return $this->dbTT->ajouter(array("tag_id_src"=>$idSrc, "tag_id_dst"=>$idDst, "poids"=>$poids, "maj"=>$date));

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


	
    /**
     * enregistre les mots clefs d'une chaine
     *
     * @param int $idDoc
     * @param string $texte
     * @param string $html
     * @param string $class
     *   
     * @return array
     */
	function saveKW($idDoc, $texte, $html="", $class="all"){
		
		if($class=="all"){
			foreach ($this->kwe as $c) {
				$result[$c] = $this->saveKW($idDoc, $texte, $html, $c);
			}
			return $result;
		}
		
		//récupère les mots clefs
		$arrKW = $this->getKW($texte, $html, $class);
		
		//récupère l'utilisateur correspondant à la classe
		$idUdst = $this->getUser(array("login"=>"KWE_".$class),true);
		
	   	//enregistre les mots clefs
	   	if($arrKW){
		   	$d = new Zend_Date();
		   	$i=0;
			switch ($class) {
				case "autokeyword":
					foreach ($arrKW as $kw=>$nb){
						$this->saveTag($kw, $idDoc, $nb, $d->get("c"),$idUdst);
						$i++;	    			
				   	}
					break;
				case "alchemy":
					if($arrKW->status=="OK"){
						foreach ($arrKW->keywords as $kw){
							$idT = $this->saveTag($kw->text, $idDoc, $kw->relevance, $d->get("c"), $idUdst);
							//enregistre le sentiment
							if($kw->sentiment){
								$poids=1;
								if(isset($kw->sentiment->score))$poids=$kw->sentiment->score;
								$this->saveTagTag("", $kw->sentiment->type, $poids, $d->get("c"), $idT);
							}
							$i++;	    			
					   	}
					}
					break;
				case "yahoo":
					if(isset($arrKW->query->results->entities)){
						foreach ($arrKW->query->results->entities->entity as $kw){
							$idT = $this->saveTag($kw->text->content, $idDoc, $kw->score, $d->get("c"), $idUdst);
							//enregistre les types
							if(isset($kw->types)){							
								foreach ($kw->types->type as $t){
									if(isset($t->content)){
										$poids=1;
										$this->saveTagTag("", $t->content, $poids, $d->get("c"), $idT);
									}
								}
							}
							/**TODO compléter avec les autres champs de réponse
							 * http://developer.yahoo.com/search/content/V2/contentAnalysis.html
							 */
							$i++;	    			
					   	}
					}
					break;
				case "zemanta":
					if($arrKW->status=="ok"){
						if(isset($arrKW->markup->links)){
							foreach ($arrKW->markup->links as $kw){
								$type=false;
								$poids = $kw->relevance;
								if(isset($kw->entity_type))$type=implode(";", $kw->entity_type);
								foreach ($kw->target as $t){
									//enregistre le tag
									$idT = $this->saveTag($t->title, $idDoc, $poids, $d->get("c"), $idUdst);
									//récupère le document lié
									$idD = $this->dbD->ajouter(array("url"=>$t->url,"titre"=>$t->title,"tronc"=>0,"maj"=>$d->get("c"), "type"=>39));
									//ajoute un lien entre zemanta et le document avec un poids
									$this->dbUD->ajouter(array("uti_id"=>$idUdst, "doc_id"=>$idD, "poids"=>$kw->confidence));										    
									//enregistre le tag pour le document
									$idTLie = $this->saveTag($t->type, $idD, $kw->confidence, $d->get("c"), $idUdst);
									//enregistre les tags liés
									$this->saveTagTag("", "", $poids, $d->get("c"), $idT, $idTLie);
									if($type){
										$this->saveTagTag("", $type, $poids, $d->get("c"), $idT);
									}
								}
								/**TODO compléter avec les autres champs de réponse
								 * http://developer.zemanta.com/docs/suggest_markup/
								 */
								$i++;	    			
						   	}
						}
					}
					break;
			}
	   	}
		return $arrKW;		
	}

    /**
     * Récupère les mots clefs d'une chaine
     *
     * @param string $texte
     * @param string $html
     * @param string $class
     *   
     * @return array
     */
	function getKW($texte, $html="", $class="autokeyword"){
		
		switch ($class) {
			case "autokeyword":
				if($html!="")$chaine = strip_tags($html);
				else $chaine = $texte;
				$rs = $this->getKWAutokeyword($chaine);
				break;
			case "alchemy":
				$rs = $this->getKWAlchemy($texte, $html);
				break;
			case "yahoo":
				$rs = $this->getKWYahoo($texte, $html);
				break;
			case "zemanta":
				$rs = $this->getKWZemanta($texte, $html);
				break;
		}
		return $rs;		
	}	
	
    /**
     * Récupère les mots clefs d'une chaine
     *
     * @param string $chaine
     *   
     * @return array
     */
	function getKWAutokeyword($chaine){
		
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
		
		$keyword = new autokeyword($params, "UTF-8");
		
		//return $keyword->get_keywords();
		return $keyword->parse_words();
		
	}	
	/**
     * Récupère les mots clefs avec AlchemyAPI
     * http://www.alchemyapi.com
     * @param string $texte
     * @param string $html
     * @param string $format
     * 
     * @return array/xml
     */
	function getKWAlchemy($texte, $html='', $format = 'json'){

		if($html!="")$chaine=strip_tags($html);
		else $chaine=$texte; 		
		
		// Create an AlchemyAPI object.
		$alchemyObj = new AlchemyAPI();
		$alchemyObj->setAPIKey(KEY_ALCHEMY);
		
		/**TODO: vérifier avec le format html
		
		if($html!=""){
			$body = $alchemyObj->HTMLGetRankedKeywords($html, "", $format);			
		}else{
			$body = $alchemyObj->TextGetRankedKeywords($texte, $format);			
		}
		*/
		
		$body = $alchemyObj->TextGetRankedKeywords($chaine, $format);			
		
		if($format=="json"){
			$result = json_decode($body);
		}else{
			$result = simplexml_load_string($body);
		}
		
		return $result;
		
	}		
	
	/**
     * Récupère les mots clefs avec Zemanta
     * http://developer.zemanta.com/
     * @param string $texte
     * @param string $html
     * @param string $format
     * 
     * @return string
     */
	function getKWZemanta($texte, $html="", $format = 'json'){
		
		if($html!="")$chaine=$html;
		else $chaine=$texte; 		
		
		/* This are the vars you may need to modify */
		/* Some may be placed in conf files */
		/* Some may be generated by your application */
		$url = 'http://api.zemanta.com/services/rest/0.0/'; //Should be in a conf file
		 // May depend of your application context
		$method="zemanta.suggest";
		$method="zemanta.suggest_markup";
		
		/* It is easier to deal with arrays */
		$args = array(
		'method'=> $method,
		'api_key'=> KEY_ZEMANTA,
		'text'=> $chaine,
		'format'=> $format
		);
		
		/* Execute the request 
		$client = new Zend_Http_Client($url);
		$client->setParameterPost($args);
		$response = $client->request(Zend_Http_Client::POST);
		*/
		$body = $this->getUrlBodyContent($url, $args, false);		
		
		if($format=="json"){
			$result = json_decode($body);
		}else{
			$result = simplexml_load_string($body);
		}
		
		
		/* $xml now contains the response body */
		return $result;		
	}	

	/**
     * Récupère les mots clefs avec Yahoo
     * http://developer.yahoo.com/search/content/V2/contentAnalysis.html
     * @param string $text
     * @param string $html
     * @param string $format
     * 
     * @return xml/array
     */
	function getKWYahoo($texte, $html="", $format='json'){
		
		$url = 'http://query.yahooapis.com/v1/public/yql'; 
		
		/**TODO:vérifier si le traitement est plsu efficace avec une url
		 * 
		 */
		if($html!="")$chaine=strip_tags($html);
		else $chaine=$texte; 		
		
		$characters = array('=', '"');
		$replacements = array('%3D', '%22');
		$chaine = str_replace($characters, $replacements, $chaine);
		
		$query = 'SELECT * FROM contentanalysis.analyze WHERE text = "'.$chaine.'"';

		/* It is easier to deal with arrays 
		text	 string (required if url parameter is not used)	 The content to perform analysis (UTF-8 encoded).
		url	 string (required if text parameter is not used)	 The url of t
		related_entities	 boolean: true (default), false	 Whether or not to include related entities/concepts in the response
		show_metadata	 boolean: true (default), false	 Whether or not to include entity/concept metadata in the response
		enable_categorizer	 boolean: true (default), false	 Whether or not to include document category information in the response
		unique	 boolean: true, false (default)	 Whether or not to detect only one occurrence of an entity or a concept that my appear multiple times		
		*/
		$args = array(
		'q'=> $query,
		'format'=> $format
		);
		
		/* Execute the request 
		$client = new Zend_Http_Client($url);
		$client->setParameterPost($args);
		$response = $client->request(Zend_Http_Client::POST);	
		$body = $response->getBody();		
		*/
		$body = $this->getUrlBodyContent($url, $args, false);		
		
		if($format=="json"){
			$result = json_decode($body);
		}else{
			$result = simplexml_load_string($body);
		}
		
		return $result;
		
		/* message de dépassement du nombre de requête
			cbfunc({
			 "error": {
			  "lang": "en-US",
			  "description": "Query syntax error(s) [line 1:9 missing FROM at 'FROdfM']"
			 }
			});
			<Error xmlns="urn:yahoo:api"  
			 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  
			 xsi:noNamespaceSchemaLocation="http://api.yahoo.com/Api/V1/error.xsd">  
			     The following errors were detected:  
			   <Message>limit exceeded</Message>  
			 </Error> 	
		 */
		
	}		
}
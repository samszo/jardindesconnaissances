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
	var $kwe = array("zemanta", "alchemy", "yahoo");
	
    function __construct($idBase=false){    	
    	
    	$this->getDb($idBase);
    	
        $frontendOptions = array(
            'lifetime' => 30000, // temps de vie du cache en seconde
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
     * getArrHier
     * 
     * Création d'un tableau hiérarchique à partir d'un tableau de parent
     *
     * @param array $arr
     * @param array $result
     * @param int $niv
     * 
     * return $arr
     */
	function getArrHier($arr, $arrParent, $result= array(), $niv=0) {

		if($arr['niveau']==$niv){
			$result[]=$arr;
		}
		$i=0;
		//recherche le bon parent
		foreach ($result as $parent){
			if($parent["tag_id"]==$arrParent[$niv])break;
			$i++;
		}
		if(!isset($result[$i]['children'])){
			$result[$i]['children']= array();			
		}
		if($niv<$arr['niveau']){
			$result[$i]['children'] = $this->getArrHier($arr, $arrParent, $result[$i]['children'], $niv+1);
		}

		return $result;
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
			$c = str_replace("::", "_", __METHOD__)."_".md5($url); 
			if($param)$c .= "_".$this->getParamString($param);
		   	$html = $this->cache->load($c);
		}
        if(!$html){
	    	$client = new Zend_Http_Client($url,array('timeout' => 30));
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
     * @param boolean $existe : mettre à false pour forcer la création 
     *   
     * @return integer
     */
	function saveTag($tag, $idD, $poids, $date, $idUser=-1, $existe = true){

		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUT)$this->dbUT = new Model_DbTable_Flux_UtiTag($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
		if($idUser==-1)$idUser=$this->user;
		
		//on ajoute le tag
		if(is_array($tag))
			$idT = $this->dbT->ajouter($tag, $existe);
		else
			$idT = $this->dbT->ajouter(array("code"=>$tag), $existe);
		//on ajoute le lien entre le tag et le doc avec le poids
		$this->dbTD->ajouter(array("tag_id"=>$idT, "doc_id"=>$idD, "poids"=>$poids));
		//on ajoute le lien entre le tag et l'uti avec le poids
		$this->dbUT->ajouter(array("tag_id"=>$idT, "uti_id"=>$idUser, "poids"=>$poids));
		//on ajoute le lien entre le tag l'utilisateur et le doc
		$this->dbUTD->ajouter(array("uti_id"=>$idUser, "tag_id"=>$idT, "doc_id"=>$idD, "maj"=>$date, "poids"=>$poids), $existe);

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
	function saveTagTag($tagSrc, $tagDst, $poids, $date, $idD, $idSrc=-1, $idDst=-1, $idUser=-1){

		if(!$this->dbTT)$this->dbTT = new Model_DbTable_Flux_TagTag($this->db);
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);

		if($idSrc==-1){
			$idSrc = $this->saveTag($tagSrc, $idD, $poids, $date, $idUser);
		}
		if($idDst==-1){
			$idDst = $this->saveTag($tagDst, $idD, $poids, $date, $idUser);
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
		
		//initialise les gestionnaires de base de données
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		
		if($class=="all"){
			foreach ($this->kwe as $c) {
				$result[$c] = $this->saveKW($idDoc, $texte, $html, $c);
			}
			return $result;
		}
		
		//récupère les mots clefs
		$arrKW = $this->getKW($texte, $html, $class);

		//récupère la date courante
		$d = new Zend_Date();
		
		//récupère l'utilisateur correspondant à la classe
		$idUdst = $this->getUser(array("login"=>"KWE_".$class),true);
		
		//enregistre l'extraction de mots clefs
		$idDe = $this->dbD->ajouter(array("titre"=>"json_".$class,"tronc"=>$idDoc,"maj"=>$d->get("c"), "type"=>78, "note"=>json_encode($arrKW)));
		
		//enregistre les mots clefs
	   	if($arrKW){
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
								$this->saveTagTag("", $kw->sentiment->type, $poids, $d->get("c"), $idDoc, $idT, -1, $idUdst);								
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
										$this->saveTagTag("", $t->content, $poids, $d->get("c"), $idDoc, $idT, -1, $idUdst);
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
						if(isset($arrKW->keywords)){
							foreach ($arrKW->keywords as $kw){
								$type = $kw->scheme;
								$poids = $kw->confidence;
								//enregistre le tag
								$idT = $this->saveTag($kw->name, $idDoc, $poids, $d->get("c"), $idUdst);
								if($type){
									$this->saveTagTag("", $type, $poids, $d->get("c"), $idDoc, $idT, -1, $idUdst);
								}
						   	}
						}
						if(isset($arrKW->markup->links)){
							foreach ($arrKW->markup->links as $kw){
								$type=false;
								$poids = $kw->relevance;
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
									$this->saveTagTag("", "", $poids, $d->get("c"), $idDoc, $idT, $idTLie, $idUdst);
									//enregistre les types
									if(isset($kw->entity_type)){
										if(is_array($kw->entity_type)){
											foreach ($kw->entity_type as $tp) {
												//enregistre les tags liés
												$this->saveTagTag("", $tp, $kw->confidence, $d->get("c"), $idD, $idTLie, -1, $idUdst);
											}											
										}else{
											$this->saveTagTag("", $kw->entity_type, $kw->confidence, $d->get("c"), $idD, $idTLie, -1, $idUdst);
										}
								}
									
								}
								$i++;	    			
						   	}
						}
						/**TODO compléter avec les autres champs de réponse
						 * http://developer.zemanta.com/docs/suggest_markup/
						 */
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
		//$method="zemanta.suggest_markup";
		
		/* It is easier to deal with arrays */
		$args = array(
		'method'=> $method,
		'api_key'=> KEY_ZEMANTA,
		'text'=> $chaine,
		'format'=> $format
		);
		
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
		
		$characters = array('=', '"', '\\');
		$replacements = array('%3D', '%22', ' ');
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

	/**
     * Récupère les mots clefs avec CEPT
     * https://cept.3scale.net/docs
     * @param string $text
     * @param string $action
     * @param string $format
     * 
     * @return xml/array
     */
	function getKWCEPT($texte, $action){
		
		$url = 'http://api.cept.at/v1/'; 
		
		switch ($action) {
			case "similarterms":
				$url .= $action."?term=".$texte; 
				break;
			case "term2bitmap":
				$url .= $action."?term=".$texte; 
				break;
			default:
				;
			break;
		}
		//$url .= "&app_key=".KEY_CEPT."&app_id=".KEY_CEPT_APP_ID;
		
		$args = array(
		'app_key'=> KEY_CEPT,
		'app_id'=> KEY_CEPT_APP_ID
		);
		
		/* Execute the request 
		*/
		$body = $this->getUrlBodyContent($url, $args, false);		
		
		$result = json_decode($body);
		
		return $result;
		
		/* message d'erreur
		    "errorCode": 400,
		    "errorMessage": "at least 'term1' or 'term2' must be specified"
		 */
		
	}
	
    /**
     * sauveImage
     *
     * enregistre l'image du document
     * 
     * @param string $url
     * 
     * @return array
     */
	function sauveImage($idDoc, $url, $titre, $chemin){

    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	if(!$this->dbDT)$this->dbDT = new Model_DbTable_Flux_DocTypes($this->db);
    	if(!$this->dbUD)$this->dbUD = new Model_DbTable_flux_utidoc($this->db);
    	
    	//création du répertoire de stockage de l'image
		if(!is_dir($chemin)) @mkdir($chemin,0777,true);
    	
		//création des données du document
		$extension = pathinfo($url, PATHINFO_EXTENSION);
    	$type = $this->dbDT->getIdByExtension($extension);
    	$arrDoc['type']=$type;
		$path = $chemin."/".$this->idBase."_".$idDoc.".".$extension;
		$urlLocal = str_replace(ROOT_PATH, WEB_ROOT, $path);     	
    	$arrDoc['url']=$urlLocal;
    	$arrDoc['titre']=$titre;
    	$arrDoc['tronc']=$idDoc;
    	
    	//ajoute le document
    	$idDoc = $this->dbD->ajouter($arrDoc);

    	//création des liens avec le flux
    	$this->dbUD->ajouter(array("doc_id"=>$idDoc,"uti_id"=>$this->user));
    	    	    	
		if(!is_file($path)){
    		//enregistre l'image sur le disque local
			if(!$img = file_get_contents($url)) { 
			  echo 'pas de fichier : '.$url."<br/>";
			}else{
				if(!$f = fopen($path, 'w')) { 
				  echo 'Ouverture du fichier impossible '.$path."<br/>";
				}elseif (fwrite($f, $img) === FALSE) { 
				  echo 'Ecriture impossible '.$path."<br/>";
				}else{
					echo 'Image '.$titre.' enregistrée : <a href="'.$urlLocal.'">local</a> -> <a href="'.$url.'">Decitre</a><br/>';
				} 				
			}				
		}    	
	} 	

    /**
     * sauveUtiByImage
     *
     * enregistre un utilisateur à partir d'une liste d'image
     * les fichiers doivent avoir la forme nom_prenom.ext
     * 
     * @param string $rep
     * @param string $role
     * 
     */
	function sauveUtiByImage($rep, $role){

    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
    	if(!$this->dbUD)$this->dbUD = new Model_DbTable_flux_utidoc($this->db);
		
		if($dossier = opendir($rep)){
			while(false !== ($fichier = readdir($dossier))){
				if($fichier != '.' && $fichier != '..'){			
					$arrNom = explode("_", substr($fichier, 0, -4));
					$foaf = '<foaf:Person>
					   <foaf:name>'.$arrNom[1]." ".$arrNom[1].'</foaf:name>
					   <foaf:firstName>'.$arrNom[1].'</foaf:firstName>
					   <foaf:surname>'.$arrNom[0].'</foaf:surname>
					   <foaf:img>'.$rep."/".$fichier.'</foaf:img>
					</foaf:Person>';
					//ajoute l'utilisateur
					$idUti = $this->dbU->ajouter(array("login"=>$arrNom[1]." ".$arrNom[0],"note"=>$foaf,"role"=>$role));
					//ajoute le document
					$idDoc = $this->dbD->ajouter(array("url"=>$rep."/".$fichier,"type"=>"foaf:img"));
					//met en relation l'uti et le doc
					$this->dbUD->ajouter(array("uti_id"=>$idUti,"doc_id"=>$idDoc));
				}
			}
		}
	}	
	
	function objectToObject($instance, $className) {
	    //merci à http://stackoverflow.com/questions/3243900/convert-cast-an-stdclass-object-to-another-class
		return unserialize(sprintf(
	        'O:%d:"%s"%s',
	        strlen($className),
	        $className,
	        strstr(strstr(serialize($instance), '"'), ':')
	    ));
	}

	
	/**
	 * Removes invalid XML
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	function stripInvalidXml($value)
	{
	    $ret = "";
	    $current;
	    if (empty($value)) 
	    {
	        return $ret;
	    }
	
	    $length = strlen($value);
	    for ($i=0; $i < $length; $i++)
	    {
	        $current = ord($value{$i});
	        if (($current == 0x9) ||
	            ($current == 0xA) ||
	            ($current == 0xD) ||
	            (($current >= 0x20) && ($current <= 0xD7FF)) ||
	            (($current >= 0xE000) && ($current <= 0xFFFD)) ||
	            (($current >= 0x10000) && ($current <= 0x10FFFF)))
	        {
	            $ret .= chr($current);
	        }
	        else
	        {
	            $ret .= " ";
	        }
	    }
	    return $ret;
	}
	
}
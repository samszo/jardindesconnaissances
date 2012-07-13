<?php
/**
 * Classe qui gère les flux Gdata
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Diigo extends Flux_Site{

	const API_KEY = "0406bdcf70493382";
	const API_URI = "https://secure.diigo.com";
    const API_URL = '/api/v2/bookmarks';
    const RSS_GROUP_URL = 'http://groups.diigo.com/group/';
    const LUCENE_INDEX = '../data/diigo-index';
	
    /**
     * Zend_Service_Rest instance
     *
     * @var Zend_Service_Rest
     */
    protected $rest;
	
	public function __construct($login, $pwd, $idBase=false)
    {
    	parent::__construct($idBase);
		
    	$this->login = $login;
    	$this->pwd = $pwd;
    	
        $this->rest = new Zend_Rest_Client();
        $this->rest->getHttpClient()->setAuth($login, $pwd);
        $this->rest->setUri(self::API_URI);
    }
    
    function getRequest($params){

		$c = str_replace("::", "_", __METHOD__)."_".$this->getParamString($params); 
	   	$arr = $this->cache->load($c);
        if(!$arr){
	    	$params['key'] = self::API_KEY;
	    	$response = $this->rest->restGet(self::API_URL, $params);
	
	        if (!$response->isSuccessful()) {
	            throw new Zend_Service_Exception("Http client reported an error: '{$response->getMessage()}'");
	        }
	
	        $json = $response->getBody();
		    $arr = json_decode($json);
	        //le decode de zend est beaucoup trop long
	        //$arr = Zend_Json_Decoder::decode($json);
	        
			$this->cache->save($arr, $c);
        }        
        return $arr;
    	
    }
    
    function saveAll(){

    	$this->getUser(array("login"=>$this->login,"flux"=>"diigo"));
		//initialise les gestionnaires de base de données
    	if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
    	//initialise le moteur d'indexation
		if(!$this->lucene){
			$this->lucene = new Flux_Lucene($this->login, $this->pwd, $this->idBase, false, self::LUCENE_INDEX);
			$this->lucene->classUrl = $this;
			$this->lucene->index->optimize();	
		}
		
    	$i = 1;
		while ($i>0) {
    		$arr = $this->getRequest(array("user"=>$this->login,"count"=>100, "start"=>$i));
    		foreach ($arr as $item){
    			$this->saveItem($item);
    		}
    		$i = $i+count($arr);
    	}
    	$this->lucene->index->optimize();
    }

    function saveItem($i){

    	
		//TODO ajouter les db pour graine utigraine et grainedoc 
		//$this->getGraine(array("titre"=>"Diigo","class"=>"Flux_Diigo","url"=>"http://www.diigo.com/"));
		//TODO ajouter la utigraine 

    	//transforme l'objet en tableau dans le cas ou on n'utilise pas le json decode de Zen
    	$item['url'] = $i->url;
    	$item['title'] = $i->title;
    	$item['created_at'] = $i->created_at;
    	$item['annotations'] = $i->annotations;
    	$item['tags'] = $i->tags;
    	//sinon 
    	//$item = $i;
    	
		//ajouter un document correspondant au lien
		$idD = $this->dbD->ajouter(array("url"=>$item['url'],"titre"=>$item['title'],"tronc"=>0,"pubDate"=>$item['created_at']));
		//index le document
		//$this->lucene->addDoc($item['url']);
		
		//TODO ajouter la grainedoc 
		$i = 0;
		//traitement des annotations
		if(count($item['annotations'])>0){
			foreach ($item['annotations'] as $note){
				$j['content'] = $note->content;
				$j['created_at'] = $note->created_at;
				$this->saveContent($j, $idD);
			}
		}		
		//traitement des mots clefs
		if($item['tags']!=""){
			$arrTags = explode(",", $item['tags']);
			foreach ($arrTags as $tag){
				$this->saveTag($tag, $idD, 1, $item['created_at']);	    			
				$i++;
			}						
		}				
		//ajoute un lien entre l'utilisateur et le document avec un poids correspondant au nombre de tag
		$this->dbUD->ajouter(array("uti_id"=>$this->user, "doc_id"=>$idD, "poids"=>$i));										    
		//TODO ajouter la grainedoc 
		
    } 

	function saveContent($item, $idD){
	   	
		//enregistre le document
		$id = $this->dbD->ajouter(array("tronc"=>$idD,"data"=>$item['content'],"pubDate"=>$item['created_at']));
		
		//récupère les mot clefs
	   	$arrKW = $this->getKW($item['content']);
	   	//enregistre les mots clefs
	   	$i=0; 
	   	foreach ($arrKW as $kw=>$nb){
			$this->saveTag($kw, $id, $nb, $item['created_at']);
			$i++;	    			
	   	}
		$this->dbUD->ajouter(array("uti_id"=>$this->user, "doc_id"=>$id, "poids"=>$i));										    
	   	//$results = $dom->query('/html/body/table[2]/tr[2]/td[2]/p[1]/strong');		
	}
    
    
    function getGroupeRss($groupe){

    	$url = self::RSS_GROUP_URL."/".$groupe."/rss?count=100&start=0";
		Zend_Feed_Reader::setCache($this->cache);
		Zend_Feed_Reader::useHttpConditionalGet();
    	
   		$lastModifiedDateReceived = 'Wed, 08 Jul 2008 13:37:22 GMT';
		$feed = Zend_Feed_Reader::import($url, null, $lastModifiedDateReceived);    	
	    foreach ($feed as $entry) {
		    $edata = array(
		        'title'        => $entry->getTitle(),
		        'description'  => $entry->getDescription(),
		        'dateModified' => $entry->getDateModified(),
		        'authors'       => $entry->getAuthors(),
		        'link'         => $entry->getLink(),
		        'content'      => $entry->getContent()
		    );
		    $data['entries'][] = $edata;
		}    	    	
    }

    function saveArchiveRss($url){

		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
    	
		//TODO ajouter les db pour graine utigraine et grainedoc 
		
		$this->getUser(array("login"=>$this->login,"flux"=>"diigo"));
		$this->getGraine(array("titre"=>"Diigo","class"=>"Flux_Diigo","url"=>"http://www.diigo.com/"));
		//TODO ajouter la utigraine 
		
		$feed = Zend_Feed_Reader::import($url);    	

		foreach ($feed as $entry) {
		    $edata = array(
		        'title'        => $entry->getTitle(),
		        'description'  => $entry->getDescription(),
		        'dateModified' => $entry->getDateModified(),
		        'authors'       => $entry->getAuthors(),
		        'link'         => $entry->getLink(),
		        'content'      => $entry->getContent()
		    );
		    			
			//ajouter un document correspondant au lien
			$idD = $this->dbD->ajouter(array("url"=>$edata['link'],"titre"=>$edata['title'],"pubDate"=>$edata['dateModified']->get("c")));
			//TODO ajouter la grainedoc 
			$i = 0;
			
				//traitement du contenu
			$dom = new Zend_Dom_Query($edata['content']);	    
	    	
		   	//récupère le titre du document
			$results = $dom->query('/html/body/table[2]/tr[2]/td[2]/p[1]/strong');
			
			
			/*
				foreach ($vSS['feuilles'] as $nWS=>$vWS) {
					//ajouter un document correspondant à la feuille
					$idDF = $this->dbD->ajouter(array("url"=>$vWS['url'],"titre"=>$vWS['titre'],"tronc"=>$idD,"pubDate"=>$date->get("c")));
					//TODO ajouter la grainedoc 
					$j = 0;
					if($idDF>44){
						foreach ($vWS['values'] as $v) {
							if(isset($v['cest'])){
								$tag = $v['cest'];
								$poids = $v['_cokwr'];
								//on ajoute le tag
								$idT = $this->dbT->ajouter(array("code"=>$tag));
								//on ajoute le lien entre le tag et le doc avec le poids
								$this->dbTD->ajouter(array("tag_id"=>$idT, "doc_id"=>$idDF,"poids"=>$poids));
								//on ajoute le lein entre le tag l'utilisateur et le doc
								$this->dbUTD->ajouter(array("uti_id"=>$this->user, "tag_id"=>$idT, "doc_id"=>$idDF,"maj"=>$date->get("c")));						
								$j ++;
							}
						}
						$i += $j;					
						//ajoute un lien entre l'utilisateur et le document avec un poids correspondant au nombre de tag
						$this->dbUD->ajouter(array("uti_id"=>$this->user, "doc_id"=>$idDF,"poids"=>$j));													
					}
				}
			*/
			
			//ajoute un lien entre l'utilisateur et le document avec un poids correspondant au nombre de tag
			$this->dbUD->ajouter(array("uti_id"=>$this->user, "doc_id"=>$idD,"poids"=>$i));								
			
		}		
		    
		    
    }
     
}
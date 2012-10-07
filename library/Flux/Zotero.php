<?php
/**
 * Classe qui gère les flux Zotero
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Zotero extends Flux_Site{

	const API_KEY = "EDRQ98DxRu1W3jKmhEPVxCEN";
	const API_URI = "https://api.zotero.org";
	var $libraryID = '13594';
	var $url = '';
	
	public function __construct($login, $idBase="flux_zotero")
    {
    	parent::__construct($idBase);
    	
    	$this->login = $login;
    }
		
    function getRequest($url, $params){

		$c = str_replace("::", "_", __METHOD__)."_".$this->getParamString($params); 
	   	$flux = $this->cache->load($c);
        if(!$flux){
	    	$params['key'] = self::API_KEY;
	    	$params['content'] = "json";
	    	$params['order'] = "dateAdded";
	    	$params['sort'] = "asc";
	    	$params['format'] = "atom";
	    	$atom = $this->getUrlBodyContent($url, $params);
	    	$flux = Zend_Feed::importString($atom);	        
			$this->cache->save($flux, $c);
        }        
        return $flux;
    	
    }	

    function saveAll(){

    	$this->getUser(array("login"=>$this->login,"flux"=>"zotero"));
		//initialise les gestionnaires de base de données
    	if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
    	$i = 1;
    	$this->url = self::API_URI."/users/".$this->libraryID."/items";
		while ($i>0) {
    		$flux = $this->getRequest($this->url,array("limit"=>99, "start"=>$i));
    		foreach ($flux as $item){
    			$this->saveItem($item);
    		}
    		$i = $i+count($flux);
		}
    	//$this->lucene->index->optimize();
    }

    function saveItem($i){

    	
		//TODO ajouter les db pour graine utigraine et grainedoc 
		//$this->getGraine(array("titre"=>"Diigo","class"=>"Flux_Diigo","url"=>"http://www.diigo.com/"));
		//TODO ajouter la utigraine 

    	$item['url'] = $i->id();
    	$item['titre'] = $i->title();
    	$item['tronc'] = 0;
    	$item['pubDate'] = $i->updated();
    	$item['note'] = $i->content();
		$content = json_decode($item['note']);
    	$item['type'] = $content->itemType;
    	
    	//gestion des notes
    	if($item['type']=="note"){
    		$item['note'] = $content->note;
    		//récupération du parent
    		$lienParent = $i->link("up");
    		$lienParent = str_replace("?content=json", "", $lienParent);
    		$idParent = substr($lienParent, strlen($this->url)+1);
    		$docParent = $this->dbD->findLikeUrl($idParent);
    		if($docParent[0]['doc_id'])
	    		$item['tronc'] = $docParent[0]['doc_id'];
	    	else
	    		$item['tronc'] = $idParent;
    	}
    	
		
		//ajouter un document correspondant au lien
		$idD = $this->dbD->ajouter($item);
		//index le document
		//$this->lucene->addDoc($item['url']);
		
		//TODO ajouter la grainedoc 

		//traitement des auteurs
		$arrAuteurs = $content->creators;
		foreach ($arrAuteurs as $auteur){
			$idU = $this->getUser(array("login"=>$auteur->firstName." ".$auteur->lastName,"flux"=>"zotero","role"=>$auteur->creatorType),true);
			$this->dbUD->ajouter(array("uti_id"=>$idU, "doc_id"=>$idD, "poids"=>0));										    
		}

		//traitement des mots clefs
		$arrTags = $content->tags;
		foreach ($arrTags as $tag){
			$this->saveTag($tag->tag, $idD, 1, $item['pubDate']);	    			
			$i++;
		}						

		//ajoute un lien entre l'utilisateur et le document avec un poids correspondant au nombre de tag
		$this->dbUD->ajouter(array("uti_id"=>$this->user, "doc_id"=>$idD, "poids"=>$i));										    
		//TODO ajouter la grainedoc 
		
    } 

    /**
     * sauveAmazonInfo
     *
     * enregistre les information d'amazon pour les document avec un ISBN
	 * 
     * 
     */
	function sauveAmazonInfo(){

    	if(!$this->dbD)$this->dbD = new Model_DbTable_flux_doc($this->db);
    	
    	$fA = new Flux_Amazon($this->idBase);
    	
    	//initialise l'utilisateur
    	$fA->getUser(array("login"=>"Flux_Amazon"));
    	
    	//récupère les documents
		$rs = $this->dbD->findByTronc("0");
    	
		foreach ($rs as $r) {
			if($r['type']=="book"){
				//vérifie si les infos amazon sont déjà enregistrées
				$doc = $this->dbD->findFiltre("tronc=".$r["doc_id"]." AND type=39 AND note !='' AND data != ''", "doc_id");
				if(count($doc)==0){
					//récupère la note json
					$obj = json_decode($r['note']);
					//création de la requête amazon
					$search = array('SearchIndex' => 'Books');
					if(isset($obj->title))$search['Title'] = $obj->title;
					//if(isset($obj->creators[0]->firstName))$search['Author'] = $obj->creators[0]->firstName." ".$obj->creators[0]->lastName;
					//if(isset($obj->creators[0]->name))$search['Author'] = $obj->creators[0]->name;
					//if(isset($obj->publisher))$search['Publisher'] = $obj->publisher;
					$fA->sauveSearch($search, $r["doc_id"]);
					echo "<br/>".$r["doc_id"]." - ".$obj->title."<br/>";
				}
			}
		}
    	
	}    
    
}
<?php
/**
 * Classe qui gère les flux venant du site Amazon
 * http://www.amazon.com
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Amazon extends Flux_Site{
	
	var $service;
	
	public function __construct($idBase=false)
    {
    	parent::__construct($idBase);
    	$this->service = $amazon = new Zend_Service_Amazon(KEY_AMAZON, 'US', AMAZON_PWD);
    	
    }
	    

    /**
     * sauveActuLivre
     *
     * enregistre les documents decitre tagger actulivre
	 * 
     * 
     */
	function sauveActuLivre(){

    	if(!$this->dbTD)$this->dbTD = new Model_DbTable_flux_tagdoc($this->db);
    	if(!$this->dbT)$this->dbT = new Model_DbTable_flux_tag($this->db);
    	
    	//initialise l'utilisateur
    	$this->getUser(array("login"=>"Flux_Amazon"));
    	
    	//récupère l'identifiant du tag
    	$tag = $this->dbT->findByCode("actulivre");
    	
		$rs = $this->dbTD->findByTagId($tag["tag_id"], false);
    	
		foreach ($rs as $r) {
			if(substr($r['url'],0,18)=="http://www.amazon."){
				$this->sauveInfoDoc($r);
			}
		}
    	
	}
	 
    /**
     * sauveInfoDoc
     *
     * sauvegarde les informations du document
     * 
     * @param array $docInfos
     * 
     * @return array
     */
    function sauveInfoDoc($doc) {
	   	
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    			
		echo $doc["titre"].' : <a href="'.$doc["url"].'">'.$doc["url"].'</a><br/>';
		
		if($doc['note']==""){
	    	//récupère le body de l'url
	    	if(!isset($doc["data"]) || $doc["data"]==""){
		    	$html = $this->getUrlBodyContent($doc["url"]);
				$this->dbD->edit($doc["doc_id"], array("data"=>$html));
				$doc["data"]=$html;				
	    	}
			
			//récupère le titre du document
			$dom = new Zend_Dom_Query($doc["data"]);	    
			$results = $dom->query('//*[@id="ASIN"]');
			$asin = "";
			foreach ($results as $result) {
			    $asin = $result->getAttribute("value");
			}	    
			if(!$asin)return;	
			//recherche l'item chez amazon
			$item = $this->service->itemLookup($asin, array('AssociateTag'=>AMAZON_AT, 'ResponseGroup'=>'Large'));
			//prépare l'item pour l'enregistrement
			if(isset($item->LargeImage->Url)){
		    	$item->SmallImage = $item->SmallImage->Url->getUri();
		    	$item->LargeImage = $item->LargeImage->Url->getUri();
		    	$item->MediumImage = $item->MediumImage->Url->getUri();
			}
	    	//encode l'item
	    	$jsnote = json_encode($item);
	    	//met à jour le document
			$this->dbD->edit($doc["doc_id"], array("note"=>$jsnote));				
		}else{
			$item = json_decode($doc['note']);
		}
		
		//récupère l'image du livre
		if($item->LargeImage) $this->sauveImage($doc["doc_id"], $item->LargeImage, $item->Title, ROOT_PATH.'/data/Amazon/img');
						
		return $doc;		 
	}	
	
    /**
     * sauveSearch
     *
     * sauvegarde les informations des documents trouvés lors d'une recherche
     * paramètre de recherche cf. http://docs.amazonwebservices.com/AWSECommerceService/2011-08-01/DG/FRSearchIndexParamForItemsearch.html
     * 
     * @param array $search
     * @param int $idTronc
     * 
     */
    function sauveSearch($search, $idTronc=-1) {
	   	
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	
    	if(!isset($search['ResponseGroup'])) $search['ResponseGroup'] = 'Large';
    	$search['AssociateTag'] = AMAZON_AT;    
    	
		$results = $this->service->itemSearch($search);
		
    	$first=true;
		foreach ($results as $item) {
			if($first){
				//prépare l'item pour l'enregistrement
				if(isset($item->LargeImage->Url)){
			    	$item->SmallImage = $item->SmallImage->Url->getUri();
			    	$item->LargeImage = $item->LargeImage->Url->getUri();
			    	$item->MediumImage = $item->MediumImage->Url->getUri();
				}
		    	//encode l'item
		    	$jsnote = json_encode($item);
			    //création d'un document
			    $arrDoc = array("url"=>$item->DetailPageURL, "titre"=>$item->Title, "note"=>$jsnote, "tronc"=>$idTronc, "type"=>39);
				$idDoc = $this->dbD->ajouter($arrDoc);
				$arrDoc["doc_id"] = $idDoc;
				$this->sauveInfoDoc($arrDoc);
				$first=false;
			}				
    	}
	}

	
    /**
     * getTagDoc
     *
     * sauvegarde les tags d'un document Amazon
     * 
     * @param array $docInfos
     * 
     * @return array
     */
    function getTagDoc($doc) {
	   	
		echo $doc["data"];
		
    }	
}
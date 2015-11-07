<?php
/**
 * Classe qui gère les flux venant du site decitre
 * http://www.decitre.fr
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Decitre extends Flux_Site{
	
	public function __construct($idBase=false)
    {
    	parent::__construct($idBase);
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
    	$this->getUser(array("login"=>"Flux_Decitre"));
    	
    	//récupère l'identifiant du tag
    	$tag = $this->dbT->findByCode("actulivre");
    	
		$rs = $this->dbTD->findByTagId($tag["tag_id"], false);
    	
		foreach ($rs as $r) {
			if(substr($r['url'],0,21)=="http://www.decitre.fr"){
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
    	
    	//récupère le body de l'url
    	if($doc["data"]==""){
	    	$html = $this->getUrlBodyContent($doc["url"]);
			$this->dbD->edit($doc["doc_id"], array("data"=>$html));
			$doc["data"]=$html;				
    	}else{
    		return "";
    	}
		$dom = new Zend_Dom_Query($doc["data"]);	    
		
		echo $doc["titre"].' : <a href="'.$doc["url"].'">'.$doc["url"].'</a><br/>';
		
		//récupère le titre du document
		$results = $dom->query('/html/body/div[1]/div/div[3]/div[1]/div[1]/div[3]/div/div[1]/div[1]/div[1]/span/h1');
		$titre = "";
		foreach ($results as $result) {
		    $titre = $result->nodeValue;
		}	    
		
		//récupère l'image du livre 
		$results = $dom->query('//*[@id="image"]');
		$img = "";
		foreach ($results as $result) {
		    $img = $result->getAttribute("src");
		}
		$this->sauveImage($doc["doc_id"], $img, $titre, ROOT_PATH.'/data/Decitre/img');
				
		//récupère les infos techniques du document 
		$results = $dom->query('/html/body/div[1]/div/div[3]/div[1]/div[1]/div[3]/div/div[1]/div[4]/ul/li');
		$note = "";
		foreach ($results as $result) {
		    //récupère le titre de l'info
		    $t = $result->nodeValue;
		    //récupère la valeur de l'info
			$v = $result->nodeValue;
			//ajoute la note
			$note[$t]=$v;
		}	    
    	$jsnote = json_encode($note);
    	$arrDoc['note']=$jsnote;
    	//met à jour le document
		$this->dbD->edit($doc["doc_id"], array("note"=>$arrDoc['note']));				
    	
		//récupère les catégories du document 
		$results = $dom->query('/html/body/div[1]/div/div[3]/div[1]/div[1]/div[4]/div/div[2]/ul/li');
		$d = new Zend_Date();
		foreach ($results as $result) {
		    //récupère la catégorie
		    $tag = $result->nodeValue;
    		$this->saveTag($tag, $doc["doc_id"], 1, $d->get("c"), $this->user);
		}	    		
		
		return $doc;		 
	}	

	
}
<?php

/**
 * Classe qui gère les flux des Tweetpalette
 *
 * @copyright  2012 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Tweetpalette extends Flux_Site{

    /**
     * Construction du gestionnaire de flux.
     *
     * @param string $idBase
     *
     * 
     */
	public function __construct($idBase=false)
    {
    	parent::__construct($idBase);
    	    	
    }
	
    /**
     * Enregistre les informations du flux dans la base
     *
     * @param string $uti
     * @param string $url
     * @param string $event
     * @param array $sem
     *
     * 
     */
    function saveTweetSem($uti, $url, $event, $sems){

		//création des tables
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
    	if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		if(!$this->dbIEML)$this->dbIEML = new Model_DbTable_Flux_Ieml($this->db);
		if(!$this->dbUIEML)$this->dbUIEML = new Model_DbTable_Flux_UtiIeml($this->db);
		if(!$this->dbTrad)$this->dbTrad = new Model_DbTable_Flux_Trad($this->db);
		
		$this->user = $this->dbU->ajouter(array("login"=>$uti,"flux"=>"Flux_Tweetpalette"));
		$idDoc = $this->dbD->ajouter(array("url"=>$url,"titre"=>$event));
		$date = new Zend_Date();
		
		//ajoute ou récupère le document de fond
		$idDocFond = $this->dbD->ajouter(array("url"=>$sems[0]["urlFond"],"titre"=>"Palette de tweet"));
		//ajoute ou récupère le clic sur le fond
		$idDocFondClic = $this->dbD->ajouter(array("tronc"=>$idDocFond."_".$idDoc,"titre"=>"clic fond"
			,"data"=>"{x:".$sems[0]["x"].",y:".$sems[0]["y"]."}", "poids"=>1, "maj"=>$date->get("c")));
		
        foreach ($sems as $sem) {
        	if($sem["lib"]){
	        	//sauvegarde le tag pour le document
				$idT = $this->saveTag($sem["lib"], $idDoc, 1, $date->get("c"));
	        	//sauvegarde le tag pour le fond
				$this->saveTag($sem["lib"], $idDocFondClic, 1, $date->get("c"));
				//sauvegarde la sémantique du tag
				$this->saveIEML($sem["ieml"], $this->user, $idT);
        	}
        }
	}	

    /**
     * récupère les clics sur une palette
     *
     * @param string $uti
     * @param string $url
     * @param string $urlFond
     * @param boolean $showAll
     * 
     * 
     * return array
     */
    function getPaletteClics($uti, $url, $urlFond, $showAll){
		//création des tables
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);

		$Doc = $this->dbD->findByUrl($url);
		$DocFond = $this->dbD->findByUrl($urlFond);

		//le format du json correspond à heatmap.js
		$dc = "";
		$max = 0;
		if($showAll){
    		//récupère tous les clics sur le fond pour un événement
    		$DocsClic = $this->dbD->findByTronc($DocFond[0]["doc_id"]."_".$Doc[0]["doc_id"]);
    		foreach ($DocsClic as $d) {
    			$coor = substr($d["data"],0,-1);
    			$dc .= $coor.",count:".$d["poids"]."},";
    			if($max<$d["poids"])$max=$d["poids"];
    		}    		
		}else{
    		$dc = "";			
    	}
    	$dc = "{max: ".$max.", data: [".substr($dc,0,-1)."]}";
    	return $dc;
    }
	
}
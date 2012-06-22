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
     * @param integer $uti
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
		
		$this->user = $uti;
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
     * @param string $event
     * @param boolean $filtrer
     * 
     * 
     * return array
     */
    function getPaletteClics($uti, $url, $urlFond, $event, $filtrer){
    	
		//création des tables
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		
		$DocFond = $this->dbD->findByUrl($urlFond);

		$dc = "";
		$max = 0;
		if($filtrer==="false"){
    		//récupère tous les clics sur le fond pour un événement
    		$DocsClic = $this->dbD->findLikeTronc($DocFond[0]["doc_id"]."_");
		}else{
			//récupère les données suivant les paramètres";
			if($uti!="no"){
				$Uti = $this->dbU->findByLogin($uti);
				$idUti = $Uti["uti_id"];
			}else{
				$idUti = false;
			}
			if($url!="no"){
				$Doc = $this->dbD->findByUrl($url);
				$DocsClic = $this->dbD->findByUtiTronc($idUti, $DocFond[0]["doc_id"]."_".$Doc[0]["doc_id"]);						
			}elseif($event!="no"){
				$Doc = $this->dbD->findByTitre($event);
				$DocsClic = $this->dbD->findByUtiTronc($idUti, $DocFond[0]["doc_id"]."_".$Doc[0]["doc_id"]);
			}else{
				$DocsClic = $this->dbD->findByUtiTronc($idUti, $DocFond[0]["doc_id"]."_", true);							
			}
    	}
		//construction du format json correspondant à heatmap.js
		foreach ($DocsClic as $d) {
    		$coor = substr($d["data"],0,-1);
    		$dc .= $coor.",count:".$d["poids"]."},";
    		if($max<$d["poids"])$max=$d["poids"];
    	}    		
    	$dc = "{max: ".$max.", data: [".substr($dc,0,-1)."]}";
    	return $dc;
    }
    
    /**
     * récupère les inputs user, event et tag
     * 
     * return array
     */
    function getInput(){
		//création des tables
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		
		$events = $this->dbD->findFiltre("titre != 'clic fond' AND titre != 'Palette de tweet'", array("titre","url"));
		$utis = $this->dbU->getAll(array("login"));
		
    	return array("events"=>$events,"utis"=>$utis);
    }    
	
}
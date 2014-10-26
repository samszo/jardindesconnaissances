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
     * @param integer $exi
     * @param string $url
     * @param string $event
     * @param array $sem
     *
     * 
     */
    function saveTweetSem($uti, $exi, $url, $event, $sems){

		//création des tables
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
		if(!$this->dbUU)$this->dbUU = new Model_DbTable_Flux_UtiUti($this->db);
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
		
		$idExi = $this->dbU->ajouter(array("login"=>$exi));
		
		//ajoute ou récupère le document de fond
		$idDocFond = $this->dbD->ajouter(array("url"=>$sems[0]["urlFond"],"type"=>"palette"));
		//ajoute ou récupère le clic sur le fond
		$idDocFondClic = $this->dbD->ajouter(array("tronc"=>$idDocFond."_".$idDoc,"titre"=>"clic fond"
			,"data"=>"{x:".$sems[0]["x"].",y:".$sems[0]["y"]."}", "poids"=>1, "maj"=>$date->get("c")));
		foreach ($sems as $sem) {
        	if($sem["lib"]){
        		//pour l'utilisateur
	        	//sauvegarde le tag pour le document 
				$idT = $this->saveTag($sem["lib"], $idDoc, 1, $date->get("c"));
	        	//sauvegarde le tag pour le fond
				$this->saveTag($sem["lib"], $idDocFondClic, 1, $date->get("c"));
				//sauvegarde la sémantique du tag
				$this->saveIEML($sem["ieml"], $this->user, $idT);

        		//pour l'existence
	        	//sauvegarde le tag pour le document 
				$idT = $this->saveTag($sem["lib"], $idDoc, 1, $date->get("c"), $idExi);
	        	//sauvegarde le tag pour le fond
				$this->saveTag($sem["lib"], $idDocFondClic, 1, $date->get("c"), $idExi);
				//sauvegarde la sémantique du tag
				$this->saveIEML($sem["ieml"], $idExi, $idT);
				
				//enregistre le lien entre l'utilisateur et le l'existence
				$this->dbUU->ajouter(array("uti_id_src"=>$this->user, "uti_id_dst"=>$idExi, "eval"=>$idT, "maj"=> new Zend_Db_Expr('NOW()')),false);
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
    	return $this->getHeatmapClic($DocsClic);
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
		
		$events = $this->dbD->findFiltre("titre != 'clic fond' AND type != 'palette' AND type != 'foaf:img'", array("titre","url"));
		$utis = $this->dbU->getAll(array("login"));
		
    	return array("events"=>$events,"utis"=>$utis);
    }    
	
    /**
     * récupère les clics sur les documents
     * @param int $idFond
     * 
     * return array
     */
    function getFondStat($idFond){
		//création des tables
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		$query = $this->dbD->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("d" => "flux_doc"), array("doc_id", "poids", "data"))                           
            ->joinInner(array('dU' => 'flux_doc'), "dU.doc_id = SUBSTRING(d.tronc,LENGTH('".$idFond."_')+1)",array("idU"=>"doc_id","urlU"=>"url"))
            ->joinInner(array('dT' => 'flux_doc'), "dT.doc_id = SUBSTRING(dU.url,INSTR(dU.url, 'id=')+3)",array("idT"=>"doc_id","urlT"=>"url","titreT"=>"titre"))
            ->joinInner(array('utd' => 'flux_utitagdoc'), "utd.doc_id = dU.doc_id",array())
            ->joinInner(array('u' => 'flux_uti'), "u.uti_id = utd.uti_id",array("login","uti_id"))
            ->joinInner(array('udFkr' => 'flux_utidoc'), "udFkr.doc_id = dT.doc_id",array())
            ->joinInner(array('uFkr' => 'flux_uti'), "uFkr.uti_id = udFkr.uti_id",array("utiFkr"=>"GROUP_CONCAT(DISTINCT uFkr.login)"))
            ->where("(d.tronc LIKE '%".$idFond."_%')")
            ->group("d.data");
		
        return $this->dbD->fetchAll($query)->toArray(); 
				
    }    
    
    
    
}
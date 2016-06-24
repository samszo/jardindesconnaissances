<?php

/**
 * Classe qui gère les méthodes de GAPAII
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Gapaii extends Flux_Site{
	
	var $idDocRoot;
	
	
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
    	    	
	    	//on récupère la racine des documents
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);	    	
	    	$this->idDocRoot = $this->dbD->ajouter(array("titre"=>"gapaï"));
	    	
    }
	
    /**
     * Enregistre les informations d'évaluation sémantique pour un document et un utilisateur
     *
     * @param string 	$idBase
     * @param integer 	$idDoc
     * @param integer 	$idUti
     * @param array 	$sem
     *
     * 
     */
    function saveSemEval($idBase, $idDoc, $idUti, $sem){

		//création des tables
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		if(!$this->dbIEML)$this->dbIEML = new Model_DbTable_Flux_Ieml($this->db);
		if(!$this->dbUIEML)$this->dbUIEML = new Model_DbTable_Flux_UtiIeml($this->db);
		if(!$this->dbTrad)$this->dbTrad = new Model_DbTable_Flux_Trad($this->db);
		
		$date = new Zend_Date();
		
		//ajoute ou récupère le clic sur le fond
		$idDocClic = $this->dbD->ajouter(array("tronc"=>$idDoc,"titre"=>$sem["titre"]
			,"data"=> json_encode($sem) , "maj"=>$date->get("c")),false);
		foreach ($sem["sems"] as $sem) {
        	if($sem["lib"]){
	        	//sauvegarde le tag pour le document et l'utilisateur
	        	//pas nécessaire car on peut le retrouver par le tronc
				//$idT = $this->saveTag($sem["lib"], $idDoc, 1, $date->get("c"), $idUti);
        		//sauvegarde le tag pour le clic sur le document et l'utilisateur
				$idT = $this->saveTag($sem["lib"], $idDocClic, $sem["degre"], $date->get("c"), $idUti, false);
				//sauvegarde la sémantique du tag
				$this->saveIEML($sem["ieml"], $idUti, $idT);
        	}
        }
	}	

    /**
     * Récupère les informations d'évaluation sémantique pour une base un document et un utilisateur
     *
     * @param integer 	$idDoc
     * @param integer 	$idUti
     * @param integer 	$idTag
     *
     * @return array
     */
    function getEval($idDoc, $idUti, $idTag){

		//création des tables
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
				
		//récupère les évaluations
		$evals = $this->dbD->getHistoEval();
		//calcul les données pour heatmap
		$arrHM = $this->getHeatmapClic($evals, true);
		$result["histo"] = $evals;
		$result["hm"] = $arrHM;
		
		return $result;
	}	
	
    /**
     * Enregistre un texte génératif avec sa sémantique
     *
     * @param string 	$idBase
     * @param integer 	$datat
     * @param integer 	$idUti
     *
     * @return integer 
     */
	function saveGen($idBase, $data, $idUti){

		//création des tables
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		/* nouvelle version sans UtiTagDoc
		 if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		*/
		$date = new Zend_Date();
				
		//ajoute ou récupère le clic sur le fond
		$idDoc = $this->dbD->ajouter(array("tronc"=>$data["idOeu"]."_".$data["idCpt"],"titre"=>$data["titre"]
			,"data"=>$data["txt"], "maj"=>$date->get("c"), "data"=>$data["data"], "parent"=>	$this->idDocRoot),true,true);
		foreach ($data["sems"] as $sem) {
	        	if($sem["lib"]){
	        		//sauvegarde la sémantique du tag pour le clic document et l'utilisateur
				$idT = $this->saveTag($sem["lib"], $idDoc, 1, $date->get("c"), $idUti);
				//sauvegarde la sémantique du tag
				$this->saveIEML($sem["ieml"], $idUti, $idT);
	        	}
        }
        return $idDoc;
	}	
	
 	/**
     * Récupère les réponses aux questions
     *
     * @param integer 	$idDoc
     * @param integer 	$idUti
     * @param integer 	$idTag
     *
     * @return array
     */
    function getRepQuest($idDoc, $idUti, $idTag){

		//création des tables
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
				
		//récupère les évaluations
		$evals = $this->dbD->getHistoEval();
		//calcul les données pour heatmap
		$arrHM = $this->getHeatmapClic($evals, true);
		$result["histo"] = $evals;
		$result["hm"] = $arrHM;
		
		return $result;
	}	    
    
}
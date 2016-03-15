<?php
/**
 * Classe qui gère les flux TEI
 *
 * @copyright  2016 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 * REFERENCES
 * http://www.tei-c.org/release/doc/tei-p5-exemplars/html/tei_lite_fr.doc.html
 * 
 * THANKS
 */
class Flux_TEI extends Flux_Site{

	var $idDocRoot;
	var $idMonade;
	
    /**
     * Constructeur de la classe
     *
     * @param  string $idBase
     * 
     */
	public function __construct($idBase=false, $bTrace=false)
    {
    		parent::__construct($idBase, $bTrace);    	

    		//on récupère la racine des documents
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);	    	
		if(!$this->dbM)$this->dbM = new Model_DbTable_Flux_Monade($this->db);	    	
		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
	    	
    }

     
}
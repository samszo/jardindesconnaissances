<?php
/**
 * Flux_TEI
 * Classe qui gère les flux TEI
 * REFERENCES
 * http://www.tei-c.org/release/doc/tei-p5-exemplars/html/tei_lite_fr.doc.html
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\LinkedOpenData
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */class Flux_TEI extends Flux_Site{

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
<?php
/**
 * Flux_Cnrtl
 * 
 * Classe qui gère les flux de la cnrtl
 * http://www.cnrtl.fr/
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Scraping
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Cnrtl extends Flux_Site{

	var $formatResponse = "json";
	var $baseUrl = 'http://www.cnrtl.fr';
	var $rs;
	var $doublons;
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

     /**
     * Récupère les synonymes d'un mot 
     * exemple : http://www.cnrtl.fr/synonymie/pomme
     *
     * @param  string $m
     *
     * @return array
     */
    public function getSynonyme($m)
    {
	    	$searchUrl = $this->baseUrl."/synonymie/".urlencode($m);
    		$html = $this->getUrlBodyContent($searchUrl,false,true);
    		//echo $html;
		$dom = new Zend_Dom_Query($html);	    
		//récupère le tag
		$xPath = '//*[@class="syno_format"]/a';
		$results = $dom->queryXpath($xPath);
		$this->rs = array();
		foreach ($results as $result) {
			//récupère le tag
			$a = array("tag"=>$result->nodeValue,"url"=>$result->getAttribute('href'));
			//récupère le poids à partir du tag
			$p = $result->parentNode->parentNode->lastChild->firstChild;
			$a["poids"]=$p->getAttribute('width');
			$this->rs[]=$a;
		}	    
    	    	return $this->rs;
    }

     /**
     * Récupère les synonymes d'un mot 
     * exemple : http://www.cnrtl.fr/antonymie/pomme
     *
     * @param  string $m
     *
     * @return array
     */
    public function getAntonyme($m)
    {
	    	$searchUrl = $this->baseUrl."/antonymie/".urlencode($m);
    		$html = $this->getUrlBodyContent($searchUrl,false,true);
    		echo $html;
		$dom = new Zend_Dom_Query($html);	    
		//récupère le tag
		$xPath = '//*[@class="anto_format"]/a';
		$results = $dom->queryXpath($xPath);
		$this->rs = array();
		foreach ($results as $result) {
			//récupère le tag
			$a = array("tag"=>$result->nodeValue,"url"=>$result->getAttribute('href'));
			//récupère le poids à partir du tag
			$p = $result->parentNode->parentNode->lastChild->firstChild;
			$a["poids"]=$p->getAttribute('width');
			$this->rs[]=$a;
		}	    
    	    	return $this->rs;
    }
    
}
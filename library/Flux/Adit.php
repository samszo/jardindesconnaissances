<?php
/**
 * Classe qui gère les flux venant du site 
 * http://www.bulletins-electroniques.com
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Adit extends Flux_Site{
		
	var $urlAllBE = "http://www.bulletins-electroniques.com/cgi/htsearch?config=bulletins_electroniques_51_slave;method=and;format=long;sort=revtitle;keywords=4444444001;page=";
		
	
	public function __construct($idBase=false)
    {
    	parent::__construct($idBase);
    	
    }
	    

    /**
     * getAllBE
     *
     * enregistre tous les bulletins électronique
     *
     *
     */
    function getAllBE(){

    	$this->bTrace = true; // pour afficher les traces
    	$this->temps_debut = microtime(true);
    	$this->trace("DEBUT ".__METHOD__);
    	   
    	//initialise l'utilisateur
    	$this->getUser(array("login"=>"Flux_Addit"));
    	
    	//la récupération des buulletins se fait à partir des pages de résultat
    	for ($i = 1; $i < 1352; $i++) {
    		$this->getPageBE($i);
    	}

    	$this->trace("FIN ".__METHOD__);
    	 
	}
	 
	/**
	 * getPageBE
	 *
	 * enregistre la page d'un bulletin électronique
	 *
     * @param int $numPage
	 *
	 */
	function getPageBE($numPage){
    	$this->trace("DEBUT ".__METHOD__);
		
    	//récupère la page
		$html = $this->getUrlBodyContent($this->urlAllBE.$numPage);
		//récupère les urls de la page
		$dom = new Zend_Dom_Query($html);
		$results = $dom->query('//p[@class="style58"]/span[@class="style85"]/a');
		foreach ($results as $r) {
			$url = $r->getAttribute('href');
			$this->sauveInfoBE($url);
		}		
		
		$this->trace("FIN ".__METHOD__);
		
	}
	
	/**
     * sauveInfoBE
     *
     * sauvegarde les informations d'un Bulletin électronique
     * 
     * @param string $url
     * 
     */
    function sauveInfoBE($url) {
	   	
    	$this->trace("DEBUT ".__METHOD__);
    	$this->trace("url ".$url);
    	     	
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		if(!$this->dbUU)$this->dbUU = new Model_DbTable_Flux_UtiUti($this->db);
		
		$html = $this->getUrlBodyContent($url);
		$idDoc = $this->dbD->ajouter(array("data"=>$html, "url"=>$url));
		
		//charge le document
		$dom = new Zend_Dom_Query($html);
		
		//récupère les infos
		$results = $dom->query('.style32');
		foreach ($results as $r) {
			$pays = $r->nodeValue;
		}
		$results = $dom->query('//*[@id="LayoutTable"]/table/tbody/tr[7]/td/table/tbody/tr[1]/td[3]/p/span[3]');
        foreach ($results as $r) {
			$date = $r->nodeValue;
		}
		
		$results = $dom->query('//*[@id="LayoutTable"]/table/tbody/tr[7]/td/table/tbody/tr[3]/td[1]/p[1]/span[1]');
		foreach ($results as $r) {
			$domaine = $r->nodeValue;
		}

		$results = $dom->query('//*[@id="LayoutTable"]/table/tbody/tr[7]/td/table/tbody/tr[3]/td[1]/p[1]/span[2]');
        foreach ($results as $r) {
			$titre = $r->nodeValue;
		}
				
		$results = $dom->query('//*[@id="LayoutTable"]/table/tbody/tr[7]/td/table/tbody/tr[3]/td[1]/p[3]/span');
        foreach ($results as $r) {
			$contenu = $r->nodeValue;
		}
		
		$results = $dom->query('//*[@id="LayoutTable"]/table/tbody/tr[7]/td/table/tbody/tr[7]/td[2]/p/span');
        foreach ($results as $r) {
			$source = $r->nodeValue;
		}
		
		$results = $dom->query('//*[@id="LayoutTable"]/table/tbody/tr[7]/td/table/tbody/tr[8]/td[2]/p/span');
        foreach ($results as $r) {
			$auteurs = $r->nodeValue;
		}
		
		$results = $dom->query('//*[@id="LayoutTable"]/table/tbody/tr[7]/td/table/tbody/tr[6]/td[2]/p/span');
        foreach ($results as $r) {
			$contact = $r->nodeValue;
		}
		
		$this->dbD->edit($idDoc, array("data"=>$html));
								
		$this->trace("FIN ".__METHOD__);
		
    }	
	
}
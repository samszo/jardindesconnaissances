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
    	for ($i = 1; $i < 1380; $i++) {
    	//for ($i = 1 ; $i < 2; $i++) {
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
    	$this->trace("numPage ".$numPage);
    	
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
     * @param string 	$url
     * @param array		$dataDoc
     * 
     */
    function sauveInfoBE($url, $dataDoc=false) {
	   	
    	$this->trace("DEBUT ".__METHOD__);
    	$this->trace("url ".$url);
    	     	
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		if(!$this->dbUU)$this->dbUU = new Model_DbTable_Flux_UtiUti($this->db);
		
		if(!$dataDoc){
			$html = $this->getUrlBodyContent($url);
			$idDoc = $this->dbD->ajouter(array("data"=>$html, "url"=>$url));
		}else{
			if(!$dataDoc['data']){
				$this->trace("FIN DATA VIDE ".__METHOD__);
				return "";
			}
			$html = $dataDoc['data'];
			$idDoc = $dataDoc['doc_id'];
		}
				
		//charge le document
		$dom = new Zend_Dom_Query($html);
		
		//récupère les infos
		$results = $dom->query('//tr[1]/td[3]/p/span[@class="style42"]');
        foreach ($results as $r) {
        	//attention aux &nbsp
			$date = str_replace(' ','',html_entity_decode($r->nodeValue));
			$date = substr($r->nodeValue, 4);
			$sqlDate = new Zend_Db_Expr("STR_TO_DATE('".$date." 00:00:00', '%e/%c/%Y %H:%i:%s')");
		}
		$results = $dom->query('//tr[3]/td[1]/p[@class="style96"]/span[@class="style17"]');
        foreach ($results as $r) {
			$titre = $r->nodeValue;
		}
		if(!$dataDoc){
			$results = $dom->query('//table/tr[7]/td/table/tr[3]/td[1]');
	        foreach ($results as $r) {
				$contenu = $this->getInnerHtml($r);
			}		
			//mise à jour du document
			$this->dbD->edit($idDoc,array("titre"=>$titre,"maj"=> $sqlDate,"note"=>$contenu));
		}		
		//récupération des mots clefs
		$results = $dom->query('//tr[3]/td[1]/p[@class="style96"]/span[@class="style42"]');
		foreach ($results as $r) {
			$domaine = $r->nodeValue;
			$this->saveTag(array("code"=>$domaine, "desc"=>"domaine"), $idDoc, 1, $sqlDate, $this->user);
		}
		$results = $dom->query('.style32');
		foreach ($results as $r) {
			$pays = $r->nodeValue;
			$arr = explode(" ",$pays);
			$this->saveTag(array("code"=>$arr[1], "desc"=>"pays"), $idDoc, 1, $sqlDate, $this->user);
		}
		
		//traitement des sources contact rédacteurs
    	for ($i = 0; $i < 3; $i++) {
			$results = $dom->query('//table/tr[7]/td/table/tr['.(6+$i).']/td[1]');
	        foreach ($results as $r) {
				$type = trim($r->nodeValue);
				if($type=="Sources :" || $type=="Pour en savoir plus, contacts :"){
					$results = $dom->query('//table/tr[7]/td/table/tr['.(6+$i).']/td[2]');
			        foreach ($results as $rv) {
						$value = $rv->nodeValue;
			        }
			        //ajoute un sous document
			        $idDocS = $this->dbD->ajouter(array("data"=>$value, "tronc"=>$idDoc));
			        //avec le tag du type
					$this->saveTag($type, $idDocS, 1, $sqlDate, $this->user);
				}
				if($type=="Rédacteurs :"){
					$results = $dom->query('//table/tr[7]/td/table/tr['.(6+$i).']/td[2]');
			        foreach ($results as $rv) {
						$value = $rv->nodeValue;
			        }
			        //ajoute un nouveau rédacteur
					$idRedac = $this->getUser(array("login"=>$value),true);
					$this->saveTag("Rédacteurs", $idDoc, 1, $sqlDate, $idRedac);
				}
	        }
    	}
												
		$this->trace("FIN ".__METHOD__);
		
    }	

	/**
	 * traiteAllDoc
	 *
	 * relance l'extraction à partir des sources html
	 *
     * 
	 *
	 */
	function traiteAllDoc(){

		set_time_limit(98000);
		$this->bTrace = true; // pour afficher les traces
    	$this->temps_debut = microtime(true);
    	$this->trace("DEBUT ".__METHOD__);
		
		//initialisation des objets
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		if(!$this->dbUU)$this->dbUU = new Model_DbTable_Flux_UtiUti($this->db);
		
    	//initialise l'utilisateur
    	$this->getUser(array("login"=>"Flux_Addit"));
		
    	//récupère tous les bulletins
		$arrBlt = $this->dbD->findFiltre("tronc = '' AND doc_id between 196718 AND 250000", array("doc_id", "data"));
		//$arrBlt = $this->dbD->findFiltre("tronc = ''", array("doc_id", "data"));
		foreach ($arrBlt as $blt) {
			$this->trace("doc_id ".$blt['doc_id']);
			$this->sauveInfoBE("", $blt);
		}
		
		$this->trace("FIN ".__METHOD__);
		
	}
	
}
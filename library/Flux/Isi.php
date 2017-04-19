<?php
/**
 * Flux_Isi
 * Classe qui gère les flux du site de l'institut international de statistique
 * http://isi.cbs.nl/glossary/
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Scraping
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Flux_Isi extends Flux_Site{

	var $rs;
	var $idTagLangue;
	var $idDocGloIsi;
	var $langues = array(
			'English' => 'en',
			'French' => 'fr',
			'German' => 'de',
			'Dutch' => 'nl',
			'Italian' => 'it',
			'Spanish' => 'es',
			'Catalan' => 'ca',
			'Portuguese' => 'pt',
			'Romanian' => 'ro',
			'Danish' => 'da',
			'Norwegian' => 'no',
			'Swedish' => 'sv',
			'Greek' => 'el',
			'Finnish' => 'fi',
			'Hungarian' => 'hu',
			'Turkish' => 'tr',
			'Estonian' => 'et',
			'Lithuanian' => 'lt',
			'Slovenian' => 'sl',
			'Polish' => 'pl',
			'Russian' => 'ru',
			'Ukrainian' => 'uk',
			'Serbian' => 'sr',
			'Icelandic' => 'is',
			'Euskara' => 'eu',
			'Farsi' => 'fa',
			'Persian-Farsi' => 'per',
			'Arabic' => 'ar',
			'Afrikaans' => 'af',
			'Chinese' => 'zh',
			'Korean' => 'ko'
		);
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
    		
    		$this->mc = new Flux_MC($idBase, $bTrace);
    		
    }

 
    
    /**
     * Enregistre les traductions d'un item du glossaire
     *
     * @param  string 	$url
     * @param  string 	$label
     * @param  int 		$idTag
     * @param  int 		$idRap
     *
     * @return array
     */
    public function setItemTrad($url, $label, $idTag, $idRap)
    {    	
    		$this->initDbTables();
    	 
	    //initialise les variables
	    	$idAct = $this->dbA->ajouter(array("code"=>__METHOD__));    	
	    //	if(!$this->idTagLangue)$this->idTagLangue = $this->dbT->ajouter(array("code"=>"langues"));
	    	//enregistre le document
	    if(!$this->idDocGloIsi){
	    		$this->idDocGloIsi= $this->dbD->ajouter(array("url"=>"http://isi.cbs.nl/glossary/"
	    			,"titre"=>"INTERNATIONAL STATISTICAL INSTITUTE - Glossaire"
	    			,"parent"=>$this->idDocRoot,"tronc"=>0
	    		));
	    }
	    //récupère la traduction
	    $html = $this->getUrlBodyContent($url,false,true);
	    $idD = $this->dbD->ajouter(array("url"=>$url
	    			,"titre"=>$url
	    			,"parent"=>$this->idDocGloIsi,"tronc"=>0,"data"=>$html
	    	));
    	
	    	//enregistre le rapport
	    	$idRapDoc = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    			,"src_id"=>$idD,"src_obj"=>"doc"
	    			,"dst_id"=>$idAct,"dst_obj"=>"acti"
	    			,"pre_id"=>$idRap,"pre_obj"=>"rapport"	    			
	    	));
	    	//constuction du xml à requeter
	    	$dom = new Zend_Dom_Query($html);
	    	//récupère la liste des citations
	    	$xp = "//tr/td";
	    	$result = $dom->queryXpath($xp);
	    	$i = 1;
	    	foreach ($result as $cn) {
	    		if ($i%2){
	    			$langue = $cn->nodeValue;
	    		}else{
	    			$cpt =  $cn->nodeValue;
	    			
	    			//on enregistre la class
	    			$idTclass = $this->dbT->ajouter(array("code"=>$cpt,"parent"=>$idTag,"ns"=>$this->langues[$langue],"uri"=>$xp."[".$i."]"));
	    			//on enregistre la langue
	    			//$idTlangue = $this->dbT->ajouter(array("code"=>$langue,"parent"=>$this->idTagLangue,"ns"=>$this->langues[$langue]));
	    			//création du rapport
	    			$idRapClass = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    					,"src_id"=>$idTag,"src_obj"=>"tag"
	    					,"dst_id"=>$idTclass,"dst_obj"=>"tag"
	    					,"pre_id"=>$idRap,"pre_obj"=>"rapport"
	    					,"valeur"=>$this->langues[$langue]
	    			));
	    			
	    			
	    			//$this->trace($langue." = ".$cpt);
	    		}
	    		$i++;
	    	}
    }    
    

}
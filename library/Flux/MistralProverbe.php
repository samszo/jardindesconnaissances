<?php
/**
 * Classe qui gère les flux de la base de donnée de proverbes Mistral
 *
 * @copyright  2016 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 * REFERENCES
 * http://www.culture.gouv.fr/public/mistral/proverbe_fr
 * 
 * THANKS
 */
class Flux_MistralProverbe extends Flux_Site{

	var $formatResponse = "json";
	var $searchUrl = '';
	var $rs;
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
		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>"Ministère de la culture - Mistral - Proverbe"));
		$this->idMonade = $this->dbM->ajouter(array("titre"=>"Ministère de la culture - Mistral - Proverbe"),true,false);
	    	
    }

        
    /**
     * Enregistre les informations contenu dans le résultat d'une recherche
     *
     * @param  	string 	$query
     * @param  	int		$page
     * @param	int		$max 		= nombre d'item par page
     * @param 	array	$arrGen 		= données du générateur
     *
     * @return array
     */
    public function saveResultSearch($query,$page=1,$max=200,$arrGen=false)
    {
		if(!$this->dbT) $this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbE) $this->dbE = new Model_DbTable_Flux_Exi($this->db);
		if(!$this->dbD) $this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbR) $this->dbR = new Model_DbTable_Flux_Rapport($this->db);		
		
		//récupère les références
		$idTagProp = $this->dbT->ajouter(array("code"=>$query));		
		$idExi = $this->dbE->ajouter(array("nom"=>"Algo Mistral Proverbes"));	

		$searchUrl = "http://www.culture.gouv.fr/public/mistral/proverbe_fr";
		
		
		$params = array("FIELD_1"=>"PROV","VALUE_1"=>$query,"ACTION"=>"CHERCHER","USRNAME"=>"nobody","USRPWD"=>"4$%34P","DICO"=>"","x"=>0,"y"=>0,"MAX3"=>$max);
    		$html = $this->getUrlBodyContent($searchUrl,$params,false,Zend_Http_Client::POST);
    		$html = strtolower($html);
		$idDocFind = $this->dbD->ajouter(array("titre"=>"Resultat recherche = ".$query,"tronc"=>$page,"parent"=>$this->idDocRoot,"data"=>$html,"note"=>json_encode($params)));
		    		
		//echo $html;
		$dom = new Zend_Dom_Query($html);	    		
		$xPath = '//td[@width=700]';
		$results = $dom->queryXpath($xPath);
		$i = 0;
		foreach ($results as $result) {
			$pro = $result->textContent;
			$idDoc = $this->dbD->ajouter(array("titre"=>$pro,"parent"=>$this->idDocRoot));
			if($arrGen){
				//on crée le générateur				
				$this->dbG->ajouter($arrGen["id_concept"],array("valeur"=>$pro,"id_dico"=>$arrGen["id_dico"]));
			}
			$this->trace($i."=".$pro); 
			$i++;
		}	    
		
	}
     
}
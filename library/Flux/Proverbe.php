<?php
/**
 * Classe qui gère les flux des bases de données de proverbes
 *
 * @copyright  2016 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 * REFERENCES
 * http://www.culture.gouv.fr/public/mistral/proverbe_fr
 * 
 * THANKS
 */
class Flux_Proverbe extends Flux_Site{

	var $mc;
	
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

		//on crée les objets nécessaire
	    	$this->mc = new Flux_MC($idBase);
	    	$this->initDbTables();
    }

	/**
     * Fonction pour initialiser les tables de la base de données
     * 
     */
      function initDbTables(){
    		//construction des objets
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbE)$this->dbE = new Model_DbTable_Flux_Exi($this->db);
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbR)$this->dbR = new Model_DbTable_Flux_Rapport($this->db);
		if(!$this->dbM)$this->dbM = new Model_DbTable_Flux_Monade($this->db);
		if(!$this->dbA)$this->dbA = new Model_DbTable_flux_acti($this->db);
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
		if(!$this->dbG)$this->dbG = new Model_DbTable_Gen_generateurs($this->db);		
		
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
    public function saveResultSearchMistral($query,$page=1,$max=200,$arrGen=false)
    {
		//récupère l'action
		$idAct = $this->dbA->ajouter(array("code"=>__METHOD__));
		
		//récupère les références
		$searchUrl = "http://www.culture.gouv.fr/public/mistral/proverbe_fr";
		
		$idTagProp = $this->dbT->ajouter(array("code"=>$query));		
		$idDocSite = $this->dbD->ajouter(array("titre"=>"Ministère de la culture - base Proverbes","url"=>$searchUrl));			

		//enregistre le rapport de l'action
		$idRapAct = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			,"src_id"=>$idDocSite,"src_obj"=>"doc"
			,"pre_id"=>$idAct,"pre_obj"=>"acti"
			,"dst_id"=>$idTagProp,"dst_obj"=>"tag"
			));					
		
		$params = array("FIELD_1"=>"PROV","VALUE_1"=>$query,"ACTION"=>"CHERCHER","USRNAME"=>"nobody","USRPWD"=>"4$%34P","DICO"=>"","x"=>0,"y"=>0,"MAX3"=>$max);
    		$html = $this->getUrlBodyContent($searchUrl,$params,false,Zend_Http_Client::POST);
		$idDocFind = $this->dbD->ajouter(array("titre"=>"Resultat recherche = ".$query,"tronc"=>$page,"parent"=>$idDocSite,"data"=>$html,"note"=>json_encode($params)));

			
		//echo $html;
		$dom = new Zend_Dom_Query($html);	    		
		
		//récupère les citations de la page
		$xPath = '//td[@width=700]';
		$results = $dom->queryXpath($xPath);
		$i = 0;
		foreach ($results as $result) {
			$pro = $result->textContent;
			$this->ajouter($pro, $idDocFind, $idRapAct, $arrGen);
			$this->trace($i."=".$pro); 
			$i++;
		}
		
	}
	
	function ajouter($pro, $idDocFind, $idRapAct, $arrGen){
		//on crée le document
		$idDoc = $this->dbD->ajouter(array("titre"=>$pro,"parent"=>$idDocFind));
		//on crée le générateur				
		if($arrGen){
			$idGen = $this->dbG->ajouter($arrGen["id_concept"],array("valeur"=>$pro,"id_dico"=>$arrGen["id_dico"]));
			//on crée le rapport entre document et générateur
			$idRapGen = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idDoc,"src_obj"=>"doc"
				,"dst_id"=>$idGen,"dst_obj"=>"generateurs"
				,"pre_id"=>$idRapAct,"pre_obj"=>"rapport"
				));					
		}
		//on ajoute les mots clefs
		$this->mc->saveForChaine($idDoc, $pro, "","autokeyword");				
	
	}
     
}
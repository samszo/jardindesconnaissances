<?php
/**
 * Classe qui gère les flux du moteur open data de l'enseignement supérieur et de la recherche française
 * https://data.enseignementsup-recherche.gouv.fr
 *
 * @copyright  2016 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 * THANKS
 * https://data.enseignementsup-recherche.gouv.fr/api/v1/console/datasets/1.0/search/
 */
class Flux_Ensuprefr extends Flux_Site{

	var $searchUrl = 'https://data.enseignementsup-recherche.gouv.fr/api/records/1.0/search/?';
	var $rs;
	
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
     * Execute une requète sur le moteur
     *
     * @param  string $query
     *
     * @return string
     */
    public function query($query)
    {
	    $url = $this->searchUrl.$query;
		return $this->getUrlBodyContent($url,false);
    }
    
    /**
     * Recherche des publications à partir d'une requête
     *
     * @param  string 	$req
     * @param  int		$start
     * @param  int		$rows
     *
     * @return string
     */
    public function getPubli($req, $start=0, $rows=100)
    {
    	$req = "dataset=fr-esr-scanr-publications-scientifiques&rows=".$rows."&start=".$start."&q=".urlencode($req);
    	return $this->query($req);
    }
    
    /**
     * Recherche des structure des recherches actives à partir d'une requête
     *
     * @param  string 	$req
     * @param  int		$start
     * @param  int		$rows
     *
     * @return string
     */
    public function getStructures($req, $start=0, $rows=100)
    {
    	$req = "dataset=fr-esr-structures-recherche-publiques-actives&rows=".$rows."&start=".$start."&q=".urlencode($req);
    	return $this->query($req);
    }
    
    /**
     * Enregistre la monade d'une recherche
     *
     * @param  string $req
     *
     * @return string
     */
    public function saveMonade($req)
    {
    	$this->trace(__METHOD__." DEBUT");
    	 
    	set_time_limit(0);
    	$this->initDbTables();
    	$this->trace("Tables initialisées");
    	 
    	//récupère l'action
    	$idAct = $this->dbA->ajouter(array("code"=>__METHOD__));    	     	 
    	
    	//enregistre le rapport
    	$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
    			,"dst_id"=>$idAct,"dst_obj"=>"acti"
    			,"src_id"=>$this->user,"src_obj"=>"uti"
    			,"valeur"=>$req
    	));
    	 
    	//
    	$i = 0;
    	$rows = 100;
    	$nhits = 1000;
    	while ($i<$nhits) {
    		$rs = json_decode($this->getPubli($req,$i,$rows));
    		$nhits = $rs->nhits;
    		foreach ($rs->records as $r){
				$this->trace($i." / ".$nhits." : ".$r->recordid);
    			$this->saveRecordPubli($r, $idRap);
    			$i++;
    		}
    		//$i=-1;
    	}    	   
    	$this->trace(__METHOD__." FIN");
    	 
    }
    
    /**
     * Enregistre un enregistrement dans une monade
     *
     * @param  object 	$r
     * @param  int		$idRap
     *
     */
    function saveRecordPubli($r, $idRap){
    
    	 
    	//récupère l'action
    	$idAct = $this->dbA->ajouter(array("code"=>__METHOD__));
    	$f = $r->fields;     
    	//enregistre le document
    	$idD = $this->dbD->ajouter(array("url"=>$f->lien,"titre"=>$f->titre
    			,"parent"=>$this->idDocRoot,"tronc"=>0, "data"=>json_encode($r)
    			,"tronc"=>$f->type_de_publication
    			,"pubDate"=>$f->date_de_publication));
    	$this->trace("document correspondant à l'enregistrement = ".$idD);
    	//enregistre le rapport
    	$idRapDoc = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
    			,"src_id"=>$idRap,"src_obj"=>"rapport"
    			,"dst_id"=>$idD,"dst_obj"=>"doc"
    			,"pre_id"=>$idAct,"pre_obj"=>"acti"
    	));
    	$this->trace("enregistre le rapport entre le document et l'action");
    	 
    	//enregistre les auteurs
    	$nomAut = explode(";",$f->noms_des_auteurs);
    	$preAut = explode(";",$f->prenoms_des_auteurs);
    	for ($i = 0; $i < count($nomAut); $i++) {
    		$idE = $this->dbE->ajouter(array("prenom"=>$preAut[$i],"nom"=>$nomAut[$i]));
    		//ATTENTION impossible de faire le lien entre les auteurs et la structure
    		//enregistre le rapport avec le document
    		$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
    				,"src_id"=>$idE,"src_obj"=>"exi"
    				,"dst_id"=>$idD,"dst_obj"=>"doc"
    				,"pre_id"=>$idAct,"pre_obj"=>"acti"
    		));    		
    	 }
    	 
    	 //enregistre les structures
    	 $arrStruc = explode(";",$f->numero_national_de_structure_de_recherche);
    	 for ($i = 0; $i < count($arrStruc); $i++) {
    	 	//récupère les données de la structure
    	 	$oStruc = json_decode($this->getStructures($arrStruc[$i]));
    	 	$s = $oStruc->records[0];
    	 	if($oStruc->nhits > 0)
	    	 	$idE = $this->dbE->ajouter(array("nom"=>$s->fields->libelle,"data"=>json_encode($s)));
    	 	else
    	 		$idE = $this->dbE->ajouter(array("nom"=>"STRUCTURE INCONNUE : ".$arrStruc[$i],"data"=>json_encode($s)));
    	 	//enregistre le rapport avec le document
    	 	$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
    	 			,"src_id"=>$idE,"src_obj"=>"exi"
    	 			,"dst_id"=>$idD,"dst_obj"=>"doc"
    	 			,"pre_id"=>$idAct,"pre_obj"=>"acti"
    	 	));
    	 }    	 
    
    	//traitement des mots clefs
    	$idTagType = $this->dbT->ajouter(array("code"=>"thématique"));
    	$arrTags = explode(";", $f->thematiques);
    	foreach ($arrTags as $tag){
    		$idTag = $this->dbT->ajouter(array("code"=>$tag,'parent'=>$idTagType));
    		$this->mc->save($tag, $idRapDoc, 1);    			 
		}
    
    }
}
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
    	$i = 6756;
    	$rows = 100;
    	$nhits = 10000;
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
    		//enregistre le rapport avec le document et la requête
    		$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
    				,"src_id"=>$idE,"src_obj"=>"exi"
    				,"dst_id"=>$idD,"dst_obj"=>"doc"
    				,"pre_id"=>$idRap,"pre_obj"=>"rapport"
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
    	 	//enregistre le rapport avec le document et la requête
    	 	$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
    	 			,"src_id"=>$idE,"src_obj"=>"exi"
    	 			,"dst_id"=>$idD,"dst_obj"=>"doc"
    				,"pre_id"=>$idRap,"pre_obj"=>"rapport"
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
    /** récupère l'historique des tags
     *
     * @param 	string	$dateUnit
     * @param 	int	$idUti
     * @param 	int	$idMonade
     * @param 	int	$idActi
     * @param 	int	$idParent
     * @param 	array	$arrTags
     * @param 	string	$q
     * @param 	array	$dates
     * @param 	string	$for
     * @param 	string	$req
     *
     * @return 	array
     *
     */
    function getTagHisto($dateUnit, $idUti, $idMonade, $idActi, $idParent, $arrTags, $q, $dates, $for, $req=""){
    
    	//ini_set("memory_limit","300M");
    	$stats = new Flux_Stats();
    	$sqlFormatDate = $dateUnit;
    	$this->bTrace = false;
    	$dbTag = new Model_DbTable_Flux_Tag($this->db);
    	$data = $dbTag->getTagHistoRapport($idMonade
    			, $idUti, $idActi, $idParent
    			, $arrTags, $sqlFormatDate, $q, $dates,"req",$req);
    	if($for=="stream"){
    		//récupère les intervales des dates
    		$arrDate = $stats->GetDatesInterval($data, $sqlFormatDate);
    		
    		//ajoute les valeurs vides pour chaque éléments
    		$oTag = $data[0]['monaId']."_".$data[0]['reqId']."_".$data[0]['key'];
    		$j=0; $i=0; $nbDate = count($arrDate); $nbData = count($data);
    		$nData;
    		//foreach ($data as $d) {
    		for ($z = 0; $z < $nbData; $z++) {
    			$d = $data[$z];
    			$this->trace('temps '.$z.' : '.$i.' / '.$j,$d);
    			$k = $d['monaId']."_".$d['reqId']."_".$d['key'];    			
    			//on vérifie si on passe à un nouveau type
    			if($oTag!=$k){
    				//on fini les temps restant
    				for ($i = $j; $i < $nbDate; $i++) {
    					$nD = array('key'=>$oTag,'type'=>$oD['type'],'desc'=>$oD['desc']
    							,'temps'=>$arrDate[$i],'score'=>0,'value'=>0,'monade'=>$oD['monade'],'req'=>$oD['req']
    							,'MinDate'=>0,'MaxDate'=>0
    					);
    					$nData[]=$nD;
    					$this->trace('fin nouveau temps '.$i .' / '. $j,$nD);
    				}
    				$j=0;
    				$oTag=$k;  
    			}
    			//on calcul les temps manquant
    			for ($i = $j; $i < $nbDate; $i++) {
    				//$this->trace($arrDate[$i]."==".$d['temps']);
    				if($arrDate[$i]==$d['temps']){
    					$D = array('key'=>$k,'type'=>$d['type'],'desc'=>$d['desc']
    							,'temps'=>$arrDate[$i],'score'=>$d['score'],'value'=>$d['value'],'monade'=>$d['monade'],'req'=>$d['req']
    							,'MinDate'=>$d['MaxDate'],'MaxDate'=>$d['MaxDate']
    					);    						
    					$nData[]=$D;
    					$j=$i+1;
    					$i=$nbDate;
    				}else{
    					$nD = array('key'=>$k,'type'=>$d['type'],'desc'=>$d['desc']
    							,'temps'=>$arrDate[$i],'score'=>0,'value'=>0,'monade'=>$d['monade'],'req'=>$d['req']
    							,'MinDate'=>0,'MaxDate'=>0
    					);
    					$nData[]=$nD;
    					$this->trace('nouveau temps '.$i .' / '. $j,$nD);
    				}
    			}
    			$oD = $d;
    		}
    		//ordonne le tableau
    		$data = $nData;
    	}
    	return $data;
    }
    
    

}
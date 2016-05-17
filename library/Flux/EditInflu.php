<?php
/**
 * Classe qui gère les flux de l'application EditInflu
 *
 * @copyright  2016 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 * REFERENCES
 * 
 * THANKS
 */
class Flux_EditInflu extends Flux_Site{

	var $formatResponse = "json";
	var $idDocRoot;
	var $idMonade;
	
    /**
     * Constructeur de la classe
     *
     * @param  string 	$idBase
     * @param  boolean 	$bTrace
     * @param  int	 	$idGeo
     * 
     */
	public function __construct($idBase=false, $bTrace=false, $idGeo = 0)
    {
    		parent::__construct($idBase, $bTrace);    	

    		//on récupère la racine des documents
		$this->initDbTables();	    	
		$this->idTagRoot = $this->dbT->ajouter(array("code"=>__CLASS__));
		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
		$this->idExiRoot = $this->dbE->ajouter(array("nom"=>__CLASS__));
		$this->idGeo = $idGeo;
		
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
    	
    }
    
    /**
     * Fonction pour créer un crible
     *
     * @param  	int 		$idUti
     * @param  	array 	$data
     * 
     * @return	array
     * 
     */
    function creaCrible($idUti, $data){
    		return $this->creaTronc($idUti, "crible", $data);
    }

    /**
     * Fonction pour créer un graph
     *
     * @param  	int 		$idUti
     * @param  	array 	$data
     * 
     * @return	array
     * 
     */
    function creaGraph($idUti, $data){
    		return $this->creaTronc($idUti, "graphInfluence", $data);
    }    
    
    /**
     * Fonction pour créer un document
     *
     * @param  	int 		$idUti
     * @param  	array 	$data
     * 
     * @return	array
     * 
     */
    function creaDoc($idUti, $data){
    		if($data["tronc"])$tronc=$data["tronc"]; else $tronc = "docInfluence";
    		return $this->creaTronc($idUti, $tronc, $data);
    }
    
    /**
     * Fonction pour créer un graph
     *
     * @param  	int 		$idUti
     * @param  	strinc	$tronc
     * @param  	array 	$data
     * 
     * @return	array
     * 
     */
    function creaTronc($idUti, $tronc, $data){

    		$this->initDbTables();
    	
		$idTagCrible = $this->dbT->ajouter(array("code"=>$tronc));
		$idAct = $this->dbA->ajouter(array("code"=>__METHOD__));
		if(!isset($data["parent"])){			
			$data["parent"]=$this->idDocRoot;
			if($data["idCrible"]){	
				$data["parent"]=$data["idCrible"];	
			}   
		}
		unset($data["idCrible"]);
		if($tronc=="ajoutDocFrag")
			$idDocCribles = $data["parent"];
		else 
			$idDocCribles = $this->dbD->ajouter(array("titre"=>$tronc."s","parent"=>$data["parent"]));
		
		//enregistre le rapport entre l'utilisateur et l'action
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			,"src_id"=>$idUti,"src_obj"=>"uti"
			,"dst_id"=>$idAct,"dst_obj"=>"acti"
			));
		//ajoute les données supplémentaires
		$data["parent"]=$idDocCribles;
		$data["tronc"]=$tronc;
		//enregistre le document
		$arr = $this->dbD->ajouter($data,true,true);
		//ajoute le(s) champ(s) pour le grid
		$arr["recid"] = $arr["doc_id"];
		//enregistre la création du doc
		$idRapRep = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			,"src_id"=>$idRap,"src_obj"=>"rapport"
			,"dst_id"=>$arr["doc_id"],"dst_obj"=>"doc"
			));					
		return $arr;
    	
    }
    
    /**
     * Fonction pour créer un acteur
     *
     * @param  	int 		$idUti
     * @param  	array 	$data
     * 
     * @return	array
     * 
     */
    function creaActeur($idUti, $data){

		$this->trace(__METHOD__." : ".$idUti);
    		$this->initDbTables();
    	
		//enregistre le rapport entre l'utilisateur et l'action
		$idActi = $this->dbA->ajouter(array("code"=>__METHOD__));		
    		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			,"src_id"=>$idUti,"src_obj"=>"uti"
			,"dst_id"=>$idActi,"dst_obj"=>"acti"
			));
			
		//nettoyage des paramètres
		$idCrible = $data['idCrible'];		
		$data = $this->cleanData($data);
		//print_r($data);
		
		//enregistre l'acteur
		$this->trace("data",$data);
		$arr = $this->dbE->ajouter($data,true,true);
		//ajoute le(s) champ(s) pour le grid
		$arr["recid"] = $arr["exi_id"];
		//enregistre la création de l'acteur
		$idRapRep = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			,"src_id"=>$idRap,"src_obj"=>"rapport"
			,"pre_id"=>$idCrible,"pre_obj"=>"doc"
			,"dst_id"=>$arr["exi_id"],"dst_obj"=>"exi"
			));
		//enregistre le portrait
		if($img){
			$arrDoc = $this->creaDoc($idUti, array("tronc"=>"portrait", "titre"=>$data['prenom']." ".$data['nom'],"url"=>$img));
			$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"dst_id"=>$arrDoc["doc_id"],"src_obj"=>"doc"
				,"pre_id"=>$idRapRep,"pre_obj"=>"rapport"
				,"src_id"=>$arr["exi_id"],"dst_obj"=>"exi"
				));
		}
		
		return $arr;    	
    }

 	/**
     * Fonction pour créer un tag
     *
     * @param  	int 		$idUti
     * @param  	array 	$data
     * 
     * @return	array
     * 
     */
    function creaTag($idUti, $data){

		$this->trace(__METHOD__." : ".$idUti);
    		$this->initDbTables();
    	
		//enregistre le rapport entre l'utilisateur et l'action
		$idActi = $this->dbA->ajouter(array("code"=>__METHOD__));		
    		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			,"src_id"=>$idUti,"src_obj"=>"uti"
			,"dst_id"=>$idActi,"dst_obj"=>"acti"
			));
			
		//nettoyage des paramètres
		$idCrible = $data['idCrible'];		
		$data = $this->cleanData($data);
		//print_r($data);
		
		//enregistre le tag
		$this->trace("data",$data);
		$arr = $this->dbT->ajouter($data,true,true);
		//ajoute le(s) champ(s) pour le grid
		$arr["recid"] = $arr["tag_id"];
		//enregistre la création du tag
		$idRapRep = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			,"src_id"=>$idRap,"src_obj"=>"rapport"
			,"pre_id"=>$idCrible,"pre_obj"=>"doc"
			,"dst_id"=>$arr["tag_id"],"dst_obj"=>"tag"
			));
		
		return $arr;    	
    }   

	/**
     * Fonction pour créer un rapport
     *
     * @param  	int 		$idUti
     * @param  	array 	$data
     * 
     * @return	array
     * 
     */
    function creaRapport($idUti, $data){

		$this->trace(__METHOD__." : ".$idUti);
    		$this->initDbTables();
    	
		//enregistre le rapport entre l'utilisateur et l'action
		$idActi = $this->dbA->ajouter(array("code"=>__METHOD__));		
    		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			,"src_id"=>$idUti,"src_obj"=>"uti"
			,"dst_id"=>$idActi,"dst_obj"=>"acti"
			));
			
		//nettoyage des paramètres
		$data = $this->cleanData($data);
		//print_r($data);
		
		//enregistre le rapport
		$this->trace("data",$data);
		$data["monade_id"]=$this->idMonade;
		$data["geo_id"]=$this->idGeo;
		$idRapRep = $this->dbR->ajouter($data);
		//ajoute le(s) champ(s) pour le grid
		$arr["recid"] = $arr["tag_id"];
			
		return $arr;    	
    }       
    
    /**
     * Fonction pour nettoyer les paramètres
     * @param	array	$data
     * 
     * @return	array
     * 
     */
    function cleanData($data){
    		/*fait en javascript
		if($data['idArk'])	{
			$data['data'] = json_encode(array("bnf"=>array("idArk"=>$data['idArk'],"liens"=>$data['liens'],"isni"=>$data['isni'])));
			unset($data['idArk']);
    			unset($data['liens']);			
		}
		if($data['data']['@id']){
			$data['data'] = json_encode(array("kg"=>array(
				"idKg"=>$data['data']['@id'],"data"=>$data['data'],"liens"=>$data['liens']
			)));
			unset($data['img']);
			unset($data['recid']);
    			unset($data['liens']);			
    			unset($data['img']);			
    			unset($data['detail']);			
    			unset($data['expanded']);			
		}
		*/
	    	if(isset($data['data']))	$data['data'] = json_encode($data['data']);
    		unset($data['idCrible']);			
    		unset($data['recid']);			
    		
    		return $data;
    	}
    
    /**
     * Fonction pour récupérer les cribles
     *
     * 
     * @return	array
     * 
     */
    function getCribles(){
    		return $this->getDocUtiByTronc('crible');
    	}
    
    /**
     * Fonction pour récupérer les graph
     *
     * 
     * @return	array
     * 
     */
    function getGraphInfluence(){
    		return $this->getDocUtiByTronc('graphInfluence');
    }    

    /**
     * Fonction pour récupérer les doc
     * 
     * @param 	int		$idCrible
     * 
     * @return	array
     * 
     */
    function getDocInfluence($idCrible){
    		return $this->getDocUtiByTronc('docInfluence',$idCrible);
    }    
    
    
    /**
     * Fonction pour récupérer les graph
     *
     * @param	string	$tronc
     * @param 	int		$idCrible
     * 
     * @return	array
     * 
     */
    function getDocUtiByTronc($tronc, $idCrible=0){
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	    $query = $this->dbD->select()
			->from( array("d" => "flux_doc"), array("recid"=>"doc_id","url","titre","data"))                           
            	->where( "d.tronc = ?", $tronc)
	        		->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
	            	->joinInner(array('r' => 'flux_rapport'),'r.dst_id = d.doc_id AND dst_obj="doc"',array())
	            	->joinInner(array('ru' => 'flux_rapport'),'ru.rapport_id = r.src_id',array())            
	            	->joinInner(array('u' => 'flux_uti'),'u.uti_id = ru.src_id',array('uti_id',"login"));            
        	if($idCrible){
        		$query->joinInner(array('dC' => 'flux_doc'),'dC.doc_id = d.parent AND dC.parent = '.$idCrible,array("parent"=>"titre"));
        	}
        return $this->dbD->fetchAll($query)->toArray(); 
    }    

    /**
     * Fonction pour récupérer les acteurs
     *
     * @param	int		$idCrible
     * @return	array
     * 
     */
    function getExiByCrible($idCrible){
		if(!$this->dbE)$this->dbE = new Model_DbTable_Flux_Exi($this->db);
    	    $query = $this->dbE->select()
			->from( array("e" => "flux_exi"), array("recid"=>"exi_id","nom","prenom","data","nait","mort"))                           
	        		->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
	            	->joinInner(array('r' => 'flux_rapport'),'r.dst_id = e.exi_id AND r.dst_obj="exi" 
	            		AND r.pre_obj = "doc" AND r.pre_id = '.$idCrible,array())
	            	->joinInner(array('ru' => 'flux_rapport'),'ru.rapport_id = r.src_id',array())            
	            	->joinInner(array('u' => 'flux_uti'),'u.uti_id = ru.src_id',array('uti_id',"login"));            	            	
	            	
        return $this->dbD->fetchAll($query)->toArray(); 
    }    
    
     /**
     * Fonction pour importer un crible
     *
     * @param	string	$urlCSV
     * @return	array
     * 
     */
    function importCrible($urlCSV){
    	
		if(!$urlCSV)	return;
    	
    		$this->initDbTables();
				
    		//on ajoute le document csv
		$idDoc = $this->dbD->ajouter(array("titre"=>"Catégorisation des rapports"
			,"url"=>$urlCSV, "parent"=>$this->idDocRoot, "tronc"=>"import"));    		
		
    		//on ajoute les tags génériques
    		$idTagCatAppli = $this->dbT->ajouter(array("code"=>"Catégories de l'application","parent"=>$this->idTagRoot)); 	    				    		
    		$idTagCrud = $this->dbT->ajouter(array("code"=>"CRUD","parent"=>$idTagCatAppli)); 	    				
    		$idTagCatNotion = $this->dbT->ajouter(array("code"=>"Catégories de notion","parent"=>$this->idTagRoot)); 	    				    		
    		$idTagRameau = $this->dbT->ajouter(array("code"=>"Matière Rameau","parent"=>$idTagCatNotion)); 	    				
    		$idTagNotion = $this->dbT->ajouter(array("code"=>"Notions","parent"=>$idTagCatNotion));
    		//on ajoute un enfant pour récupérer les tags génériques
    		$idT = $this->dbT->ajouter(array("code"=>"Vide (physique)","uri"=>"http://data.bnf.fr/ark:/12148/cb12573072b","parent"=>$idTagRameau));  	    				
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			,"src_id"=>$idDoc,"src_obj"=>"doc"
			,"pre_id"=>$this->idExiRoot,"pre_obj"=>"exi"
			,"dst_id"=>$idT,"dst_obj"=>"tag"
			));							
    		$idT = $this->dbT->ajouter(array("code"=>"Vide","parent"=>$idTagNotion));  	    				
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			,"src_id"=>$idDoc,"src_obj"=>"doc"
			,"pre_id"=>$this->idExiRoot,"pre_obj"=>"exi"
			,"dst_id"=>$idT,"dst_obj"=>"tag"
			));							
    						
		//définition des regroupements
		$arrTagGroupe = array('Professions' => "Catégories d'acteur"
				,'Fonctions' => "Catégories d'acteur"
				,'Spécialités scientifiques' => "Catégories d'acteur"
				,'Rapports Acteur → Acteur' => "Catégories de rapport"
				,'Rapports Acteur → Lieu' => "Catégories de rapport"
				,'Rapports Acteur → Notion' => "Catégories de rapport"
				,'Rapports Notion → Acteur' => "Catégories de rapport"
				,'Rapports Acteur → Document' => "Catégories de rapport"
				,'Rapports Document → Notion' => "Catégories de rapport");		
				
    		//chargement des csv
    		$csv = $this->csvToArray($urlCSV,0,",");
    		$arrCol = array();
    		$nbCol = 10;
    		foreach ($csv as $c) {
    			$nom = $c[10];
    			if(!$arrCol){
    				//récupère la liste des colonnes
    				for ($i = 1; $i < $nbCol; $i++) {
    					//récupère le tag de regroupement
    					$idTagGroupe = $this->dbT->ajouter(array("code"=>$arrTagGroupe[$c[$i]],"parent"=>$idTagCatNotion)); 	    				
    					//enregistre les tags    					
	    				$idT = $this->dbT->ajouter(array("code"=>$c[$i],"parent"=>$idTagGroupe)); 
	    				$arrCol[$i] = array("tag_id"=>$idT,"code"=>$c[$i]);   					
    					$this->trace("Colonne $i :".$c[$i]." - groupe:".$arrTagGroupe[$c[$i]]);
    				}
    				//$this->trace("Tableau des colonnes :",$arrCol);
    			}else{
    				//enregistre l'utilisateur
    				$idUti = $this->dbU->ajouter(array("login"=>$nom,"mdp"=>$nom));
    				//enregistre l'existence
    				$idE = $this->dbE->ajouter(array("nom"=>$nom,"uti_id"=>$idUti));
    				//lie l'existence à l'utilisateur pour gérer les droits CRUD
				$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
					,"src_id"=>$idE,"src_obj"=>"exi"
					,"pre_id"=>$idTagCrud,"pre_obj"=>"tag"
					,"dst_id"=>$idUti,"dst_obj"=>"uti"
					));							
    				//lie l'existence à l'utilisateur racine pour gérer les droits CRUD
				$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
					,"src_id"=>$this->idExiRoot,"src_obj"=>"exi"
					,"pre_id"=>$idTagCrud,"pre_obj"=>"tag"
					,"dst_id"=>$idUti,"dst_obj"=>"uti"
					));
				//création du crible
				$arrCrible = $this->creaCrible($idUti, array("titre"=>$nom));							
    				$this->trace("crible de :".$nom." ".$idE);
    				for ($i = 1; $i < $nbCol; $i++) {
    					if($c[$i]){
		    				$this->trace("Colonne ".$i." : ".$c[$i]);
		    				$arrT = explode(",",$c[$i]);
		    				foreach ($arrT as $t) {
			    				//enregistre les tags
	    						$idT = $this->dbT->ajouter(array("code"=>trim($t),"parent"=>$arrCol[$i]["tag_id"])); 
	    						//enregistre le lien avec l'existence
							$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
								,"src_id"=>$idE,"src_obj"=>"exi"
								,"pre_id"=>$arrCrible["doc_id"],"pre_obj"=>"doc"
								,"dst_id"=>$idT,"dst_obj"=>"tag"
								));							
	    						//enregistre le lien avec l'existence racine
							$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
								,"src_id"=>$this->idExiRoot,"src_obj"=>"exi"
								,"pre_id"=>$arrCrible["doc_id"],"pre_obj"=>"doc"
								,"dst_id"=>$idT,"dst_obj"=>"tag"
								));							
	    						$this->trace($nom." référence enregistrée : ".$arrCol[$i]["code"]." ".$t);	    						
	    					}
    					}
    				}
    			}
    		}    	
    	
    }    
    
}
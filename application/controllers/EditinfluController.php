<?php

/**
 * EditinfluController
 * 
 * @author Samuel Szoniecky
 * @version 0.0
 */

require_once 'Zend/Controller/Action.php';

class EditinfluController extends Zend_Controller_Action {
	
	var $idBase = "flux_editinflu";
	var $ssUti;
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->initInstance();
		$this->view->title = "Editeurs de réseaux d'influences";
	    $this->view->headTitle($this->view->title, 'PREPEND');
	    
	    $s = new Flux_Site($this->view->idBase);
	    
	    /* récupère les cribles = les rapports attachés à un document avec un tronc "crible"
	     */
	    $dbETD = new Model_DbTable_Flux_ExiTagDoc($s->db);
	    $this->view->rsCrible = json_encode($dbETD->GetExisByDocTronc("crible"));
	    
	    /* récupère les graph = les docs de type graphInfluence
	     */
	    $dbDoc = new Model_DbTable_Flux_Doc($s->db);
	    $this->view->rsGraph = json_encode($dbDoc->findByTronc("graphInfluence",true,array("recid"=>"doc_id","url","titre")));
	    
	}


    public function navigrameauAction()
    {
    		$this->view->idBNF = $this->_getParam('idBNF');
    }	    
        
    	public function importAction()
    {
		$this->initInstance();
    	
    		switch ($this->_getParam('type', 'rien')) {
    			case "init":
	    			$this->importCrible();
	    			$this->importActeur();
	    			$this->importOpenAnnotation();
	    			break;
    			case "crible":
	    			$this->importCrible();
	    			break;
    			case "acteur":
	    			$this->importActeur();
	    			break;
    			case "oa":
	    			$this->importOpenAnnotation();
	    			break;
	    		case "rien":
	    			echo "aucun type";
	    			break;
    		}
    }

    	public function ajoutAction()
    {
		$this->initInstance();
	    $s = new Flux_Site($this->view->idBase);
	    $s->bTrace = false;
	    
    		//récupère les paramètres
    		$params = $this->_request->getParams();
    		$s->trace("params",$params);
    		$oName = $params['obj'];

    		//enlève les paramètres Zend
    		unset($params['controller']);
    		unset($params['action']);
    		unset($params['module']);
    		//et les paramètres de l'ajout
    		unset($params['obj']);

    		//ajout suivant les cas	    
    		switch ($oName) {
    			case "acteur":
    				$params['data'] = json_encode(array("idArk"=>$params['idArk'],"liens"=>$params['liens'],"isni"=>$params['isni']));
    				unset($params['idArk']);
    				unset($params['liens']);
    				//initialise les objets
		    		$s->dbT = new Model_DbTable_Flux_Tag($s->db);
		    		$s->dbE = new Model_DbTable_Flux_Exi($s->db);
		    		$s->dbUE = new Model_DbTable_Flux_UtiExi($s->db);
		    		$s->dbETD = new Model_DbTable_Flux_ExiTagDoc($s->db);
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
		    		//récupère les références
    				$tagActeur = $s->dbT->findByCode('Acteur');
    				$s->trace("tagActeur",$tagActeur);
    				$docBio = $s->dbD->findByUrl('jdc/public/biolographes');
    				//ajoute la référence
    				$exi=$s->dbE->ajouter($params,true,true);
	    			//le mot clef acteur
	    			$arrUTD = array("exi_id"=>$exi["exi_id"],"tag_id"=>$tagActeur["tag_id"],"doc_id"=>$docBio["doc_id"]);
    				$s->trace("arrUTD",$arrUTD);
	    			$s->dbETD->ajouter($arrUTD);
	    			//et le créateur de l'existence = l'utilisateur connecté
	    			$s->dbUE->ajouter(array("exi_id"=>$exi["exi_id"],"uti_id"=>$this->ssUti->uti["uti_id"]));
    				$this->view->rs = $exi;
    				$this->view->message = "L'acteur est ajouté"; 
    				break;
    			case "graph":
    				//initialise les objets
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
		    		$s->dbUD = new Model_DbTable_Flux_UtiDoc($s->db);
		    		//récupère les références
    				$docBio = $s->dbD->ajouter(array('url'=>'jdc/public/biolographes'));
    				//ajoute le document
    				$params["parent"]=$docBio["doc_id"];
    				$params['data'] = json_encode(array("prevLoc"=>array(),"nodes"=>array(),"links"=>array()));
    				
    				$rsDoc=$s->dbD->ajouter($params,false,true);
    				//ajoute le lien à l'utilisateur
    				$s->dbUD->ajouter(array("doc_id"=>$rsDoc["doc_id"],"uti_id"=>$this->ssUti->uti["uti_id"]));
    				$rsDoc["uti_id"]=$this->ssUti->uti["uti_id"];
    				$this->view->rs = $rsDoc;
    				$this->view->message = "La graph est ajouté"; 
    				break;
    			case "tag":
    				//initialise les objets
		    		$s->dbT = new Model_DbTable_Flux_Tag($s->db);
		    		$s->dbUT = new Model_DbTable_flux_utitag($s->db);
		    		$s->dbUTD = new Model_DbTable_Flux_UtiTagDoc($s->db);
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
		    		$s->dbE = new Model_DbTable_Flux_Exi($s->db);
		    		$s->dbETD = new Model_DbTable_Flux_ExiTagDoc($s->db);
	    			
		    		//récupère les références
    				$docBio = $s->dbD->findByUrl('jdc/public/biolographes');
    				if(!$params["parent"]){
	    				$arrTagParent=$s->dbT->findByCode("Catégories de notion");	
	    				$params["parent"] = $arrTagParent["tag_id"];
    				}
    				//ajoute le tag
    				$rs=$s->dbT->ajouter($params,true,true);
	    			//le mot clef acteur
	    			$arrUTD = array("uti_id"=>$this->ssUti->uti["uti_id"],"tag_id"=>$rs["tag_id"],"doc_id"=>$docBio["doc_id"]);
    				$s->trace("arrUTD",$arrUTD);
	    			$s->dbUTD->ajouter($arrUTD);
    				//on récupère l'existence racine
				$ERacine = $s->dbE->findByNom("Biolographes");    				
				$s->dbETD->ajouter(array("exi_id"=>$ERacine["exi_id"],"tag_id"=>$rs["tag_id"],"doc_id"=>$docBio["doc_id"]));
    				$s->trace("ERacine",$ERacine);
    				//on récupère l'existence utilisateur
				$EUti = $s->dbE->findByUtiId($this->ssUti->uti["uti_id"]);    				
				$s->dbETD->ajouter(array("exi_id"=>$EUti["exi_id"],"tag_id"=>$rs["tag_id"],"doc_id"=>$docBio["doc_id"]));
    				$s->trace("EUti",$EUti);
    				
    				$this->view->rs = $rs;
    				$this->view->message = "Le tag est ajouté"; 
    				break;
    			case "doc":
    				//initialise les objets
		    		$s->dbUD = new Model_DbTable_Flux_UtiDoc($s->db);
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
		    		$s->dbE = new Model_DbTable_Flux_Exi($s->db);
		    		$s->dbETD = new Model_DbTable_Flux_ExiTagDoc($s->db);
		    		$s->dbT = new Model_DbTable_Flux_Tag($s->db);
		    		
    				if(!isset($params["parent"])){
			    		//récupère les références
	    				$docBio = $s->dbD->findByUrl('jdc/public/biolographes');
			    		$params["parent"] = $docBio["doc_id"];			
    				}
    				//ajoute le doc
    				$rs=$s->dbD->ajouter($params,true,true);
	    			//lie le document à l'utilisateur pour la gestion CRUD
	    			$arrUD = array("uti_id"=>$this->ssUti->uti["uti_id"],"doc_id"=>$rs["doc_id"]);
    				$s->trace("arrUD",$rs);
	    			$s->dbUD->ajouter($arrUD);
	    			//on récupère le mot clef 
	    			$rsTag = $s->dbT->findByCode("Document ajouté");
    				//on ajoute l'existence racine pour l'affichage du crible commun
				$ERacine = $s->dbE->findByNom("Biolographes");    				
				$s->dbETD->ajouter(array("exi_id"=>$ERacine["exi_id"],"tag_id"=>$rsTag["tag_id"],"doc_id"=>$rs["doc_id"]));
    				$s->trace("ERacine",$ERacine);
    				//on ajoute l'existence utilisateur pour l'affichage du crible de l'existence
				$EUti = $s->dbE->findByUtiId($this->ssUti->uti["uti_id"]);    				
				$s->dbETD->ajouter(array("exi_id"=>$EUti["exi_id"],"tag_id"=>$rsTag["tag_id"],"doc_id"=>$rs["doc_id"]));
    				$s->trace("EUti",$EUti);
    				//
    				$this->view->rs = $rs;
    				$this->view->message = "Le document est ajouté"; 
    				break;
    	    		}
    }
    
	public function editAction()
    {
		$this->initInstance();
	    $s = new Flux_Site($this->view->idBase);
	    $s->bTrace = false;
	    
    		//récupère les paramètres
    		$params = $this->_request->getParams();
    		$s->trace("params",$params);
    		$oName = $params['obj'];

    		//enlève les paramètres Zend
    		unset($params['controller']);
    		unset($params['action']);
    		unset($params['module']);
    		//et les paramètres de l'ajout
    		unset($params['obj']);
		$id = $params['recid'];
    		unset($params['recid']);
    		
    		//ajout suivant les cas	    
    		switch ($oName) {
    			case "acteur":
    				$params['data'] = json_encode(array("idArk"=>$params['idArk'],"liens"=>$params['liens'],"isni"=>$params['isni']));
    				unset($params['idArk']);
    				unset($params['liens']);
    				//initialise les objets
		    		$s->dbE = new Model_DbTable_Flux_Exi($s->db);
		    		//met à jour les données
    				$this->view->rs = $s->dbE->edit($id, $params);
    				$this->view->message = "L'acteur est mis à jour"; 
    				break;
    			case "graph":
    				$params['data'] = json_encode(array("posis"=>$params['posis'],"nodes"=>$params['nodes'],"links"=>$params['links']),JSON_NUMERIC_CHECK);
    				unset($params['nodes']);
    				unset($params['links']);
    				unset($params['posis']);
    				//initialise les objets
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
		    		//met à jour les données
    				$this->view->rs = $s->dbD->edit($id, $params);
    				$this->view->message = "Le graph est mis à jour"; 
    				break;
    			case "doc":
    				//initialise les objets
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
		    		//met à jour les données
    				$this->view->rs = $s->dbD->edit($id, $params);
    				$this->view->message = "Le document est mis à jour"; 
    				break;
    		}
    }

	public function deleteAction()
    {
		$this->initInstance();
	    $s = new Flux_Site($this->view->idBase);
	    $s->bTrace = false;
	    
    		//récupère les paramètres
    		$params = $this->_request->getParams();
    		$s->trace("params",$params);
    		$oName = $params['obj'];
    		
    		//ajout suivant les cas	    
    		switch ($oName) {
    			case "graph":
    				//initialise les objets
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
		    		//met à jour les données
    				$this->view->rs = $s->dbD->remove($params['id']);
    				$this->view->message = "Le graph est supprimé."; 
    				break;
    			case "doc":
    				//initialise les objets
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
		    		//met à jour les données
    				$this->view->rs = $s->dbD->remove($params['id']);
    				$this->view->message = "Le document est supprimé."; 
    				break;
    		}
    }    
	public function getAction()
    {
		$this->initInstance();
	    $s = new Flux_Site($this->view->idBase);
	    $s->bTrace = false;
	    
    		//récupère les paramètres
    		$params = $this->_request->getParams();
    		
    		//ajout suivant les cas	    
    		switch ($params['obj']) {
    			case "graph":
    				//initialise les objets
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
		    		//met à jour les données
    				$this->view->rs = $s->dbD->findBydoc_id($params['id']);
    				$this->view->message = "Voici le graph"; 
    				break;
    			case "crible":
    				//initialise les objets
		    		$s->dbETD = new Model_DbTable_Flux_ExiTagDoc($s->db);
		    		$s->dbE  = new Model_DbTable_Flux_Exi($s->db);
		    		//met à jour les données
		    		$notions = $s->dbETD->GetExiTagDoc($params['idExi'],$params['idDoc'],"",2);
		    		$docs = $s->dbETD->GetExiDocs($params['idExi'],"tronc='ajoutDoc' OR tronc='ajoutDocFrag'");		    		
				$acteurs = $s->dbE->getExiByTag('Acteur');
		    		
    				$this->view->rs = array("notions"=>$notions,"docs"=>$docs,"acteurs"=>$acteurs);
		    		$this->view->message = "Le crible est chargé."; 
    				break;
    			case "fragment":
    				//initialise les objets
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
    				$this->view->rs = $s->dbD->findByParent($params['idParent']);		    		
    				$this->view->message = "Les fragments sont chargés."; 
    				break;
    				
    		}
    }    
    
    function importCrible()
    {
		$this->initInstance();
    	
		//création des objets
    		$s = new Flux_Site($this->idBase);
    		$s->bTrace = true;
    		$s->bTraceFlush = true;
    		$bnf = new Flux_Databnf();
    		$s->dbE = new Model_DbTable_Flux_Exi($s->db);
    		$s->dbU = new Model_DbTable_Flux_Uti($s->db);
    		$s->dbT = new Model_DbTable_Flux_Tag($s->db);
    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
    		$s->dbR = new Model_DbTable_Flux_Rapport($s->db);
    		$s->dbM = new Model_DbTable_Flux_Monade($s->db);

    		//on récupère le document racine
		$idDocRacine = $s->dbD->ajouter(array("titre"=>"Editeur de réseaux d'influences","url"=>"jdc/public/editinflu","tronc"=>"application"));    		    		    		
    		//on récupère l'existence racine
    		$idERacine = $s->dbE->ajouter(array("nom"=>"EditInflu"));
		
    		//on ajoute le document csv
		$idDoc = $s->dbD->ajouter(array("titre"=>"Catégorisation des rapports entre biologie et littérature  (réponses)"
			,"url"=>$this->urlGoogleCVS, "parent"=>$idDocRacine, "tronc"=>"crible"));    		
		
    		//on ajoute le tag global
    		$idTagG = $s->dbT->ajouter(array("code"=>"Cribles biolographes"));
    		
    		//on ajoute les tags génériques
    		$idTagCatAppli = $s->dbT->ajouter(array("code"=>"Catégories de l'application","parent"=>$idTagG)); 	    				    		
    		$idTagCrud = $s->dbT->ajouter(array("code"=>"CRUD","parent"=>$idTagCatAppli)); 	    				
    		$idTagCatNotion = $s->dbT->ajouter(array("code"=>"Catégories de notion","parent"=>$idTagG)); 	    				    		
    		$idTagRameau = $s->dbT->ajouter(array("code"=>"Matière Rameau","parent"=>$idTagCatNotion)); 	    				
    		$idTagNotion = $s->dbT->ajouter(array("code"=>"Notions","parent"=>$idTagCatNotion));
    		//on ajoute un enfant pour récupérer les tags génériques
    		$idT = $s->dbT->ajouter(array("code"=>"Vide (physique)","uri"=>"http://data.bnf.fr/ark:/12148/cb12573072b","parent"=>$idTagRameau));  	    				
	    	$s->dbETD->ajouter(array("exi_id"=>$idERacine,"tag_id"=>$idT,"doc_id"=>$idDoc));
    		$idT = $s->dbT->ajouter(array("code"=>"Vide","parent"=>$idTagNotion));  	    				
	    	$s->dbETD->ajouter(array("exi_id"=>$idERacine,"tag_id"=>$idT,"doc_id"=>$idDoc));
    		$idT = $s->dbT->ajouter(array("code"=>"Document ajouté","parent"=>$idTagCrud)); 	    				
	    	$s->dbETD->ajouter(array("exi_id"=>$idERacine,"tag_id"=>$idT,"doc_id"=>$idDoc));
    		
		//problème ssl sur mac
		$this->urlGoogleCVS = WEB_ROOT."/data/biolographes/CategorisationRapportsNew.csv";
		
		//définition des regroupements
		$arrTagGroupe = array('Professions' => "Catégories d'acteur"
				,'Fonctions' => "Catégories d'acteur"
				,'Spécialités scientifiques' => "Catégories d'acteur"
				,'Académies' => "Catégories de lieu"
				,'Sociétés savantes' => "Catégories de lieu"
				,'Universités françaises' => "Catégories de lieu"
				,'Sociétés savantes françaises' => "Catégories de lieu"
				,'Universités allemandes' => "Catégories de lieu"
				,'Sociétés savantes allemandes' => "Catégories de lieu"
				,'Espaces de sociabilité: cercles et salons mondains ou littéraires' => "Catégories de lieu"
				,'Autres lieux de savoirs' => "Catégories de lieu"
				,'Rapports Acteur → Acteur' => "Catégories de rapport"
				,'Rapports Acteur → Lieu' => "Catégories de rapport"
				,'Rapports Acteur → Notion' => "Catégories de rapport"
				,'Rapports Notion → Acteur' => "Catégories de rapport"
				,'Rapports Acteur → Document' => "Catégories de rapport"
				,'Rapports Document → Notion' => "Catégories de rapport"
				,'Notions de biologie' => "Catégories de notion"
				,'Notions de Science de la vie' => "Catégories de notion"
				,'Notions de Science de la Terre' => "Catégories de notion"
				,'Notions d\'Anatomie' => "Catégories de notion"
				,'Villégiatures' => "Catégories de lieu");		
    		//chargement des csv
    		$csv = $s->csvToArray($this->urlGoogleCVS,0,",");
    		$arrCol = array();
    		$nbCol = 23;
    		foreach ($csv as $c) {
    			$nom = $c[23];
    			if(!$arrCol){
    				//récupère la liste des colonnes
    				for ($i = 1; $i < $nbCol; $i++) {
    					//récupère le tag de regroupement
    					$idTagGroupe = $s->dbT->ajouter(array("code"=>$arrTagGroupe[$c[$i]],"parent"=>$idTagG)); 	    				
    					//enregistre les tags    					
	    				$idT = $s->dbT->ajouter(array("code"=>$c[$i],"parent"=>$idTagGroupe)); 
	    				$arrCol[$i] = array("tag_id"=>$idT,"code"=>$c[$i]);   					
    					$s->trace("Colonne $i :".$c[$i]." - groupe:".$arrTagGroupe[$c[$i]]);
    				}
    				//$s->trace("Tableau des colonnes :",$arrCol);
    			}else{
    				//enregistre l'utilisateur
    				$idUti = $s->dbU->ajouter(array("login"=>$nom,"mdp"=>$nom));
    				//enregistre l'existence
    				$idE = $s->dbE->ajouter(array("nom"=>$nom,"uti_id"=>$idUti));
    				//lie l'existence à l'utilisateur pour gérer les droits CRUD
    				$s->dbUE->ajouter(array("uti_id"=>$idUti, "exi_id"=>$idE)); 
    				//lie l'existence à l'utilisateur racine pour gérer les droits CRUD
    				$s->dbUE->ajouter(array("uti_id"=>$idERacine, "exi_id"=>$idE)); 
    				$s->trace("crible de :".$nom." ".$idE);
    				for ($i = 1; $i < $nbCol; $i++) {
    					if($c[$i]){
		    				$s->trace("Colonne ".$i." : ".$c[$i]);
		    				$arrT = explode(",",$c[$i]);
		    				foreach ($arrT as $t) {
			    				//enregistre les tags
	    						$idT = $s->dbT->ajouter(array("code"=>trim($t),"parent"=>$arrCol[$i]["tag_id"])); 
	    						//enregistre le lien avec l'existence
	    						$s->dbETD->ajouter(array("exi_id"=>$idE,"tag_id"=>$idT,"doc_id"=>$idDoc));
	    						//enregistre le lien avec l'existence racine
	    						$s->dbETD->ajouter(array("exi_id"=>$idERacine,"tag_id"=>$idT,"doc_id"=>$idDoc));
	    						$s->trace($nom." référence enregistrée : ".$arrCol[$i]["code"]." ".$t);	    						
	    					}
    					}
    				}
    			}
    		}
    }	    

    public function editeurAction()
    {
		$this->initInstance();
    		$this->view->connect =  $this->_getParam('connect', 0);
    }	    
    
        
	function importActeur()
    {
		$this->initInstance();
    	
		//création des objets
    		$s = new Flux_Site($this->idBase);
    		$s->bTrace = true;
    		//$s->bTraceFlush = true;
    		$bnf = new Flux_Databnf();
    		$s->dbT = new Model_DbTable_Flux_Tag($s->db);
    		$s->dbE = new Model_DbTable_Flux_Exi($s->db);
    		$s->dbUE = new Model_DbTable_Flux_UtiExi($s->db);
    		$s->dbETD = new Model_DbTable_Flux_ExiTagDoc($s->db);
    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
    		
    		//on récupère le document racine
		$idDocRacine = $s->dbD->ajouter(array("titre"=>"Editeur de réseaux d'influences","url"=>"jdc/public/biolographes"));    		    		    		
    		    		
		//problème ssl sur mac
		$url = WEB_ROOT."/data/biolographes/appartenances_savants.csv";
    		
		//on ajoute le document csv
		$idDoc = $s->dbD->ajouter(array("titre"=>"Appartenance savant","url"=>$url,"parent"=>$idDocRacine, ));    		
		
    		//on ajoute le tag global
    		$TagGlobal = $s->dbT->findByCode("Cribles biolographes");
    		$idTagAct = $s->dbT->ajouter(array("code"=>"Acteur","parent"=>$TagGlobal["tag_id"]));
    		$idTagSpe = $s->dbT->ajouter(array("code"=>"Spécialités scientifiques"));
    		$idTagSS = $s->dbT->ajouter(array("code"=>"Sociétés savantes"));
    		
    		//chargement des csv
    		$csv = $s->csvToArray($url,0,",");
    		$nbCol = 14;
    		foreach ($csv as $c) {
			//enregistre l'existence
    			$idE = $s->dbE->ajouter(array("nom"=>$c[0],"prenom"=>$c[1]));
			$s->dbETD->ajouter(array("exi_id"=>$idE,"tag_id"=>$idTagAct,"doc_id"=>$idDoc));
    			//lie l'existence à l'utilisateur admin pour gérer les droits CRUD
			$s->dbUE->ajouter(array("uti_id"=>1, "exi_id"=>$idE)); 
			
	    		$s->trace("acteur enregistré : ".$c[0]." ".$c[1]." = ".$idE);
	    		//ajoute la spécialité
	    		if($c[2]){	    			
	    			$idTag = $s->dbT->ajouter(array("code"=>$c[2],"parent"=>$idTagSpe));
				$s->dbETD->ajouter(array("exi_id"=>$idE,"tag_id"=>$idTag,"doc_id"=>$idDoc));
		    		$s->trace("spécialité enregistrée : ".$c[2]);
	    		}    		
			for ($i = 3; $i <= $nbCol; $i++) {
    				if($c[$i]){
	    				//ajoute la société savante
	    				$idTag = $s->dbT->ajouter(array("code"=>$c[$i],"parent"=>$idTagSS));
					$s->dbETD->ajouter(array("exi_id"=>$idE,"tag_id"=>$idTag,"doc_id"=>$idDoc));
		    			$s->trace("société savante enregistrée : ".$c[$i]);
    				}
    			}
    		}
    }	
    
    function initInstance(){
		$this->view->ajax = $this->_getParam('ajax');
    		$this->view->idBase = $this->idBase = $this->_getParam('idBase', $this->idBase);
		
		$auth = Zend_Auth::getInstance();
		$this->ssUti = new Zend_Session_Namespace('uti');
		if ($auth->hasIdentity()) {						
			// l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $this->view->uti = json_encode($this->ssUti->uti);
		}else{			
		    //$this->view->uti = json_encode(array("login"=>"inconnu", "id_uti"=>0));
		    $this->ssUti->redir = "/editinflu";
		    $this->ssUti->dbNom = $this->idBase;
		    if($this->view->ajax)$this->_redirect('/auth/finsession');		    
		    else $this->_redirect('/auth/login');
		}
		    	
    }
    
    
}

<?php

/**
 * GAPAII
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class GapaiiController extends Zend_Controller_Action {
	
	//var $idBase = "flux_gapaii";
	var $idBase = "flux_proverbes";
	var $idBaseSpip = "spip_proverbe";
	var $idOeu = 57;//37;//
	var $idUti = 2;
	var $idDoc = 1;
	var $idCpt = 169964;// poeme stein 158393;//158278 - test poème;//;
	var $idGeo;
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		if($this->_getParam('idBase')) $this->idBase = $this->_getParam('idBase');
		if($this->_getParam('idOeu')) $this->idOeu = $this->_getParam('idOeu');
		if($this->_getParam('idDoc')) $this->idDoc = $this->_getParam('idDoc');
		if($this->_getParam('idCpt')) $this->idCpt = $this->_getParam('idCpt');
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $ssUti = new Zend_Session_Namespace('uti');
		    $this->view->idUti = $ssUti->idUti;
		}else{
			$this->view->idUti = $this->idUti;
		}
		$this->view->idBase = $this->idBase;
		$this->view->idOeu = $this->idOeu;
		$this->view->idDoc = $this->idDoc;
		$this->view->idCpt = $this->idCpt;
	}

	public function savegenAction() {
		$this->initInstance();
		
		//enregistre le document généré
		if($this->_getParam('data', 0)){
			//dans la base flux
			$g = new Flux_Gapaii($this->idBase);
			$arrDoc = $g->saveGen($this->idBase, $this->_getParam('data'),$this->idUti);
			//dans la base SPIP
			//$s = new Flux_Spip($this->idBaseSpip); 
			//$s->creaArticleFromFlux($arrDoc);
			$this->view->idDoc = $arrDoc["doc_id"];
		}
	}

	public function saverepquestAction() {
		$this->initInstance();
		
		$g = new Flux_Gapaii($this->idBase);
		$g->dbT = new Model_DbTable_Flux_Tag($g->db);
		$g->dbD = new Model_DbTable_Flux_Doc($g->db);
		$g->dbR = new Model_DbTable_Flux_Rapport($g->db);
		$g->dbM = new Model_DbTable_Flux_Monade($g->db);
		$g->dbA = new Model_DbTable_flux_acti($g->db);
		$g->dbS = new Model_DbTable_Flux_Spip($g->db);
		
		//enregistre les paramètres
		$quest = $this->_getParam('quest');
		$gen = $this->_getParam('gen');
		$acti = $this->_getParam('acti');
		
		$this->idMonade = $g->dbM->ajouter(array("titre"=>"gapaï"),true,false);
		$this->idDocEvalRoot = $g->dbD->ajouter(array("titre"=>"évaluations"));
		
		//enregistre la question
		if($quest){
			$idDocQRoot = $g->dbD->ajouter(array("titre"=>"questions"));
			$idDocQ = $g->dbD->ajouter(array("titre"=>$quest["questTitre"],"parent"=>$idDocQRoot,"data"=>json_encode($quest)));
			//enregistre le rapport avec le doc évalué
			$idTagPre = $g->dbT->ajouter(array("code"=>"question -> document"));
			$idRapQ = $g->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idDocQ,"src_obj"=>"doc"
				,"pre_id"=>$idTagPre,"pre_obj"=>"tag"
				,"dst_id"=>$gen,"dst_obj"=>"doc"
				));
		}
		
		//enregistre l'axe évalué
		if($this->_getParam('axe')){
			//$this->saveRepAxe($this->_getParam('axe'));	
		}
		
		//enregistre tous les axes du radar
		if($this->_getParam('radarData')){
			$data = $this->_getParam('radarData');
			$idDocRadar = $g->dbD->ajouter(array("titre"=>"Evaluation radar","parent"=>$this->idDocEvalRoot,"data"=>json_encode($data)));
			//enregistre chaque axe
			foreach ($data as $axe) {
				$this->saveRepAxe($axe);	
			}
			//enregistre le rapport entre la question, l'utilisateur et l'action
			$idRap = $g->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idRapQ,"src_obj"=>"rapport"
				,"pre_id"=>$this->idUti,"pre_obj"=>"uti"
				,"dst_id"=>$idAct,"dst_obj"=>"acti"
				));
			//enregistre la réponse à la question par l'utilistaeur
			$idRapRep = $g->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idRap,"src_obj"=>"rapport"
				,"pre_id"=>$this->idUti,"pre_obj"=>"uti"
				,"dst_id"=>$idDocRadar,"dst_obj"=>"doc"
				));				
		}
		
		
	}
	
	function saveRepAxe($axe){

		$idTag = $g->dbT->ajouter(array("code"=>$axe["axis"]));
		$idAct = $g->dbA->ajouter(array("code"=>$acti));
		$g->dbS->ajouter(array("id_flux"=>$idTag,"id_spip"=>$axe["idSpip"],"obj_flux"=>"tag","obj_spip"=>"mots"));	
		//enregistre le rapport entre la question, l'utilisateur et l'action
		$idRap = $g->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			,"src_id"=>$this->idRapQ,"src_obj"=>"rapport"
			,"pre_id"=>$this->idUti,"pre_obj"=>"uti"
			,"dst_id"=>$idAct,"dst_obj"=>"acti"
			));
		//enregistre la réponse
		$idDocEval = $g->dbD->ajouter(array("titre"=>"Evaluations axe","parent"=>$this->idDocEvalRoot));
		$idDocRep = $g->dbD->ajouter(array("titre"=>"rapport=".$idRap,"data"=>json_encode($axe),"parent"=>$idDocEval),false);
		//enregistre la réponse à la question par l'utilistaeur
		$idRapRep = $g->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			,"src_id"=>$idTag,"src_obj"=>"tag"
			,"pre_id"=>$idRap,"pre_obj"=>"rapport"
			,"dst_id"=>$idDocRep,"dst_obj"=>"doc"
			,"niveau"=>$axe["value"]
			));				
		
	}
	
	public function savesemevalAction() {
		//récupère les informations de la palette
		//print_r($this->getRequest()->getParams());
		if($this->_getParam('idBase', 0) && $this->_getParam('idDoc', 0) && $this->_getParam('idUti', 0) && $this->_getParam('sem', 0)){
			$g = new Flux_Gapaii($this->_getParam('idBase', 0));
			$g->saveSemEval($this->_getParam('idBase'), $this->_getParam('idDoc'), $this->_getParam('idUti'), $this->_getParam('sem'));
			//$this->view->sem = $this->_getParam('sem');
		}else echo "pas de donnée !";
	}

	public function getevalAction() {
		//récupère les critère de l'évaluation
		//print_r($this->getRequest()->getParams());
		$g = new Flux_Gapaii($this->_getParam('idBase', $this->idBase));
		$this->view->data = $g->getEval($this->_getParam('idDoc'), $this->_getParam('idUti'), $this->_getParam('idTag'));
	}

	public function importAction() {
		//importation des données
		
	}
	
	public function evalAction() {
		$this->initInstance();
		//vue pour l'évaluation des fragments
		
	}
	
	/**TODO: utiliser ce type de requête pour proposer des images plutôt que des mots
	 * http://thenounproject.com/search/?q=animal
	 */
	
    function initInstance(){
		if($this->_getParam('idBase')) $this->idBase = $this->_getParam('idBase');
		if($this->_getParam('idBaseSpip')) $this->idBaseSpip = $this->_getParam('idBaseSpip');
		if($this->_getParam('idOeu')) $this->idOeu = $this->_getParam('idOeu');
		if($this->_getParam('idDoc')) $this->idDoc = $this->_getParam('idDoc');
		if($this->_getParam('idCpt')) $this->idCpt = $this->_getParam('idCpt');
		$this->idGeo = $this->_getParam('idGeo',-1);
		
		$this->view->idBase = $this->idBase;
		$this->view->idOeu = $this->idOeu;
		$this->view->idDoc = $this->idDoc;
		$this->view->idCpt = $this->idCpt;
		$this->view->idGeo = $this->idGeo;
		
		$auth = Zend_Auth::getInstance();
		$this->ssUti = new Zend_Session_Namespace('uti');
		if ($auth->hasIdentity()) {						
			// l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $this->view->uti = json_encode($this->ssUti->uti);
		    $this->view->idUti = $ssUti->idUti;
		}else{			
		    //$this->view->uti = json_encode(array("login"=>"inconnu", "id_uti"=>0));
		    //$this->ssUti->redir = "/gapaii";
		    	$this->view->idUti = $this->idUti;
			$this->ssUti->dbNom = $this->idBase;
		    if($this->view->ajax)$this->_redirect('/auth/finsession');		    
		    else $this->_redirect('/auth/login');
		}
		
    }
	
}

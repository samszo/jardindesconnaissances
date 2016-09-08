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
	    
	    $ei = new Flux_EditInflu($this->view->idBase);
	    $dbD = new Model_DbTable_Flux_Doc($ei->db);
	    
	    /* récupère les cribles
	     */
	    $this->view->rsCrible = json_encode($ei->getCribles());
	    
	    /* récupère les graph
	     */
	    $this->view->rsGraph = json_encode($ei->getGraphInfluence());
	    
	}


    public function navigrameauAction()
    {
    		$this->view->idBNF = $this->_getParam('idBNF');
    		$this->view->fontSize = $this->_getParam('fontSize',18);
    		$this->view->nivMax = $this->_getParam('nivMax',1);
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
				$ei = new Flux_EditInflu($this->view->idBase,true);
				$ei->importCrible($this->_getParam('csv', ''));
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
    		//initialise les objets
		$this->initInstance();
	    $ei = new Flux_EditInflu($this->view->idBase);
	    $ei->bTrace = false;
	    $ei->idGeo = 0;
	    
    		//récupère les paramètres
    		$params = $this->_request->getParams();
    		//$ei->trace("params",$params);
    		$oName = $params['obj'];
    		$idUti = $this->_getParam('idUti', $this->ssUti->uti["uti_id"]);
    		
    		//enlève les paramètres Zend
    		$params = $this->cleanParamZend($params);
    		
    		//ajout suivant les cas	    
    		switch ($oName) {
    			case "crible":    				
    				$this->view->rs = $ei->creaCrible($idUti, $params);
    				$this->view->message = "Le crible est ajouté"; 
    				break;
    			case "acteur":
    				$this->view->rs = $ei->creaActeur($idUti, $params);    				
    				$this->view->message = "L'acteur est ajouté"; 
    				break;
    			case "graph":
    				$this->view->rs = $ei->creaGraph($idUti, $params);
    				$this->view->message = "La graph est ajouté"; 
    				break;
    			case "tag":
    				$this->view->rs = $ei->creaTag($idUti, $params);
    				$this->view->message = "Le tag est ajouté"; 
    				break;
    			case "rapport":
    				$this->view->rs = $ei->creaRapport($idUti, $params);
    				$this->view->message = "Le rapport est ajouté"; 
    				break;
    			case "doc":
    				$this->view->rs = $ei->creaDoc($idUti, $params);
    				$this->view->message = "Le document est ajouté"; 
    				break;
    	    		}
    	    	$ei->trace("FIN");
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

    		$params = $this->cleanParamZend($params);
    		
    		//et les paramètres de l'ajout
		$id = $params['recid'];
    		unset($params['recid']);
    		
    		//ajout suivant les cas	    
    		switch ($oName) {
    			case "crible":
    				//initialise les objets
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
		    		//met à jour les données
    				$this->view->rs = $s->dbD->edit($id, $params);
    				$this->view->message = "Le crible est mis à jour"; 
    				break;
    			case "acteur":
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
    			case "crible":
    				//initialise les objets
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
    				$this->view->rs = $s->dbD->remove($params['id']);
    				$this->view->message = "Le crible est supprimé."; 
    				break;
    			case "graph":
    				//initialise les objets
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
    				$this->view->rs = $s->dbD->remove($params['id']);
    				$this->view->message = "Le graph est supprimé."; 
    				break;
    			case "doc":
    				//initialise les objets
		    		$s->dbD = new Model_DbTable_Flux_Doc($s->db);
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
		    		$ei = new Flux_EditInflu($this->view->idBase);
		    		$docs = $ei->getDocInfluence($this->_getParam('idCrible', 0));
		    		$notions = array();
				$acteurs = $ei->getExiByCrible($this->_getParam('idCrible', 0));
		    		
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
    
    public function editeurAction()
    {
		$this->initInstance();
    		$this->view->connect =  $this->_getParam('connect', 0);
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
    
    function cleanParamZend($params){
    		//enlève les paramètres Zend
    		unset($params['controller']);
    		unset($params['action']);
    		unset($params['module']);
    		unset($params['idBase']);
    		unset($params['ajax']);
    		unset($params['idUti']);
		unset($params['obj']);
		    		
    		return $params;
    	
    }
}

<?php

/**
 * EditinfluController
 * 
 * Pour le projet EditInflu
 * cartographier les réseaux d'influences à travers l'Open Linked Data
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
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
    		$this->view->label = $this->_getParam('label');
    		$this->view->fontSize = $this->_getParam('fontSize',48);
    		$this->view->nivMax = $this->_getParam('nivMax',1);
    		$this->view->urlData = $this->_getParam('urlData',"../flux/databnf?obj=rameau&nivMax=".$this->view->nivMax);
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
	    //echo 'post_max_size='.ini_get('post_max_size');
	    
    		//récupère les paramètres
    		$params = $this->_request->getParams();
    		//$ei->trace("params",$params);
    		$oName = $this->_getParam('obj');
    		$ei->trace("oName ".$oName);
    		$idUti = $this->_getParam('idUti', $this->ssUti->uti["uti_id"]);
    		$ei->trace("idUti ".$idUti);
    		
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
    		$s->trace("params clean",$params);
    		
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
		    		if(isset($params['data']))$params['data'] = json_encode($params['data'],JSON_NUMERIC_CHECK);		    		
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
    		    case "acteur":
    		        //initialise les objets
    		        $s->dbE = new Model_DbTable_Flux_Exi($s->db);
    		        $this->view->rs = $s->dbE->remove($params['id']);
    		        $this->view->message = "L'acteur est supprimé.";
    		        break;
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

    public function diagrammeAction()
    {
        $this->initInstance();
        $this->view->connect =  $this->_getParam('connect', 0);
    }
    
    public function tritagAction()
    {
    	$this->initInstance();
    	$this->view->connect =  $this->_getParam('connect', 0);
    }

    public function linkboardAction()
    {
		$this->idBase = 'flux_linkboard';
    	$this->initInstance("/linkboard");
    }
	

    function initInstance($redir=""){
        $this->view->ajax = $this->_getParam('ajax');
        $this->view->idBase = $this->idBase = $this->_getParam('idBase', $this->idBase);
        
        $auth = Zend_Auth::getInstance();
        $this->ssUti = new Zend_Session_Namespace('uti');
        $ssGoogle = new Zend_Session_Namespace('google');
        
        if ($auth->hasIdentity() || isset($this->ssUti->uti)) {
            //utilisateur authentifier
            $this->ssUti->uti['mdp'] = '***';
            $this->view->login = $this->ssUti->uti['login'];
            $this->view->uti = json_encode($this->ssUti->uti);
        }elseif($this->_getParam('idUti') ){
            //authentification CAS ou google
            $s = new Flux_Site($this->idBase);
            $dbUti = new Model_DbTable_Flux_Uti($s->db);
            $uti = $dbUti->findByuti_id($this->_getParam('idUti'));
            $this->ssUti->uti = $uti;
            $this->ssUti->uti['mdp'] = '***';
            $this->view->login = $this->ssUti->uti['login'];
            $this->view->uti = json_encode($uti);                        
        }else{
            //$this->view->uti = json_encode(array("login"=>"inconnu", "id_uti"=>0));
            $this->ssUti->redir = "/editinflu".$redir;
            $this->ssUti->dbNom = $this->idBase;
            if($this->view->ajax)$this->redirect('/auth/finsession');
            else $this->redirect('/auth/connexion');
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

<?php

/**
 * ICE
 * 
 * Indice de Complexité Existentiel
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
require_once 'Zend/Controller/Action.php';

class ICEController extends Zend_Controller_Action {
	
	var $dbNom = "flux_ice";
	var $idUti = 1;
	var $redir = '/auth/login?redir=ICE&idBase=';
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');
		$this->view->idBase = $this->dbNom;
		$this->redir .= $this->dbNom;
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $ssUti = new Zend_Session_Namespace('uti');
		    $this->view->idUti = $ssUti->idUti;
		    $this->view->redir = $this->redir;
		    //récupère les monades disponibles
			$s = new Flux_Site($this->dbNom);
			//on créer une nouvelle monade
			$dbM = new Model_DbTable_Flux_Monade($s->db);
		    $this->view->monades = $dbM->getAll();
		}else{
			$this->_redirect($this->redir);
		}
	}

	public function ajoutmonadeAction() {
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
				// l'identité existe ; on la récupère
				$ssUti = new Zend_Session_Namespace('uti');
				$o = new Flux_Site($this->_getParam('db', 0));
				//echo "ssUti->idUti ".$ssUti->idUti;
				//on créer une nouvelle monade
				$dbM = new Model_DbTable_Flux_Monade($o->db);
				$r = $dbM->creer(array("titre"=>$this->_getParam('titre'), "uti_id"=>$ssUti->idUti));				
	    			$r["ICE"] = $dbM->getICE($r["monade_id"]);    			
	    			$this->view->data = $r;
    			//
			}else{
				if(isset($ssUti->redir))
				    $this->_redirect($ssUti->redir);
				else
				    $this->_redirect('/auth/login');
			}
	}    

	public function ajoutdocAction() {
		$this->initInstance();
		$o = new Flux_Site($this->idBase);
		//on ajoute un nouveau document pour la monade
		$dbM = new Model_DbTable_Flux_Monade($o->db);
		$dbM->ajoutDoc($this->_getParam('idMon'), $this->_getParam('data'));				
    		$r["ICE"] = $dbM->getICE($this->_getParam('idMon'));    			
    		$this->view->data = $r;
	}    

	public function editmonadeAction() {
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
			$o = new Flux_Site($this->_getParam('db', 0));
			$dbM = new Model_DbTable_Flux_Monade($o->db);		
			$this->view->data = $dbM->edit($this->_getParam('idMon', 0), array("titre"=>$this->_getParam('titre', "")));
			
		}else{
			if(isset($ssUti->redir))
			    $this->_redirect($ssUti->redir);
			else
			    $this->_redirect('/auth/login');
		}
	}    

	public function getmonadeAction() {
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
			$s = new Flux_Site($this->_getParam('db', 0));
			$dbM = new Model_DbTable_Flux_Monade($s->db);
			$r = $dbM->findById($this->_getParam('idMon'));
			//on récupère les infos				    	
   			$r["ICE"] = $dbM->getICE($this->_getParam('idMon'));    			
			
			$this->view->data = $r;
			
		}else{
			if(isset($ssUti->redir))
			    $this->_redirect($ssUti->redir);
			else
			    $this->_redirect('/auth/login');
		}
	}    

	public function getacteursAction() {
		// l'identité existe ; on la récupère
		$s = new Flux_Site($this->_getParam('db', 0));
		$db = new Model_DbTable_Flux_Exi($s->db);
		$r = $db->getExiByTag('Acteur');
		$this->view->data = $r;
			
	}    
	
	public function removemonadeAction() {
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
			$o = new Flux_Site($this->_getParam('db', 0));
			$dbM = new Model_DbTable_Flux_Monade($o->db);		
			$dbM->remove($this->_getParam('idMon', 0));
			$this->view->data = "OK";
		}else{
			if(isset($ssUti->redir))
			    $this->_redirect($ssUti->redir);
			else
			    $this->_redirect('/auth/login');
		}
	} 	
	
	public function animationAction() {
		$this->view->urlSVG = $this->_getParam('urlSVG','../svg/modeleAlgo.svg');
	}

	public function iemlAction(){
		//M:M:.A.-M:M:.A.-F.O.-'
    	$this->view->urlData = $this->_getParam('urlData',"../flux/ieml?f=getDicoItem&ieml=".$this->_getParam('code', "M:M:.a.-M:M:.a.-f.o.-%27"));
    	$this->view->urlDico = $this->_getParam('urlDico',"../flux/ieml?f=getDicoPlus");
	}

	public function editeurAction(){
    	$this->view->urlIeml = "../flux/ieml?f=getDicoItem&ieml=";
    	$this->view->urlDico = $this->_getParam('urlDico',"../flux/ieml?f=getDicoPlus");
	}

	public function sauveformAction(){
		//$this->initInstance();
		$p = $this->_request->getParams();
		print_r($p);
		if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');
		$this->view->idBase = $this->dbNom;

		//$ice = new Flux_Ice($this->view->idBase,$this->_getParam('trace',false));
		//$ice->sauveFormSem($this->_getParam('questions'),$this->_getParam('params'),$this->_getParam('reponses'));

	}

    function initInstance(){
		$this->view->ajax = $this->_getParam('ajax');
    	$this->view->idBase = $this->idBase = $this->_getParam('idBase', $this->idBase);
		
		$auth = Zend_Auth::getInstance();
		$ssUti = new Zend_Session_Namespace('uti');
		if ($auth->hasIdentity()) {						
			// l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $this->view->uti = json_encode($ssUti->uti);
		}else{			
		    //$this->view->uti = json_encode(array("login"=>"inconnu", "id_uti"=>0));
		    $ssUti->redir = "/ice";
		    $ssUti->dbNom = $this->idBase;
		    if($this->view->ajax)$this->_redirect('/auth/finsession');		    
		    else $this->_redirect('/auth/login');
		}
		    	
    }
	
}

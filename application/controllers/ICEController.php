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

	public function getlisteformAction(){
		$this->view->idBase = $this->dbNom = $this->_getParam('idBase');
		$ice = new Flux_Ice($this->dbNom,$this->_getParam('trace',false));
		$this->view->data = $ice->getListeForm();
	}

	public function getformAction(){
		$this->view->idBase = $this->dbNom = $this->_getParam('idBase');
		$ice = new Flux_Ice($this->dbNom,$this->_getParam('trace',false));
		$this->view->data = $ice->getForm($this->_getParam('idForm'),$this->_getParam('reponse'));
	}


	public function sauveformAction(){
		//$this->initInstance();
		$p = $this->_request->getParams();
		$this->view->idBase = $this->dbNom = $this->_getParam('idBase');
		$rs = array('idBase' => $this->dbNom, 'erreur'=>0);
		$ice = new Flux_Ice($this->dbNom,$this->_getParam('trace',false));

		$idForm = $ice->sauveFormSem($p['form']['params']);
		$rs['idForm']=$idForm;
		$rs['q']=array();
		$refs = array();
		foreach ($p['questions'] as $q) {
			if(!$q['idForm'])$q['idForm']=$idForm;
			//enregistre la question			
			$idQ = $ice->sauveQuestionFormSem($q);
			$refs['q'.$q['recid']]=$idQ;
			$arr = array('idQ'=>$idQ,'recid'=>$q['recid'],'rp'=>array(),'liens'=>array());
			//enregistre les propositions
			foreach ($q['propositions'] as $prop) {
				if(!$$prop['idQ'])$prop['idQ']=$idQ;
				$idP=$ice->sauvePropositionFormSem($prop);
				$arr['p'][] = array('idDico'=>$prop['idDico'],'recid'=>$prop['recid'],'idP'=>$idP);
				$refs['p'.$prop['idDico']]=$idP;
				$refs['precid'.$prop['recid']]=$idP;
			}			
			//enregistre les liens calculés
			foreach ($q['liens'] as $l) {
				//mise des références
				if(!$l['idPS'])$l['idPS']=$refs['precid'.$l['recidS']];
				if(!$l['idPT'])$l['idPT']=$refs['precid'.$l['recidT']];
				if(!$l['idPT'])
					$toto = 2;
				$arr['liens'][] = array('idEdge'=>$l['idEdge'],'idL'=>$ice->sauveLiensFormSem($l));
			}
			$rs['q'][]=$arr;
		}
		$rs['r']=array();
		$rs['c']=array();
		$rs['pc']=array();
		$rs['p']=array();
		//enregistre les réponses des utilisateurs
		foreach ($p['reponses'] as $r) {
			//enregistre la réponse
			$r['idQ'] = $refs['q'.$r['recidQuest']];
			$idR = $ice->sauveReponseFormSem($r);	
			$rs['r'][]=	array('idQ'=>$r['idQ'],'idR'=>$idR,'recidQuest'=>$r['recidQuest'],'idUti'=>$r['idUti'],'t'=>$r['t']);					
			//enregistre les choix
			foreach ($r['c'] as $c) {
				//récupère les références
				$c['idR'] = $idR;
				$c['idP'] = $refs['p'.$c['idDico']];
				$idC = $ice->sauveChoixReponseFormSem($c);				
				$rs['c'][]= array('idDico'=>$c['idDico'],'idQ'=>$r['idQ'],'idR'=>$idR,'idP'=>$c['idP'],'idC'=>$idC);
			}
			//enregistre les possibilité de choix
			foreach ($r['pc'] as $pc) {
				//récupère les références
				$pc['idR'] = $idR;
				$pc['idP'] = $refs['p'.$pc['idDico']];
				$idPC = $ice->sauveChoixReponseFormSem($pc,true);				
				$rs['pc'][]= array('idDico'=>$pc['idDico'],'idQ'=>$r['idQ'],'idR'=>$idR,'idP'=>$pc['idP'],'idPC'=>$idPC);
			}

			//enregistre le processus
			foreach ($r['p'] as $p) {
				//récupère les références
				$p['idR'] = $idR;
				$p['idP'] = $refs['p'.$p['idDico']];
				$rs['p'][]= array('idR'=>$p['idR'],'idQ'=>$r['idQ'],'idP'=>$p['idP'],'idProc'=>$ice->sauveProcessusReponseFormSem($p));					
			}

		}
		$this->view->data = $rs;
		
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

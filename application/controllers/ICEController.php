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

class IceController extends Zend_Controller_Action {
	
	var $idBase = "flux_formsem";
	var $idUti = 1;
	var $redir = '/auth/login?redir=ICE&idBase=';
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->initInstance();
		//récupère les monades disponibles
		$s = new Flux_Site($this->idBase);
		//on créer une nouvelle monade
		$dbM = new Model_DbTable_Flux_Monade($s->db);
		$this->view->monades = $dbM->getAll();
	}

	public function ajoutmonadeAction() {
		$this->initInstance();
		$o = new Flux_Site($this->_getParam('db', 0));
		//echo "ssUti->idUti ".$ssUti->idUti;
		//on créer une nouvelle monade
		$dbM = new Model_DbTable_Flux_Monade($o->db);
		$r = $dbM->creer(array("titre"=>$this->_getParam('titre'), "uti_id"=>$ssUti->idUti));				
		$r["ICE"] = $dbM->getICE($r["monade_id"]);    			
		$this->view->data = $r;
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
		$this->initInstance();
		$o = new Flux_Site($this->idBase);
		$dbM = new Model_DbTable_Flux_Monade($o->db);		
		$this->view->data = $dbM->edit($this->_getParam('idMon', 0), array("titre"=>$this->_getParam('titre', "")));
	}    

	public function getmonadeAction() {
		$this->initInstance();
		$s = new Flux_Site($this->idBase);
		$dbM = new Model_DbTable_Flux_Monade($s->db);
		$r = $dbM->findById($this->_getParam('idMon'));
		//on récupère les infos				    	
		$r["ICE"] = $dbM->getICE($this->_getParam('idMon'));    			
		
		$this->view->data = $r;
	}    

	public function getacteursAction() {
		// l'identité existe ; on la récupère
		$s = new Flux_Site($this->_getParam('db', 0));
		$db = new Model_DbTable_Flux_Exi($s->db);
		$r = $db->getExiByTag('Acteur');
		$this->view->data = $r;
			
	}    
	
	public function removemonadeAction() {
		$this->initInstance();
		$s = new Flux_Site($this->idBase);
		$dbM = new Model_DbTable_Flux_Monade($s->db);		
		$dbM->remove($this->_getParam('idMon', 0));
		$this->view->data = "OK";
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
    	$this->view->uti = "[]";
		$this->initInstance('editeur');

    	$this->view->urlIeml = "../flux/ieml?f=getDicoItem&ieml=";
    	$this->view->urlDico = $this->_getParam('urlDico',"../flux/ieml?f=getDicoPlus");
	}

	public function getlisteformAction(){
		$this->view->idBase = $this->idBase = $this->_getParam('idBase');
		$ice = new Flux_Ice($this->idBase,$this->_getParam('trace',false));
		$this->view->data = $ice->getListeForm();
	}

	public function getformAction(){
		$this->view->idBase = $this->idBase = $this->_getParam('idBase');
		$ice = new Flux_Ice($this->idBase,$this->_getParam('trace',false));
		if($this->_getParam('idForm'))
			$this->view->data = $ice->getForm($this->_getParam('idForm'),$this->_getParam('reponse'));
		if($this->_getParam('idQ'))
			$this->view->data = $ice->getQuest($this->_getParam('idQ'));

	}

	public function deleteformAction(){
		$this->initInstance('editeur');
		$ice = new Flux_Ice($this->idBase,$this->_getParam('trace',false));
		$rs = array('result' => 0, 'erreur'=>0);
		switch ($this->_getParam('table')) {
			case 'form':
				$forms = $this->_getParam('data');
				foreach ($forms as $f) {
					$rs['result']+=$ice->deleteFormSem($f);
				}				
				break;
			case 'question':
				$quest = $this->_getParam('data');
				foreach ($quest as $q) {
					$rs['result']+=$ice->deleteQuestSem($q);
				}				
				break;
			case 'prop':
				$prop = $this->_getParam('data');
				foreach ($prop as $p) {
					$rs['result']+=$ice->deletePropSem($p);
				}				
				break;
		}
		$this->view->data = $rs;

	}

	public function updateformAction(){
		$this->initInstance('editeur');
		$p = $this->_request->getParams();
		$ice = new Flux_Ice($this->idBase,$this->_getParam('trace',false));
		$rs = array('result' => array(), 'erreur'=>0);
		$data = $this->_getParam('data');

		switch ($this->_getParam('table')) {
			case 'form':
				$ice->sauveFormSem($data, true);
				break;
			case 'question':
				$ice->sauveQuestionFormSem($data,true);
				break;
			case 'prop':
				$ice->sauvePropositionFormSem($data,false,true);
				break;
			case 'props':
				$data = $this->_getParam('data');
				foreach ($data as $p) {
					$rsP = $ice->sauvePropositionFormSem($p,true);
					$rs['result'][]=$rsP;
				}
				break;
			case 'liens':
				$data = $this->_getParam('data');
				foreach ($data as $p) {
					foreach ($p['liens'] as $l) {
						$rs['result'][] = array('idEdge'=>$l['idEdge'],'idL'=>$ice->sauveLiensFormSem($l));
					}
				}
				break;
			case 'reponse':
				$r = $this->_getParam('data');
				$id = $ice->sauveReponseFormSem($r);	
				//enregistre les choix
				foreach ($r['c'] as $c) {
					$idC = $ice->sauveChoixReponseFormSem($c);				
				}
				//enregistre les possibilité de choix
				foreach ($r['pc'] as $pc) {
					$idPC = $ice->sauveChoixReponseFormSem($pc,true);				
				}
				//enregistre le processus
				foreach ($r['p'] as $p) {
					$idProc=$ice->sauveProcessusReponseFormSem($p);					
				}
				break;
		}

		$this->view->data = $rs;		
	}

	public function addformAction(){
		$this->initInstance('editeur');
		$p = $this->_request->getParams();
		$ice = new Flux_Ice($this->idBase,$this->_getParam('trace',false));
		$rs = array('result' => array(), 'erreur'=>0);

		switch ($this->_getParam('table')) {
			case 'form':
				$data = $this->_getParam('data');
				$id = $ice->sauveFormSem($data);
				$data['idForm']=$id;
				$data['recid']=$id;
				$rs['result']=$data;
				break;
			case 'question':
				$data = $this->_getParam('data');
				$id = $ice->sauveQuestionFormSem($data);
				$data['idQ']=$id;
				$data['recid']=$id;
				$rs['result']=$data;
				break;
			case 'prop':
				$rs['result'] = $ice->sauvePropositionFormSem($this->_getParam('data'),true);
				break;
			case 'props':
				$data = $this->_getParam('data');
				foreach ($data as $p) {
					$rsP = $ice->sauvePropositionFormSem($p,true);
					$rs['result'][]=$rsP;
				}
				break;
			case 'liens':
				$data = $this->_getParam('data');
				foreach ($data as $p) {
					foreach ($p['liens'] as $l) {
						$rs['result'][] = array('idEdge'=>$l['idEdge'],'idL'=>$ice->sauveLiensFormSem($l));
					}
				}
				break;
			case 'reponse':
				$r = $this->_getParam('data');
				$idR = $ice->sauveReponseFormSem($r);	
				//enregistre les choix
				foreach ($r['c'] as $c) {
					$c['idR']=$idR;
					$idC = $ice->sauveChoixReponseFormSem($c);				
				}
				//enregistre les possibilité de choix
				foreach ($r['pc'] as $pc) {
					$pc['idR']=$idR;
					$idPC = $ice->sauveChoixReponseFormSem($pc,true);				
				}
				//enregistre le processus
				foreach ($r['p'] as $p) {
					$p['idR']=$idR;
					$idProc=$ice->sauveProcessusReponseFormSem($p);					
				}
				break;
		}

		$this->view->data = $rs;
		
	}

	public function navigiemlAction(){
    	$this->view->urlDico = $this->_getParam('urlDico',"../../data/ieml/ieml_dictionary.json");
    	$this->view->ieml = $this->_getParam('ieml',"E:");
	
	}

	public function sauveformAction(){
		$this->initInstance('editeur');
		$p = $this->_request->getParams();
		$ice = new Flux_Ice($this->idBase,$this->_getParam('trace',false));
		$rs = array('result' => array(), 'erreur'=>0);


		foreach ($p['forms'] as $f) {
			$ice->getDb($f['f']['bdd']);

			$idForm = $ice->sauveFormSem($f['f']);
			$rsf=array('idForm'=>$idForm,'recid'=>$f['f']['recid'],'q'=>array(),'r'=>array(),'c'=>array(),'pc'=>array(),'p'=>array());
			$refs = array();
			foreach ($f['q'] as $q) {
				if(!$q['idForm'])$q['idForm']=$idForm;
				//enregistre la question			
				$idQ = $ice->sauveQuestionFormSem($q);
				$refs['q'.$q['recid']]=$idQ;
				$arr = array('idQ'=>$idQ,'recid'=>$q['recid'],'rp'=>array(),'liens'=>array());
				//enregistre les propositions
				foreach ($q['propositions'] as $prop) {
					if(!$prop['idQ'])$prop['idQ']=$idQ;
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
				$rsf['q'][]=$arr;
			}
			//enregistre les réponses des utilisateurs
			foreach ($p['reponses'] as $r) {
				//enregistre la réponse
				$r['idQ'] = $refs['q'.$r['recidQuest']];
				$idR = $ice->sauveReponseFormSem($r);	
				$rsf['r'][]=	array('idQ'=>$r['idQ'],'idR'=>$idR,'recidQuest'=>$r['recidQuest'],'idUti'=>$r['idUti'],'t'=>$r['t']);					
				//enregistre les choix
				foreach ($r['c'] as $c) {
					//récupère les références
					$c['idR'] = $idR;
					$c['idP'] = $refs['p'.$c['idDico']];
					$idC = $ice->sauveChoixReponseFormSem($c);				
					$rsf['c'][]= array('idDico'=>$c['idDico'],'idQ'=>$r['idQ'],'idR'=>$idR,'idP'=>$c['idP'],'idC'=>$idC);
				}
				//enregistre les possibilité de choix
				foreach ($r['pc'] as $pc) {
					//récupère les références
					$pc['idR'] = $idR;
					$pc['idP'] = $refs['p'.$pc['idDico']];
					$idPC = $ice->sauveChoixReponseFormSem($pc,true);				
					$rsf['pc'][]= array('idDico'=>$pc['idDico'],'idQ'=>$r['idQ'],'idR'=>$idR,'idP'=>$pc['idP'],'idPC'=>$idPC);
				}
	
				//enregistre le processus
				foreach ($r['p'] as $p) {
					//récupère les références
					$p['idR'] = $idR;
					$p['idP'] = $refs['p'.$p['idDico']];
					$rsf['p'][]= array('idR'=>$p['idR'],'idQ'=>$r['idQ'],'idP'=>$p['idP'],'idProc'=>$ice->sauveProcessusReponseFormSem($p));					
				}
			}
			$rs['result'][]=$rsf;
		}

		$this->view->data = $rs;
		
	}


	public function complexeAction(){
		$ice = new Flux_Ice($this->_getParam('idBase',false),$this->_getParam('trace',false));
		$rs = array('result' => array(), 'erreur'=>0);
		switch ($this->_getParam('q',false)) {
			case 'save':
				$rs['result']=$ice->saveComplexDoc($this->_getParam('idDoc',false),$this->_getParam('tronc',false),$this->_getParam('parent',false));
				break;			
			default:
				$rs['result']=$ice->getComplexEcosystem(
					$this->_getParam('idDoc',0), 
					$this->_getParam('idTag',0), 
					$this->_getParam('idExi',0), 
					$this->_getParam('idGeo',0), 
					$this->_getParam('idMonade',0), 
					$this->_getParam('idRapport',0),
					$this->_getParam('trace',false)
				);
				break;
		}
		$this->view->data = $rs;

	}


    function initInstance($view=""){
		$this->view->ajax = $this->_getParam('ajax');
        $this->view->idBase = $this->idBase = $this->_getParam('idBase', $this->idBase);
		$this->view->uti = "{}";                        
        
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
            $this->ssUti->redir = "/ice/".$view;
            $this->ssUti->dbNom = $this->idBase;
            if($this->view->ajax)$this->redirect('/auth/finsession');
            else $this->redirect('/auth/connexion');
        }		    	
    }
	
}

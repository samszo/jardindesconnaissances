<?php

/**
 * MigreController
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class MigreController extends Zend_Controller_Action {
	
	var $idBaseSrc;
	var $idBaseDst;
	var $ssUti;
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		
	}

	/**
	 * action pour migrer des proverbes entre deux bases
	 */
	public function proverbesAction(){
		$this->initInstance();
		if($this->idBaseSrc && $this->idBaseDst){
			//initialise les objets			
			$sSrc = new Flux_Site($this->idBaseSrc, true);
			$sSrc->bTraceFlush = true;
			$pro = new Flux_Proverbe($this->idBaseDst,"generateur");
			$dbDoc = new Model_DbTable_Flux_Doc($pro->db);
			$idAct = $pro->dbA->ajouter(array("code"=>"Migre -> proverbes"));
			$idDico = 153;
			//enregistre le rapport de l'action
			$idRapAct = $pro->dbR->ajouter(array("monade_id"=>$pro->idMonade,"geo_id"=>$pro->idGeo
				,"src_id"=>$this->idBaseSrc,"src_obj"=>"base"
				,"pre_id"=>$idAct,"pre_obj"=>"acti"
				,"dst_id"=>$this->idBaseDst,"dst_obj"=>"base"
				));					
			//tableau des corrections
			$corr = array(
				array('<br>','')	,
				array('<br />',''),
				array('?il','œil'),
				array('b?uf','bœuf'),
				array('C?ur','Cœur'),
				array('c?ur','cœur'),
				array('?uf','œuf'),
				array('m?ur','mœur'),
				array('n?ud','nœud'),
				array('s?ur','sœur'),
				array('?uvre','œuvre'),
				array('f?tus','fœutus'),
				array('l?',"l'"),
				array('s?',"s'"),
				array('n?',"n'"),
				array('qu?',"qu'"),
				array('C?',"C'"),
				array('c ?',"c'"),
				array('?'," ?"),
				array('  '," ")
				);										 

			//récupère les données sources
			$dbSrc = new Model_DbTable_Flux_Doc($sSrc->db);
			$arrSrc = $dbSrc->exeQuery("SELECT p.idPage, p.urlPage pa, pro.proverbeContent po
				FROM pages p
				INNER JOIN proverbes pro ON pro.idPage = p.idPage 
				WHERE p.IdPage BETWEEN 0 AND 10
				ORDER BY p.idPage");
				
	    		//ajoute les sources à la base de destination
	    		$oIdPage = 0;
	    		foreach ($arrSrc as $p) {
	    			if($p["idPage"]!=$oIdPage){
	    				//récupère le domaine comme document parent
					$domaine = $sSrc->getNomDeDomaine($p["pa"]);
					$idDocSite = $pro->dbD->ajouter(array("titre"=>$domaine,"url"=>$domaine,"parent"=>$pro->idDocRoot));						
	    				$sSrc->trace("Récupère le domaine = ".$domaine." : ".$idDocSite);
					//récupère le domaine comme concept
	    				$idCpt = $pro->dbC->ajouter(array("id_dico"=>$idDico,"lib"=>$domaine,"type"=>"page web"));
	    				$sSrc->trace("Récupère le concept = ".$idCpt);
	    				//enregistre l'url
	    				$params  = explode("/", $p["pa"]);
	    				$arr = array("titre"=>$domaine." - ".$params[5]."_".$params[6],"url"=>$p["pa"],"parent"=>$idDocSite);
					$idDocPage = $pro->dbD->ajouter($arr);			
	    				$sSrc->trace("nouvelle page = ".$idDocPage,$arr);
	    			}
				$oIdPage=$p["idPage"];
				//corrige les caractères
				/*
				 	UPDATE flux_doc SET titre = REPLACE(titre,'<br>','')	
				 	UPDATE flux_doc SET titre = REPLACE(titre,'<br />','')	
				 	UPDATE flux_doc SET titre = REPLACE(titre,'?il','œil')	
				 	UPDATE flux_doc SET titre = REPLACE(titre,'b?uf','bœuf');
					UPDATE flux_doc SET titre = REPLACE(titre,'C?ur','Cœur');
					UPDATE flux_doc SET titre = REPLACE(titre,'c?ur','cœur');
					UPDATE flux_doc SET titre = REPLACE(titre,'?uf','œuf');
					UPDATE flux_doc SET titre = REPLACE(titre,'m?ur','mœur');					 
					UPDATE flux_doc SET titre = REPLACE(titre,'n?ud','nœud');					 
					UPDATE flux_doc SET titre = REPLACE(titre,'s?ur','sœur');					 
					UPDATE flux_doc SET titre = REPLACE(titre,'?uvre','œuvre');
					UPDATE flux_doc SET titre = REPLACE(titre,'f?tus','fœutus');
					UPDATE flux_doc SET titre = REPLACE(titre,'l?',"l'");
					UPDATE flux_doc SET titre = REPLACE(titre,'s?',"s'");
					UPDATE flux_doc SET titre = REPLACE(titre,'n?',"n'");
					UPDATE flux_doc SET titre = REPLACE(titre,'qu?',"qu'");
					UPDATE flux_doc SET titre = REPLACE(titre,'C?',"C'");
					UPDATE flux_doc SET titre = REPLACE(titre,'c ?',"c'");
					UPDATE flux_doc SET titre = REPLACE(titre,'?'," ?");
					UPDATE flux_doc SET titre = REPLACE(titre,'  '," ");										 
				 */		
				$txt = $p["po"];
				foreach ($corr as $c) {
					$txt = str_replace($c[0],$c[1],$txt);
				}			
				$pro->ajouter($txt, $idDocPage, $idRapAct, array("id_concept"=>$idCpt,"id_dico"=>$idDico));
	    			$sSrc->trace($oIdPage." = ".$txt);
	    		}
	    		$sSrc->trace("FIN");
		}else{
			echo "Il manque des paramètres";
		}
	}
	
	function initInstance(){
		$this->view->ajax = $this->_getParam('ajax');
		$this->view->idBaseSrc = $this->idBaseSrc = $this->_getParam('idBaseSrc',false);
    		$this->view->idBaseDst = $this->idBaseDst = $this->_getParam('idBaseDst',false);
    		
		$auth = Zend_Auth::getInstance();
		$this->ssUti = new Zend_Session_Namespace('uti');
		if ($auth->hasIdentity()) {						
			// l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $this->view->uti = json_encode($this->ssUti->uti);
		}else{			
		    //$this->view->uti = json_encode(array("login"=>"inconnu", "id_uti"=>0));
		    $this->ssUti->redir = "/migre";
		    $this->ssUti->dbNom = $this->idBaseDst;
		    if($this->view->ajax)$this->_redirect('/auth/finsession');		    
		    else $this->_redirect('/auth/login');
		}
		    	
    }	
}

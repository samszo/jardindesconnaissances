<?php

/**
 * ExportController
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class ExportController extends Zend_Controller_Action {
	
	var $idBase;
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
		if($this->idBase){
			//initialise les objets			
			$s = new Flux_Site($this->idBase);
			$dbDoc = new Model_DbTable_Flux_Doc($s->db);
			$arr = $dbDoc->exeQuery("SELECT d.titre
				FROM flux_doc as d
					INNER JOIN flux_doc as dp ON dp.doc_id = d.parent
				WHERE dp.parent = 3");
			$type = "txt";
	    		//affiche les proverbes
	    		foreach ($arr as $p) {
	    			switch ($type) {
	    				case "txt":
	    					echo $p["titre"]."<br/>";
	    					break;
	    			}
	    		}
		}else{
			echo "Il manque des paramètres";
		}
	}
	
	function initInstance(){
		$this->view->ajax = $this->_getParam('ajax');
		$this->view->idBase = $this->idBase = $this->_getParam('idBase',false);
    		
		$auth = Zend_Auth::getInstance();
		$this->ssUti = new Zend_Session_Namespace('uti');
		if ($auth->hasIdentity()) {						
			// l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $this->view->uti = json_encode($this->ssUti->uti);
		}else{			
		    //$this->view->uti = json_encode(array("login"=>"inconnu", "id_uti"=>0));
		    $this->ssUti->redir = "/export";
		    $this->ssUti->dbNom = $this->idBase;
		    if($this->view->ajax)$this->_redirect('/auth/finsession');		    
		    else $this->_redirect('/auth/login');
		}
		    	
    }	
}

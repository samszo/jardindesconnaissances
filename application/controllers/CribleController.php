<?php

/**
 * Crible
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class CribleController extends Zend_Controller_Action {
	
	//var $dbNom = "flux_tweetpalette";
	var $dbNom = "flux_etu";
	var $urlRedir = '/auth/login?redir=crible&idBase=';
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		try {
			if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');
			$this->view->idBase = $this->dbNom;
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
			    // l'identité existe ; on la récupère
			    $this->view->identite = $auth->getIdentity();
			    $ssUti = new Zend_Session_Namespace('uti');
			    $this->view->idUti = $ssUti->idUti;
			    $this->view->tag = $this->_getParam('tag');
			    $this->view->url = $this->_getParam('url');
			    $this->view->iframe = $this->_getParam('iframe', false);
			    $this->view->idPalette = $this->_getParam('idPalette', 0);
			    //récupère les palettes disponibles
			    $s = new Flux_Site($this->dbNom);
			    $dbDoc = new Model_DbTable_Flux_Doc($s->db);
			    $this->view->palettes = $dbDoc->findByTronc("crible");
			    //récupère les rôles disponibles
				$dbU = new Model_DbTable_Flux_Uti($s->db);
				$this->view->roles = $dbU->getRolesUtis("role");
			}else{
			    $this->_redirect($this->urlRedir.$this->dbNom);
			}
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}

	/**
	 * The default action - show the home page
	 */
	public function litAction() {
		$this->initInstance();
		//récupère les informations de la palette
		if($this->_getParam('idBase', 0) && $this->_getParam('url', 0) && $this->_getParam('exi', 0) 
		&& $this->_getParam('urlFond', 0) && $this->_getParam('filtrer', 0) && $this->_getParam('event', 0)){
			$tp = new Flux_Tweetpalette($this->_getParam('idBase', 0));
			$this->view->json = $tp->getPaletteClics($this->_getParam('exi', 0), $this->_getParam('url', 0)
			, $this->_getParam('urlFond', 0), $this->_getParam('event', 0), $this->_getParam('filtrer', 0));
			//$s = new Flux_Stats($this->_getParam('idBase', 0));
			//$this->view->stats = $s->GetUtiTagDoc($this->_getParam('uti', 0), $this->_getParam('url', 0));
		}else $this->view->json = "vide";
		
	}
	
	public function ajoutAction() {
		try {
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
				//récupère les informations de la palette
				//print_r($this->getRequest()->getParams());
				if($this->_getParam('idBase', 0) && $this->_getParam('event', 0) && $this->_getParam('url', 0) && $this->_getParam('uti', 0) && $this->_getParam('exi', 0) && $this->_getParam('sem', 0)){
					$tp = new Flux_Tweetpalette($this->_getParam('idBase', 0));
					$tp->saveTweetSem($this->_getParam('uti', 0), $this->_getParam('exi', 0),$this->_getParam('url', 0), $this->_getParam('event', 0), $this->_getParam('sem'));
					//$this->view->sem = $this->_getParam('sem');
				}
			}else{
			    $this->_redirect($this->urlRedir.$this->dbNom);
			}			
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}

	public function inputAction() {
		try {
			//récupère les informations pour les input
			$tp = new Flux_Tweetpalette($this->_getParam('idBase', 0));
			$this->view->data = $tp->getInput();			
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}

    public function sauveraisonAction()
    {
		try {
			if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
			    $s = new Flux_Site($this->dbNom);
				$idUti = $this->_getParam('idUti', 0);			
			    $idExi = $this->_getParam('idExi', 0);			
		    	$raison = $this->_getParam('raison', 0);			
		    	
		    	$dbT = new Model_DbTable_Flux_Tag($s->db);
		    	$idT = $dbT->ajouter(array("code"=>$raison,"desc"=>"raison"));
		    	
		    	$dbUT = new Model_DbTable_flux_utitag($s->db);
		    	$dbUT->ajouter(array("uti_id"=>idExi, "tag_id"=>$idT, "maj"=> new Zend_Db_Expr('NOW()')), false);

				//enregistre le lien entre l'utilisateur et le l'existence
		    	$dbUU = new Model_DbTable_Flux_UtiUti($s->db);
		    	$dbUU->ajouter(array("uti_id_src"=>$idUti, "uti_id_dst"=>$idExi, "eval"=>$idT, "maj"=> new Zend_Db_Expr('NOW()')),false);
		    	
				$this->view->data = idExi." ".$idT; 					
			}else{
			    $this->_redirect($this->urlRedir.$this->dbNom);
			}
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
    	
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
		    $ssUti->redir = "/biolographes";
		    $ssUti->dbNom = $this->idBase;
		    if($this->view->ajax)$this->_redirect('/auth/finsession');		    
		    else $this->_redirect('/auth/login');
		}
		    	
    }    
}

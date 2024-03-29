<?php

/**
 * Tweetpalette
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

require_once 'Zend/Controller/Action.php';

class TweetpaletteController extends Zend_Controller_Action {
	
	var $dbNom = "flux_tweetpalette";
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		try {
			if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
			    // l'identité existe ; on la récupère
			    $this->view->identite = $auth->getIdentity();
			    $ssUti = new Zend_Session_Namespace('uti');
			    $this->view->idUti = 1;//$ssUti->idUti;
			    $this->view->tag = $this->_getParam('tag');
			    $this->view->url = $this->_getParam('url');
			    $this->view->idBase = $this->dbNom;
			    $this->view->iframe = $this->_getParam('iframe', false);
			    $this->view->idPalette = $this->_getParam('idPalette', 0);
			    //récupère les palettes disponibles
			    $s = new Flux_Site($this->dbNom);
			    $sRef = new Flux_Site("flux_tweetpalette");
			    $dbDoc = new Model_DbTable_Flux_Doc($sRef->db);
			    $this->view->palettes = $dbDoc->findByType("palette");
			    //récupère les rôles disponibles
				$dbU = new Model_DbTable_Flux_Uti($s->db);
				$this->view->roles = $dbU->getRolesUtis("role");
			}else{
			    $this->_redirect('/auth/login?redir=tweetpalette&idBase='.$this->dbNom);
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
		try {
			//récupère les informations de la palette
			if($this->_getParam('idBase', 0) && $this->_getParam('url', 0) && $this->_getParam('exi', 0) 
			&& $this->_getParam('urlFond', 0) && $this->_getParam('filtrer', 0) && $this->_getParam('event', 0)){
				$tp = new Flux_Tweetpalette($this->_getParam('idBase', 0));
				$this->view->json = $tp->getPaletteClics($this->_getParam('exi', 0), $this->_getParam('url', 0)
				, $this->_getParam('urlFond', 0), $this->_getParam('event', 0), $this->_getParam('filtrer', 0));
				//$s = new Flux_Stats($this->_getParam('idBase', 0));
				//$this->view->stats = $s->GetUtiTagDoc($this->_getParam('uti', 0), $this->_getParam('url', 0));
			}else{
				$this->view->json = "vide";
			}
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
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
			    $this->_redirect('/auth/login?redir=tweetpalette&idBase='.$this->dbNom);
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
			    $this->_redirect('/auth/login?redir=tweetpalette&idBase='.$this->dbNom);
			}
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
    	
    }
	
}

<?php
/**
 * SonarController
 *
 * Pour gérer l'annotation en temps réel des événements
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class SonarController extends Zend_Controller_Action
{
	var $idBase = "flux_sonar";
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->initInstance();
	}

	public function diaporamaAction() {
		$this->initInstance(false,"/diaporama");
	}

	public function fluxAction() {
		$rs = array('result' => array(), 'erreur'=>0);
		$this->initInstance();		
		$sonar = new Flux_Sonar($this->idBase);
		switch ($this->_getParam('q')) {
			case 'listeFlux':
				$rs['result'] = $sonar->getListeFlux();
				break;			
			case 'savePosi':
				$rs['result'] = $sonar->savePosi();
				break;			
		}
		$this->view->data = $rs;
	}

	function initInstance($idUti=false, $redir=""){
		$this->view->ajax = $this->_getParam('ajax');
		$this->view->idBase = $this->idBase = $this->_getParam('idBase', $this->idBase);
		$idUti = $this->_getParam('idUti',$idUti);
		$this->view->uti = "{}";                        
        
		$auth = Zend_Auth::getInstance();
		$this->ssUti = new Zend_Session_Namespace('uti');
		$ssGoogle = new Zend_Session_Namespace('google');
		
		if ($auth->hasIdentity() || isset($this->ssUti->uti)) {
				//utilisateur authentifier
				$this->ssUti->uti['mdp'] = '***';
				$this->view->login = $this->ssUti->uti['login'];
				$this->view->uti = json_encode($this->ssUti->uti);
		}elseif($idUti){
				//authentification CAS ou google
				$s = new Flux_Site($this->idBase);
				$dbUti = new Model_DbTable_Flux_Uti($s->db);
				$uti = $dbUti->findByuti_id($idUti);
				$this->ssUti->uti = $uti;
				$this->ssUti->uti['mdp'] = '***';
				$this->view->login = $this->ssUti->uti['login'];
				$this->view->uti = json_encode($uti);                        
		}else{
				$this->ssUti->redir = "/sonar".$redir;
				$this->ssUti->dbNom = $this->idBase;
				if($this->view->ajax)$this->redirect('/auth/finsession');
				else $this->redirect('/auth/connexion');
		}		    	
	}

}
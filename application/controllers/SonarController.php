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
	var $idBaseOmk = "omk_sonar";
	var $s; //objet sonar

	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->initInstance();
		//récupère les ressources disponibles
		//http://localhost/omeka-s/api/item_sets?resource_class_label=MediaResource
	}

	public function diaporamaAction() {
		$this->initInstance(false,"/diaporama");

		//initialise les objets omk
        $this->s->initVocabulaires();

		//récupère les collections
		$this->view->urlColIIIF = $this->s->getCollection($this->s->titleColIIIF);
		//récupère les cribles
		$this->view->urlColCribles = $this->s->getCollection($this->s->titleColCrible);
		//récupère les cribles
		$this->view->urlItemCribles = $this->s->getUrlFindItemByCol();
	}

	public function iiifAction() {
		$this->initInstance(false,"/iiif");

		//initialise les objets omk
        $this->s->initVocabulaires();

		//récupère les collections
		$this->view->urlColIIIF = $this->s->getCollection($this->s->titleColIIIF);
		//récupère les cribles
		$this->view->urlColCribles = $this->s->getCollection($this->s->titleColCrible);
		//récupère les cribles
		$this->view->urlItemCribles = $this->s->getUrlFindItemByCol();
	}

	public function fluxAction() {
		$this->initInstance();
		$rs = array('result' => array(), 'erreur'=>0);

		switch ($this->_getParam('q')) {
			case 'listeFlux':
				$rs['result'] = $this->s->getListeFlux();
				break;			
			case 'savePosi':
				$rs['result'] = $this->s->savePosi($this->_getParam('dt'),$this->_getParam('type'),$this->_getParam('geo'),$this->ssUti->uti);
				break;			
			case 'savePosiOmk':
				$rs['result'] = $this->s->savePosiOmk($this->_getParam('dt'), $this->ssUti->uti);
				break;			
			case 'getEvals':
				$rs['result'] = $this->s->getEvals($this->_getParam('type'),$this->_getParam('id'));
				break;
			case 'getEvalsOmk':
				$rs['result'] = $this->s->getEvalsOmk($this->_getParam('inScheme'),$this->_getParam('id'));
				break;
		}
		$this->view->data = $rs;
	}

	public function jardinAction() {
		$this->initInstance();
		$rs = array('result' => array(), 'erreur'=>0);

	}

	function initInstance($idUti=false, $redir=""){
		$this->view->ajax = $this->_getParam('ajax');
		$this->view->idBase = $this->idBase = $this->_getParam('idBase', $this->idBase);

		//initalise l'objet SONAR
		$this->s = new Flux_Sonar($this->idBase);
		$this->s->dbOmk = $this->_getParam('dbOmk',$this->idBaseOmk);
		//enregistrement de l'objet OMK en cession pour éviter les rechargement des propriétés		
		$omk = new Zend_Session_Namespace('omk');
		//pour le debug $omk->o = false;
		if(!$omk->o)$omk->o=$this->s->initOmeka(OMEKA_SONAR_ENDPOINT, OMEKA_SONAR_API_IDENT,OMEKA_SONAR_API_KEY);		
		$this->s->omk = $omk->o;

		//gestion des authentifications
		$dbUti = new Model_DbTable_Flux_Uti($this->s->db);
		$idUti = $this->_getParam('idUti',$idUti);
		//connexion anonyme par défaut
		if(!$idUti){
			$idUti = $dbUti->ajouter(array('login'=>'Anonyme'));
		}

		$this->view->uti = "{}";                        
        
		$auth = Zend_Auth::getInstance();
		$this->ssUti = new Zend_Session_Namespace('uti');
		$ssGoogle = new Zend_Session_Namespace('google');
		
		if ($auth->hasIdentity() || isset($this->ssUti->uti)) {
				//utilisateur authentifier
				//$this->ssUti->uti['mdp'] = '***';
				$this->view->login = $this->ssUti->uti['login'];
				$this->view->uti = json_encode($this->ssUti->uti);
		}elseif($idUti){
				//authentification CAS ou google
				$uti = $dbUti->findByuti_id($idUti);
				$this->ssUti->uti = $uti;
				//$this->ssUti->uti['mdp'] = '***';
				$this->view->login = $this->ssUti->uti['login'];
				$this->view->uti = json_encode($uti);                        
		}else{
				//problème de récupératon des sessions ???			
				$this->ssUti->redir = "/sonar".$redir;
				$this->ssUti->dbNom = $this->idBase;
				if($this->view->ajax)$this->redirect('/auth/finsession');
				else $this->redirect('/auth/connexion?idBase='.$this->idBase.'&redir=/sonar'.$redir);
		}	
		
		
	}

}
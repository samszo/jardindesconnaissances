<?php

/**
 * CartoController
 * 
 * Pour la gestion des cartogrpahies
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
require_once 'Zend/Controller/Action.php';

class CartoController extends Zend_Controller_Action {

	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		
	}

    public function zoomifyAction()
    {
    }	

    public function iiifAction()
    {
	    	$config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
	    	$apikeys = $config->getOption('resources');

	    	$this->view->idBase = $this->_getParam('idBase',$apikeys['db']['params']['dbname']);
    		$this->view->idUti = $this->_getParam('idUti',0);
    	 
    }

    public function savelayerAction()
    {
    		//récupère les paramètre passer	
    		$type = $this->_getParam('type');
    		$feat = $this->_getParam('features');
    		$titre = $this->_getParam('titre');
    		$nb = count($feat);		
    		$idBase = $this->_getParam('idBase',$apikeys['db']['params']['dbname']);
    		
    		//création des objets de base de données sur la bonne base
    		$s = new Flux_Site($idBase);    		
    		$dbDoc = new Model_DbTable_Flux_Doc($s->db);
    		
    		//ajouter les données dans la base
    		$rs = $dbDoc->ajouter(array("titre"=>$titre,"note"=>json_encode($feat)));
    		
    		$arr["savelayer"] = array("reponse"=>json_encode($rs),"type"=>$type, "nb"=>$nb,"feat"=>$feat);
    		$this->view->result = json_encode($arr);
    }
    
    public function getlayerAction()
    {
	    	$idBase = $this->_getParam('idBase',$apikeys['db']['params']['dbname']);
	    
	    	//création des objets de base de données sur la bonne base
	    	$s = new Flux_Site($idBase);
	    	$dbDoc = new Model_DbTable_Flux_Doc($s->db);
	    
	    	//ajouter les données dans la base
	    	$rs = $dbDoc->ajouter(array("titre"=>$titre,"note"=>json_encode($feat)));
	    
	    	$arr["savelayer"] = array("reponse"=>json_encode($rs),"type"=>$type, "nb"=>$nb,"feat"=>$feat);
	    	$this->view->result = json_encode($arr);
    }
    
    public function tempoAction()
    {
    	
    }

    public function mondeAction()
    {
    	 
    }
    
    public function hexagoneAction(){
    	
    }
}

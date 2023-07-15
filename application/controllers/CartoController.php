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

    public function worldtourAction()
    {
        $toto = 'OK';
    }

    public function iiifAction()
    {
	    	$config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
	    	$apikeys = $config->getOption('resources');

	    	$this->view->idBase = $this->_getParam('idBase',$apikeys['db']['params']['dbname']);
    		$this->view->idUti = $this->_getParam('idUti',0);
    		
    		$this->view->manifest = $this->_getParam('manifest',"http://localhost/ValArNum/omk/iiif/1570/manifest");
    		
    	 
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
    		$data = array("titre"=>$titre);    		
    		if($this->_getParam('table')=="flux_doc" && $this->_getParam('col')=="doc_id" && $this->_getParam('val'))
    		    $data['parent']=$this->_getParam('val');

    		//ajouter les données dans la base
    		$idDoc = $dbDoc->ajouter($data,false);
    		
    		//met à jour la propriété doc_id
    		$feat[0]['properties']['doc_id']=$idDoc;
    		$dbDoc->edit($idDoc, array("note"=>json_encode($feat)));
    		
    		//récupère les données
    		$rs = $dbDoc->findBydoc_id($idDoc);
    		
    		$arr = array("rs"=>$rs);
    		$this->view->result = json_encode($arr);
    }
    
    public function updatelayerAction()
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
        $data = array("titre"=>$titre,"note"=>json_encode($feat));
        if($this->_getParam('table')=="flux_doc" && $this->_getParam('col')=="doc_id" && $this->_getParam('val'))
            $data['parent']=$this->_getParam('val');
            
        //met à jour les données dans la base
        $dbDoc->edit($this->_getParam('doc_id'), $data);
        
        //récupère les données
        $rs = $dbDoc->findBydoc_id($this->_getParam('doc_id'));
        
        $arr = array("rs"=>$rs);
        $this->view->result = json_encode($arr);
    }
    
    
    public function getlayerAction()
    {
        //création des objets de base de données sur la bonne base
        $idBase = $this->_getParam('idBase',$apikeys['db']['params']['dbname']);
	    	$s = new Flux_Site($idBase);
	    	$dbDoc = new Model_DbTable_Flux_Doc($s->db);
	    	
	    	//récupère les layers par leur parent
	    	if($this->_getParam('table')=="flux_doc" && $this->_getParam('col')=="doc_id" && $this->_getParam('val'))
	    	    $rs = $dbDoc->findByParent($this->_getParam('val'));
	    	     
	    	$this->view->result = json_encode($rs);
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

<?php

class EvalController extends Zend_Controller_Action
{
	var $dbNom = "flux_gapaii";
	var $idUtiAnon = 2;
	var $idDocVide = 1;
	var $urlSaveEvalSem = "gapaii/savesemeval";
	
    public function indexAction()
    {
    	//récupère les paramètres
		if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');
		$this->view->idBase = $this->dbNom;
		$idUti = $this->_getParam('idUti', $this->idUtiAnon);
		$idDoc = $this->_getParam('idDoc', $this->idDocVide);
		$s = new Flux_Site($this->dbNom);
		$this->view->urlSaveEvalSem = $this->_getParam('urlSaveEvalSem', $this->urlSaveEvalSem);
		
		//récupère les infos de l'utilisateur
		$dbU = new Model_DbTable_Flux_Uti($s->db);
		$this->view->uti = $dbU->findByuti_id($idUti);
			
		//récupère les infos du document
		$dbD = new Model_DbTable_Flux_Doc($s->db);
		$this->view->doc = $dbD->findBydoc_id($idDoc);		
    }

    public function navigationAction()
    {
    	//récupère les paramètres
		if($this->_getParam('idBase')) $this->dbNom = $this->_getParam('idBase');
		$this->view->idBase = $this->dbNom;

		$s = new Flux_Site($this->dbNom);
		
		//récupère les utilisateurs
		$dbU = new Model_DbTable_Flux_Uti($s->db);
		$this->view->utis = $dbU->getAll();
			
		//récupère les documents
		$dbD = new Model_DbTable_Flux_Doc($s->db);
		$this->view->docs = $dbD->findByTitre("gapaii_genPro");		
				
		//récupère les tags
		$dbT = new Model_DbTable_Flux_Tag($s->db);
		$this->view->tags = $dbT->getAll();		

		//récupère les liens
		$dbUTD = new Model_DbTable_Flux_UtiTagDoc($s->db);
		$this->view->links = $dbUTD->getAll();		
		
    }
    
    /**
     * construction du format json correspondant à heatmap.js
     * @param array $DocsClic
     * 
     * return array
     */
    function getHeatmapClic($DocsClic){
		$dc = "";
		$max = 0;
    	foreach ($DocsClic as $d) {
    		$coor = substr($d["data"],0,-1);
    		$dc .= $coor.",count:".$d["poids"].",doc_id:".$d["doc_id"]."},";
    		if($max<$d["poids"])$max=$d["poids"];
    	}    		
    	$dc = "{max: ".$max.", data: [".substr($dc,0,-1)."]}";
    	return $dc;
    }    
    
    
    
}




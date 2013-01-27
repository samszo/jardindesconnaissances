<?php

/**
 * BiblioController
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class BiblioController extends Zend_Controller_Action {
	
	var $dbNom = "flux_diigo";

	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		try {
			if($this->_getParam('db', 0))$this->dbNom = $this->_getParam('db'); 
			
			$s = new Flux_Site($db=$this->dbNom);
			$dbD = new Model_DbTable_Flux_Doc($s->db);

			if($this->dbNom=="flux_zotero"){
		        $query = $dbD->select()
		        	->setIntegrityCheck(false)
		        	->from(array("d" => "flux_doc"))
		            ->joinInner(array('dAmz' => 'flux_doc'),'dAmz.tronc = d.doc_id AND dAmz.type = 39'
		            	,array('dAmzTitre'=>'dAmz.titre', 'dAmzUrl'=>'dAmz.url','dAmzNote'=>'dAmz.note'))
		            ->joinInner(array('dTof' => 'flux_doc'),'dTof.tronc = dAmz.doc_id'
		            	,array('dTofTitre'=>'dTof.titre', 'dTofUrl'=> 'dTof.url'));
			}
			if($this->dbNom=="flux_diigo"){
		        $query = $dbD->select()
		        	->setIntegrityCheck(false)
		        	->from(array("dAmz" => "flux_doc")
		        		,array('dAmzTitre'=>'dAmz.titre', 'dAmzUrl'=>'dAmz.url','dAmzNote'=>'dAmz.note'))
		            ->joinInner(array('dTof' => 'flux_doc'),'dTof.tronc = dAmz.doc_id'
		            	,array('dTofTitre'=>'dTof.titre', 'dTofUrl'=> 'dTof.url'));
			}
			$this->view->biblio = $dbD->fetchAll($query)->toArray(); 					
			
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}

	/**
	 * ordonner un document par rapport à un axe
	 */
	public function classaxeAction() {
		try {
			if($this->_getParam('db', 0))$this->dbNom = $this->_getParam('db'); 
			
			$s = new Flux_Site($db=$this->dbNom);

			$this->view->biblio = $dbD->fetchAll($query)->toArray(); 					
			
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}
	
    public function deweyAction()
    {
		$z = new Flux_Zotero();
		$this->view->stats = $z->getDeweyTagDoc();
    	
    }
    
    public function bookdetailAction()
    {
		if($this->_getParam('idsDoc', 0)){
			$z = new Flux_Zotero("");
			if($this->_getParam('tag', 0)){
				$this->view->biblio = $z->getDocTags($this->_getParam('idsDoc'));
			}else
				$this->view->biblio = $z->getDocDetail($this->_getParam('idsDoc'));
		}
    	    	
    }
    
    public function utidetailAction()
    {
		$s = new Flux_Site($this->_getParam('db', 0));
		$dbUD = new Model_DbTable_Flux_UtiDoc($s->db);
		$arr = $dbUD->getNbDocByUti($this->_getParam('idsUti', 0));
		$this->view->biblio = array("login"=>"auteurs","nbDoc"=>count($arr),"children"=>$arr);			
    }
    
}

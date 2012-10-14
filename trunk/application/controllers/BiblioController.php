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
	
}

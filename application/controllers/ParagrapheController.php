<?php

/**
 * ParagrapheController
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class ParagrapheController extends Zend_Controller_Action {
	
	var $dbNom = "flux_paragraphe";
		
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {

			
	}

	/**
	 * Importation des données biographiques saisies dans google drive
	 */
	public function importbioAction() {
	    $ei = new Flux_EditInflu($this->dbNom);
	    $arrCrible = $ei->creaCrible(1, array("titre"=>"Import Bios Axes"));
	    $ei->bTrace = false;
	    $ei->idGeo = 0;
	    $this->view->idCrible = $arrCrible["recid"];			
	}
	
	/**
	 * récupère un concept map
	 */
	public function conceptmapAction() {
		$s = new Flux_Site($this->dbNom);
	    $dbDoc = new Model_DbTable_Flux_Doc($s->db);
	    $cm = $dbDoc->findBydoc_id($this->_getParam('idDoc', 137));
		$this->view->json = 	$cm["note"];	
			
	}
	
}

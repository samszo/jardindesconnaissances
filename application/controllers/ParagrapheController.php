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
	var $googleId = "1vnEGo1i_-n7xZ0QXg8V20rru-Cw1x-f9ky2cEHPyH3Q";
		
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {

	    $this->view->googleId = $this->_getParam('googleId', $this->googleId);
		
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
	    $this->view->googleId = $this->_getParam('googleId', $this->googleId);
	}
	
	/**
	 * récupère un concept map
	 */
	public function conceptmapAction() {
		$s = new Flux_Site($this->dbNom);
	    $dbDoc = new Model_DbTable_Flux_Doc($s->db);
	    $cm = $dbDoc->findByUrl("../flux/google?type=css&gDocId=".$this->_getParam('googleId', $this->googleId));
		$this->view->json = 	$cm["note"];	
			
	}
	
}

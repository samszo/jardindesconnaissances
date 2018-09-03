<?php

/**
 * ParagrapheController
 * 
 * Pour gérer les représentations des bilans du laboratoire Paragraphe
 * http://paragraphe.info/
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
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

	/**
	 * affiche les Axes et les Thème sous forme de Box
	 */
	public function axesboxAction() {
	    $this->view->type = $this->_getParam('type', 'axes');
	    $this->view->filtreC = $this->_getParam('filtreC');
	    $this->view->filtreV = $this->_getParam('filtreV');
	}

	/**
	 * affiche les données des formations
	 */
	public function formationAction() {
		$this->view->urlJson = "../../data/paragraphe/HumaNumMentionAll.json";
		$this->view->colorsKey = '["LP","M1","M2","AVUN","CEN","DWM","GIS","NET","PTN"]';
		$this->view->groupKey = "['niveau','parcours','UE nom court','EC nom court']";
		$this->view->lblCalcul = "VH EC";

	}
	

}

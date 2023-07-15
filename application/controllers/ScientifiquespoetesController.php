<?php

/**
 * ScientifiquespoetesController
 * 
 * Pour le projet d'humanité numérique sur les poètes scientifiques
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

require_once 'Zend/Controller/Action.php';

class ScientifiquespoetesController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->view->title = "Visualisations disponibles";
	    $this->view->headTitle($this->view->title, 'PREPEND');
	}

    public function heatmapAction()
    {
    }	

    
}

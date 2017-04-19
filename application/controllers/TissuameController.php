<?php

/**
 * TissuameController
 * 
 * Un fourmillement de petites inclinaisons
 * http://www2.univ-paris8.fr/deleuze/article.php3?id_article=1
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

require_once 'Zend/Controller/Action.php';

class TissuameController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->view->title = "Graphiques 3D disponible";
	    $this->view->headTitle($this->view->title, 'PREPEND');
	}

    public function monadeAction()
    {
    }	

    public function fullereneAction()
    {
    }	
    
}

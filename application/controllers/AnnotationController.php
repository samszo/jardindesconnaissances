<?php

/**
 * AnnotationController
 * 
 * @author Samuel Szoniecky
 * @version 0.0
 */

require_once 'Zend/Controller/Action.php';

class AnnotationController extends Zend_Controller_Action {
	
	var $idBase = "flux_annotation";
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {

		$s = new Flux_Site($this->idBase);
	    $dbD = new Model_DbTable_Flux_Doc($s->db);
	    $rs = $dbD->findBydoc_id($this->_getParam('id'));
	    $this->view->message = $rs ? $rs["note"] : "{}";
	    
	}
    
}

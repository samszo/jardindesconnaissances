<?php

/**
 * AnnotationController
 * 
 * Pour gérer les annotation au format Open Annotation
 * http://www.openannotation.org/
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version    Release: @package_version@
 */

require_once 'Zend/Controller/Action.php';

class AnnotationController extends Zend_Controller_Action {
	
	var $idBase = "flux_annotation";
	/**
	 * index
	 *
	 * vue par défaut 
	 * renvoie les notes d'un document
	 * @category Vue
	 * @param string $idBase
	 * @param int	 $id
	 *
	 * @return json
	 */
	public function indexAction() {

		$s = new Flux_Site($this->idBase);
	    $dbD = new Model_DbTable_Flux_Doc($s->db);
	    $rs = $dbD->findBydoc_id($this->_getParam('id'));
	    $this->view->message = $rs ? $rs["note"] : "{}";
	    
	}
    
}

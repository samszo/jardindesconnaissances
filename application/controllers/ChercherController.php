<?php

/**
 * ChercherController
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class ChercherController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		try {			
			$this->view->title = "Chercher dans les flux";
		    $this->view->headTitle($this->view->title, 'PREPEND');
			$form = new Form_Chercher();
		    $this->view->form = $form;
			if ($this->getRequest()->isPost()) {
		        $formData = $this->getRequest()->getPost();
		        if ($form->isValid($formData)) {
					$lu = new Flux_Lucene(null, null, "flux_DeleuzeSpinoza", false, '../data/deleuze-index');
					$this->view->fields = $lu->index->getFieldNames();
					$this->view->resultats = $lu->find($form->getValue('recherche'));
		        }
		    }	
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}

	/**
	 * processus de recherche
	 */
	public function resultatAction() {
	    $this->view->resultats = "";
    		if($this->_getParam('recherche', 0)){
			$lu = new Flux_Lucene(null, null, "flux_DeleuzeSpinoza", false, '../data/deleuze-index');
			$hits = $lu->find($this->_getParam('recherche', 0));
			$arr="";
			foreach ($hits as $hit) {
			    $arr[] = array("score"=>$hit->score,"title"=>$hit->title,"titre"=>$hit->titre,"url"=>$hit->url,"mp3"=>$hit->mp3);
			}			
			$this->view->resultats = $arr;
	    }
	}

	/**
	 * affichage des positions d'un term
	 */
	public function positionAction() {
	    $this->view->resultats = "";
    	if($this->_getParam('term', 0)){
			$lu = new Flux_Lucene(null, null, "flux_DeleuzeSpinoza", false, '../data/deleuze-index');
			$fiels = array("titre","url","mp3");
			
			$this->view->resultats = $lu->getTermPositions(array('field'=>'body', 'text'=>$this->_getParam('term', 0)),$fiels);
			$this->view->term = $this->_getParam('term', 0);
	    }
	}
	
}

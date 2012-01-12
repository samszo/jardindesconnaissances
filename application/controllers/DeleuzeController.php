<?php

/**
 * DeleuzeController
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class DeleuzeController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		try {
			$this->view->title = "Affichage des flux Deleuze";
		    $this->view->headTitle($this->view->title, 'PREPEND');
		    $site = new Flux_Site();
		    $db = $site->getDb("fluxDeleuzeSpinoza");
			$dbUTD = new Model_DbTable_Flux_UtiTagDoc($db);
		    if($this->_getParam('tag', 0)){
		    	$arr = $dbUTD->findByTag($this->_getParam('tag', 0));
		        $this->view->arr = $arr;
		    }elseif ($this->_getParam('url', 0)){
		    	$arr = $dbUTD->findByUrl($this->_getParam('url', 0));
		        $this->view->arr = $arr;
		    }else{
			    $arr = $dbUTD->getAllInfo();
		        $this->view->arr = $arr;
		    }
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}
	
}

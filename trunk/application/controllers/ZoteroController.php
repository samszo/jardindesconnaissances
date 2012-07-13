<?php

/**
 * DeleuzeController
 * 
 * @author : samuel szoniecky
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class DeleuzeController extends Zend_Controller_Action {
	
	var $dbNom = "flux_zotero";

	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		try {

			$library = new Zotero_Library("user", "13594", 'all_things_zotero', "7a4ffbd7c1c185a7484f");
			$library->setCacheTtl(90);
						
			
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}
	
}

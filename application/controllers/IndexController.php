<?php
/**
 * IndexController
 *
 * Porte d'entrée du jardin des connaissances
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class IndexController extends Zend_Controller_Action
{

    public function init()
    {
    	
    }

    public function indexAction()
    {
	    /*;
	    $dbUD = new Model_DbTable_Flux_UtiDoc();
		$rs = array();
	    $arr = $dbUD->findDocByUtiTag('luckysemiosis','agent');
	    foreach ($arr as $d){
		    	$tags = $dbUD->findTagByDocUti($d["doc_id"],$d["uti_id"]);
		    	$r = array("d:"=>$d["titre"],"dt:"=>$d["pubDate"],"u:"=>$d["url"],"t:"=>$tags);
		    	$rs[] = $r;
		}
		*/
    	
    	if($this->_getParam('cleanCache')){
    		$s = new Flux_Site();
    		// nettoie tous les enregistrements du cache
    		$s->cache->clean(Zend_Cache::CLEANING_MODE_ALL);
    		echo "cache nettoyé";
    		if($this->_getParam('url')) $this->_redirect($this->_getParam('url')."?logout=1");
    	}
    	
    }

    public function svgAction()
    {
		
		
	}

	
}




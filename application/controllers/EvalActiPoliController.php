<?php
/**
 * EvalactipoliController
 *
 * Pour le projet Evaluation de l'activité politique
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class EvalactipoliController extends Zend_Controller_Action
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
    }

}




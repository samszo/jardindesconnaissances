<?php

class TagCribleController extends Zend_Controller_Action
{


    public function indexAction()
    {
    }
        
    public function gettagAction()
    {
    	$s = new Flux_Site("flux_zotero");
    	$dbUT = new Model_DbTable_flux_utitag($s->db);
    	$this->view->tags = $dbUT->findTagUti("luckysemiosis");
    }
    
}




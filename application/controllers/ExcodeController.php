<?php

class ExcodeController extends Zend_Controller_Action
{
	var $idBase = "flux_excode";
	var $urlRedir = 'excode/plateau';
	
    public function indexAction()
    {
    		//récupère les params
		$config = Zend_Controller_Front::getInstance()->getParam('bootstrap');    		
		$apikeys = $config->getOption('apikeys');
    		$this->view->userGeoname = $apikeys['geoname']['username'];     	
		$this->view->idBase = $this->_getParam('idBase', $this->idBase);
		$this->view->urlConnect = "auth/cas?idBase=".$this->view->idBase."&redir=".$this->urlRedir;
			     
    }

    public function plateauAction()
    {
    		$ssUti = new Zend_Session_Namespace('uti');
    		echo "redir=".$this->_getParam('idUti');
    		//if(!$ssUti->uti)	$this->_redirect('excode');
    	 		    	 
    }
    
}


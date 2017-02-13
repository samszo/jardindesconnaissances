<?php

class ExcodeController extends Zend_Controller_Action
{
	var $idBase = "flux_excode";
	var $urlRedir = 'Excode?idBase=';
	
    public function indexAction()
    {
    		//récupère les params
		$config = Zend_Controller_Front::getInstance()->getParam('bootstrap');    		
		$apikeys = $config->getOption('apikeys');
    		$this->view->userGeoname = $apikeys['geoname']['username'];     	
		$this->view->idBase = $this->_getParam('idBase', $this->idBase);

    		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {						
			// l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $ssUti = new Zend_Session_Namespace('uti');
		    $ssUti->redir = $this->urlRedir.$this->dbNom;
		    $this->view->uti = $ssUti->uti;
		}else{			
		    $this->view->identite = false;
		}   
		 		
    }

    public function plateauAction()
    {

    	 
    }
    
}


<?php
/**
 * ExcodeController
 *
 * Pour le projet Labex Arts H2H Excode
 * http://www.labex-arts-h2h.fr/excode-humanum.html
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
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


<?php
/**
 * TchatController
 *
 * web socket pour tchater
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class TchatController extends Zend_Controller_Action
{


    public function indexAction()
    {
    		$this->view->data = "Liste des mails disponibles";
	    	
    	
    }    
    
}




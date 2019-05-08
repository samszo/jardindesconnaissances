<?php
/**
 * P2NetController
 *
 * Pour gérer les brevets 
 * http://patent2netv2.vlab4u.info/
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class P2NetController extends Zend_Controller_Action
{
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->view->data = "OK"; 					
			
	}	

    public function keshifAction()
    {
    }	    
	

}
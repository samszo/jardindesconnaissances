<?php
/**
 * KeshifController
 *
 * Pour créer des visualisation Keshif
 * https://keshif.me/
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class KeshifController extends Zend_Controller_Action
{

    public function indexAction()
    {
		$this->view->titre = $this->_getParam('titre', "Navigateur par facettes");
    }

    
}




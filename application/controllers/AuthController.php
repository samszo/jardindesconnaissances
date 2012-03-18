<?php
class AuthController extends Zend_Controller_Action
{
 
    public function loginAction()
    {
    	try {
    		
			$site = new Flux_Site();
			$db = $site->getDb("flux_DeleuzeSpinoza");

	    	// Obtention d'une référence de l'instance du Singleton de Zend_Auth
			$auth = Zend_Auth::getInstance();
			$auth->clearIdentity();
			
			$loginForm = new Form_Auth_Login();

			if ($this->getRequest()->isPost()) {
		        $formData = $this->getRequest()->getPost();
				if ($loginForm->isValid($formData)) {
		            $adapter = new Zend_Auth_Adapter_DbTable(
		                $db,
		                'flux_exi',
		                'mail',
		                'mdp'
		                );		                
		            $adapter->setIdentity($loginForm->getValue('mail'));
		            $adapter->setCredential($loginForm->getValue('password'));
		            
		            // Tentative d'authentification et stockage du résultat
					$result = $auth->authenticate($adapter);
		            
		            //print_r($result);
		            if ($result->isValid()) {		            	
		            	//met en sessions les informations de l'existence
		            	$r = $adapter->getResultRowObject(array('exi_id','role'));
		            	$ssExi = new Zend_Session_Namespace('exi');
		            	$ssExi->idExi = $r->exi_id;
		            	$ssExi->role = $r->role;
		                
		                $this->_redirect('/deleuze/position');
		                return;
		            }
				}
	        }
	        
	        $this->view->form = $loginForm;

    	}catch (Zend_Exception $e) {
			echo "Récupère exception: " . get_class($e) . "\n";
		    echo "Message: " . $e->getMessage() . "\n";
		}	        
    }
 
}
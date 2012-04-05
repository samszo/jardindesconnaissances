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
		        
		        if(isset($formData["crea"]))$this->_redirect('auth/inscription');
				
		        if ($loginForm->isValid($formData)) {
		            $adapter = new Zend_Auth_Adapter_DbTable(
		                $db,
		                'flux_uti',
		                'login',
		                'mdp'
		                );		                
		            $adapter->setIdentity($loginForm->getValue('login'));
		            $adapter->setCredential($loginForm->getValue('password'));
		            
		            // Tentative d'authentification et stockage du résultat
					$result = $auth->authenticate($adapter);
		            
		            //print_r($result);
		            if ($result->isValid()) {		            	
		            	//met en sessions les informations de l'existence
		            	$r = $adapter->getResultRowObject(array('uti_id','role'));
		            	$ssExi = new Zend_Session_Namespace('uti');
		            	$ssExi->idUti = $r->uti_id;
		            	$ssExi->role = $r->role;
		                
		                $this->_redirect('/deleuze/navigation');
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

    public function inscriptionAction()
    {
    	try {
    		$this->view->form = $form = new Form_Auth_Inscription();
   	    	if($this->getRequest()->isPost()){
				$formData = $this->getRequest()->getPost();
				if($form->isValid($formData)){
					//supprime les données du bouton
					unset($formData['envoyer']);
					$formData['ip_inscription'] = $_SERVER['REMOTE_ADDR'];
					$formData['date_inscription'] = date('Y-m-d H:i:s');
					//print_r($formData);
					$db = new Model_DbTable_Flux_Uti();
					$db->ajouter($formData);
					$this->_redirect('auth/login');
				}else{
					$form->populate($formData);
				}
				
			}    			        
	        
    	}catch (Zend_Exception $e) {
			echo "Récupère exception: " . get_class($e) . "\n";
		    echo "Message: " . $e->getMessage() . "\n";
		}	        
    }
}
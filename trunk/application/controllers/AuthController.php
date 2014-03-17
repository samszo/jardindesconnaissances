<?php
class AuthController extends Zend_Controller_Action
{
    public function loginAction()
    {
    	try {
    		
			$site = new Flux_Site();
			$ssExi = new Zend_Session_Namespace('uti');
			
			if($this->_getParam('idBase', 0)){
				$dbNom=$this->_getParam('idBase', 0);
				$ssExi->dbNom = $dbNom;	
			} 
			elseif(!isset($ssExi->dbNom)) $dbNom="flux_DeleuzeSpinoza";
			else $dbNom = $ssExi->dbNom;
			//echo  $dbNom;
			$db = $site->getDb($dbNom);
			
			if(isset($ssExi->redir)){
				$redir=$ssExi->redir;
			} 
			if($this->_getParam('redir', 0)){
				$redir='/'.$this->_getParam('redir', 0);
				$ssExi->redir = $redir;
			}
			//echo  $redir;
			
	    	// Obtention d'une référence de l'instance du Singleton de Zend_Auth
			$auth = Zend_Auth::getInstance();
			$auth->clearIdentity();
			
			$loginForm = new Form_Auth_Login();

			if ($this->getRequest()->isPost()) {
		        $formData = $this->getRequest()->getPost();
		        
		        if(isset($formData["crea"])) $this->_redirect('auth/inscription');
		        if(isset($formData["ajax"])) $this->view->ajax = true;
		        else $this->view->ajax = false;
		        
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
		            	$ssExi->idUti = $r->uti_id;
		            	$ssExi->role = $r->role;
		                
		            	if($this->view->ajax){
		            		$this->view->idUti = $ssExi->idUti;
		            	}else{
			            	$this->_redirect($redir);
			                return;
		            	}
		            }else{
		            	switch ($result->getCode()) {
		            		case 0:
								$this->view->erreur = "Problème d'identification. Veuillez contacter le webmaster.";		            	
			            		break;		            		
		            		case -1:
								$this->view->erreur = "Le login n'a pas été trouvé.";		            	
			            		break;		            		
		            		case -2:
								$this->view->erreur = "Le login est ambigue.";		            	
			            		break;		            		
		            		case -2:
								$this->view->erreur = "Le login et/ou le mot de passe ne sont pas bons.";		            	
			            		break;		            		
		            	}
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
				$this->view->ajax = isset($formData['ajax']);
				if(isset($formData['idBase']))$ssExi->dbNom=$formData['idBase'];						
				if($form->isValid($formData)){
					//supprime les données superflux
					unset($formData['envoyer']);					
					unset($formData['ajax']);					
					unset($formData['idBase']);					
					
					$formData['ip_inscription'] = $_SERVER['REMOTE_ADDR'];
					$formData['date_inscription'] = date('Y-m-d H:i:s');
					//print_r($formData);
					$ssExi = new Zend_Session_Namespace('uti');
					//echo "ssExi->dbNom = ".$ssExi->dbNom;
					if(isset($ssExi->dbNom)){
						$site = new Flux_Site();
						$db = $site->getDb($ssExi->dbNom);						
						$dbUti = new Model_DbTable_Flux_Uti($db);
					}else $dbUti = new Model_DbTable_Flux_Uti();					
					$idUti = $dbUti->ajouter($formData);
					if($this->view->ajax)
						$this->view->idUti = $idUti;
					else
						$this->_redirect('auth/login');
				}else{
					if($this->view->ajax)
						$this->view->idUti = -1;
					else
						$form->populate($formData);
				}
				
			}    			        
	        
    	}catch (Zend_Exception $e) {
			echo "Récupère exception: " . get_class($e) . "\n";
		    echo "Message: " . $e->getMessage() . "\n";
		}	        
    }
    
}
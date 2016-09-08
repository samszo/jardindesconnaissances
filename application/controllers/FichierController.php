<?php

class FichierController extends Zend_Controller_Action
{

	var $idBase = "";
	var $idBaseSpip = "";
	
    public function indexAction()
    {
    	
    }

    public function chargerAction()
    {
		$this->initInstance();
    	
	    foreach(array('video', 'audio') as $type) {
	    		//print_r($_FILES);
		    if (isset($_FILES["${type}-blob"])) {
		
		        $fileName = $_POST["${type}-filename"];
		        $uploadDirectory = UPLOAD_PATH."/$fileName";
		
		        if (!move_uploaded_file($_FILES["${type}-blob"]["tmp_name"], $uploadDirectory)) {
		            $reponse["erreur"]="problème au chargement du fichier";
		        }
		
		        $reponse["fichier"]=$uploadDirectory;
		        $reponse["url"]=str_replace(ROOT_PATH, WEB_ROOT, $uploadDirectory);
			}

		}
		if($this->_getParam('bdd')){
			$s = new Flux_Site($this->idBase);
			$dbDoc = new Model_DbTable_Flux_Doc($s->db);
			$reponse["idDoc"]= $dbDoc->ajouter(array("url"=>$reponse["url"],"data"=>$_FILES["${type}-blob"]));
		}
    		$this->view->reponse=json_encode($reponse);
    }
    
    
	function initInstance(){
		if($this->_getParam('idBase')) $this->idBase = $this->_getParam('idBase');
		if($this->_getParam('idBaseSpip')) $this->idBaseSpip = $this->_getParam('idBaseSpip');
		if($this->_getParam('idOeu')) $this->idOeu = $this->_getParam('idOeu');
		if($this->_getParam('idDoc')) $this->idDoc = $this->_getParam('idDoc');
		if($this->_getParam('idCpt')) $this->idCpt = $this->_getParam('idCpt');
		if($this->_getParam('idUti')) $this->idUti = $this->_getParam('idUti');
		$this->idGeo = $this->_getParam('idGeo',-1);
		
		$this->view->idBase = $this->idBase;
		$this->view->idOeu = $this->idOeu;
		$this->view->idDoc = $this->idDoc;
		$this->view->idCpt = $this->idCpt;
		$this->view->idGeo = $this->idGeo;
		
		//pas d'authentification si idUti
		//echo "this->idUti=".$this->idUti."\n";
		if($this->idUti){
			$this->view->idUti = $this->idUti;
			return;
		}
		$auth = Zend_Auth::getInstance();
		$this->ssUti = new Zend_Session_Namespace('uti');
		if ($auth->hasIdentity()) {						
			// l'identité existe ; on la récupère
		    $this->view->identite = $auth->getIdentity();
		    $this->view->uti = json_encode($this->ssUti->uti);
		    $this->view->idUti = $this->idUti = $this->ssUti->idUti;
		}else{			
			$this->view->idUti = $this->idUti;
			$this->ssUti->dbNom = $this->idBase;
		    if($this->view->ajax)$this->_redirect('/auth/finsession');		    
		    else $this->_redirect('/auth/login');
		}
		
    }
	    
}




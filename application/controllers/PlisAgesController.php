<?php

class PlisAgesController extends Zend_Controller_Action
{
	var $idBase = "flux_plisages";
	var $urlRedir = 'PlisAges?idBase=';
	
    public function indexAction()
    {
    		//récupère les params
		$config = Zend_Controller_Front::getInstance()->getParam('bootstrap');    		
		$apikeys = $config->getOption('apikeys');
    		$this->view->userGeoname = $apikeys['geoname']['username']; 
    	
		$this->view->idBase = $this->_getParam('idBase', $this->idBase);
	    	//chargement des inclanations        
	    	$s = new Flux_Site($this->view->idBase);
	    	$dbDoc = new Model_DbTable_Flux_Doc($s->db);

    		$this->view->arrCribles = $dbDoc->findByTronc("crible", true, array("doc_id", "titre", "maj", "parent"));
	    	$this->view->arrInclinations = $dbDoc->findByTronc("inclination", true, array("doc_id", "titre", "maj", "parent"));
    		$this->view->arrInclinaisons = $dbDoc->findByTronc("inclinaison", true);

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

    public function sauveinclinaisonAction()
    {
    		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			//vérifie les paramètres
			if($this->_getParam('idDoc') && $this->_getParam('idUti') && $this->_getParam('data')){
		    		$s = new Flux_Site($this->_getParam('idBase', $this->idBase));
		    		$this->dbDoc = new Model_DbTable_Flux_Doc($s->db);
		    		$idDoc = $this->_getParam('idDoc');		    		
        			$this->dbUtiDoc = new Model_DbTable_Flux_UtiDoc($s->db);		    		
	    			$dt = $this->_getParam('data');
	    			
	    			//sauve le recto
				$arrR = $this->sauveFicSvg("recto", $idDoc, $this->_getParam('titre')." recto", $dt, $this->_getParam('idUti'));
	    			//sauve le verso
				$arrV = $this->sauveFicSvg("verso", $idDoc, $this->_getParam('titre')." verso", $dt, $this->_getParam('idUti'));					  			
				//met à jour le parent
				$result = array("recto"=>$arrR["doc_id"],"verso"=>$arrV["doc_id"]);
				$this->dbDoc->edit($idDoc,array("data"=>json_encode($result)),false);				
		        $this->view->rs = $result;
		        
			}else{
			    $this->view->erreur = array("erreur"=>"Il manque des paramètres.");				
			}			
		}else{			
		    $this->view->erreur = array("erreur"=>"aucun utilisateur connecté");
		}   	        
    }
    
    function sauveFicSvg($type, $idDoc, $titre, $dt, $idUti){
    			//récupère le document
			$arr = $this->dbDoc->findByTronc($type."_".$idDoc);	
			if(count($arr)==0){
				/**TODO: voir s'il faut enregistrer le fichier
				 * 
				 */				
				//création du document enfant
				$arr = $this->dbDoc->ajouter(array("tronc"=>"inclination","titre"=>$titre,"note"=>$type."_".$idDoc,"data"=>$dt[$type], "parent"=>$idDoc),false,true);
		        //enregistre le lien avec l'utilisateur
	        		$this->dbUtiDoc->ajouter(array("doc_id"=>$arr["doc_id"],"uti_id"=>$idUti));
			}else{
				//mise à jour de l'enfant
				$arr = $arr[0]; 						
				$this->dbDoc->edit($arr["doc_id"],array("titre"=>$titre,"data"=>$dt[$type]),false);						
			}	    			
		return $arr;    	
    }
    
    public function copieinclinaisonAction()
    {
    		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {			
		    	$s = new Flux_Site($this->_getParam('idBase', $this->idBase));
	    		$dbDoc = new Model_DbTable_Flux_Doc($s->db);
	    		//copie le document comme enfant du modèle
	    		$arrDoc = $dbDoc->copie($this->_getParam('idDoc'),$this->_getParam('idDoc'));
	    		//change le titre
	    		$dbDoc->edit($arrDoc["doc_id"], array("titre"=>$this->_getParam('titre'),"tronc"=>"inclinaison"));
	    		$arrDoc["titre"] = $this->_getParam('titre');
	    		//ajoute un lien vers l'utilisateur
	    		$dbUtiDoc = new Model_DbTable_Flux_UtiDoc($s->db);
	    		$dbUtiDoc->ajouter(array("uti_id"=>$this->_getParam('idUti'),"doc_id"=>$arrDoc["doc_id"]));
	    		$this->view->rs = $arrDoc;
		}else{			
		    $this->view->erreur = array("erreur"=>"aucun utilisateur connecté");
		}   
    	    	
    }
    
    public function suppinclinaisonAction()
    {
    		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			//vérifie les paramètres
			if($this->_getParam('idDoc')){
		    		$s = new Flux_Site($this->_getParam('idBase', $this->idBase));
		    		$this->dbDoc = new Model_DbTable_Flux_Doc($s->db);
		    		$this->dbDoc->remove($this->_getParam('idDoc'));
		        $this->view->rs = array("message"=>"inclinaison supprimée");
			}else{
			    $this->view->erreur = array("erreur"=>"Il manque des paramètres");				
			}			
		}else{			
		    $this->view->erreur = array("erreur"=>"aucun utilisateur connecté");
		}   	        
    }    
        
}


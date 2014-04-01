<?php

/**
 * GraphController
 * 
 * @author Samuel Szoniecky
 * @version 0.0
 */

require_once 'Zend/Controller/Action.php';

class GraphController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		try {			
			$this->view->title = "Graphiques disponibles";
		    $this->view->headTitle($this->view->title, 'PREPEND');
		    $this->view->stats = array("tags pour un utilisateur", "utilisateurs en relation");
		}catch (Zend_Exception $e) {
			echo "Récupère exception: " . get_class($e) . "\n";
		    echo "Message: " . $e->getMessage() . "\n";
		}
	}

    public function bullesAction()
    {
	    $this->view->stats = "";
	    
	    $request = $this->getRequest();
		$url = $request->getRequestUri();
		$arrUrl = explode("?",$url);
		
		$this->view->urlStats = "../stat/tagassos?".$arrUrl[1];	    
    }	

    public function chordAction()
    {
	    
	    $request = $this->getRequest();
		$url = $request->getRequestUri();
		$arrUrl = explode("?",$url);
		$this->view->idBase =  $this->_getParam('idBase', 0);
		$this->view->bases = array("flux_h2ptm"=>"H2ptm", "flux_diigo"=>"Diigo", "flux_zotero"=>"Zotero", " flux_gmail_intelligence_collective"=>"Google Alerte");
		$this->view->titre = "Explorateur d'observations";
		$this->view->tags = $this->_getParam('tags', 0);
		//$this->view->urlStats = "../stat/matricetagassos?".$arrUrl[1];	    
		$this->view->urlStats = "http://localhost/jdc/data/matricetagassos.json";	    
		
    }	
    
    public function audiowaveAction()
    {
    	if($this->_getParam('idDoc', 0) && $this->_getParam('idBase', 0)){
    		//connexion à la base
			$s = new Flux_Site($this->_getParam('idBase', 0));
    		//récupère le document et son contenu
    		$db = new Model_DbTable_flux_doc($s->db);
			$arr = $db->findBydoc_id($this->_getParam('idDoc', 0));
			//print_r($arr[0]);
			$this->view->urlTitre = $arr[0]["titre"];
			$this->view->urlSon = $arr[0]["url"];
			$this->view->urlWave = "../stat/audiowave?idDoc=".$this->_getParam('idDoc', 0);
			$this->view->urlStat = "../deleuze/position?term=abstrait";
			$text = htmlspecialchars(preg_replace("/(\r\n|\n|\r)/", " ", $arr[0]["note"]));
			$this->view->texte = $text;
	    }
    	
    }	

    public function tagcloudAction()
    {
	    $request = $this->getRequest();
		$url = $request->getRequestUri();
		$arrUrl = explode("?",$url);
		$idBase = "flux_DeleuzeSpinoza";
		$s = new Flux_Site($idBase);		
		$dbU = new Model_DbTable_Flux_Uti($s->db);
		$this->view->utis = json_encode($dbU->getAll(array("login")));
		
		//$this->view->urlStats = "../stat/tagassos?idBase=".$idBase."&tags[]=intelligence&tags[]=collective";	    
		$this->view->urlStats = "../stat/tagassos?idBase=".$idBase."&tags[]=rapport";	    
    }	

    public function arbreAction()
    {
	    $request = $this->getRequest();
		$url = $request->getRequestUri();
		$arrUrl = explode("?",$url);		
    }	

    public function branchesAction()
    {
    }	
    
    
    public function sunburstAction()
    {
	    /*
    	$request = $this->getRequest();
		$url = $request->getRequestUri();
		$arrUrl = explode("?",$url);
		$idBase = "flux_diigo";
		$s = new Flux_Site($idBase);		
		$dbU = new Model_DbTable_Flux_Uti($s->db);
		$this->view->utis = json_encode($dbU->getAll(array("login")));
		
		$this->view->urlStats = "../stat/tagassos?idBase=".$idBase."&tags[]=intelligence&tags[]=collective";
		*/	    
    }	
    
    public function iemlAction()
    {
    	try {
	 		set_time_limit(0); 
	 		
	 		$ieml = new Flux_Ieml("flux_ieml");
	 		if($this->_getParam('ieml', 0)){
	    		$this->view->svg = $ieml->genereSvgAdresse(array("code"=>$this->_getParam('ieml')));    		
	    	}else{
				//$ieml->genereSequences(3,true);
				$this->view->svg = $ieml->genereSvgPlanSeq($this->_getParam('nb', 6));
	    	}
		}catch (Zend_Exception $e) {
			echo "Récupère exception: " . get_class($e) . "\n";
		    echo "Message: " . $e->getMessage() . "\n";
		}
    }	

    public function handicateurAction()
    {
    	try {

    		$x = new ArrayMixer();
    		$x->append(array('audio0.jpg','audio1.jpg','audio2.jpg','audio3.jpg'));
    		$x->append(array('cog0.jpg','cog1.jpg','cog.jpg','cog.jpg'));
    		$x->append(array('moteur0.jpg','moteur.jpg','moteur.jpg','moteur.jpg'));
    		$x->append(array('visu0.jpg','visu1.jpg','visu2.jpg','visu3.jpg'));
    		$x->proceed();
    		$ls = $x->result();
    		print_r($ls);
    		
    		$ieml = new Flux_Ieml("flux_ieml");
    		if($this->_getParam('ieml', 0)){
    			$this->view->svg = $ieml->genereSvgAdresse(array("code"=>$this->_getParam('ieml')));
    		}else{
    			//$ieml->genereSequences(3,true);
    			$this->view->svg = $ieml->genereSvgPlanSeq($this->_getParam('nb', 6));
    		}
    	}catch (Zend_Exception $e) {
    		echo "Récupère exception: " . get_class($e) . "\n";
    		echo "Message: " . $e->getMessage() . "\n";
    	}
    }
    
    public function motionAction()
    {
    	
    }	
    
}

<?php

/**
 * GraphController
 * 
 * @author Samuel Szoniecky
 * @version 0.0
 */

require_once 'Zend/Controller/Action.php';

class GraphController extends Zend_Controller_Action {

	var $formatTemps = array("jourheure"=>"%d-%c-%y %H h");
	
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
		$this->view->bases = array("flux_sic_new"=>"SFSIC", "flux_h2ptm"=>"H2ptm", "flux_diigo"=>"Diigo", "flux_zotero"=>"Zotero", " flux_gmail_intelligence_collective"=>"Google Alerte");
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
		$this->view->idBase = $this->_getParam('idBase', "flux_DeleuzeSpinoza");
		$s = new Flux_Site($this->view->idBase);		
		$dbU = new Model_DbTable_Flux_Uti($s->db);
		$this->view->utis = json_encode($dbU->getAll(array("login")));
		
		$this->view->idUti = $this->_getParam('idUti');
		$this->view->svg = $this->_getParam('svg');
		if($this->view->idUti){
			$this->view->urlStats = "../stat/taguti?idBase=".$this->view->idBase."&idUti=".$this->view->idUti;	    
		}else{
			//$this->view->urlStats = "../stat/tagassos?idBase=".$idBase."&tags[]=intelligence&tags[]=collective";	    
			$this->view->urlStats = "../stat/tagassos?idBase=".$this->view->idBase."&tags[]=rapport";	    
		}
		
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
    
    public function sankeyAction()
    {
    		$this->view->urlStats = $this->_getParam('urlStats');
    }	
        
    public function conceptmapAction()
    {
    		$this->view->urlStats = $this->_getParam('urlStats');
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
    
    public function editeurAction()
    {
    	if($this->_getParam('stat')){
    		//on récupère les data des stats
    		switch ($this->_getParam('stat')) {
    			case "histopubli":
    				$s = new Flux_Site($this->_getParam('idBase'));
    				$dbD = new Model_DbTable_Flux_Doc($s->db);
    				if($this->_getParam('temps') && $this->formatTemps[$this->_getParam('temps')]){
    					$ft = $this->formatTemps[$this->_getParam('temps')];
    				}else $ft = "%d-%c-%y";
    				$arr = $dbD->getHistoPubli($this->_getParam('tronc',""), $ft);
    				$data[] = array("date", "nb d'extraction");
    				foreach ($arr as $vals) {
    					$data[]=array($vals['temps'],intval($vals['nb']));
    				}
			    	$this->view->data = json_encode($data);
    			break;
    			case "geo":
    				$oStat = new Flux_Stats($this->_getParam('idBase'));
    				$q = $this->_getParam('q');
    				if($q){
	    				$arr = $oStat->GetTagNbDocFT($q, "pays");
	    				//$data[] = array("geo", "pertinence totale", "nb de document");
	    				$data[] = array("geo", "pertinence");
	    				foreach ($arr as $vals) {
	    					if($q=="tout")
		    					$p = intval($vals['nbDoc']);
	    					else
		    					$p = intval($vals['score'])/intval($vals['nbDoc']);
	    					//$info = intval($vals['score'])." ".intval($vals['nbDoc']);
	    					//$data[]=array($vals['code'], intval($vals['score']), intval($vals['nbDoc']));
	    					$data[]=array($vals['code'], $p);
	    				}
    				}
    				if($this->_getParam('tags')){
	    				$arr = $oStat->GetTagNbDoc($this->_getParam('tags'), "pays");
	    				$data[] = array("geo", "nbDoc");
	    				foreach ($arr as $vals) {
	    					$data[]=array($vals['code'], intval($vals['nbDoc']));
	    				}
    				}
    				$this->view->data = json_encode($data);
    		}
    	}else{
			//défini les data du graphique
	    	//attention suivant le type les datas ne sont pas les mêmes
	    	$this->view->data = $this->_getParam('data', "[['pas de data'],['0']]");
    	}
    	
    	//défini le titre du graphique
    	$this->view->titre = $this->_getParam('titre', 'sans titre');
		//défini le type de graphique la liste est ici : https://developers.google.com/chart/interactive/docs/gallery
		// voir si celui-ci est intéressant : https://developers.google.com/chart/interactive/docs/gallery/annotationchart    	
    	$this->view->type = $this->_getParam('type', 'ColumnChart');
    	
    }	
    
    public function areaAction()
    {
    }	
    
    public function candlestickAction()
    {
    }	
    
    public function googletreemapAction(){
    	
    }

    public function d3treemapAction(){
    	
    }
    
    public function grapheditorAction(){
		$this->view->connect =  $this->_getParam('connect', 1);
    		$this->view->idBase =  $this->_getParam('idBase', "flux_biolographes");
    	
    }
    
    public function barsmatrixAction(){
    	
    }

    public function radarAction(){

    		$dt = '[]';
    		$dt = '[[//iPhone
						{axis:"Battery Life",value:22},
						{axis:"Brand",value:28},
						{axis:"Contract Cost",value:29},
						{axis:"Design And Quality",value:17},
						{axis:"Have Internet Connectivity",value:22},
						{axis:"Large Screen",value:02},
						{axis:"Price Of Device",value:21},
						{axis:"To Be A Smartphone",value:-50}			
					  ],[//Samsung
						{axis:"Battery Life",value:27},
						{axis:"Brand",value:16},
						{axis:"Contract Cost",value:35},
						{axis:"Design And Quality",value:-13},
						{axis:"Have Internet Connectivity",value:20},
						{axis:"Large Screen",value:13},
						{axis:"Price Of Device",value:35},
						{axis:"To Be A Smartphone",value:38}
    			]]';
		$this->view->data =  $this->_getParam('data', $dt);    	
		$this->view->titre =  $this->_getParam('titre', "Radar sans titre");    	
		
    }
    
    public function roueemotionAction(){
		$dt = "[
	      	{id:'tspan4022',en:'disapproval',fr:'désapprobation',color:'#ffffff',value:0}
	      	,{id:'tspan4026',en:'remorse',fr:'remord',color:'#ffffff',value:0}
	      	,{id:'tspan4030',en:'contempt',fr:'mépris',color:'#ffffff',value:0}
	      	,{id:'tspan4034',en:'awe',fr:'crainte',color:'#ffffff',value:0}
	      	,{id:'tspan4038',en:'submission',fr:'soumission',color:'#ffffff',value:0}
	      	,{id:'tspan4042',en:'love',fr:'amour',color:'#ffffff',value:0}
	      	,{id:'tspan4046',en:'optimism',fr:'optimisme',color:'#ffffff',value:0}
	      	,{id:'tspan4050',en:'aggressiveness',fr:'aggressivité',color:'#ffffff',value:0}
	      	,{id:'tspan3007',en:'pensiveness',fr:'songerie',color:'#8c8cff',value:0}
	      	,{id:'tspan3836',en:'annoyance',fr:'gêne',color:'#ff8c8c',value:0}
	      	,{id:'tspan3840',en:'anger',fr:'colère',color:'#ff0000',value:0}
	      	,{id:'tspan3844',en:'rage',fr:'rage',color:'#d40000',value:0}
	      	,{id:'tspan3891',en:'ecstasy',fr:'extase',color:'#ffe854',value:0}
	      	,{id:'tspan3895',en:'joy',fr:'joie',color:'#ffff54',value:0}
	      	,{id:'tspan3899',en:'serenity',fr:'sérénité',color:'#ffffb1',value:0}
	      	,{id:'tspan3903',en:'terror',fr:'terreur',color:'#008000',value:0}
	      	,{id:'tspan3907',en:'fear',fr:'peur',color:'#009600',value:0}
	      	,{id:'tspan3911',en:'apprehension',fr:'appréhension',color:'#8cc68c',value:0}
	      	,{id:'tspan3915',en:'admiration',fr:'adoration',color:'#00b400',value:0}
	      	,{id:'tspan3919',en:'trust',fr:'confiance',color:'#54ff54',value:0}
	      	,{id:'tspan3923',en:'acceptance',fr:'résignation',color:'#8cff8c',value:0}
	      	,{id:'tspan3927',en:'vigilance',fr:'vigilance',color:'#ff7d00',value:0}
	      	,{id:'tspan3931',en:'anticipation',fr:'excitation',color:'#ffa854',value:0}
	      	,{id:'tspan3935',en:'interest',fr:'intérêt',color:'#ffc48c',value:0}
	      	,{id:'tspan3939',en:'boredom',fr:'ennui',color:'#ffc6ff',value:0}
	      	,{id:'tspan3943',en:'disgust',fr:'dégoût',color:'#ff54ff',value:0}
	      	,{id:'tspan3947',en:'loathing',fr:'aversion',color:'#de00de',value:0}
	      	,{id:'tspan3951',en:'amazement',fr:'stupéfaction',color:'#0089e0',value:0}
	      	,{id:'tspan3955',en:'surprise',fr:'surprise',color:'#59bdff',value:0}
	      	,{id:'tspan3959',en:'distraction',fr:'distraction',color:'#a5dbff',value:0}
	      	,{id:'tspan3828',en:'sadness',fr:'tristesse',color:'#5151ff',value:0}
	      	,{id:'tspan3832',en:'grief',fr:'chagrin',color:'#0000c8',value:0}
	      	]";
		//vérifie s'il faut récupérer les données dans spip
		if($this->_getParam('idMotParent', false) && $this->_getParam('idBase', false)){
			$s = new Flux_Site($this->_getParam('idBase'));
			$dbM = new Model_DbTable_Spip_mots($s->db);
			$arrM = $dbM->findByIdMotParent($this->_getParam('idMotParent'));
			$dt = "[";
			$deb = true;
			foreach ($arrM as $m) {
				if(!$deb)$dt .= ",";
				$dt .= $m["descriptif"];
				$deb = false;
			}
			$dt .= "]";			
		}
				
		$this->view->data =  $this->_getParam('data', $dt);    	
		$this->view->w =  $this->_getParam('w', 0);    	
		$this->view->h =  $this->_getParam('h', 0);    	
		$this->view->langue =  $this->_getParam('langue', "fr");    	
		$this->view->titre =  $this->_getParam('titre', "Roue des émotions");    	
		
    }    
}

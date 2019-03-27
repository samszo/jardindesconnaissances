<?php

/**
 * GraphController
 * 
 * Pour la génération de graph à partir de données
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
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

    public function testAction()
    {
	}

    public function vennAction()
    {
    	$this->view->urlData = $this->_getParam('urlData',"../flux/ieml?f=getDicoItem&ieml=M:M:.a.-M:M:.a.-f.o.-%27");
	}

	public function vennmatriceAction()
    {
    	$this->view->urlData = $this->_getParam('urlData',"../flux/ieml?f=getDicoItem&ieml=M:M:.a.-M:M:.a.-f.o.-%27");
	}


	public function bullesAction()
    {
	    $this->view->stats = "";
	    
	    $request = $this->getRequest();
		$url = $request->getRequestUri();
		$arrUrl = explode("?",$url);
		
		$this->view->urlStats = "../stat/tagassos?".$arrUrl[1];	    
    }	

    public function forceclusterAction()
    {
	}

    public function forcelabelAction()
    {
	}

    public function forceradialAction()
    {
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
        //column[@name='an']
	    $request = $this->getRequest();
		$url = $request->getRequestUri();
		$arrUrl = explode("?",$url);
		//$this->view->idBase = $this->_getParam('idBase', "flux_DeleuzeSpinoza");
		$this->view->idBase = $this->_getParam('idBase');
		$s = new Flux_Site($this->view->idBase);		
		$dbU = new Model_DbTable_Flux_Uti($s->db);
		$this->view->utis = json_encode($dbU->getAll(array("login")));
		
		$this->view->idUti = $this->_getParam('idUti');
		$this->view->svg = $this->_getParam('svg');
		if($this->view->idUti){
			$this->view->urlStats = "../stat/taguti?idBase=".$this->view->idBase."&idUti=".$this->view->idUti;	    
		}elseif ($this->_getParam('idDoc')) {
			$dbD = new Model_DbTable_Flux_Doc($s->db);
			$rs = $dbD->findBydoc_id($this->_getParam('idDoc'));
			$this->view->urlStats = $rs["url"];	    
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
    		$this->view->urlStats = $this->_getParam('urlStats',"../data/flare.json");
    }
	
	public function sunburstinteractifAction()
    {
		$this->view->urlCSV = "../../data/paragraphe/allMentionHN.csv";
		$this->view->colorsKey = '["LP","M1","M2","AVUN","CEN","PTN","GIS","DWM","NET"]';
		$this->view->groupKey = "['niveau','parcours','UE nom court','EC nom court']";
		$this->view->lblCalcul = "VH EC";

    }

    public function dendrochronologieAction()
    {
    	$this->view->w =  $this->_getParam('w', 0);
    	$this->view->h =  $this->_getParam('h', 0);
    	$this->view->titre =  $this->_getParam('titre', "Dendrochronologie");
    	$this->view->urlStats = $this->_getParam('urlStats',"../data/dendrochronologieTOT.json");
    }	

    public function sankeyAction()
    {
    		$this->view->urlStats = $this->_getParam('urlStats',"../../data/paragraphe/paragrapheThemeMC.json");
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

    public function barsAction(){
        switch ($this->_getParam('q','compareEco')) {
            case 'compareUrl':
                //../flux/an?q=compareComplexEcosystemUrl&idsBase[]=flux_valarnum&idsBase[]=flux_valarnum_prod&idsBase[]=flux_valarnum_prod1&idsBase[]=flux_valarnum_prod1_1&url=http://www.siv.archives-nationales.culture.gouv.fr/mm/media/download/FRAN_0133_0027_L-medium.jpg
                $url = urldecode($this->_getParam('url', 'http%3A%2F%2Fwww.siv.archives-nationales.culture.gouv.fr%2Fmm%2Fmedia%2Fdownload%2FFRAN_0133_0027_L-medium.jpg'));
                $this->view->urlData =  urldecode($this->_getParam('urlData', "..%2Fflux%2Fan%3Fq%3DcompareComplexEcosystemUrl%26idsBase%5B%5D%3Dflux_valarnum%26idsBase%5B%5D%3Dflux_valarnum_prod%26idsBase%5B%5D%3Dflux_valarnum_prod1%26idsBase%5B%5D%3Dflux_valarnum_prod1_1%26url%3D".$url));
                break;
            case 'compareEco':
                //../flux/an?q=compareComplexEcosystem&idsBase[]=flux_valarnum&idsBase[]=flux_valarnum_prod&idsBase[]=flux_valarnum_prod1&idsBase[]=flux_valarnum_prod1_1
                $this->view->urlData =  urldecode($this->_getParam('urlData', "..%2Fflux%2Fan%3Fq%3DcompareComplexEcosystem%26idsBase%5B%5D%3Dflux_valarnum%26idsBase%5B%5D%3Dflux_valarnum_prod%26idsBase%5B%5D%3Dflux_valarnum_prod1%26idsBase%5B%5D%3Dflux_valarnum_prod1_1"));
                $this->view->urlData =  "../flux/an?q=compareComplexEcosystem&idsBase[]=flux_valarnum_prod1&idsBase[]=flux_valarnum_prod1_1";
                break;
        }
        $this->view->catX =  $this->_getParam('catX', "idBase");
        $this->view->catY =  $this->_getParam('catY', "sumComplex");
        $this->view->serie =  $this->_getParam('serie', "type");
        
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
    		$dt = '[[//atelierlaboratoirestructurant
    		{axis:"Durée",value:22},
    		{axis:"Nombre d’étudiant",value:28},
    		{axis:"Coût",value:29},
    		{axis:"Théorie",value:17},
    		{axis:"Pratique",value:22},
    		{axis:"Pédagogie active",value:02},
    		{axis:"Pédagogie passive",value:21},
    		{axis:"ECTS",value:50}
    		]]';
    		
		$this->view->data =  $this->_getParam('data', $dt);    	
		$this->view->titre =  $this->_getParam('titre', "atelier laboratoire structurant");    	
		
    }
    
    public function axeAction(){

    		$dt = '[]';
		$this->view->conceptG =  $this->_getParam('conceptG', "MAUVAIS");    	
		$this->view->conceptD =  $this->_getParam('conceptD', "BON");    	
		$this->view->type =  $this->_getParam('type', "point");    	
		$this->view->data =  $this->_getParam('data', $dt);    	
		$this->view->titre =  $this->_getParam('titre', "Axe d'évaluation");    	

		//$this->view->conceptG = "Littéral : celui qui baisse son regard, repose son coeur";
		//$this->view->conceptD = "Figuré : celui qui s&apos;abstient de regarder les interdits, allège son cœur";
		//$this->view->titre =  "Proverbe : "."من غضً طرفه أراح قلبه";
		
    }
    
    public function posicolorAction(){
        
        $dt = '[]';
        $this->view->data =  $this->_getParam('data', $dt);
        $ex = $this->_getParam('ex', 'ActeurChromecastNbIndividu');
        switch ($ex) {
            case 'ActeurChromecastNbIndividu':
                $this->view->urlData =  urldecode($this->_getParam('urlData', "..%2F..%2Fdata%2Fice%2FActeurChromecastNbIndividu.csv"));
                $this->view->refNb = 'Nb individu';
                $this->view->refLbl = 'Acteur';
                break;            
            case 'iceCommunChromecastMiseSurMarchePereEnfant':
                $this->view->urlData =  urldecode($this->_getParam('urlData', "..%2F..%2Fdata%2Fice%2FiceCommunChromecastMiseSurMarchePereEnfant.csv"));
                $this->view->refNb = '% commun';
                $this->view->refLbl = 'Dimensions communes';
                break;
            case 'iceCommunChromecastPereFille':
                $this->view->urlData =  urldecode($this->_getParam('urlData', "..%2F..%2Fdata%2Fice%2FiceCommunChromecastPereFille.csv"));
                $this->view->refNb = '% commun';
                $this->view->refLbl = 'Dimensions communes';
                break;
        }
        $this->view->titre =  $this->_getParam('titre', "Positions en couleur");
        
    }
    
        
    public function paralleleAction(){
    
	    	$this->view->titre =  $this->_getParam('titre', "Test Pararallèle");
    
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
			$dt = $this->getSpipMot();
		}
		if($this->_getParam('pageSPIP', false)){
			//$dt = $this->getSpipMot();
			$s = new Flux_Site();
			$dt = $s->getUrlBodyContent($this->_getParam('pageSPIP'));
		}
		
		$this->view->data =  $this->_getParam('data', $dt);    	
		$this->view->w =  $this->_getParam('w', 0);    	
		$this->view->h =  $this->_getParam('h', 0);    	
		$this->view->langue =  $this->_getParam('langue', "fr");    	
		$this->view->titre =  $this->_getParam('titre', "Roue des émotions");    	
		
    }   
    
    public function emostatAction(){
		$dt = "[
	      	{id:'path292',en:'Exitation',fr:'Exitation',alpha:1,color:'#ffe956',value:0}
	      	,{id:'path276',en:'Exitation',fr:'Exitation',alpha:0.9,color:'#ffe956',value:0}
	      	,{id:'path248',en:'Exitation',fr:'Exitation',alpha:0.8,color:'#ffe956',value:0}
	      	,{id:'path220',en:'Exitation',fr:'Exitation',alpha:0.7,color:'#ffe956',value:0}
	      	,{id:'path192',en:'Exitation',fr:'Exitation',alpha:0.6,color:'#ffe956',value:0}
	      	,{id:'path164',en:'Exitation',fr:'Exitation',alpha:0.5,color:'#ffe956',value:0}
	      	,{id:'path136',en:'Exitation',fr:'Exitation',alpha:0.4,color:'#ffe956',value:0}
	      	,{id:'path108',en:'Exitation',fr:'Exitation',alpha:0.3,color:'#ffe956',value:0}
	      	,{id:'path80',en:'Exitation',fr:'Exitation',alpha:0.2,color:'#ffe956',value:0}
	      	,{id:'path52',en:'Exitation',fr:'Exitation',alpha:0.1,color:'#ffe956',value:0}
	      	,{id:'path300',en:'Frustration',fr:'Frustration',alpha:1,color:'rgb(13, 129, 54)',value:0}
	      	,{id:'path284',en:'Frustration',fr:'Frustration',alpha:0.9,color:'rgb(13, 129, 54)',value:0}
	      	,{id:'path256',en:'Frustration',fr:'Frustration',alpha:0.8,color:'rgb(13, 129, 54)',value:0}
	      	,{id:'path228',en:'Frustration',fr:'Frustration',alpha:0.7,color:'rgb(13, 129, 54)',value:0}
	      	,{id:'path200',en:'Frustration',fr:'Frustration',alpha:0.6,color:'rgb(13, 129, 54)',value:0}
	      	,{id:'path172',en:'Frustration',fr:'Frustration',alpha:0.5,color:'rgb(13, 129, 54)',value:0}
	      	,{id:'path144',en:'Frustration',fr:'Frustration',alpha:0.4,color:'rgb(13, 129, 54)',value:0}
	      	,{id:'path116',en:'Frustration',fr:'Frustration',alpha:0.3,color:'rgb(13, 129, 54)',value:0}
	      	,{id:'path88',en:'Frustration',fr:'Frustration',alpha:0.2,color:'rgb(13, 129, 54)',value:0}
			,{id:'path60',en:'Frustration',fr:'Frustration',alpha:0.1,color:'rgb(13, 129, 54)',value:0}
			,{id:'path296',en:'Meditation',fr:'Méditation',alpha:1,color:'rgb(35, 63, 146)',value:0}
			,{id:'path280',en:'Meditation',fr:'Méditation',alpha:0.9,color:'rgb(35, 63, 146)',value:0}
			,{id:'path252',en:'Meditation',fr:'Méditation',alpha:0.8,color:'rgb(35, 63, 146)',value:0}
			,{id:'path224',en:'Meditation',fr:'Méditation',alpha:0.7,color:'rgb(35, 63, 146)',value:0}
			,{id:'path196',en:'Meditation',fr:'Méditation',alpha:0.6,color:'rgb(35, 63, 146)',value:0}
			,{id:'path169',en:'Meditation',fr:'Méditation',alpha:0.5,color:'rgb(35, 63, 146)',value:0}
			,{id:'path140',en:'Meditation',fr:'Méditation',alpha:0.4,color:'rgb(35, 63, 146)',value:0}
			,{id:'path112',en:'Meditation',fr:'Méditation',alpha:0.3,color:'rgb(35, 63, 146)',value:0}
			,{id:'path84',en:'Meditation',fr:'Méditation',alpha:0.2,color:'rgb(35, 63, 146)',value:0}
			,{id:'path56',en:'Meditation',fr:'Méditation',alpha:0.1,color:'rgb(35, 63, 146)',value:0}
			,{id:'path288',en:'Engagement',fr:'Engagement',alpha:1,color:'rgb(212, 20, 23)',value:0}
			,{id:'path272',en:'Engagement',fr:'Engagement',alpha:0.9,color:'rgb(212, 20, 23)',value:0}
			,{id:'path244',en:'Engagement',fr:'Engagement',alpha:0.8,color:'rgb(212, 20, 23)',value:0}
			,{id:'path216',en:'Engagement',fr:'Engagement',alpha:0.7,color:'rgb(212, 20, 23)',value:0}
			,{id:'path188',en:'Engagement',fr:'Engagement',alpha:0.6,color:'rgb(212, 20, 23)',value:0}
			,{id:'path160',en:'Engagement',fr:'Engagement',alpha:0.5,color:'rgb(212, 20, 23)',value:0}
			,{id:'path132',en:'Engagement',fr:'Engagement',alpha:0.4,color:'rgb(212, 20, 23)',value:0}
			,{id:'path104',en:'Engagement',fr:'Engagement',alpha:0.3,color:'rgb(212, 20, 23)',value:0}
			,{id:'path76',en:'Engagement',fr:'Engagement',alpha:0.2,color:'rgb(212, 20, 23)',value:0}
			,{id:'path48',en:'Engagement',fr:'Engagement',alpha:0.1,color:'rgb(212, 20, 23)',value:0}
			
	      	]";
				
		$this->view->data =  $this->_getParam('data', $dt);    	
		$this->view->w =  $this->_getParam('w', 0);    	
		$this->view->h =  $this->_getParam('h', 0);    	
		$this->view->langue =  $this->_getParam('langue', "fr");    	
		$this->view->titre =  $this->_getParam('titre', "Emo Stat");    	
		
    }   

    
    public function streamAction(){
		$this->view->urlData =  urldecode($this->_getParam('urlData', "..%2Fdata%2FdataStreamTest.csv"));   
		$this->view->color =  $this->_getParam('color', "blue");     	
		$this->view->cribles =  $this->_getParam('cribles', "false");    
		$this->view->timeFormat =  $this->_getParam('timeFormat', "%m/%d/%y"); 
		//https://github.com/d3/d3-3.x-api-reference/blob/master/Time-Intervals.md   
		$this->view->timeInterval =  $this->_getParam('timeInterval', "d3.time.weeks");    
		$this->view->mc = $this->getSpipMot();		
		/*url ppour stream emotion
		http://localhost/jdc/public/graph/stream?timeFormat=%25Y-%25m-%25d%20%25H%3A%25M%3A%25S&urlData=..%2Fgapaii%2Fgetrepquest%3Fcsv%3D1%26idQuest%3D5163&idMotParent=26&idBase=spip_proverbe&timeInterval=d3.time.days		 
		*/
		 	
    }

    public function streamvAction(){
	    	$this->view->titre =  $this->_getParam('titre', "Stream Graph Vertical");
	    	$this->view->soustitre =  $this->_getParam('soustitre', "Pour plonger dans le temps");
	    	$this->view->urlData =  urldecode($this->_getParam('urlData', "..%2Fdata%2Ftrends.csv"));
	    	$this->view->legendetitre =  $this->_getParam('legendetitre', "Catégories");
	    	$this->view->nbMin = $this->_getParam('nbMin', 0);
	    	$this->view->legende =  1;//par défaut la légende apparait     
	    	$this->view->q =  $this->_getParam('q');   
	    	switch ($this->_getParam('type')) {
	    		case "diigo":
	    			//var url = "http://localhost/jdc/public/flux/diigo?q=getHistoTagLies&idTag=27&idMonade=2&idUti=1&idActi=2&idParent=1&dateUnit=%Y-%m&for=stream&arrTags[]=ecosysteminfo";//&dates[]=1456669433&dates[]=1489415262";
	    			$this->view->titre =  "Evolution des mots clefs dans https://www.diigo.com/user/luckysemiosis";
	    			$this->view->soustitre =  "Pour la recherche : ".$this->_getParam('q', "ecosysteminfo");
	    			$this->view->urlData = "../flux/diigo?q=getHistoTagLies&idMonade=2&idUti=1&idActi=2&idParent=1&dateUnit=%Y-%m&for=stream"
	    						."&nbLimit=".$this->_getParam('nbLimit', 1)."&idTag=".urldecode($this->_getParam('idTag', "27"))
	    						."&nbMin=".$this->_getParam('nbMin', 5);
	    			$this->view->legendetitre =  "Mot-clefs";
	    			break;
	    		case "getHistoDiscipline":
	    			$this->view->titre =  "Evolution des disciplines dans https://www.rechercheisidore.fr";
	    			$this->view->soustitre =  "Pour la recherche : ".$this->_getParam('q', "écosystème");
	    			$this->view->urlData = "../flux/isidore?q=getHistoDiscipline&req=".urldecode($this->_getParam('q', "écosystème"));
	    			$this->view->legendetitre =  "Disciplines";
	    			break;
	    		case "valarnum":
	    		    $this->view->titre =  "Evolution des explorations émotionnelles";
	    		    $this->view->soustitre =  "Valorisation des archives numériques";
	    		    $this->view->urlData = "../flux/an?q=getEvalsMonadeHistoTag&stream=1&idBase=".$this->_getParam('idBase', 0)."&idMonade=".$this->_getParam('idMonade', 0);
	    		    $this->view->legendetitre =  "Emotions";
	    		    $this->view->legende =  0;//pas de légende pour ce graph
	    		    break;
	    	}
    	 
    }
    
    public function treemapAction(){
        $this->view->titre =  $this->_getParam('titre', "Tree Map");
        $this->view->soustitre =  $this->_getParam('soustitre', "Fragmenter les documents");
        $this->view->urlData =  urldecode($this->_getParam('urlData', "..%2Fdata%2Fflare.json"));
        switch ($this->_getParam('type')) {
            case "valarnum":
                $this->view->titre =  "Valorisation des archives numériques";
                $this->view->soustitre =  "Répartition des séries";
                $this->view->urlData = "../flux/an?q=getTreemapPhoto&idBase=".$this->_getParam('idBase', 'flux_valarnum_prod')."&idDoc=".$this->_getParam('idDoc', 3);
                break;
        }
        
    }
    
    public function imagegridAction(){
	
		$this->view->kSrc = $this->_getParam('kSrc', 'src');
		$this->view->kId = $this->_getParam('kId', 'id');
		
        $this->view->urlData =  $this->_getParam('urlData', '../data/googleApi.csv');
		
    }

    /**
     * action pour afficher des images dynamique dans un treemap
     *
     * @param  	string 		$q = pour afficher un type de collection
     * @example 'getOmkCollection' = une collection à partir d'un ItemSet d'Omeka S + IIIF 
     * @example 'getCollectionFaces' = une collection de visage à partir d'un ItemSet d'Omeka S + IIIF + Google Vision
     *
     * @param  	int  		$idCol = identifiant de l'ItemSet Omeka
     *
     */
    public function dynatofsAction(){
        
        $this->view->q =  $this->_getParam('q', "getOmkCollection");
        $this->view->idCol =  $this->_getParam('idCol', 1572);
        
        
    }
    
    
    public function multilignesAction(){
    	
    		//pour les performance diigo
    		$url ="../flux/diigo?q=performance&csv=1&deb=2017-01-01&fin=2017-31-01";
    		$this->view->formatTemps =  "%Y-%m-%d %H:%M:%S";
    		$this->view->champTemps =  "temps";
    		
    		/*pour l'historique complet d'un tag
    		$url ="../flux/diigo?q=getHistoTagLies&idTag=27&idMonade=2&idUti=1&idActi=2&idParent=1&dateUnit=%Y-%m&for=stream&for=multiligne&csv=1&tags[]=27";
    		$this->view->formatTemps =  "%Y-%m-%d";
    		$this->view->champTemps =  "DateTimeId";
    		//pour l'historique des tags liés
    		$url ="../flux/diigo?q=getHistoTagLies&idTag=27&idMonade=2&idUti=1&idActi=2&idParent=1&dateUnit=%Y-%m&for=stream&dates[]=1456669433&dates[]=1489415262&for=multiligne&csv=1";
    		$this->view->formatTemps =  "%Y-%m-%d";
    		$this->view->champTemps =  "DateTimeId";
    		//pour l'historique des statuts d'url non accessibles
    		$url ="../flux/diigo?q=getStatutUrl&csv=1";//&statuts[]=404&statuts[]=200";
    		$this->view->formatTemps =  "%Y";
    		$this->view->champTemps =  "DateTimeId";

	    	$this->view->formatTemps =  $this->_getParam('formatTemps', "%Y-%m-%d %H:%M:%S");
	    	$this->view->champTemps =  $this->_getParam('champTemps', "temps");;
    		*/
    	 
    		$this->view->urlData =  urldecode($this->_getParam('urlData', $url));
    
    }
    
    public function multiplelignesAction(){
    	     
	    	$this->view->formatTemps =  $this->_getParam('formatTemps', "%m-%Y");
	    	$this->view->champX =  $this->_getParam('champX', "temps");;
	    	$this->view->champY =  $this->_getParam('champY', "nbDoc");;
	    	$this->view->champSerie =  $this->_getParam('champSerie', "login");;
	    	
	    	$this->view->urlData =  urldecode($this->_getParam('urlData', $url));
    
    }
    
    public function pourcentverticalstepareaAction(){
    	 
	    	/*pour l'historique des statuts d'url non accessibles
	    	$url ="../flux/diigo?q=getStatutUrl&csv=1&for=area";//&statuts[]=404&statuts[]=200";
	    	$this->view->formatTemps =  "%Y";
	    	$this->view->champTemps =  "temps";
	    	$this->view->champGroupe =  "type";
	    	$this->view->filtreGroupe =  "'vivant','mort'";
	    	$this->view->axeX =  "nbDoc";
	    	*/
    	
    		$this->view->formatTemps =  $this->_getParam('formatTemps', "%Y-%m-%d %H:%M:%S");
    		$this->view->champTemps =  $this->_getParam('champTemps', "temps");
    		$this->view->champGroupe =  $this->_getParam('champGroupe', "type");
     	$this->view->axeX =  $this->_getParam('axeX', "nbDoc");
		$this->view->urlData =  urldecode($this->_getParam('urlData', $url));
    
    }
    
	public function matriceAction(){
		
	}
	
	public function correlationAction(){
		
	}

	public function combinatoireAction(){
	    
	}
	
	public function concentricringAction(){
	    
	}

	public function balayageAction(){
	    
	}

	public function quatreaxesAction(){
		$json = "[{'lbl':'clair','posi':0},{'lbl':'obscur','posi':180},{'lbl':'pertinent','posi':90},{'lbl':'inadapté','posi':270}]";
		$this->view->axes = $json;	    
	}
		
    function getSpipMot(){
	    	//vérifie s'il faut récupérer les données dans spip
		$dt = "[";
    		if($this->_getParam('idMotParent', false) && $this->_getParam('idBase', false)){
			$s = new Flux_Site($this->_getParam('idBase'));
			$dbM = new Model_DbTable_Spip_mots($s->db);
			$arrM = $dbM->findByIdMotParent($this->_getParam('idMotParent'));
			$deb = true;
			foreach ($arrM as $m) {
				if(!$deb)$dt .= ",";
				$dt .= $m["descriptif"];
				$deb = false;
			}
		}    	
		
		$dt .= "]";			
		return $dt;
    }
    
}

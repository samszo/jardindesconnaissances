<?php
/**
 * Flux_Ieml
 * Classe qui gère les flux IEML
 *
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

 class Flux_Ieml extends Flux_Site{

	//var $PATH_STAR_PARSER = 'http://starparser.ieml.org/cgi-bin/star2xml.cgi?iemlExpression=';
	var $PATH_STAR_PARSER = 'http://localhost/ieml/parser/star2xml.php?iemlExpression=';
	//var $PATH_STAR_PARSER = 'http://gapai.univ-paris8.fr/ieml/parser/star2xml.php?iemlExpression=';
	var $XPATH_BINARY = '//@binary';
  	var $XPATH_PRIMITIVE = '//@primitiveSet';
  	var $PRIMITIVE_VALUE = array("E"=>1,"U"=>2,"A"=>4,"S"=>8,"B"=>16,"T"=>32);
  	var $LAYER_PONCT = array(":",".","-","'",",","_",";");
	var $COLORS = array('E'=>"rgb(0,0,0)",'U'=>"rgb(0,255,255)",'A'=>"rgb(255,0,0)",'S'=>"rgb(0,255,0)",'B'=>"rgb(0,0,255)",'T'=>"rgb(255,255,0)");
	//var $COLORS = array('E'=>"rgb(20,0,0)",'U'=>"rgb(0,40,0)",'A'=>"rgb(0,0,60)",'S'=>"rgb(80,80,0)",'B'=>"rgb(0,100,100)",'T'=>"rgb(120,0,120)");
	var $PATH_DICO_TABLE = 'https://dictionary.ieml.io/api/scripts/tables';
	var $PATH_DICO_RELA = 'https://dictionary.ieml.io/api/relations';
	var $PATH_DICO_RANK = 'https://dictionary.ieml.io/api/terms/ranking';
	var $PATH_DICO = 'https://dictionary.ieml.io/api/all?version=';
	var $VERSION_DICO = 'dictionary_2018-09-03_20:39:39';
	
  	public function __construct($idBase=false,$bTrace=false,$bCache=true)
    {
    	parent::__construct($idBase,$bTrace,$bCache);
    	
    }

	/**
	 * Envoie un code au parser et return la valeur xml
	 * @param string $code IEML
	 * @param boolean $obj=false précise s'il faut retourner un objet ou une chaine de caractère 
	 * 
	 * @return string or xml
	 */
    function getParse($code,$obj=false){
	    set_time_limit(1000);
	    
		//parse le code
		$code = stripslashes ($code);
	    $lien = $this->PATH_STAR_PARSER.$code;
		
	    // request URL
		$sResult = $this->getUrlBodyContent($lien);
			
		//nettoie le résultat du parser
		$sResult = str_replace("<XMP>","",$sResult);
		$sResult = str_replace("</XMP>","",$sResult);
		$xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>'," ",$sResult);
		
		if(strstr($xml,"ERROR:")){
			return $xml;
		}
	    if($obj){
    		$xml = simplexml_load_string($xml);
	    }
		return $xml;	    
	    
	}    

	/**
	 * Enregistre le résultat du parser dans la base
	 */
    function saveParse(){

    	$this->dbIEML = new Model_DbTable_Flux_Ieml($this->db);
    	
    	//récupère les tag ieml sans parser
    	$arr = $this->dbIEML->findByParse("");

    	foreach ($arr as $r) {
    		$xml = $this->getParse($r['code']);
    		$this->dbIEML->edit($r['ieml_id'], array("parse"=>$xml));
    	}    	
	}    

	/**
	 * Enregistre le résultat du binaire dans la base
	 */
    function saveBinary(){

    	$this->dbIEML = new Model_DbTable_Flux_Ieml($this->db);
    	
    	//récupère les tag ieml sans parser
    	$arr = $this->dbIEML->getAll();

    	foreach ($arr as $r) {
    		if($r['parse']!="" && $r['binary']==""){
    			$xml = simplexml_load_string($r['parse']);
    			$bin = $xml->xpath($this->XPATH_BINARY); 
    			$this->dbIEML->edit($r['ieml_id'], array("binary"=>$bin[0]['binary']));
    		}
    	}    	
	}    

	/**
	 * Enregistre la valeur script d'une séquance
	 */
    function saveScriptValeur(){

    	$this->dbIEML = new Model_DbTable_Flux_Ieml($this->db);
    	
    	//récupère les tag ieml sans parser
    	$arr = $this->dbIEML->getAll();
		$v = "";
		$arrPV = $this->PRIMITIVE_VALUE;
    	foreach ($arr as $r) {
    		if($r['parse']!="" && $r['ordre']==""){
    			$xml = simplexml_load_string($r['parse']);
    			$primitives = $xml->xpath($this->XPATH_PRIMITIVE); 
    			foreach ($primitives as $ps){
    				$strPS = str_replace(" ","",substr($ps['primitiveSet'], 1, -1));
    				$arrP = explode(",",$strPS);
    				foreach($arrP as $p){
	    				$v .= decbin($arrPV[substr($p, 1, -1)]);  					
    				} 
    			}
    			$this->dbIEML->edit($r['ieml_id'], array("ordre"=>$v));
    		}
    	}    	
	}    
		
	/**
	 * Calcule la distance entre les formes binaire de deux adresses IEML
	 * @param string $bin1
	 * @param string $bin2
	 * 
	 * @return integer
	 */
    function getDistanceBinary($bin1, $bin2){

    	//récupère l'adresse la plus grande
    	if(strlen($bin1)>=strlen($bin2)) {$binSrc=$bin1;$binDst=$bin2;}
    	else {$binSrc=$bin1;$binDst=$bin2;}
    	
    	//boucle sur la référence
    	$d = 0;
    	$nbSrc = strlen($binSrc);
    	$nbDst = strlen($binDst);
    	for ($i = 0; $i < $nbSrc; $i++) {
    		if($nbDst <= $i){
    			$d += $binSrc[$i];
    		}elseif($binDst[$i] != $binSrc[$i]){
    			$d += abs($binDst[$i]-$binSrc[$i]);
    		}
    	}
    	
    	return $d;
    	
	}    
	

	/**
	 * Calcule la distance Hamming entre les formes binaire de deux adresses IEML
	 * http://fr.wikipedia.org/wiki/Distance_de_Hamming
	 * @param string $bin1
	 * @param string $bin2
	 * 
	 * @return integer
	 */
    function getDistanceHamming($bin1, $bin2){

    	//initialise les valeurs
    	$ham1 = gmp_init($bin1);
    	$ham2 = gmp_init($bin2);
    	
		//calcule la distance
		$d = gmp_hamdist($ham1, $ham2);
		
    	return $d;
    	
	}    
	
	/**
	 * Génération des séquences sur une ou plusieurs couches
	 * 
	 * @param int $nbLayer
	 * @param boolean $db = pour enregistrer dans une base de données
	 * @param array $s1
	 * @param array $s2
	 * @param array $s3
	 * 
	 * @return integer
	 */
    function genereSequences($nbLayer=1, $db=false, $s1 = array('E','U','A','S','B','T'), $s2 = array('E','U','A','S','B','T'), $s3 = array('E','U','A','S','B','T')){
		
    	if($db)$db = new Model_DbTable_Flux_Ieml($this->db);
    	for ($i = 0; $i < $nbLayer; $i++) {
			$x = new ArrayMixer($this->LAYER_PONCT[$i], $this->LAYER_PONCT[$i+1], $db);
			$x->append($s1);
			$x->append($s2);
			$x->append($s3);
			$x->proceed();
			$ls = $x->result();	
			print_r($ls);
			//réinitialise les tableaux
			$s1 = $ls;	
			$s2 = $ls;	
			$s3 = $ls;	
    	}
    }	

	/**
	 * Génération d'un plan svg des séquences
	 * 
	 * @param int $nb
	 * @param array $colors
	 * 
	 * @return SvgDocument
	 */
    function genereSvgPlanSeq($nb=1000, $colors = null){
		
    	require_once("svg/Svg.php");

    	if($colors)$this->COLORS = $colors;
    	
		
    	$db = new Model_DbTable_Flux_Ieml($this->db);
    	$arrSeq = $db->getAll("ieml_id",$nb);
    	$i = 1;
    	$x=10;
    	$y=30;
    	$r=20;
    	$maxLigne=5;
    	$numLigne=0;
    	$debLigne=-1;
    	$maxColo=3;
    	$numColo=0;
    	$debColo=10;
    	$intColo = 1;
    	$first2 = true;

    	$svg = new SvgDocument($nb/1302*2+1000, 1302*5*$r);
		$dDegrad = new SvgDefs();
		$gRect = new SvgGroup();
    	
    	foreach ($arrSeq as $c){
    		//vérifie si on traite une couche de niveau 1
    		if($c['niveau']==1){
    			//création d'un stop pour chaque couleur
    			$arr = explode(":", $c['code']);
    			//création du dégradé
    			$dDegrad->addChild($this->genereDegrad($c['code']));
				//$dDegrad->addChild(new SvgRadialGradient("lg_".$c['code'], array(0,0.5,1), array($this->COLORS[$arr[0]],$this->COLORS[$arr[1]],$this->COLORS[$arr[2]])));
	    		//création de la bulle
				$gRect->addChild(new SvgCircle($intColo*$r+$x, 2*$r*$numLigne+$y, $r, "fill:url(#rg_".$c['code'].")","","","c_".$c['code']));
    		}
    		//vérifie si on traite une couche de niveau 2
    		if($c['niveau']==2){
    			if($first2){
    			 	$first2 = false;
			    	$x=10;
    				$numLigne = $maxLigne-1;
    				$debLigne = $numLigne-1;
    				$maxLigne = 1302;
    				$numColo = 1;
    				$debColo=10;
    				$r += $r;
    			} 
    			/*création de 3 bulles
    			$arr = explode(".", $c['code']);
				$gRect->addChild(new SvgCircle(2*$r+$x, 2*$r*$numLigne+$y, $r, "fill:url(#lg_".$arr[0].".)","","","c_".$c['code']));
				$x+=2*$r;
				$gRect->addChild(new SvgCircle(2*$r+$x, 2*$r*$numLigne+$y, $r, "fill:url(#lg_".$arr[1].".)","","","c_".$c['code']));
				$x+=2*$r;
				$gRect->addChild(new SvgCircle(2*$r+$x, 2*$r*$numLigne+$y, $r, "fill:url(#lg_".$arr[2].".)","","","c_".$c['code']));
				 = 5;
				*/
    			//création d'une bulle
    			$dDegrad->addChild($this->genereDegrad($c['code']));
	    		//création de la bulle
				$gRect->addChild(new SvgCircle($intColo*$r+$x, 2*$r*$numLigne+$y, $r, "fill:url(#rg_".$c['code'].")","","","c_".$c['code']));
    			$nextColo = 1;
    							
				$x = $debColo;
    		}
    		if($maxLigne<=$numLigne){
    			$x+=$intColo*$r;
		    	$numLigne = $debLigne;
		    	if($c['niveau']==2){
					$debColo = $nextColo*$r+$x;
					$x = $debColo;
					$numColo ++;
		    	}			
    		}
    		
    		$i++;
    		$numLigne++;
    	}
				
		/*
		$svg->asXML("test.svg");
    	$svg->output();
		*/
    	
		$dDegrad->addParent($svg);
		$gRect->addParent($svg);
		
		return $svg;
    	
	}	
	
	/**
	 * Récupère le détail d'un code IEML
	 * 
	 * @param string $code
	 * 
	 * @return xml
	 */
    function getDetail($code){

		$db = new Model_DbTable_Flux_Ieml($this->db);

		//récupère le détail de l'adresse
		$arr = $db->findByCode($code);    	
		if(count($arr)==0){
			$xml = $this->getParse($code, true);
			$parse = $xml->asXML();
			$ieml_id = $db->ajouter(array("code"=>$code,"parse"=>$parse));	
		}else{
			if($arr[0]['parse']==""){
				$xml = $this->getParse($arr[0]['code'], true);
				$arr[0]['parse'] = $xml->asXML();
				$db->edit($arr[0]['ieml_id'], array("parse"=>$arr[0]['parse']));
			}
			$xml = simplexml_load_string($arr[0]['parse']);
		}
		return $xml;
	}

	/**
	 * Récupère la liste des primitives
	 * 
	 * @param xml $xml
	 * 
	 * @return array
	 */
    function getPrimitives($xml){

	    $result = $xml->xpath($this->XPATH_PRIMITIVE);
    	//arsort($result);
    	
	    //construction de la liste des primitives
	    $prims = "";
		foreach ($result as $value) {
			$arrPrim = explode(",", substr($value, 1,-1));
			foreach ($arrPrim as $p){
				$prims .= str_replace("'", "", trim($p)).":";				
			}
		}
		//récupère la valeur binaire
		$bin = $xml->xpath($this->XPATH_BINARY); 

		return array($result, $prims, $bin[0]['binary']->__toString());
	}	
	/**
	 * Génération d'un plan svg d'une adresse IEML
	 * 
	 * @param array $ieml
	 * 
	 * @return SvgDocument
	 */
    function genereSvgAdresse($ieml){
		
    	require_once("svg/Svg.php");		

		//récupère le détail de l'adresse
		$xml = $this->getDetail($ieml['code']);
		//récupère les primitives
		$r = $this->getPrimitives($xml);
		$result = $r[0];
		$prims = $r[1];
		
		//construction du svg
		$taille = 6*count($result);
		$marge = 10;
		$svg = new SvgDocument();
		$dDegrad = new SvgDefs();
		$gRect = new SvgGroup();
		
		//création du dégradé radial
    	$dDegrad->addChild($this->genereDegrad($prims));
		
    	//création du dégradé linéaire
    	$dDegrad->addChild($this->genereDegrad($prims,"linéaire"));
    	
    	//création de la bulle
		$gRect->addChild(new SvgCircle($taille/2+$marge, $taille/2+$marge, $taille/2, "fill:url(#rg_".$prims.")","","","c_".$prims));
    	//création de la barre
		$gRect->addChild(new SvgRect($taille+$marge, $marge, $taille/2, $marge*2, "fill:url(#lg_".$prims.")","","","b_".$prims));
    	//création des textes
		$gRect->addChild(new SvgText($taille+$marge, $marge*6, stripslashes($arr[0]['code'])));
		$gRect->addChild(new SvgText($taille+$marge, $marge*8, stripslashes($arr[0]['desc'])));
		
		/*
		$svg->asXML("test.svg");
    	$svg->output();
		*/
    	
		$dDegrad->addParent($svg);
		$gRect->addParent($svg);
		
		return $svg;
    	
    }	
            
	/**
	 * Génération des dégradés d'une adresse
	 * 
	 * @param string $code
	 * @param string $type
	 * 
	 * @return SvgRadialGradient
	 */
    function genereDegrad($code, $type="radial"){
    	
    	$arrP = explode(":", $code);
    	$nbColors = count($arrP)-1;
    	$pasOffset = 1/$nbColors;
    	$arrOffset = array();
    	$arrColor = array();
		for ($i = 0; $i < $nbColors; $i++) {
			$p = str_replace($this->LAYER_PONCT, "", $arrP[$i]);
			if($i==0){
				$arrOffset[] = 0;
			}else{
				$arrOffset[] = $i*$pasOffset;
			}
			$arrColor[] = $this->COLORS[$p];
		}    	

		if($type=="radial")
			return new SvgRadialGradient("rg_".$code, $arrOffset, $arrColor);
		else
			return new SvgLinearGradient("lg_".$code, $arrOffset, $arrColor);
		
    }
    
	/**
	 * importation d'un tableau IEML en SVG
	 * 
	 * @param string $urlSVG
	 * 
	 */
    function importSVG($urlSVG){

    	$this->bTrace = true; // pour afficher les traces
    	$this->temps_debut = microtime(true);
    	$this->trace("DEBUT ".__METHOD__);
    	$this->trace("urlSVG = ".$urlSVG);    	
    	
    	//récupère la page
		$html = $this->getUrlBodyContent($urlSVG);
		$dom = new Zend_Dom_Query($html);
		
		//récupère les graphiques parents
		$results = $dom->query('//*[@id="parent_1"]');
		foreach ($results as $r) {
			$s = simplexml_import_dom($r);
			foreach ($s->g as $g) {
				foreach ($g->g[0]->g[0]->text[0]->tspan as $t) {
					$this->trace((string)$t);					
				}
			}
		}		

		//récupère les graphiques 
		$results = $dom->query('//*[@id="tspan5478"]');
		foreach ($results as $r) {
			$this->trace($r->nodeValue);					
		}		
		
    	$this->trace("FIN ".__METHOD__);
    	
    }
	
	/**
	 * récupère les informations d'une item du dictionnaire
	 * 
	 * @param string $code
	 * 
	 */
    function getDicoItem($code){

    	$this->trace("DEBUT ".__METHOD__." = ".$code);
    	
    	//récupère les élements de l'item
		$jsonTable = $this->getUrlBodyContent($this->PATH_DICO_TABLE
			,array('version'=>$this->VERSION_DICO,'ieml'=>$code),$this->bCache);
		$jsonRela = $this->getUrlBodyContent($this->PATH_DICO_RELA
			,array('version'=>$this->VERSION_DICO,'ieml'=>$code),$this->bCache);
		$jsonRank = $this->getUrlBodyContent($this->PATH_DICO_RANK
			,array('version'=>$this->VERSION_DICO,'ieml'=>$code),$this->bCache);
		
		$this->trace("FIN ".__METHOD__);
		
		return array('table'=>json_decode($jsonTable),'rela'=>json_decode($jsonRela),'rank'=>json_decode($jsonRank));
    }	
	/**
	 * récupère les items d'une version de dictionnaire
	 * 
	 * @param string $version
	 * 
	 */
    function getDico($version=false){
		if($version)$this->VERSION_DICO=$version; 
    	$this->trace("DEBUT ".__METHOD__." = ".$this->VERSION_DICO);
    	
    	//récupère le dictionnaire
		$json = $this->getUrlBodyContent($this->PATH_DICO
			,array('version'=>$this->VERSION_DICO,'ieml'=>$code),$this->bCache);

		$this->trace("FIN ".__METHOD__);
		
		return json_decode($json);
    }	
	/**
	 * récupère les items d'une version de dictionnaire et les enrichie
	 * 
	 * @param string $version
	 * 
	 */
    function getDicoPlus($version=false){
		if($version)$this->VERSION_DICO=$version; 
		set_time_limit(0);
    	$this->trace("DEBUT ".__METHOD__." = ".$this->VERSION_DICO);
    	
		if($this->bCache){
			$c = str_replace("::", "_", __METHOD__)."_".md5($version); 
			if($param)$c .= "_".$this->getParamString($param);
			   $arr = $this->cache->load($c);
		}
		if(!$arr){
			//récupère le dictionnaire
			$arr = $this->getDico($version);
			$nb = count($json);
			$i = 0;
			foreach ($arr as $ieml) {
				//pour le débugage $i>=4733 && 
				/** ATTENTION on ne peut pas prendre en compte les valeurs composée avec + */
				if(!strrpos($ieml->IEML,"+")){
					//récupère le détail de l'adresse
					$this->trace($i." : ".$ieml->IEML);
					$xml = $this->getDetail($ieml->IEML);
					//récupère les primitives
					$r = $this->getPrimitives($xml);
					//met à jour les infos
					$ieml->primitives = $r[1]; 
					$this->trace($ieml->primitives);
					$ieml->binary = $r[2]; 
					$this->trace($ieml->binary);
				}
				$i++;
			}

			if($this->bCache)$this->cache->save($arr, $c);
		}


		$this->trace("FIN ".__METHOD__);
		
		return $arr;
    }	
    
}
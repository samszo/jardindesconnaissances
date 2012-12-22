<?php
/**
 * Classe qui gère les flux IEML
 *
 * @copyright  2012 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_IEML extends Flux_Site{

  	var $PATH_STAR_PARSER = 'http://starparser.ieml.org/cgi-bin/star2xml.cgi?iemlExpression=';
  	var $XPATH_BINARY = '//@binary';
  	var $XPATH_PRIMITIVE = '//@primitiveSet';
  	var $PRIMITIVE_VALUE = array("E"=>1,"U"=>2,"A"=>4,"S"=>8,"B"=>16,"T"=>32);
  	var $LAYER_PONCT = array(":",".","-","'",",","_",";");
	var $COLORS = array('E'=>"rgb(0,0,0)",'U'=>"rgb(0,255,255)",'A'=>"rgb(255,0,0)",'S'=>"rgb(0,255,0)",'B'=>"rgb(0,0,255)",'T'=>"rgb(255,255,0)");
	
  	public function __construct($idBase=false)
    {
    	parent::__construct($idBase);
    	
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
	    if(eregi('<(.*)>(.*)<(.*)>',$sResult)){
	    	$xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>'," ",$sResult);
	    }
		
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

    	$this->dbIEML = new Model_DbTable_flux_ieml($this->db);
    	
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

    	$this->dbIEML = new Model_DbTable_flux_ieml($this->db);
    	
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

    	$this->dbIEML = new Model_DbTable_flux_ieml($this->db);
    	
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
		
    	if($db)$db = new Model_DbTable_flux_ieml($this->db);
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
	 * @return string
	 */
    function genereSvgPlanSeq($nb=1000, $colors = null){
		
    	require_once("svg/Svg.php");

    	if($colors)$this->COLORS = $colors;
    	
		
    	$db = new Model_DbTable_flux_ieml($this->db);
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
				$gRect->addChild(new SvgCircle($intColo*$r+$x, 2*$r*$numLigne+$y, $r, "fill:url(#lg_".$c['code'].")","","","c_".$c['code']));
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
				$gRect->addChild(new SvgCircle($intColo*$r+$x, 2*$r*$numLigne+$y, $r, "fill:url(#lg_".$c['code'].")","","","c_".$c['code']));
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
		
		// Send a message to the svg instance to start printing.
		$svg->printElement();
    	
    }	
    
	/**
	 * Génération des dégradés d'une adresse
	 * 
	 * @param string $code
	 * 
	 * @return SvgRadialGradient
	 */
    function genereDegrad($code){
    	
    	$arrP = explode(":", $code);
    	$nbColors = count($arrP)-1;
    	$pasOffset = 1/($nbColors-1);
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
    	
		return new SvgRadialGradient("lg_".$code, $arrOffset, $arrColor);
    	
    }
    
}
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
	
}
<?php
/**
 * Classe qui gère les flux dbpedia
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 * THANKS
 * http://data.bnf.fr/sparql/
 * http://data.bnf.fr/docs/doc_requetes_data.pdf
 */
class Flux_Databnf extends Flux_Site{

	var $formatResponse = "json";
	var $searchUrl = 'http://data.bnf.fr/sparql?';
	var $rs;
	var $doublons;
	
    /**
     * Constructeur de la classe
     *
     * @param  string $idBase
     * 
     */
	public function __construct($idBase=false, $bTrace=false)
    {
    		parent::__construct($idBase, $bTrace);    	
    }

    /**
     * Execute une resuète sur databnf
     *
     * @param  string $query
     *
     * @return string
     */
    public function query($query)
    {
	    $url = $this->searchUrl.'query='.urlencode($query)
	      	.'&format='.$this->formatResponse;
		return $this->getUrlBodyContent($url,false);
    }
    
    /**
     * Recherche un terme à partir de l'autocomplétion
     *
     * @param  string $term
     *
     * @return string
     */
    public function getTerm($term)
    {
		return $this->getUrlBodyContent("http://data.bnf.fr/search-letter/?term=".urlencode($term));
    }

    /*
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
SELECT DISTINCT ?original_rameau ?prefLabel ?uri_1 ?label_1 ?uri_a ?label_a ?uri_b ?label_b
WHERE {
?original_rameau bnf-onto:FRBNF "11933190"^^xsd:integer; 
 skos:prefLabel ?prefLabel ;
 skos:broader ?uri_1 ;
 skos:narrower ?uri_a .
MINUS {?original_rameau foaf:focus ?focus .}
?uri_a skos:prefLabel ?label_a .
?uri_1 skos:prefLabel ?label_1 .
OPTIONAL {
?uri_a skos:narrower ?uri_b .
?uri_b skos:prefLabel ?label_b .
}
}
ORDER BY ASC (?label_a) 
*/
    
    /**
     * Recherche une biographie à partir d'un identifiant BNF
     * cf. http://www.bnf.fr/fr/professionnels/issn_isbn_autres_numeros/a.ark.html
     *
     * @param  string $idBnf
     *
     * @return string
     */
    public function getBio($idBnf)
    {	   	
	   	//récupère les infos de data bnf
	   	$query =
		'SELECT DISTINCT ?idArk ?nom ?prenom ?nait ?mort  WHERE
		{
		?idArk bnf-onto:FRBNF "'.$idBnf.'"^^xsd:integer;
		foaf:focus ?identity.
		?identity foaf:familyName ?nom;
		foaf:givenName ?prenom.
		OPTIONAL {?identity bio:birth ?nait.}
		OPTIONAL {?identity bio:death ?mort.} 
		}';	   			   	
		$result = $this->query($query);
		$obj1 = json_decode($result);
		//construction de la réponse
		$objResult->data=array("bnf"=>array("idArk"=>$obj1->results->bindings[0]->idArk->value,"liens"=>array(),"isni"=>""));
		$objResult->nom = $obj1->results->bindings[0]->nom->value;				
		$objResult->prenom = $obj1->results->bindings[0]->prenom->value;				
		$objResult->nait = $obj1->results->bindings[0]->nait->value;				
		$objResult->mort = $obj1->results->bindings[0]->mort->value;				
		
		//récupère les données liées
		$query =
		'SELECT DISTINCT ?p ?o WHERE {
		<'.$obj1->results->bindings[0]->idArk->value.'> ?p ?o.
		}';	   	
		$result = $this->query($query);
		$obj2 = json_decode($result);		
		//construction de la réponse
		$liens = array();
		$i=0;
		foreach ($obj2->results->bindings as $val) {
			//ajoute les liens direct
			if($val->p->value=="http://www.w3.org/2004/02/skos/core#exactMatch"){
				array_push($liens, array("value"=>$val->o->value,"recid"=>$i,"type"=>"ref"));
				$i++;
			}
			//ajout l'isni
			if($val->p->value=="http://isni.org/ontology#identifierValid")
				$objResult->data["bnf"]["isni"] = $val->o->value;				
		}
		$objResult->data->liens=$liens;		
		return json_encode($objResult);
    }	
    
     /**
     * Recherche une fiche sudoc à partir d'un isbn
     *
     * @param  string $isbn
     *
     * @return string
     */
    public function getSudocAutoriteByISBN($isbn){
    		$arrMC = array();
    		//execute la recherche
		$searchUrl = "http://www.sudoc.abes.fr/DB=2.1/SET=14/TTL=1/CMD?ACT=SRCHA&IKT=7&SRT=RLV&TRM=".$isbn;
		$rdfUrl = "http://www.sudoc.fr/";
    		$html = $this->getUrlBodyContent($searchUrl,false);
    		//echo $html;
		$dom = new Zend_Dom_Query($html);	    
		//récupère la liste
		$xPath = '//table[@summary="short title presentation"]/tr/td[3]/input';
		$results = $dom->queryXpath($xPath);
		foreach ($results as $result) {
			//récupère l'identifiant du livre
		    $idBook = $result->getAttribute("value");
		    $rdf = $this->getUrlBodyContent($rdfUrl.$idBook.".rdf",false);
		  	$domRdf = new Zend_Dom_Query($rdf);
			//récupère la liste des thèmes
			$rsTheme = $domRdf->queryXpath("//dc:subject");
			foreach ($rsTheme as $theme) {
				$arrMC[]=$theme->nodeValue;
			}
		}	    
    		return $arrMC;
    }

     /**
     * Recherche des données dans Gallica
     *
     * @param  string $term
     *
     * @return string
     */
    public function getGallicaByTerm($term){
    		$arrMC = array();
    		//execute la recherche
		$searchUrl = "http://gallica.bnf.fr/SRU";
		$html = $this->getUrlBodyContent($searchUrl
			,array("operation"=>"searchRetrieve","version"=>1.2,"maximumRecords"=>10,"startRecord"=>1
				,"query"=>'gallica%20all%20"'.$term.'"'),false);
    		echo $html;
    		/*
		$dom = new Zend_Dom_Query($html);	    
		//récupère la liste
		$xPath = '//table[@summary="short title presentation"]/tr/td[3]/input';
		$results = $dom->queryXpath($xPath);
		foreach ($results as $result) {
			//récupère l'identifiant du livre
		    $idBook = $result->getAttribute("value");
		    $rdf = $this->getUrlBodyContent($rdfUrl.$idBook.".rdf",false);
		  	$domRdf = new Zend_Dom_Query($rdf);
			//récupère la liste des thèmes
			$rsTheme = $domRdf->queryXpath("//dc:subject");
			foreach ($rsTheme as $theme) {
				$arrMC[]=$theme->nodeValue;
			}
		}	    
    		return $arrMC;
    		*/
    }
    
    
     /**
     * Recherche un livre dans databnf à partir d'un isbn
     *
     * @param  string $isbn
     *
     * @return string
     */
    public function getBookByISBN($isbn){
	   	
	   	//récupère les infos de data bnf
		$query =
		'SELECT DISTINCT ?work ?title ?name ?creator ?p ?o
			WHERE {
			  ?work rdfs:label ?title;
			    dcterms:creator ?creator.
			  ?manifestation bnf-onto:isbn "'.$isbn.'" ;
			    rdarelationships:workManifested ?work.
			  ?creator foaf:name ?name.
			  ?work ?p ?o .  
			}';	   	
		$result = $this->query($query);
		return $result;
		//construction de la réponse
		$obj = json_decode($result);		
		$objResult->liens = array();
		foreach ($obj->results->bindings as $val) {
			//ajoute les liens direct
			if($val->p->value=="http://www.w3.org/2004/02/skos/core#exactMatch")
				array_push($objResult->liens, $val->o->value);
			//ajout l'isni
			if($val->p->value=="http://isni.org/ontology#identifierValid")
				$objResult->isni = $val->o->value;				
		}
		return json_encode($objResult);
    }

	
	/**
     * Compte le nombre de document avec un mot-clef rameau
     *
     * @param  string $idBnf
     *
     * @return integer
     */
    public function countDocByRameau($idBnf){	   	
	   	//récupère les infos de data bnf
		$query =
			'SELECT ?uSujet ?lSujet (COUNT(DISTINCT ?dSujet) as ?cDS) 
			WHERE { 
				?uSujet bnf-onto:FRBNF "'.$idBnf.'"^^xsd:integer;
			    skos:prefLabel ?lSujet;
			    dcterms:isPartOf ?scheme.	
			  	?dSujet dcterms:subject ?uSujet.
			} 
			GROUP BY ?uSujet ?lSujet ';	   	
		$result = $this->query($query);
		
		//construction de la réponse
		$obj = json_decode($result);		
		return json_encode($objResult);
    }      
    
	/**
     * Recherche un mot-clef rameau à partir d'un IDBNF ou d'un label
     *
     * @param  string 	$idBnf
     * @param  string 	$label
     * @param  int 		$niv
     * @param  int 		$nivMax
     *
     * @return string
     */
    public function getRameau($idBnf, $label, $niv=0, $nivMax=1){	   	
	   	//récupère les infos de data bnf
		$query = 'SELECT DISTINCT ?iSujet ?uSujet ?lSujet ?uLarge ?iLarge ?lLarge ?uLien ?iLien ?lLien ?uPrecis ?iPrecis ?lPrecis ';
		if($label){
			$query .= ' WHERE {
 				?uSujet skos:altLabel ?aSujet;
				bnf-onto:FRBNF ?iSujet;
				skos:prefLabel ?lSujet;  
				skos:altLabel ?aSujet;  
			    dcterms:isPartOf ?scheme.
			  FILTER (regex(?aSujet, "'.$label.'", "i" ) || regex(?lSujet, "'.$label.'", "i" )) ';
		}
		if($idBnf){
			$query .=' WHERE {
					?uSujet bnf-onto:FRBNF "'.$idBnf.'"^^xsd:integer;
				    bnf-onto:FRBNF ?iSujet;
				    skos:prefLabel ?lSujet;
				    dcterms:isPartOf ?scheme. ';
		}	    
		$query .=' OPTIONAL {
			    ?uLarge skos:narrower ?uSujet;
			    bnf-onto:FRBNF ?iLarge;
			    skos:prefLabel ?lLarge.
			  }
			  OPTIONAL {
			    ?uLien skos:related ?uSujet;
			    bnf-onto:FRBNF ?iLien;
			    skos:prefLabel ?lLien.
			  }
			  OPTIONAL {
			    ?uPrecis skos:broader ?uSujet;
			    bnf-onto:FRBNF ?iPrecis;
			    skos:prefLabel ?lPrecis.
			  }
			} ';	   	
		$result = $this->query($query);
		
		//construction de la réponse
		$obj = json_decode($result);		
		/*construction de la réponse pour un affichage réseau
		 * {"nodes":[{"name":"Agricultural 'waste'"},...],
		 * "links":[{"source":0,"target":1,"value":124.729},...]
		 * }
		 */
		//ajoute les noeuds "plus large" et "plus précis" 
		if(!$this->rs)$this->rs = (object) array("nodes" => array(
									array("name"=>"Plus générique","uri"=>"","recid"=>0)
									,array("name"=>"Plus spécifique","uri"=>"","recid"=>1)
									), 
									"links" => array());
		if(!$this->doublons)$this->doublons = array();
		foreach ($obj->results->bindings as $val) {
			if(!$idBnf)$idBnf=$val->iSujet->value;
			$s = $this->ajoutNoeud(array("name"=>$val->lSujet->value,"uri"=>$val->uSujet->value,"recid"=>$idBnf,"type"=>"sujet"));
			if(isset($val->lLien) && $niv < $nivMax){
				$l = $this->ajoutNoeud(array("name"=>$val->lLien->value,"uri"=>$val->uLien->value,"recid"=>$val->iLien->value,"type"=>"lien"));
				if($niv < $nivMax){
					//récupère la définition du lien
					$this->getRameau($val->iLien->value,'',$niv+1,$nivMax);
				}
			}
			if(isset($val->lPrecis)){
				$p = $this->ajoutNoeud(array("name"=>$val->lPrecis->value,"uri"=>$val->uPrecis->value,"recid"=>$val->iPrecis->value,"type"=>"precis"));
				if($niv+1 < $nivMax){
					//récupère la définition du lien
					$this->getRameau($val->iPrecis->value,'',$niv+1,$nivMax);
				}				
				$this->ajoutLien($s, $p);			
				$this->ajoutLien($p, 1);			
			}else{
				$this->ajoutLien($s, 1);			
			}	
			if(isset($val->lLarge)){
				$l = $this->ajoutNoeud(array("name"=>$val->lLarge->value,"uri"=>$val->uLarge->value,"recid"=>$val->iLarge->value,"type"=>"large"));
				if($niv+1 < $nivMax){
					//récupère la définition du lien
					$this->getRameau($val->iLarge->value,'',$niv+1,$nivMax);
				}				
				$this->ajoutLien($l, $s);			
				$this->ajoutLien(0, $l);			
			}else{
				$this->ajoutLien(0, $s);			
			}		
		}
		return $this->rs;
    }    

	/**
     * Ajout un noeud au résultat
     *
     * @param  array $arr
     *
     * @return int
     */
    function ajoutNoeud($arr){
		if(!isset($this->doublons[$arr["name"]])){
			$arr["num"]=	count($this->rs->nodes);				
			$this->rs->nodes[] = $arr;
			$this->doublons[$arr["name"]] = $arr["num"];
		}
		return $this->doublons[$arr["name"]];		
    }

	/**
     * Ajout un lien au résultat
     *
     * @param  int $s
     * @param  int $t
     * @param  int $v
     *
     * @return int
     */
    function ajoutLien($s, $t, $v=1){
		if(!isset($this->doublons[$s."_".$t])){					
			$this->rs->links[] = array("source"=>$s,"target"=>$t,"value"=>$v);
			$this->doublons[$s."_".$t] = count($this->rs->links)-1;						
		}else{
			//$this->rs->links[$this->doublons[$s."_".$t]]["value"]++;
		} 
		return $this->doublons[$s."_".$t];		
    }
    
    /*arboressence rameau par id
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
SELECT DISTINCT ?original_rameau ?prefLabel ?uri_1 ?label_1 ?uri_a ?label_a ?uri_b ?label_b
WHERE {
?original_rameau bnf-onto:FRBNF "12162891"^^xsd:integer; 
 skos:prefLabel ?prefLabel ;
 skos:broader ?uri_1 ;
 skos:narrower ?uri_a .
MINUS {?original_rameau foaf:focus ?focus .}
?uri_a skos:prefLabel ?label_a .
?uri_1 skos:prefLabel ?label_1 .
OPTIONAL {
?uri_a skos:narrower ?uri_b .
?uri_b skos:prefLabel ?label_b .
}
}
ORDER BY ASC (?label_a) 

//si pas de thème plus large
SELECT DISTINCT ?original_rameau ?prefLabel ?uri_a ?label_a ?uri_b ?label_b
WHERE {
?original_rameau bnf-onto:FRBNF "12162891"^^xsd:integer; 
 skos:prefLabel ?prefLabel ;
 skos:narrower ?uri_a .
MINUS {?original_rameau foaf:focus ?focus .}
?uri_a skos:prefLabel ?label_a .
OPTIONAL {
?uri_a skos:narrower ?uri_b .
?uri_b skos:prefLabel ?label_b .
} 
}
ORDER BY ASC (?label_a) 

//relation d'un thème
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
SELECT DISTINCT ?sujet ?uLarge ?lLarge ?uLien ?lLien ?uPrecis ?lPrecis
WHERE {
 ?sujet skos:prefLabel "Culture"@fr;
    dcterms:isPartOf ?scheme.
  OPTIONAL {
    ?uLarge skos:narrower ?sujet;
    skos:prefLabel ?lLarge.
  }
  OPTIONAL {
    ?uLien skos:related ?sujet;
    skos:prefLabel ?lLien.
  }
  OPTIONAL {
    ?uPrecis skos:broader ?sujet;
    skos:prefLabel ?lPrecis.
  }
} 
ORDER BY ASC (?lLien)
*/
/*Livre à partir d'un isbn
PREFIX dcterms: <http://purl.org/dc/terms/>
SELECT DISTINCT ?work ?title ?name ?creator ?p ?o
WHERE {
  ?work rdfs:label ?title;
    dcterms:creator ?creator.
  ?manifestation bnf-onto:isbn "2-7073-0307-0" ;
    rdarelationships:workManifested ?work.
  ?creator foaf:name ?name.
  ?work ?p ?o .  
}
LIMIT 100
 * 
 */        
}
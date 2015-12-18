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
		$objResult->idArk = $obj1->results->bindings[0]->idArk->value;				
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
		$objResult->liens = array();
		$i=0;
		foreach ($obj2->results->bindings as $val) {
			//ajoute les liens direct
			if($val->p->value=="http://www.w3.org/2004/02/skos/core#exactMatch"){
				array_push($objResult->liens, array("value"=>$val->o->value,"recid"=>$i));
				$i++;
			}
			//ajout l'isni
			if($val->p->value=="http://isni.org/ontology#identifierValid")
				$objResult->isni = $val->o->value;				
		}
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
     * Recherche un mot-clef rameau à partir d'un label
     *
     * @param  string $isbn
     *
     * @return string
     */
    public function getRameauByLabel($label){
	   	$format = 'json';	 
	   	
	   	//récupère les infos de data bnf
		$query =
			'PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
			SELECT DISTINCT ?sujet ?uLarge ?lLarge ?uLien ?lLien ?uPrecis ?lPrecis
			WHERE {
			 ?sujet skos:prefLabel "'.$label.'"@fr;
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
			ORDER BY ASC (?lLien)';	   	
		$result = $this->query($query);
		
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
     * Recherche un mot-clef rameau à partir d'un IDBNF
     *
     * @param  string $idBnf
     *
     * @return string
     */
    public function getRameauByIdBnf($idBnf){	   	
	   	//récupère les infos de data bnf
		$query =
			'SELECT DISTINCT ?sujet ?lSujet ?uLarge ?lLarge ?uLien ?lLien ?uPrecis ?lPrecis ?lLargeLien ?uLargeLien ?lPrecisLien ?uPrecisLien
			WHERE {
				?sujet bnf-onto:FRBNF "'.$idBnf.'"^^xsd:integer;
			    skos:prefLabel ?lSujet;
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
			  OPTIONAL {
			    ?uLargeLien skos:narrower ?uLien;
			    skos:prefLabel ?lLargeLien.
			  }  
			  OPTIONAL {
			    ?uPrecisLien skos:broader ?uLien;
			    skos:prefLabel ?lPrecisLien.
			  }    			  
			} 
			ORDER BY ASC (?lLien)';	   	
		$result = $this->query($query);
		
		//construction de la réponse
		$obj = json_decode($result);		
		/*construction de la réponse pour un affichage réseau
		 * {"nodes":[{"name":"Agricultural 'waste'"},...],
		 * "links":[{"source":0,"target":1,"value":124.729},...]
		 * }
		 */
		//ajoute les noeuds "plus large" et "plus précis" 
		$objResult = (object) array("nodes" => array(
									array("name"=>"Plus large","uri"=>"","recid"=>0)
									,array("name"=>"Plus précis","uri"=>"","recid"=>1)
									), 
									"links" => array());
		$doublons = array();
		$taille = 64;	
		foreach ($obj->results->bindings as $val) {
			if(!isset($doublons[$val->lSujet->value])){
				$objResult->nodes[] = array("name"=>$val->lSujet->value,"uri"=>$val->sujet->value,"recid"=>count($objResult->nodes),"type"=>"sujet");
				$doublons[$val->lSujet->value] = count($objResult->nodes)-1;
				$ss = $doublons[$val->lSujet->value];
			}
			if(isset($val->lLien)){
				if(!isset($doublons[$val->lLien->value])){
					$objResult->nodes[] = array("name"=>$val->lLien->value,"uri"=>$val->uLien->value,"recid"=>count($objResult->nodes),"type"=>"lien");
					$doublons[$val->lLien->value] = count($objResult->nodes)-1;
				}
				$t = $doublons[$val->lLien->value];
				if(isset($val->lLargeLien)){
					if(!isset($doublons[$val->lLargeLien->value])){
						$objResult->nodes[] = array("name"=>$val->lLargeLien->value,"uri"=>$val->uLargeLien->value,"recid"=>count($objResult->nodes),"type"=>"lien_large");
						$doublons[$val->lLargeLien->value] = count($objResult->nodes)-1;
					}
					$s = $doublons[$val->lLargeLien->value];
					if(!isset($doublons[$s."_".$t])){						
						$objResult->links[] = array("source"=>$s,"target"=>$t,"value"=>$taille);
						$objResult->links[] = array("source"=>0,"target"=>$s,"value"=>$taille);
						$doublons[$s."_".$t] = true;
					}
				}elseif(!isset($doublons["0_".$t])){
						$objResult->links[] = array("source"=>0,"target"=>$t,"value"=>$taille);						
						$doublons["0_".$t] = true;
					}
				$s = $t;
				if(isset($val->lPrecisLien)){
					if(!isset($doublons[$val->lPrecisLien->value])){
						$objResult->nodes[] = array("name"=>$val->lPrecisLien->value,"uri"=>$val->uPrecisLien->value,"recid"=>count($objResult->nodes),"type"=>"lien_precis");
						$doublons[$val->lPrecisLien->value] = count($objResult->nodes)-1;
					}
					$t = $doublons[$val->lPrecisLien->value];			
					if(!isset($doublons[$s."_".$t])){
						$objResult->links[] = array("source"=>$s,"target"=>$t,"value"=>$taille);
						$objResult->links[] = array("source"=>$t,"target"=>1,"value"=>$taille);
						$doublons[$s."_".$t] = true;
					}
				}elseif(!isset($doublons[$s."_1"])){
					$objResult->links[] = array("source"=>$s,"target"=>1,"value"=>$taille);
					$doublons[$s."_1"] = 1;						
				}					
			}
			if(isset($val->lPrecis)){
				if(!isset($doublons[$val->lPrecis->value])){
					$objResult->nodes[] = array("name"=>$val->lPrecis->value,"uri"=>$val->uPrecis->value,"recid"=>count($objResult->nodes),"type"=>"precis");
					$doublons[$val->lPrecis->value] = count($objResult->nodes)-1;
				}
				$t = $doublons[$val->lPrecis->value];
				if(!isset($doublons[$ss."_".$t])){										
					$objResult->links[] = array("source"=>$ss,"target"=>$t,"value"=>$taille);
					$objResult->links[] = array("source"=>$t,"target"=>1,"value"=>$taille);
					$doublons[$ss."_".$t] = true;
				}
			}elseif(!isset($doublons[$ss."_1"])){
				$objResult->links[] = array("source"=>$ss,"target"=>1,"value"=>$taille);
				$doublons[$ss."_1"] = true;					
			}	
			if(isset($val->lLarge)){
				if(!isset($doublons[$val->lLarge->value])){
					$objResult->nodes[] = array("name"=>$val->lLarge->value,"uri"=>$val->uLarge->value,"recid"=>count($objResult->nodes),"type"=>"large");
					$doublons[$val->lLarge->value] = count($objResult->nodes)-1;
				}
				$s = $doublons[$val->lLarge->value];
				if(!isset($doublons[$s."_".$ss])){										
					$objResult->links[] = array("source"=>$s,"target"=>$ss,"value"=>$taille);
					$objResult->links[] = array("source"=>0,"target"=>$s,"value"=>$taille);
					$doublons[$s."_".$ss]=true;
				}
			}elseif(!isset($doublons["0_".$ss])){
				$objResult->links[] = array("source"=>0,"target"=>$ss,"value"=>$taille*2);					
				$doublons["0_".$ss] = true;
			}		
		}
		return json_encode($objResult);
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
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
    		
    		//on récupère la racine des documents
    		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    		if(!$this->dbM)$this->dbM = new Model_DbTable_Flux_Monade($this->db);
    		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
    		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
    		
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
     * @param  string 	$idBnf
     * @param  boolean 	$json
     *
     * @return string
     */
    public function getBio($idBnf, $lies=true)
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
		
		if(!isset($obj1->results->bindings[0]))return false;
		
		//construction de la réponse
		$b = $obj1->results->bindings[0];
		$objResult = new stdClass();
		$objResult->nom = $b->nom->value;				
		$objResult->prenom = $b->prenom->value;				
		$objResult->nait = isset($b->nait->value) ? $b->nait->value : "";				
		$objResult->mort = isset($b->mort->value) ? $b->mort->value : "";						
		if(!$lies) return $objResult;
		$objResult->data=array("bnf"=>array("idArk"=>$b->idArk->value,"liens"=>array(),"isni"=>""));
		
		
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
     * Enregistre les références bibliographique d'une cote
     * ATTENTION les cotes ne sont pas toute dans databnf, il faut passer par le site de la BNF
     *
     * @param  	string 	$cote
     * @param  	int 		$page
     * @param	int		$nbResult
     * @param	string  $affinage
     *
     * @return array
     */
    function saveCote($cote, $page=1, $nbResult=100, $affinage=""){
    	
	    	$this->trace("DEBUT ".__METHOD__);	    	
	    	set_time_limit(0);
	    		    	
	    	//initialise les gestionnaires de base de données
	    	$this->initDbTables();
	    	$this->trace("Tables initialisées");    	 
    	
	    	//récupère l'action
	    if(!isset($this->idAct))$this->idAct = $this->dbA->ajouter(array("code"=>__METHOD__));
	    	
    		//récupère la page des côtes
    		$url = "http://catalogue.bnf.fr/changerPageCote.do?cote=".$cote."&pageRech=rco&listeAffinages=".$affinage."&nbResultParPage=".$nbResult."&afficheRegroup=false&affinageActif=false&pageEnCours=".$page."&triResultParPage=0";    	
    		$html = $this->getUrlBodyContent($url);    		
    		$this->trace("page récupérée ".$url);
    		
    		//enregistre la page
    		$idD = $this->dbD->ajouter(array("url"=>$url,"titre"=>"Recherche cote ".$cote." p.".$page." : ".$affinage, "parent"=>$this->idDocRoot,"data"=>$html));
    		//$this->trace("document correspondant au lien ajouté = ".$idD);    		
    		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
    				,"src_id"=>$idD,"src_obj"=>"doc"
    				,"dst_id"=>$this->idAct,"dst_obj"=>"acti"
    		));
    		
    		//recherche les item;
    		$dom = new Zend_Dom_Query($html);
    		//récupère les items 
    		$xPath = '//*[@class="notice-synthese"]/a';
    		$results = $dom->queryXpath($xPath);
    		$arr = array();
    		foreach ($results as $result) {
    			 $i = $this->saveItemBNF("http://catalogue.bnf.fr".$result->getAttribute('href'), $idRap);
    			 $arr[] = $i;
    		}
    		$page++;
    		if($i>0)$this->saveCote($cote, $page, $nbResult, $affinage);
    		
	    	return $arr;
    }
    
    /**
     * Enregistre les références du catalogue général de la BNF 
     *
     * @param  	string $url
     * @param  	int $idRap
     *
     * @return int
     */
    function saveItemBNF($url, $idPre=0, $objPre='rapport'){
    	 
	    	$this->trace("DEBUT ".__METHOD__." = ".$url);    
	    	 
	    	//vérifie si l'url est dans la base
	    	$existe = $this->dbD->findByUrl($url);
	    	if($existe && count($existe)>0){
	    		$this->trace("EXISTE ".$existe["doc_id"]);
	    		return $existe["doc_id"];
	    	}
	    	
	    	
	    	//récupère l'action
	    	if(!$this->idActItem)$this->idActItem = $this->dbA->ajouter(array("code"=>__METHOD__));
	    	//récupère le tag général
	    	if(!$this->idTagGen)$this->idTagGen = $this->dbT->ajouter(array("code"=>"Classe notice BNF"));
	    	 
	    	//récupère la page Web
	    	$html = $this->getUrlBodyContent($url);
	    
	    	//enregistre la page
	    	$idD = $this->dbD->ajouter(array("url"=>$url,"titre"=>"Recherche cote ".$cote." : ".$page, "parent"=>$this->idDocRoot));
	    	//$this->trace("document correspondant au lien ajouté = ".$idD);
	    	$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    			,"src_id"=>$idD,"src_obj"=>"doc"
	    			,"dst_id"=>$this->idActItem,"dst_obj"=>"acti"
	    			,"pre_id"=>$idPre,"pre_obj"=>$objPre
	    	));
	    
	    	//recherche les informations;
	    	$dom = new Zend_Dom_Query($html);
	    	//récupère les items
	    	$xPath = '//*[@class="notice"]';
	    	$results = $dom->queryXpath($xPath);
	    	foreach ($results as $result) {
	    		//enregistre le champ
	    		$champ = $result->getAttribute('id');
			$idTag = $this->dbT->ajouter(array("code"=>$champ,"parent"=>$this->idTagGen));
			//récupère la valeur
			$spans =  $result->getElementsByTagName("span");
			$i=0;
			foreach ($spans as $s) {
				//on ne prend pas en compte le label
				if($i>0){
					//on récupère les liens
					$liens =  $s->getElementsByTagName("a");
					$j=0;
					foreach ($liens as $l) {
						$idRapChamp = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
								,"src_id"=>$idD,"src_obj"=>"doc"
								,"dst_id"=>$idTag,"dst_obj"=>"tag"
								,"pre_id"=>$idRap,"pre_obj"=>"rapport"
								,"valeur"=>$l->nodeValue
						));						
						$h = $l->getAttribute('href');
						//on enregistre le lien vers le document
						$idDocChamp = $this->dbD->ajouter(array("url"=>$h,"titre"=>$l->nodeValue, "parent"=>$idD));
						$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
								,"src_id"=>$idD,"src_obj"=>"doc"
								,"dst_id"=>$idDocChamp,"dst_obj"=>"doc"
								,"pre_id"=>$idRapChamp,"pre_obj"=>"rapport"
						));														
						$j++;
					}
					//si pas de liens on enregistre  la valeur du span
					if($j==0){
						$idRapChamp = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
								,"src_id"=>$idD,"src_obj"=>"doc"
								,"dst_id"=>$idTag,"dst_obj"=>"tag"
								,"pre_id"=>$idRap,"pre_obj"=>"rapport"
								,"valeur"=>$s->nodeValue
						));						
					}
				}
				$i++;
			}
			
	    	}
	    	$this->trace("FIN ".__METHOD__." nb item = ".$i);	    	
    		return $i;
    }
    
    /**
     * Enregistre les propriété pour une ressource
     *
     * @param  	string $url
     *
     * @return array
     */
    function saveProp($url){
    
	    //	$this->trace("DEBUT ".__METHOD__." = ".$url);
	    	$this->initDbTables();
	    	
	    	//récupère l'action
	    	if(!isset($this->idActProp))$this->idActProp = $this->dbA->ajouter(array("code"=>__METHOD__));
	    	//récupère le tag général
	    	if(!isset($this->idTagProp))$this->idTagProp = $this->dbT->ajouter(array("code"=>"Propriétés databnf"));
	    	
	    	//récupère les données liées
	    $query =
	    'SELECT DISTINCT ?p ?o WHERE {
			<'.$url.'> ?p ?o.
			}';
	    $result = $this->query($query);
	    	$obj2 = json_decode($result);
	     
	    //enregistre le document
	    $idD = $this->dbD->ajouter(array("url"=>$url,"titre"=>"Propriété databnf", "parent"=>$this->idDocRoot));
	    $idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    		,"src_id"=>$idD,"src_obj"=>"doc"
	    		,"dst_id"=>$this->idActProp,"dst_obj"=>"acti"
	    ));
	     
	    
	    //construction de la réponse
	    $rs = array();
	    $i=0;
	    foreach ($obj2->results->bindings as $val) {
	    		//enregistre la propriété
	    		$c = parse_url($val->p->value, PHP_URL_FRAGMENT);
	    		if(!$c){
			    	$c = parse_url($val->p->value, PHP_URL_PATH);
			    	$arrC = explode("/",$c);
			    	$c = array_pop($arrC);
	    		}
	    		$rs[$c]=$val->o->value;
	    		$idTag = $this->dbT->ajouter(array("code"=>$c,"parent"=>$this->idTagProp,"uri"=>$val->p->value));	    	
		    $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
		    			,"src_id"=>$idD,"src_obj"=>"doc"
		    			,"dst_id"=>$idTag,"dst_obj"=>"tag"
		    			,"pre_id"=>$idRap,"pre_obj"=>"rapport"
		    			,"valeur"=>$val->o->value
		    ));
	    }
	    $rs["rapport_id"]=$idRap;
	    $rs["doc_id"]=$idD;
	    $this->trace("FIN ".__METHOD__." = ".$idD);
	     
	    return $rs;
	}
	
	/**enregistre les propriétés des documents du catalogue bnf
	 * @param  	string 	$titre

	 */
	function savePropDocCata($titre){
		
		$this->trace("DEBUT ".__METHOD__);
		$this->initDbTables();
		set_time_limit(0);
		
		//récupère l'action
		if(!isset($this->idAct))$this->idAct = $this->dbA->ajouter(array("code"=>__METHOD__));
	
		$docs = $this->dbD->findByTitre($titre);
		
		//foreach ($docs as $d) {
		$nbDoc = count($docs);
		for ($i = 0; $i < $nbDoc; $i++) {
			$d = $docs[$i];
			$url = str_replace("catalogue","data",$d["url"]);
			$this->trace($i." / ".$nbDoc."  = ".$url);
			$rs = $this->saveProp($url);
			//change le titre
			$this->dbD->edit($d["doc_id"],array("tronc"=>"item catalogue BNF","titre"=>$rs['title']));
			//création du rapport
			$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
					,"src_id"=>$d["doc_id"],"src_obj"=>"doc"
					,"dst_id"=>$rs["doc_id"],"dst_obj"=>"doc"
					,"pre_id"=>$rs["rapport_id"],"pre_obj"=>"rapport"
			));				
		}
		
		
	}
	
	/**enregistre les propriétés des acteurs du catalogue bnf
	 * 
	 * 	
	 */
	function savePropActeurCata(){
	
		$this->trace("DEBUT ".__METHOD__);
		set_time_limit(0);
		
		$this->initDbTables();
	
		//récupère l'action
		if(!isset($this->idAct))$this->idAct = $this->dbA->ajouter(array("code"=>__METHOD__));
		
		//recherche les FRBNF 
		$sql = "SELECT * FROM `flux_doc` WHERE `url` LIKE '/ark:/12148/%'";
		$docs = $this->dbD->exeQuery($sql);
		$nbDoc = count($docs);
		//foreach ($docs as $d) {
		for ($i = 422; $i < $nbDoc; $i++) {				
			$d = $docs[$i];
			$url = "http://data.bnf.fr".$d["url"];
			$rs = $this->saveProp($url);
			if($rs){
				if(isset($rs['FRBNF'])){
					$obj = $this->getBio($rs['FRBNF'], false);
					if($obj){
						$rs['nom']=$obj->nom;
						$rs['prenom']=$obj->prenom;
						$rs['nait']=$obj->nait;
						$rs['mort']=$obj->mort;
						if(!isset($rs["identifierValid"]))$rs["identifierValid"]=0;
						//ajoute l'existence
						$idExi = $this->dbE->ajouter(array("nom"=>$obj->nom,	"prenom"=>$obj->prenom
								,	"data"=>json_encode($rs),	"nait"=>$obj->nait,	"mort"=>$obj->mort,	"isni"=>$rs["identifierValid"]));
						//création du rapport
						$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
								,"src_id"=>$d["doc_id"],"src_obj"=>"doc"
								,"dst_id"=>$idExi,"dst_obj"=>"exi"
								,"pre_id"=>$rs["rapport_id"],"dst_obj"=>"rapport"
						));
					}
				}
				//création du rapport
				$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
						,"src_id"=>$d["doc_id"],"src_obj"=>"doc"
						,"dst_id"=>$rs["doc_id"],"dst_obj"=>"doc"
						,"pre_id"=>$rs["rapport_id"],"dst_obj"=>"rapport"
				));	
				$this->trace($i." / ".$nbDoc.' '.$d["url"]);
			}
		}
	
	
	}
	
	
	/**récupère les documents dans une période ou abscent d'une période
	 * @param int		$deb année de début
	 * @param int		$deb année de fin
	 * @param string 	$not si NOT = récupère les docs qui ne sont pas dans la periode
	 * 	 
	 * @return array
	 *
	 */
	function getDocPeriode($deb, $fin, $not=""){
		
		$idTag = 62; //"firstDate"
		//cette requête ne renvoie pas les document qui n'ont pas le mot clef
		$sql = "SELECT 
			    d.doc_id, d.url, d.titre, SUBSTRING(d.url, 31)
			FROM
			    flux_rapport rFiltre
			        INNER JOIN
			    flux_doc d ON d.doc_id = rFiltre.src_id
			WHERE
			    rFiltre.dst_obj = 'tag'
		        AND rFiltre.dst_obj = 'tag'
		        AND rFiltre.src_obj = 'doc'
		        AND rFiltre.pre_obj = 'rapport'
			    	AND rFiltre.dst_id = $idTag
			    AND CONVERT( rFiltre.valeur , UNSIGNED) $not BETWEEN $deb AND $fin
			";
		//cette requête revnoie tous les documents qui ne sont pas dans la liste de la période
		//mais uniquement pour data.bnf.fr
		$sql = "	SELECT
			d.doc_id ,d.url, d.titre, SUBSTRING(d.url,31)
			FROM flux_doc d
			WHERE d.url LIKE 'http://data.bnf.fr/ark:/12148/%'
			AND d.doc_id not in (
					SELECT
					d.doc_id
					FROM flux_doc d
					inner join  flux_rapport rFiltre on d.doc_id = rFiltre.src_id
					AND rFiltre.dst_obj = 'tag' and rFiltre.dst_id = 62 AND CONVERT(rFiltre.valeur,UNSIGNED) BETWEEN 1800 and 1899
					);";
			echo $sql;
				
			$db = new Model_DbTable_Flux_Doc($this->db);
			$rs = $db->getAdapter()->query($sql);
			return $rs->fetchAll();
	}

	
	/**supprime les documents qui sont ou pas dans une période
	 * @param int		$deb année de début
	 * @param int		$deb année de fin
	 * @param string 	$not si NOT = récupère les docs qui ne sont pas dans la periode
	 *
	 */
	function supDocPeriode($deb, $fin, $not=""){
		
		set_time_limit(0);
		$arr = $this->getDocPeriode($deb, $fin, $not);
		$db = new Model_DbTable_Flux_Doc($this->db);		
		$nbDoc = count($arr);
		for ($i = 0; $i < $nbDoc; $i++) {
			$d = $arr[$i];			
			//supprime le document et ses dépendances
			$nbSup = $db->remove($d["doc_id"]);
			$this->trace(__METHOD__." ".$i." / ".$nbDoc." = ".$nbSup." : ".$d["doc_id"].' '.$d["url"]);
			//supprime le document catalogue
			//oublie = 3
			$dCat = $this->dbD->findByUrl(str_replace("data","catalogue",$d["url"]));
			if($dCat){	
				$nbSup = $db->remove($dCat["doc_id"]);
				$this->trace(__METHOD__." ".$i." / ".$nbDoc." = ".$nbSup." : ".$dCat["doc_id"].' '.$dCat["url"]);
			}
		}
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
    
/* nombre de notice ayant un mot
 * 
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX bnf-onto: <http://data.bnf.fr/ontology/bnf-onto/>
select ?decennie (count(?manif) as ?nbManif) where {
  ?manif a <http://rdvocab.info/uri/schema/FRBRentitiesRDA/Manifestation>.
  ?manif dcterms:title ?titre.
  ?manif bnf-onto:firstYear ?date.
  BIND (concat(substr(str(?date),1,3), "0") as ?decennie)
  FILTER (regex(?titre, "science")).
  FILTER (?date > 1599)
}
ORDER BY ?decennie
 */    
    
/* tous les ouvrages lié à un sujet rameau
 * 
PREFIX bnf-onto: <http://data.bnf.fr/ontology/bnf-onto/>
SELECT ?doc ?title ?idArk ?date ?editeur ?cote
WHERE {
	?doc dcterms:subject <http://data.bnf.fr/ark:/12148/cb121118875>.   
  
	OPTIONAL{?doc dcterms:date ?date}
	OPTIONAL{?doc dcterms:title ?title}
	OPTIONAL{?doc dcterms:publisher ?editeur}
  	OPTIONAL{?doc bnf-onto:FRBNF ?idArk}
  	OPTIONAL{?doc bnf-onto:cote ?cote}
}
 */    
/* les cote ayant "col" dans leur libéllé
 SELECT DISTINCT ?cote ?a WHERE
{
  ?cote bnf-onto:cote ?a.
  FILTER (regex(?a, "col", "i"))
}
 */
/*requête pour renvoyer les différentes version d'une oeuvre
 * SELECT DISTINCT ?edition ?title ?date ?editeur WHERE {
<http://data.bnf.fr/ark:/12148/cb11947965f> foaf:focus ?Oeuvre .
?edition rdarelationships:workManifested ?Oeuvre.
OPTIONAL{?edition dcterms:date ?date}
OPTIONAL{?edition dcterms:title ?title}
OPTIONAL{?edition dcterms:publisher ?editeur}
}
 */ 
    
    
}
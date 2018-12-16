<?php
/**
 * Flux_Ontostat
 * 
 * Classe qui gère les flux du projet Ontostat
 * merci à 
 * @file csv2skosxl.php
 * http://skos.um.es/unescothes/
 * https://www.w3.org/TR/2005/WD-swbp-skos-core-spec-20051102
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Flux_Ontostat extends Flux_Site{

    var $mc;
    private static $scheme = 'http://ontostats.univ-paris8.fr/terms';
    private static $nomThe = 'OntoStats';

	public function __construct($idBase=false, $bTrace=false)
    {
    	parent::__construct($idBase,$bTrace);

    	
    	//on récupère la racine des documents
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	if(!$this->dbM)$this->dbM = new Model_DbTable_Flux_Monade($this->db);
    	$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
    	$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
    	        	
	    $this->mc = new Flux_MC($idBase, $bTrace);
       	
    }   
    
    
    /**
     * Initialise les variables pour l'importation Omk
     *
     *
     */
     function initVarOmk(){
	    	//initialistion des variables
     	$this->dbR = new Model_DbTable_Omk_Resource($this->db);
     	$this->dbIS = new Model_DbTable_Omk_ItemSet($this->db);
     	$this->dbI = new Model_DbTable_Omk_Item($this->db);
     	$this->dbV = new Model_DbTable_Omk_Value($this->db);
     	$this->dbVoc = new Model_DbTable_Omk_Vocabulary($this->db);
     	$this->dbP = new Model_DbTable_Omk_Property($this->db);
     	$this->dbRC = new Model_DbTable_Omk_ResourceClass($this->db);
     	$this->dbIIS = new Model_DbTable_Omk_ItemItemSet($this->db);
     	$this->owner = "ontostats@univ-paris8.fr";
     	$this->idOwner = 2;
     	$this->ResClassId = 2;
     	$this->arrClass = array();
    }
    
    /**
     * Enrichir l'ontologie avec les données de la base
     *
     * @param  object 	$file
     *
     * @return DOMDocument
     *
     */
    function creerOntoToOmeka($file){
    	
		$this->trace(__METHOD__." ".$file);
    	
	    	//chargement de l'ontologie
	    	$this->doc = new DOMDocument();
	    	$this->doc->load($file);
	    	$this->xpath = new DOMXpath($this->doc);
	    	$this->xpath->registerNamespace("ostat","http://ontostats.univ-paris8.fr/terms");
	    	$this->xpath->registerNamespace("dbpedia-owl","http://dbpedia.org/ontology/");
	    	$this->xpath->registerNamespace("rdf","http://www.w3.org/1999/02/22-rdf-syntax-ns#");
	    	$this->xpath->registerNamespace("owl","http://www.w3.org/2002/07/owl#");
	    	$this->xpath->registerNamespace("xml","http://www.w3.org/XML/1998/namespace");
	    	$this->xpath->registerNamespace("xsd","http://www.w3.org/2001/XMLSchema#");
	    	$this->xpath->registerNamespace("skos","http://www.w3.org/2004/02/skos/core#");
	    	$this->xpath->registerNamespace("rdfs","http://www.w3.org/2000/01/rdf-schema#");
	    	
		$this->initVarOmk();
	    	
	    	//Item-set, owner, Class, dcterms:abstract, dcterms:identifier, dcterms:isPartOf, dcterms:relation, dcterms:language
	    	
	    $this->idVocab = $this->dbVoc->ajouter(array("owner_id"=>$this->idOwner,"namespace_uri"=>"http://ontostats.univ-paris8.fr/terms","prefix"=>"ontostat","label"=>"OntoStats","comment"=>"Ontologie pour le référencement des ressources pédagogiques en statistiques"));
	    	
	    	//parcourt les class
	    	$class = $this->doc->getElementsByTagName('Class');
	    foreach ($class as $c) {
	    		//if($i > 230 && $i < 240)
	    		//if($i < 100)
	    			$this->creerResourceOmk($c);
	    		$this->trace($i);
			$i++;
	    }

	    	$this->trace(__METHOD__." FIN ");
	    	
    }

    /**
     * Créer une ressource dans la base omeka s 
     * à partir d'un noeud de l'ontologie
     * 
     * @param  DOMElement $c
     *
     * @return integer
     *
     */
    function creerResourceOmk($c){
    	    	
    		
    		//récupère la clef de la classe
	    	$about = $c->getAttribute('rdf:about');
	    	$k = substr($about, strlen(self::$scheme)+1);
	    	$this->trace(__METHOD__." ".$k);
	    	
	    	/*pour tester les erreurs
	    	$arrTest = array('SeuilDacceptation','SeuilDeConfiance','SeuilDeSignification');
	    	if(in_array($k,$arrTest)){
	    		$this->trace('ABANDON : '.$k);
	    		return;
	    	}
	    	*/
	    	
	    	//vérifie que la class n'est pas déjà créée
	    	$rC = $this->dbV->findByUri($about);
	    	if($rC){
	    		$this->trace('EXISTE : '.$k);
	    		return $rC['resource_id'];
	    	}
	    	 

	    	//créer la ressource
	    	$idR = $this->dbR->ajouter(array("resource_type"=>"Omeka\Entity\ItemSet","owner_id"=>$this->idOwner,"is_public"=>1),false);
	    	//ajouter l'itemSet
	    	$this->dbIS->ajouter(array("id"=>$idR,"is_open"=>1));

	    	//ajouter les valeurs
	    	//en limitant au français
		$arrLang = array('fr','en');
		
	    	//reference
	    	$this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>10,"type"=>"uri","value"=>$k,"uri"=>$about));
	    	
	    	//description
	    	$comment = "";
	    	$vals = $c->getElementsByTagName('comment');
	    	foreach ($vals as $v){
	    		$lang = $v->getAttribute("xml:lang");
	    		$comment = $v->nodeValue."";
	    		if(in_array($lang,$arrLang))$this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>4,"type"=>"literal","lang"=>$lang,"value"=>$v->nodeValue));
	    	}
	    	//titre
	    	$vals = $c->getElementsByTagName('label');
	    	foreach ($vals as $v){
	    		$lang = $v->getAttribute("xml:lang");
	    		if(in_array($lang,$arrLang))$this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>1,"type"=>"literal","lang"=>$lang,"value"=>$v->nodeValue));
	    	}
	    	//short title
	    	$vals = $c->getElementsByTagName('prefLabel');
	    	foreach ($vals as $v){
	    		$lang = $v->getAttribute("xml:lang");
	    		if(in_array($lang,$arrLang))$this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>117,"type"=>"literal","lang"=>$lang,"value"=>$v->nodeValue));
	    	}
	    	//alt terms
	    	$vals = $c->getElementsByTagName('altLabel');
	    	foreach ($vals as $v){
	    		$lang = $v->getAttribute("xml:lang");
	    		if(in_array($lang,$arrLang))$this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>17,"type"=>"literal","lang"=>$lang,"value"=>$v->nodeValue));
	    	}
	    	//abstract
	    	$vals = $c->getElementsByTagName('abstract');
	    	foreach ($vals as $v){
	    		$lang = $v->getAttribute("xml:lang");
	    		if(in_array($lang,$arrLang))$this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>86,"type"=>"literal","lang"=>$lang,"value"=>$v->nodeValue));
	    	}
	    	//isReferencedBy
	    	$vals = $c->getElementsByTagName('seeAlso');
	    	foreach ($vals as $v){
	    		$uri = $v->getAttribute("rdf:resource");
	    		$this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>35,"type"=>"uri","uri"=>$uri,"value"=>$uri));
	    	}
	    	
	    	//gestion des subClassOf
	    	$vals = $c->getElementsByTagName('subClassOf');
	    	foreach ($vals as $v){
	    		$lbl = $v->nodeName;
	    	
	    		$res = $v->getAttribute("rdf:resource");
	    		
	    		//création du vocabulaire
	    		if($this->idVocab){
		    		if($res=="http://ontostats.univ-paris8.fr/terms/Propriete"){
		    			//créer une property
		    			$idProp = $this->dbP->ajouter(array("owner_id"=>$this->idOwner,"vocabulary_id"=>$this->idVocab,"local_name"=>$k,"label"=>$k,"comment"=>$comment));
		    		}
		    		//créer une class
		    		$idClass = $this->dbRC->ajouter(array("owner_id"=>$this->idOwner,"vocabulary_id"=>$this->idVocab,"local_name"=>$k,"label"=>$k,"comment"=>$comment));
		    		//mise à jour de la class
		    		$this->dbR->edit($idR,array("resource_class_id"=>$idClass));
	    		}
	    		
	    		//récupère la classe parente
	    		$q = '//owl:Class[@rdf:about="'.$res.'"]';
	    		$elements = $this->xpath->query($q);
	    		if (!is_null($elements)) {
	    			foreach ($elements as $e) {
	    				$idParent = $this->creerResourceOmk($e);
	    				//ajouter le lien entre la class et son parent
	    				$this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>33,"type"=>"resource","value_resource_id"=>$idParent));	    				 
	    			}
	    		}
	    	}
	    	 
    	 
	    	$this->arrClass[$k]=$idR;
	    	$this->trace(__METHOD__." FIN ");
	    	
	    	return $idR;
    	
    }
    
    
    /**
     * Lier dans Omeka les items avec les itemSet 
     * la comparaison se fait par égalité de chaine
     *
     * 
     *
     */
    function lierItemToItemSet(){
    		$this->trace(__METHOD__);
    	 
		/*
		SELECT 
			count(*) nb, 
			vS.value, group_concat(vS.resource_id) ids, 
			vC.value, vC.resource_id 
			FROM value vS 
			inner join value vC on vC.value LIKE CONCAT('%',vS.value,'%') AND vC.property_id IN (1,4,17,19,117)
			inner join resource r on r.id = vC.resource_id AND r.resource_type = 'Omeka\\Entity\\ItemSet'
			WHERE vS.property_id = 3 
			 group by vS.value
			 ORDER BY vS.value
	    	*/
    		$this->initVarOmk();
    	 
	    	$query = $this->dbV->select()
		    	->from( array("vS" => "value"), array("vS"=>"value","ids"=>"group_concat(DISTINCT vS.resource_id)","nbItem"=>"COUNT(*)"))
		    	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
		    	->joinInner(array("vC" => "value")
		    			,"vC.value LIKE CONCAT('%',vS.value,'%') AND vC.property_id IN (1,4,17,19,117)"
		    			,array("vC"=>"value", "resource_id"))
    			->joinInner(array("r" => "resource")
    					,"r.id = vC.resource_id AND r.resource_type LIKE '%ItemSet'"
    					,array())
    			->where("vS.property_id = 3")
    			->group(array("vS.value"))
		    	->order(array("vS.value"));
		$rs = $this->dbV->fetchAll($query)->toArray();
		
		$j = 0;
		foreach ($rs as $v) {
			if($j==100){
				$this->trace($i." ".$id." => ".$v["resource_id"]);
			}
			$this->trace($j." : ".$v["vS"]." => ".$v["vC"]);
			$arrId = explode(",",$v["ids"]);
			$i=1;
			foreach ($arrId as $id) {
				$this->dbIIS->ajouter(array("item_id"=>$id,"item_set_id"=>$v["resource_id"]));
				//création des rapports pour les itemSet liés
				$this->dbIIS->ajouterTroncParBranche($id, $v["resource_id"]);
				$this->trace($i." ".$id." => ".$v["resource_id"]);
				$i++;
			}
			$j++;
		}
		$this->trace(__METHOD__." FIN ");
		
    }
    	
    /**
     * Créer le vocabulaire pour Omaka avec l'ontologie
     * TODO : vérifier le format car bug d'importation
     * @param  object 	$file
     *
     * @return DOMDocument
     *
     */
    function creerVocabulairePourOmk($file){
    	
	    	//chargement de l'ontologie
	    	$onto = new DOMDocument();
	    	$onto->load($file);
	    	
	    	//chargement du modèle de base
	    	$doc = new DOMDocument('1.0', 'UTF-8');
	    	$doc->formatOutput = true;
	    	$rdf = 	$doc->createElement("rdf:RDF");
	    	$rdf = $doc->appendChild($rdf);
	    	$rdf->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:owl', 'http://www.w3.org/2002/07/owl#');
	    	$rdf->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
	    	$rdf->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
	    	
	    	//définie les tag à importer
	    	$arrTag = array('rdfs:label','rdfs:seeAlso','rdfs:comment','skos:altLabel','skos:prefLabel','rdfs:subClassOf');
	    	
	    	//parcourt les class
	    	$class = $onto->getElementsByTagName('Class');
	    	foreach ($class as $c) {

	    		/*
	    		<rdf:Description rdf:about="http://purl.org/dc/terms/NLM">
		    		<rdfs:label xml:lang="en">NLM</rdfs:label>
		    		<rdfs:comment xml:lang="en">The set of conceptual resources
		    		specified by the National Library of Medicine Classification.</rdfs:comment>
		    		<rdfs:isDefinedBy rdf:resource="http://purl.org/dc/terms/" />
		    		<dcterms:issued rdf:datatype="http://www.w3.org/2001/XMLSchema#date">2005-06-13</dcterms:issued>
		    		<dcterms:modified rdf:datatype="http://www.w3.org/2001/XMLSchema#date">2008-01-14</dcterms:modified>
		    		<rdf:type rdf:resource="http://purl.org/dc/dcam/VocabularyEncodingScheme" />
		    		<dcterms:hasVersion
		    		rdf:resource="http://dublincore.org/usage/terms/history/#NLM-002" />
		    		<rdfs:seeAlso rdf:resource="http://wwwcf.nlm.nih.gov/class/" />
	    		</rdf:Description>
	    		 */
	    		$about = $c->getAttribute('rdf:about');
	    		$k = substr($about, strlen(self::$scheme)+1);
	    		$this->trace($k);	    		 
	    		$d = $doc->createElement("rdf:Description");
	    		$a = $doc->createAttribute('rdf:about');
	    		$a->value = $about;
	    		$d->appendChild($a);
	    		 
	    		foreach ($c->childNodes as $el){
	    			if($el->nodeName!="#text"){
		    			$lbl = $el->nodeName;
		    			$this->trace($lbl);
		    			if(in_array($el->nodeName, $arrTag)){
		    				$n = $doc->importNode($el, true);
		    				// Et on l'ajoute dans le le noeud racine "<root>"
		    				$d->appendChild($n);
		    			}
		    			if($el->nodeName=="rdfs:subClassOf"){
		    				$t = $doc->createElement("rdf:type");
		    				$a = $doc->createAttribute('rdf:resource');
		    				$res = $el->getAttribute("rdf:resource");
		    				switch ($res) {
		    					case "http://ontostats.univ-paris8.fr/terms/Propriete":
		    						$a->value = "http://www.w3.org/1999/02/22-rdf-syntax-ns#Property";	    							
		    						break;
		    					default:
		    						$a->value = "http://www.w3.org/2000/01/rdf-schema#Class";
		    						break;
		    				}
		    				$t->appendChild($a);
		    				$d->appendChild($t);	    				 
		    			}
	    			}
	    		}
	    		$rdf->appendChild($d);
	    		
	    	}
	    	
	    	return $doc;
	    	
    	
    }
    
    
    /**
     * Enrichir l'ontologie avec les données de la base
     *
     * @param  object 	$file
     * 
     * @return DOMDocument
     *
     */
    function enrichirOnto($file){
    
	    	//initialisation des objets
	    	$this->initDbTables();
	    	
	    	//chargement de l'ontologie
	    $doc = new DOMDocument();
	    $doc->load($file);
		
	    //parcourt les class
	    $class = $doc->getElementsByTagName('Class');
	    foreach ($class as $c) {
		    //récupère la clef de la classe
		    $k = $c->getAttribute('rdf:about');
		    $k = substr($k, strlen(self::$scheme)+1);
		    $this->trace($k);
		    //recherche les traductions de la class
	    		$rsT = $this->getClassTrad(0,$k);
	    		$seeAlso = array();
	    		//ajoute les traductions
	    		foreach ($rsT as $t) {
	    			//gestion des traductions de label
	    			if($t['url']==null){
	    				$lbls = str_replace(',',';',$t['label']);
	    				$arrT = explode(";",$lbls);
	    				for ($i = 0; $i < count($arrT); $i++) {
	    					$tag = $i==0 ? 'skos:prefLabel' : 'skos:altLabel';    					
	    					//$e = $doc->createElement($tag,$arrT[$i]);	
	    					$e = $doc->createElement($tag,$arrT[$i]);	    						
	    					$a = $doc->createAttribute('xml:lang');
	    					$a->value = $t['ns'];
	    					$e->appendChild($a);
	    					$c->appendChild($e);    						
	    					$this->trace("nouvelle traduction = ".$t['ns']. " - ".$arrT[$i]);    						
	    				}
	    			}else{
	    				//ajoute l'élément
	    				if($t['uri']=="http://dbpedia.org/ontology/abstract")
	    					$e = $doc->createElement("dbpedia-owl:abstract",$t['label']);
    					if($t['uri']=="http://www.w3.org/2000/01/rdf-schema#label")
    						$e = $doc->createElement("rdfs:label",$t['label']);
	    				$a = $doc->createAttribute('xml:lang');
    					$a->value = $t['ns'];
    					$e->appendChild($a);
					/*ajoute le lien vers la ressource
    					$ar = $doc->createAttribute('rdf:resource');
    					$ar->value = $t['url'];
    					$e->appendChild($ar);
    					*/	
    					//$sa->appendChild($e);
    					$c->appendChild($e);
    					$this->trace("nouveau élément : ".$t['label']);
    				}
	    		}
	    		//recherche les documents liés à la class
	    		$rsL = $this->getClassDocLie(0,$k);
	    		//ajoute les seeAlso
	    		$first = true;
	    		foreach ($rsL as $l) {
	    			if($first){
	    				$sa = $doc->createElement("rdfs:seeAlso");
	    				$a = $doc->createAttribute('rdf:resource');
	    				$a->value = $l['urlD'];
	    				$sa->appendChild($a);
	    				$c->appendChild($sa);
	    				$this->trace("rdfs:seeAlso : ".$l['urlD']);
	    				$first = false;
	    			}
    				$sa = $doc->createElement("rdfs:seeAlso");
    				$a = $doc->createAttribute('rdf:resource');
    				$a->value = $l['urlDl'];
    				$sa->appendChild($a);
    				$this->trace("rdfs:seeAlso : ".$l['urlDl']);
    				$c->appendChild($sa);    				
	    		}	    		 
	    		 
	    }	    
	    
	    return $doc;
	    
    }
    
    /**
     * Enregistre les données d'une glossaire à partir d'un fichier csv de sélection
     *
     * @param  object 	$file
     *
     */
    function saveGlossaireSelection($file){
    
	    //initialisation des objets
    		$this->initDbTables();
	    	$isi = new Flux_Isi($this->idBase);
	    	$dbP = new Flux_Dbpedia($this->idBase);
	    	
	    	//récupère l'action
	    	$idAct = $this->dbA->ajouter(array("code"=>__METHOD__));
	    
	    	//enregistre le document
	    	$idDsrc = $this->dbD->ajouter(array("url"=>$file
	    			,"titre"=>"Sélection de catégories à importer"
	    			,"parent"=>$this->idDocRoot,"tronc"=>0
	    	));
	    	$this->trace("document correspondant à l'enregistrement = ".$idDsrc);
	    
	    	//enregistre le rapport
	    	$idRapDoc = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    			,"src_id"=>$idDsrc,"src_obj"=>"doc"
	    			,"dst_id"=>$idAct,"dst_obj"=>"acti"
	    	));
	    	$this->trace("enregistre le rapport entre le document et l'action");
	    	 
	    	//récupère le tag ConceptGroup
	    	$idCptGroup = $this->dbT->ajouter(array("code"=>"ConceptGroup"));
	    	
	    	//récupère les données
	    	$arr = $this->csvToArray($file);
	    	$nb = 1109;//count($arr);
	    	$deb = 1108;//page dbpedia impossibles:840,1108
	    	$t = $arr[0];
	    	//enregistre les données
	    	for ($i = $deb; $i < $nb; $i++) {
	    		//récupère la ligne d'en tête
	    		if($i!=0 && $arr[$i][3]!="Non" && $arr[$i][3]!="non"){
	    			//traitement après la ligne d'entête
	    			$d = $arr[$i];
	    			//on enregistre le document
	    			$idD = $this->dbD->ajouter(array("url"=>$d[0]
	    					,"titre"=>$d[1]
	    					,"parent"=>$idDsrc
	    			));
	    			$this->trace($i."/".$nb." = $idD url ISI : ".$d[0]);
	    			//enregistre le parent de la classe = Concept, Propriete, Procedure
	    			$idTP = $this->dbT->ajouter(array("code"=>$arr[$i][3],"desc"=>$arr[$i][3],"parent"=>$idCptGroup));
	    
	    			//on enregistre la class
	    			$idTclass = $this->dbT->ajouter(array("code"=>$d[2],"desc"=>$d[2], "parent"=>$idTP));
	    
	    			//création du rapport
	    			$idRapClass = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    					,"src_id"=>$idD,"src_obj"=>"doc"
	    					,"dst_id"=>$idTclass,"dst_obj"=>"tag"
	    					,"pre_id"=>$idRapDoc,"pre_obj"=>"rapport"
	    					,"valeur"=>$t[2]
	    			));
	    			//création des traductions
	    			if($d[0])$isi->setItemTrad($d[0], $d[1], $idTclass, $idRapClass);
	    			//vérifie les informations complémentaires
	    			for ($j = 4; $j < 9; $j++) {
	    				if($d[$j]){
		    					//gestion des liens externe
		    					if($j < 6){
			    					$idExt = $this->dbD->ajouter(array("url"=>$d[$j]
			    							,"titre"=>$t[$j]." - ".$d[1]
			    							,"parent"=>$idDsrc
			    					));
			    					//création du rapport
			    					$idRLE = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			    							,"src_id"=>$idDsrc,"src_obj"=>"doc"
			    							,"dst_id"=>$idExt,"dst_obj"=>"doc"
			    							,"pre_id"=>$idRapClass,"pre_obj"=>"rapport"
			    							,"valeur"=>$t[$j]
			    					));
			    					//si colonne dbpedia récupérer la définition = abstract
			    					if($j==5 && $d[$j]!=""){
			    						$this->trace($d[$j]);			    						 
			    						//enregistre les abstracts
			    						$dbP->savePropObjet($d[$j],array("http://dbpedia.org/ontology/abstract","http://www.w3.org/2000/01/rdf-schema#label"),$idExt, $idTclass);
			    					}
		    					}
	    				}
	    			}
	    		}
	    	}
    }
    
    
    /** exporte le vocabulaire en skos XML
     *
     * 
     * 	
     *
     */
    function exportToSkos(){
		
	    	//récupère les data
    		$rs = $this->getClassData();
    	 
    		$scheme = self::$scheme;
    		$nomThe = self::$nomThe;
    		$uriThe = $scheme."/mnd".$rs[0]['monade_id']."d".$rs[0]['docid'];
    		$d = new DateTime();
    		$dateJ = $d->format('Y-m-d');
    		
    		//récupère l'identifiant unique
    		$guid = $this->guid();
    		    		
    		// Start building up a RDF graph
    		$ret = array(
    				<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<rdf:RDF
        xmlns:ostat="$scheme"
        xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
        xmlns:owl="http://www.w3.org/2002/07/owl#"
        xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
        xmlns:dct="http://purl.org/dc/terms/"
    		xmlns:iso-thes="http://purl.org/iso25964/skos-thes#"
    		xmlns:dcterms="http://purl.org/dc/terms/"		
        xmlns:skos="http://www.w3.org/2004/02/skos/core#">
    	<skos:ConceptScheme rdf:about="$uriThe">	
		<dcterms:title xml:lang="en">$nomThe Thesaurus</dcterms:title>
    		<dcterms:title xml:lang="es">Tesauro $nomThe</dcterms:title>
    		<dcterms:title xml:lang="fr">Thésaurus $nomThe</dcterms:title>
    		<skos:prefLabel xml:lang="en">$nomThe Thesaurus</skos:prefLabel>
    		<skos:prefLabel xml:lang="es">Tesauro $nomThe</skos:prefLabel>
    		<skos:prefLabel xml:lang="fr">Thésaurus $nomThe</skos:prefLabel>
    		<dcterms:creator>Université Paris 8</dcterms:creator>
    		<dcterms:publisher>Jean-Marc Meunier</dcterms:publisher>
    		<dcterms:publisher>Samuel Szoniecky</dcterms:publisher>
    		<dcterms:description xml:lang="en">The $nomThe Thesaurus is a controlled and structured list of terms used in satistics.</dcterms:description>
    		<dcterms:description xml:lang="fr">Le Thésaurus $nomThe est une liste de termes contrôlés et structurés pour la recherche et l'analyse de documents et publications dans le domaine des stratistiques.</dcterms:description>
    		<dcterms:created rdf:datatype="http://www.w3.org/2001/XMLSchema#date">1977-01-01</dcterms:created>
    		<dcterms:type>thesaurus</dcterms:type>
    		<dcterms:identifier>$uriThe</dcterms:identifier>
    		<dcterms:language rdf:resource="http://id.loc.gov/vocabulary/iso639-2/eng"/>
    		<dcterms:language rdf:resource="http://id.loc.gov/vocabulary/iso639-2/fre"/>
    		<dcterms:language rdf:resource="http://id.loc.gov/vocabulary/iso639-2/spa"/>
    		<dcterms:language rdf:resource="http://id.loc.gov/vocabulary/iso639-2/rus"/>
    		<dcterms:conformsTo>ISO 25964</dcterms:conformsTo>
    		<dcterms:license rdf:resource="http://creativecommons.org/licenses/by-sa/3.0/igo/"/>
    		<dcterms:rights>CC-BY-SA</dcterms:rights>
    		<dcterms:rightsHolder>Université Paris 8</dcterms:rightsHolder>
    		<dcterms:issued rdf:datatype="http://www.w3.org/2001/XMLSchema#date">$dateJ</dcterms:issued>    				
EOT
    		);
    		$output = "";

    		//création du RDF
    		$arrMember = array();
    		foreach ($rs as $r) {
    			$uri = $scheme."/".$r['code'];
    			//création des top Concepts
    			$output .= "<skos:hasTopConcept rdf:resource='".$uri."'/>" . PHP_EOL;
    			
    			//création des membres d'un concepts de regroupement    			    
    			if(!isset($arrMember[$r['idType']])) $arrMember[$r['idType']] = array();
    			$arrMember[$r['idType']][]="<skos:member rdf:resource='".$uri."'/>". PHP_EOL;    			
    		}
    		$output .= "</skos:ConceptScheme>". PHP_EOL;
    		
    		//création des concepts de regroupement
    		$rsCptGroup = $this->getConceptGroup();
    		foreach ($rsCptGroup as $cg) {
    			$output .= "<iso-thes:ConceptGroup rdf:about='".$scheme."/cg".$cg['tag_id']."'>
    				<rdfs:label xml:lang='fr'>".$this->xml_entities($cg['code'])."</rdfs:label>
    				<skos:prefLabel xml:lang='fr'>".$this->xml_entities($cg['code'])."</skos:prefLabel>
    				<skos:notation>".$cg['tag_id']."</skos:notation>
    						". PHP_EOL;
    			foreach ($arrMember[$cg['tag_id']] as $m) {
    				$output .= $m;
    			}
    			$output .= "</iso-thes:ConceptGroup>". PHP_EOL;
    		}
    		$ret[] = $output;
    		
    		//création des concepts
    		foreach ($rs as $r) {
    			$uri = $scheme."/".$r['code'];
    			 
    			//création des concepts
    			$output  = '<skos:Concept rdf:about="'.$uri.'">';
    			
    			//récupère la traduction des labels
    			$rsT = $this->getClassTrad($r["tag_id"]);
    			foreach ($rsT as $rT) {
    				//récupère les différents labels
    				if(strstr($rT["label"], ';'))
    					$arrLbl = explode(";",$rT["label"]);
    				else
    					$arrLbl = explode(",",$rT["label"]);
				//ajoute les label
				for ($i = 0; $i < count($arrLbl); $i++) {
    					if($i==0){
    						// skos:prefLabel
    						$output  .= '<skos:prefLabel xml:lang="'.$rT["ns"].'">'.$this->xml_entities($arrLbl[$i]).'</skos:prefLabel>'. PHP_EOL;
    					}else{
    						// skos:altLabel
    						$output  .= '<skos:altLabel xml:lang="'.$rT["ns"].'">'.$this->xml_entities($arrLbl[$i]).'</skos:altLabel>'. PHP_EOL;
    					}
    				}    				
    			}
    			$output  .= '<skos:notation>'.$r['tag_id'].'</skos:notation>'. PHP_EOL;
    			$output  .= '<skos:definition xml:lang="fr">'.$this->xml_entities($r['desc']).'</skos:definition>'. PHP_EOL;
    			$output  .= '<skos:inScheme rdf:resource="'.$uriThe.'" />'. PHP_EOL;
    			$output  .= '</skos:Concept>'. PHP_EOL;
    			 
    			$ret[] = $output;
    		}
		$ret[] = '</rdf:RDF>';
    		return implode($ret, PHP_EOL);
    
    }
    
    /** exporte le vocabulaire en skos XML
     *
     *
     *
     *
     */
    function exportToSimpleRDF(){
        
    	$scheme = self::$scheme;
    
    	// Start building up a RDF graph
    	$ret = array(
    			<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<rdf:RDF
        xmlns:ostat="$scheme"
        xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    		xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
    		xmlns:owl="http://www.w3.org/2002/07/owl#"
    			>
EOT
    	);
    	$output = "";
    
    	//création des concepts de regroupement
    	$rsCptGroup = $this->getConceptGroup();
    	foreach ($rsCptGroup as $cg) {
    		$output .= '<rdfs:Class rdf:about="'.$scheme."/".$cg['code'].'">
    			<rdfs:label xml:lang="fr">'.$this->xml_entities($cg['code']).'</rdfs:label>
    			<rdfs:comment>Pas de commentaire</rdfs:comment>
		    </rdfs:Class>'. PHP_EOL;
    		$ret[] = $output;
    	}
    	 
    	
    	//création du RDF
    	//récupère les data
    	$rs = $this->getClassData();
    	foreach ($rs as $r) {
    		$uri = $scheme."/".$r['code'];
    		//création des top Concepts
    		$output = '<rdfs:Class rdf:about="'.$uri.'">
            <rdfs:label xml:lang="fr">'.$r['code'].'</rdfs:label>
            <rdfs:comment xml:lang="fr">'.$this->xml_entities($r['desc']).'</rdfs:comment>
            	<rdfs:subClassOf rdf:resource="'.$scheme."/".$r['type'].'"/>';
    		if(isset($r['uri'])){
    			$output .= '<rdfs:isDefinedBy rdf:datatype="http://www.w3.org/2001/XMLSchema#anyURI">'.$r['uri'].'</rdfs:isDefinedBy>' . PHP_EOL;    			 
    		}
    		$output .= '</rdfs:Class>' . PHP_EOL;
    		$ret[] = $output;    		
    	}

    	$ret[] = '</rdf:RDF>';
    	return implode($ret, PHP_EOL);
    
    }
    	
    	    
    /**
     * Fonction pour récupérer toutes les données de la monade
     *
     *
     * @return	array
     *
     */
    function getAllData(){
	    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
	    	/*
	    	 SELECT 
		    d.doc_id,
		    rt.valeur,
		    t.tag_id recid, t.code, t.desc, tP.code type,
		    COUNT(rT.src_id) nbDoc,
		    COUNT(rT.dst_id) nbTag,
		    TL.ns, tL.code,
			tT.code 
		FROM
		    flux_doc d
		        INNER JOIN
		    flux_rapport r ON r.src_id = d.doc_id
		        AND r.src_obj = 'doc'
		        AND r.dst_obj = 'acti'
		        INNER JOIN
		    flux_rapport rT ON rT.pre_id = r.rapport_id
		        AND rT.src_obj = 'doc'
		        AND rT.dst_obj = 'tag'
		        AND rT.pre_obj = 'rapport'
		        INNER JOIN
		    flux_tag t ON t.tag_id = rT.dst_id
		        INNER JOIN
		    flux_tag tP ON tP.tag_id = t.parent
		        INNER JOIN
		    flux_rapport rTt ON rTt.pre_id = rT.rapport_id
		        AND rTt.src_obj = 'tag'
		        AND rTt.dst_obj = 'tag'
		        AND rTt.pre_obj = 'rapport'
		        INNER JOIN
		    flux_tag tL ON tL.tag_id = rTt.valeur
		        INNER JOIN
		    flux_tag tT ON tT.tag_id = rTt.dst_id
		-- where tL.ns = 'fa'
		GROUP BY tL.tag_id, tT.tag_id, t.tag_id
		ORDER BY t.code; -- nbDoc desc;
	    	 */
	    	$query = $this->dbD->select()
	    	->from( array("d" => "flux_doc"), array("docid"=>"doc_id"))
	    	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
		    	->joinInner(array('r' => 'flux_rapport'),"r.src_id = d.doc_id
		        AND r.src_obj = 'doc'
		        AND r.dst_obj = 'acti'",array())
		    	->joinInner(array('rT' => 'flux_rapport'),"rT.pre_id = r.rapport_id
		        AND rT.src_obj = 'doc'
		        AND rT.dst_obj = 'tag'
		        AND rT.pre_obj = 'rapport'",array())
		    	->joinInner(array('t' => 'flux_tag'),'t.tag_id = rT.dst_id',array("recid"=>"tag_id", "code", "desc"))
		    	->joinInner(array('tP' => 'flux_tag'),'tP.tag_id = t.parent',array("type"=>"code"))
		    	->joinInner(array('rTt' => 'flux_rapport'),"rTt.pre_id = rT.rapport_id
		        AND rTt.src_obj = 'tag'
		        AND rTt.dst_obj = 'tag'
		        AND rTt.pre_obj = 'rapport'",array())
		    ->joinInner(array('tL' => 'flux_tag'),'tL.tag_id = rTt.valeur',array("lang"=>"ns"))
		    ->joinInner(array('tT' => 'flux_tag'),'tT.tag_id = rTt.dst_id',array("trad"=>"code"))
		    ->group(array("tL.tag_id", "tT.tag_id", "t.tag_id"))
		    ->order(array("t.code"))
		    ;
		    	 
	    	return $this->dbD->fetchAll($query)->toArray();
    }
    
    /**
     * Fonction pour récupérer toutes les données de la monade
     *
     *
     * @return	array
     *
     */
    function getClassData(){
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	/*
    	 SELECT 
	COUNT(t.code) nbClass,
    d.doc_id,
    rT.valeur, rT.rapport_id,
    t.tag_id recid, t.code, t.desc, tP.code type
FROM
    flux_doc d
        INNER JOIN
    flux_rapport r ON r.src_id = d.doc_id
        AND r.src_obj = 'doc'
        AND r.dst_obj = 'acti'
        INNER JOIN
    flux_rapport rT ON rT.pre_id = r.rapport_id
        AND rT.src_obj = 'doc'
        AND rT.dst_obj = 'tag'
        AND rT.pre_obj = 'rapport'
        INNER JOIN
    flux_tag t ON t.tag_id = rT.dst_id
        INNER JOIN
    flux_tag tP ON tP.tag_id = t.parent
WHERE rT.valeur = 'Class'
GROUP BY t.tag_id
ORDER BY t.code ;
    		*/
	    	$query = $this->dbD->select()
	    	->from( array("d" => "flux_doc"), array("docid"=>"doc_id","nbClass"=>"COUNT(t.code)"))
	    	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
	    	->joinInner(array('r' => 'flux_rapport'),"r.src_id = d.doc_id
			        AND r.src_obj = 'doc'
			        AND r.dst_obj = 'acti'",array())
		->joinInner(array('rT' => 'flux_rapport'),"rT.pre_id = r.rapport_id
			        AND rT.src_obj = 'doc'
			        AND rT.dst_obj = 'tag'
			        AND rT.pre_obj = 'rapport'",array("rapport_id","monade_id"))
	    	->joinInner(array('t' => 'flux_tag'),'t.tag_id = rT.dst_id',array("tag_id", "code", "desc"))
	    	->joinInner(array('tP' => 'flux_tag'),'tP.tag_id = t.parent',array("type"=>"code","idType"=>"tag_id"))
	    	->where("rT.valeur = 'Class'")
	    	->group(array("t.tag_id"))
	    	->order(array("t.code"))
	    		        ;
    
    		return $this->dbD->fetchAll($query)->toArray();
    }
 

    /**
     * Fonction pour récupérer les traduction d'une langue
     *
     * @param integer 	$idTag
     * @param string		$code 
     *
     * @return	array
     *
     */
    function getClassTrad($idTag, $code=""){
    	
	    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    		/*
		SELECT 
			tT.ns lang, tT.code trad, tT.uri, tT.tag_id,
		    t.code
		    ,d.titre, d.url
		FROM flux_tag t
		        INNER JOIN
		    flux_tag tT ON tT.parent = t.tag_id
				LEFT JOIN
			flux_rapport r ON r.dst_id = tT.tag_id AND r.dst_obj = "tag" AND r.pre_id=t.tag_id AND r.pre_obj="tag" AND r.src_obj = "doc"
		    LEFT JOIN
		    flux_doc d ON d.doc_id = r.src_id
			    					
		-- WHERE t.tag_id = 3
		WHERE t.code = "AmplitudeInterquartile"
    	 	*/
	    	$query = $this->dbD->select()
		    	->from( array("t" => "flux_tag"), array("tag_id","code"))
		    	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
			->joinInner(array('tT' => 'flux_tag'),'tT.parent = t.tag_id',array("ns", "label"=>"code", "uri"))
			->joinLeft(array('r' => 'flux_rapport'),'r.dst_id = tT.tag_id AND r.dst_obj = "tag" AND r.pre_id=t.tag_id AND r.pre_obj="tag" AND r.src_obj = "doc"'
					,array())
			->joinLeft(array('d' => 'flux_doc'),'d.doc_id = r.src_id'
					,array("url","titre"))
			->order(array("d.url", "tT.uri"))
		    ;
		    	
		if($idTag)
			$query->where("t.tag_id = ?",$idTag);
		if($code)
			$query->where("t.code = ?",$code);
					
 		
		return $this->dbD->fetchAll($query)->toArray();
    }
 
    
    /**
     * Fonction pour récupérer les documents liés à une class
     *
     * @param integer 	$idTag
     * @param string		$code
     *
     * @return	array
     *
     */
    function getClassDocLie($idTag, $code=""){
    	 
	    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
	    	/*
	    		SELECT
	    t.code
	    ,d.titre, d.url
	    , r.valeur
	    , rdl.valeur
	    , dl.titre, dl.url
	    FROM flux_tag t
	    		INNER JOIN
	    		flux_rapport r ON r.dst_id = t.tag_id AND r.dst_obj = "tag" AND r.dst_id=t.tag_id AND r.pre_obj="rapport" AND r.src_obj = "doc"
	    		INNER JOIN
	    		flux_doc d on d.doc_id = r.src_id
	    		INNER JOIN
	    		flux_rapport rdl ON rdl.pre_id = r.rapport_id AND rdl.pre_obj="rapport" AND rdl.dst_obj="doc"
	    		INNER JOIN
	    		flux_doc dl on dl.doc_id = rdl.dst_id
	    	WHERE t.code = "AmplitudeInterquartile"
	    		*/
	    	$query = $this->dbD->select()
	    	->from( array("t" => "flux_tag"), array("tag_id","code"))
	    	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
	    	->joinInner(array('r' => 'flux_rapport'),'r.dst_id = t.tag_id AND r.dst_obj = "tag" AND r.dst_id=t.tag_id AND r.pre_obj="rapport" AND r.src_obj = "doc"'
	    			,array("typeD"=>"valeur"))
	    	->joinInner(array('d' => 'flux_doc'),'d.doc_id = r.src_id'
	    			,array("urlD"=>"url","titreD"=>"titre"))
	    	->joinInner(array('rdl' => 'flux_rapport'),'rdl.pre_id = r.rapport_id AND rdl.pre_obj="rapport" AND rdl.dst_obj="doc"'
	    			,array("typeDl"=>"valeur"))
		->joinInner(array('dl' => 'flux_doc'),'dl.doc_id = rdl.dst_id'
	    					,array("urlDl"=>"url","titreDl"=>"titre"))
	    					;
    					 
		if($idTag)
    			$query->where("t.tag_id = ?",$idTag);
    		if($code)
    			$query->where("t.code = ?",$code);
    								
		return $this->dbD->fetchAll($query)->toArray();
    }
    
    
    /**
     * Fonction pour récupérer les concepts les plus globaux du thésaurus
     *
     *
     * @return	array
     *
     */
    function getConceptGroup(){
    	 
	    	if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
	    	/*
	    	 SELECT 
			   t.tag_id, t.code 
			FROM
			    flux_tag t
			        INNER JOIN
			    flux_tag tp ON tp.tag_id = t.parent
			WHERE tp.code = "ConceptGroup";
	    		*/
	    	$query = $this->dbD->select()
		    	->from( array("t" => "flux_tag"), array("tag_id","code"))
	    		->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
	    	    ->joinInner(array('tp' => 'flux_tag'),'tp.tag_id = t.parent',array())
			->where("tp.code = 'ConceptGroup'")
	    	    ->order(array("t.code"));
    	        	
		return $this->dbD->fetchAll($query)->toArray();
    }
    
}
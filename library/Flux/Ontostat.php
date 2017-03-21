<?php
/**
 * Classe qui gère les flux Gdata
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 * merci à 
 *  * @file csv2skosxl.php
 * @license Licensed under WTFPL (http://www.wtfpl.net/txt/copying/)
 * @author Cristian Romanescu <cristian.romanescu@eaudeweb.ro>
 * 
 * http://skos.um.es/unescothes/
 * https://www.w3.org/TR/2005/WD-swbp-skos-core-spec-20051102
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
     * @param integer $idTag
     *
     * @return	array
     *
     */
    function getClassTrad($idTag){
    	
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	/*
    	 SELECT 
    tL.ns lang, tL.code langue,
	tT.code trad,
    t.code
FROM flux_tag tT
        INNER JOIN
    flux_rapport rTt ON rTt.dst_id = tT.tag_id
		AND rTt.src_id = tT.parent
        AND rTt.src_obj = 'tag'
        AND rTt.dst_obj = 'tag'
        AND rTt.pre_obj = 'rapport'        
        INNER JOIN
    flux_tag tL ON tL.tag_id = rTt.valeur
        INNER JOIN
    flux_tag t ON t.tag_id = tT.parent
-- where tL.ns = 'fa'
-- GROUP BY tL.tag_id, tT.tag_id
WHERE tT.parent = 2
ORDER BY tL.code; -- nbDoc desc;
    	 */
	    	$query = $this->dbD->select()
	    	->from( array("tT" => "flux_tag"), array("tag_id","label"=>"code","uri"))
	    	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
	    	->joinInner(array('rTt' => 'flux_rapport'),"rTt.dst_id = tT.tag_id
			AND rTt.src_id = tT.parent
	        AND rTt.src_obj = 'tag'
	        AND rTt.dst_obj = 'tag'
	        AND rTt.pre_obj = 'rapport'",array())
		->joinInner(array('tL' => 'flux_tag'),'tL.tag_id = rTt.valeur',array("ns", "langue"=>"code"))
		->joinInner(array('t' => 'flux_tag'),'t.tag_id = tT.parent',array("code"))
		->where("tT.parent = ?",$idTag)
		->order(array("tL.code"))
	    ;
 		
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
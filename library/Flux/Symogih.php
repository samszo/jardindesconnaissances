<?php
/**
 * Flux_Symogih
 * 
 * Classe qui gère les flux de l'ontologie Symogih
 * merci à 
 * http://symogih.org
 * http://bhp-publi.ish-lyon.cnrs.fr:8888/sparql
 * http://symogih.org/?q=type-of-knowledge-unit-classes-tree
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\LinkedOpenData
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Flux_Symogih extends Flux_Site{

    var $mc;
    var $idUtiSym;
    var $idTagSym;
    var $ei;
    var $formatResponse = "json";
    var $searchUrl = 'http://bhp-publi.ish-lyon.cnrs.fr:8888/sparql?';
    var $jsonTreeUrl = 'http://symogih.org/sites/all/libraries/custom/tree.php';
    var $ressourceUrl = 'http://symogih.org/resource/';
    
	public function __construct($idBase=false, $bTrace=false)
    {
        	parent::__construct($idBase,$bTrace);
    
        	
        	//on récupère la racine des documents
        	$this->initDbTables();
        	$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
        	$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
        	
        	//création des classe liée        	        	
        $this->mc = new Flux_MC($idBase, $bTrace);
        $this->ei = new Flux_EditInflu($idBase, $bTrace);
        
    }   
    
    /*récupère toute les class
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX viaf: <http://viaf.org/viaf/>
PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX sym: <http://symogih.org/ontology/>
PREFIX syr: <http://symogih.org/resource/>
SELECT *
WHERE
{ GRAPH <http://symogih.org/graph/symogih-kute>
{ ?s ?a ?n .
}
}
     */
    
    
    /**
     * Execute une requète sur le endpoint sparql
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
     * Initialise les variables pour l'importation de l'ontologie
     *
     *
     */
     function initVarOnto(){
	    	//initialistion des variables
     	$this->prefix = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX viaf: <http://viaf.org/viaf/>
PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX sym: <http://symogih.org/ontology/>
PREFIX syr: <http://symogih.org/resource/>
";
     	$this->idUtiSym = $this->dbU->ajouter(array("login"=>"symogih","role"=>"ontologie"));
     	$this->idTagSym = $this->dbT->ajouter(array("code"=>"Ontologie Symogih","parent"=>$this->ei->idTagCatNotion));
     	
    }

    /**
     * Importe l'ontologie dans la base à partir de l'arbre JSON du site
     * cf. http://symogih.org/?q=type-of-knowledge-unit-classes-tree
     *
     *
     * @return void
     *
     */
    function importTree(){
        
        $this->trace(__METHOD__." ");
        
        //intialisation
        $this->ei->initTags();
        $this->initVarOnto();
    
        //récupère le json
        $json = $this->getUrlBodyContent($this->jsonTreeUrl);
        $this->tree = json_decode($json);

        //enregistre le document comme crible
        $rsTronc = $this->ei->creaCrible($this->ei->idExiRoot, array("url"=>$this->jsonTreeUrl
            ,"parent"=>$this->idDocRoot,"titre"=>"symogih","tronc"=>0,"data"=>$json
        ));
        $this->idTronc = $rsTronc['doc_id'];
        
        //enregistrement des tags dans la base
        foreach ($this->tree as $t) {
            $this->saveBranche($t);
            
        }
                
    }
    
    
    /**
     * enregistre une branche de l'arbre
     * cf. http://symogih.org/?q=type-of-knowledge-unit-classes-tree
     *
     * @param   stdClass $b
     *
     * @return int
     *
     */
    function saveBranche($b){

        $idAct = $this->dbA->ajouter(array("code"=>__METHOD__));
        
        //vérifie si le parent existe
        $idParent = $this->idTagSym;
        if($b->parent_id){
            $rs = $this->dbT->findByType($b->parent_id);
            if(count($rs)>0) {
                $idParent = $rs[0]['tag_id'];
            }else{
                //recherche le parent dans le json
                $key = array_search($b->parent_id, array_column($this->tree, 'real_id'));
                $idParent = $this->saveBranche($this->tree[$key]);
            }
        };

        $url = $this->ressourceUrl.$b->real_id;
        $idTag = $this->setTag(array("type"=>$b->real_id,"uri"=>$this->ressourceUrl.$b->real_id,"code"=>$b->name,"parent"=>$this->idTagSym));
        
        //récupère le document qui décrit la classe
        $html = $this->getUrlBodyContent($url);

        //enregistre le document
        $idD = $this->dbD->ajouter(array("url"=>$url
            ,"parent"=>$this->idTronc,"tronc"=>0,"data"=>$html
        ));        
        /*création du rapport pour préciser
         * src : le parent du tag
         * dst : le tag
         * pre : le document source
         *
         */
        $idRapTag = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
            ,"src_id"=>$idParent,"src_obj"=>"tag"
            ,"dst_id"=>$idTag,"dst_obj"=>"tag"
            ,"pre_id"=>$idD,"pre_obj"=>"doc"
            ,"valeur"=>__METHOD__
        ));
        
        //constuction du xml à requeter
        $dom = new Zend_Dom_Query($html);

        //récupère le titre
        $xp = '//*[@id="page-title"]';
        $result = $dom->queryXpath($xp);
        foreach ($result as $cn) {
            //met à jour les informations
            $val = trim($cn->nodeValue);
            $this->dbD->edit($idD, array("titre"=>$val));
            //$this->dbT->edit($idTag, array("code"=>$val));
        }        
        
        //récupère la description        
        $xp = '//*[@id="block-system-main"]/div/div/div/div/div/span';
        $result = $dom->queryXpath($xp);
        foreach ($result as $cn) {
            //met à jour les informations
            $val = trim($cn->nodeValue);
            $this->dbT->edit($idTag, array("desc"=>$val));
        }
        
        $this->saveLignesData($dom, '//*[@id="block-views-symogih-ref-tyco-tkuc-block"]/div/div/div/table/tbody/tr', "type de contenus associés",$idTag,$idD);
        
        $this->saveLignesData($dom, '//*[@id="block-views-symogih-ref-tyin-tkuc-block"]/div/div/div/table/tbody/tr', "type d'informations associés",$idTag,$idD);
        
        $this->saveLignesData($dom, '//*[@id="quicktabs-tabpage-types_de_roles_et_informations-0"]/div/div/table/tbody/tr', "type de roles et d'informations associés",$idTag,$idD);
        
        
        return $idTag;        
        
    }
    
    
    
    /**
     * enregistre ou récupère les information de tag
     *
     * @param   array $p
     *
     * @return int
     *
     */
    function setTag($p){
        
        if($p['type']=="TKUC13"){
            echo 'toto';
        }
        
        //recherche si le tag a déjà été importé par l'ontologie
        $rs = $this->dbT->findByType($p['type']);
        if(count($rs)>0) {
            /*on supprime l'ancienne occurrence
             $nbSup = $this->dbT->remove($rs[0]['tag_id']);
             $this->trace($rs[0]['code']." supprimé = ".$nbSup);
             */
            //on change l'url et le code
            $idTag = $rs[0]['tag_id'];
            $this->dbT->edit($idTag, $p);
            
        }else{
            /*création du tag
             * avec un parent générique
             */
            $idTag = $this->dbT->ajouter($p);
        }
        $this->trace($idTag." : ".$p['code']);
        return $idTag;
    }
    
    /**
     * enregistre les informations d'une ligne de tableau
     *
     * @param   DOMDocument $dom
     * @param   string      $xp
     * @param   string      $type
     * @param   int         $idTag
     * @param   int         $idD
     *
     * @return void
     *
     */
    function saveLignesData($dom, $xp, $type, $idTag, $idD){
        
        $this->trace("récupère : ".$type);
        $result = $dom->queryXpath($xp);
        foreach ($result as $n) {
            //recupère les infos
            $val = $this->getLignesData($n);
            //met à jour les informations
            if($type=="type de roles et d'informations associés")
                $idT = $this->setTag(array("code"=>$val[0],"type"=>$val[1],"desc"=>$val[2],"parent"=>$this->idTagSym));
            else
                $idT = $this->setTag(array("code"=>$val[1],"type"=>$val[0],"desc"=>$val[2],"uri"=>$val[3],"parent"=>$this->idTagSym));
            //création du rapport
            $idR = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                ,"src_id"=>$idTag,"src_obj"=>"tag"
                ,"dst_id"=>$idT,"dst_obj"=>"tag"
                ,"pre_id"=>$idD,"pre_obj"=>"doc"
                ,"valeur"=>$type
            ));
            $this->trace($idT." : ".$val[0]." - ".$val[1]." = ".$idR);
            
        }
        
    }
        
    /**
     * récupère les informations d'une ligne de tableau
     *
     * @param   DOMNode $n
     *
     * @return array
     *
     */
    function getLignesData($n){
        
        $infos = $n->getElementsByTagName('td');
        $i = 0; $l="";
        $val = array();
        foreach ($infos as $inf) {
            $val[$i] = trim($inf->nodeValue);
            $ch = $inf->childNodes;
            foreach ($ch as $c) {
                if($c->nodeName == "a"){
                    $l = $c->getAttribute('href');
                }
            }
            $i++;
        }
        $val[$i]=$l;
        return $val;
        
    }
        
    /**
     * Importe l'ontologie dans la base à partir de SPARQL
     * ATTENTION : les références ne correspondent pas complètement avec le site 
     * par exemple on ne trouve pas les références : TKUCn
     *
     *
     * @return void
     *
     */
    function importOnto(){
    	
		$this->trace(__METHOD__." ");
    	
	    	//intialisation
	    	$this->ei = new Flux_EditInflu($this->idBase);
	    	$this->ei->initTags();
	    	$this->initVarOnto();
	    	
	    	//récupère toutes les classes
	    	$query = $this->prefix."
            SELECT distinct ?s
            WHERE
            { GRAPH <http://symogih.org/graph/symogih-kute>
            { ?s ?a ?n .}
            }
            ";
	    	$arr = json_decode($this->query($query));
	    	foreach ($arr->results->bindings as $r) {
	    	    //enregistre les valeurs de tags
	    	    $idTagS = $this->saveClass($r->s);
	    	    if($r->s->value=="http://symogih.org/resource/TyIn1"){
	    	        $t = true;
	    	    }
	    	    //récupère le détail de la class
	    	    $query = $this->prefix."
            SELECT ?a ?n
            WHERE
            { GRAPH <http://symogih.org/graph/symogih-kute>
            { <".$r->s->value."> ?a ?n .}
            }
            ";
	    	    $arrD = json_decode($this->query($query));
	    	    foreach ($arrD->results->bindings as $k => $v) {
	    	        //vérifie s'il faut mettre à jour la description
	    	        switch ($v->a->value) {
	    	            case "http://www.w3.org/2000/01/rdf-schema#label":
	    	                $this->dbT->edit($idTagS, array("code"=>$v->n->value));
	    	            case "http://symogih.org/ontology/description":
	    	                $this->dbT->edit($idTagS, array("desc"=>$v->n->value));
	    	                break;
	    	            case "http://symogih.org/ontology/creationTimestamp":
	    	                //on n'enregistre pas la date de création dans l'ontologie
	    	                $t = false;
	    	                break;	    	                
	    	            default:
	    	                //enregistre les valeurs de tags
	    	                $idTagA = $this->saveClass($v->a);
	    	                $idTagN = $this->saveClass($v->n);
	    	                /*enregistre le rapport entre
	    	                 * src = tag S
	    	                 * dst = tag N
	    	                 * pre = tag A
	    	                 */
	    	                $idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    	                    ,"src_id"=>$idTagS,"src_obj"=>"tag"
	    	                    ,"dst_id"=>$idTagN,"dst_obj"=>"tag"
	    	                    ,"pre_id"=>$idTagA,"pre_obj"=>"tag"
	    	                ));
	    	                break;
	    	        }
	    	    }
	    	}

	    	$this->trace(__METHOD__." FIN ");
	    	
    }
    
    /**
     * Enregistre la class dans la base
     *
     * @param stdClass  $c
     * 
     * @return int
     *
     */
    function saveClass($c){
        if(filter_var($c->value, FILTER_VALIDATE_URL)){
            $u = parse_url($c->value);
            $p = pathinfo($u["path"]);
            $l = $p['filename'];
            if(isset($u["fragment"]))$l = $u["fragment"];                
            $idTag = $this->dbT->ajouter(array("code"=>$l,"type"=>$l,"uri"=>$c->value,"parent"=>$this->idTagSym));
        }else{
            $l = $c->value;
            $idTag = $this->dbT->ajouter(array("code"=>$l,"parent"=>$this->idTagSym));
        }
        $this->trace($idTag." : ".$l);
        return $idTag;
    }
        
    
    
}
<?php
/**
 * Flux_Omek    
 * 
 * Classe qui gère les flux du projet Omeka
 * merci à 
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\API
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Flux_Omeka extends Flux_Site{

    var $endpoint = "http://localhost/omeka-s/api/";
    var $idByTypeName;
    var $snNameId;
    var $language = 'fr';
    var $is_public = 1;
    var $API_IDENT;
    var $API_KEY;
    var $doublons;
    var $param;
    var $hier;
    var $id;
    var $idsCol;
    var $reseau; 

	public function __construct($idBase=false, $bTrace=false)
    {
    	parent::__construct($idBase,$bTrace);
        //initVarOmk();
    }   
    
    
    /**
     * Initialise les variables pour l'importation Omk
     *
     *
     */
     function initVarOmk(){
    	//on récupère la racine des documents
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	if(!$this->dbM)$this->dbM = new Model_DbTable_Flux_Monade($this->db);
    	$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
    	$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);

        //initialistion des variables
     	$this->dbR = new Model_DbTable_Omk_Resource($this->db);
     	$this->dbIS = new Model_DbTable_Omk_ItemSet($this->db);
     	$this->dbI = new Model_DbTable_Omk_Item($this->db);
     	$this->dbV = new Model_DbTable_Omk_Value($this->db);
     	$this->dbVoc = new Model_DbTable_Omk_Vocabulary($this->db);
     	$this->dbP = new Model_DbTable_Omk_Property($this->db);
     	$this->dbRC = new Model_DbTable_Omk_ResourceClass($this->db);
     	$this->dbIIS = new Model_DbTable_Omk_ItemItemSet($this->db);
     	$this->owner = "samuel.szoniecky@univ-paris8.fr";
        $this->idOwner = 1;
        
        //récupère les infos de propriété

    }

    /**TODO
     * Enregistre une annotation par describe leaflet comme enfant de l'image
     *postItem
     * @param array $data
     *
    function postAnnotationImageDetail($data){

    }
     */

    /**
     * Enregistre une annotation avec l'API
     *postItem
     * @param array $data
     *
     */
    function postAnnotation($data){
        //initialisation des paramètres
        $param['o:resource_template']['o:id']=$this->getIdByName('Annotation','resource_templates');//6
        $param['o:resource_class']['o:id']=$this->getIdByName('Annotation','resource_classes');//136;
        
        $param['oa:motivatedBy'][0]['@value']= $data['motivatedBy'] ? $data['motivatedBy'] : 'tagging';
        $param['oa:motivatedBy'][0]['property_id']= $this->getIdByName('motivatedBy','properties');// 248;
        $param['oa:motivatedBy'][0]['type']= 'customvocab:'.$this->getIdByName('Annotation oa:motivatedBy','custom_vocabs');// 10;
        
        $param['oa:hasBody'][0]['rdf:value'][0]['@value']=$data['body'];
        $param['oa:hasBody'][0]['rdf:value'][0]['property_id']=$this->getIdByName('value','properties');//195;
        $param['oa:hasBody'][0]['rdf:value'][0]['type']='literal';
        $param['oa:hasBody'][0]['oa:hasPurpose'][0]['@value']=$data['hasPurpose'];//'classifying';
        $param['oa:hasBody'][0]['oa:hasPurpose'][0]['property_id']=$this->getIdByName('hasPurpose','properties');//241;
        $param['oa:hasBody'][0]['oa:hasPurpose'][0]['type']='customvocab:'.$this->getIdByName('Annotation Body oa:hasPurpose','custom_vocabs');//10
      
        $param['oa:hasTarget'][0]['oa:hasSource'][0]['property_id']=$this->getIdByName('hasSource','properties');//244;
        $param['oa:hasTarget'][0]['oa:hasSource'][0]['type']='resource';
        $param['oa:hasTarget'][0]['oa:hasSource'][0]['value_resource_id']=$data['hasSource'];//1248;

        $param['oa:hasSource'][0]['property_id']=$this->getIdByName('hasSource','properties');//244;
        $param['oa:hasSource'][0]['type']='resource';
        $param['oa:hasSource'][0]['value_resource_id']=$data['hasSource'];//1248;

        $param['oa:hasTarget'][0]['rdf:type'][0]['property_id']= $this->getIdByName('type','properties');//191;
        $param['oa:hasTarget'][0]['rdf:type'][0]['type']='customvocab:'.$this->getIdByName('Annotation Target rdf:type','custom_vocabs');//13
        if($data['hasTarget']['id']){
            $param['oa:hasTarget'][0]['rdf:type'][0]['@value']='o:Item';
            $param['oa:hasTarget'][0]['rdf:value'][0]['value_resource_id']=$data['hasTarget']['id'];
            $param['oa:hasTarget'][0]['rdf:value'][0]['property_id']=$this->getIdByName('value','properties');//195;
            $param['oa:hasTarget'][0]['rdf:value'][0]['type']='resource';    
        }else{
            $param['oa:hasTarget'][0]['rdf:type'][0]['@value']='dctype:Text';
            $param['oa:hasTarget'][0]['rdf:value'][0]['@value']=$data['hasTarget'];
            $param['oa:hasTarget'][0]['rdf:value'][0]['property_id']=$this->getIdByName('value','properties');//195;
            $param['oa:hasTarget'][0]['rdf:value'][0]['type']='literal';    
        }

        $param['o:is_public']= 1;


        return $this->send('annotations','POST',$this->paramsAuth(),$param,'',true);
    }


    /**
     * construction des paramètres poru l'API
     *
     * @param array $data
     *
     * @return array
     */
    function setParamAPI($data){

        foreach ($data as $k => $v) {
            switch ($k) {
                case 'item_set':
                    $i = 0;
                    $v = is_array($v) ? $v : array($v);
                    foreach ($v as $d) {
                        $param['o:item_set'][]=$d;
                        $i++;
                    }
                    break;
                case 'resource_template':
                    $param['o:resource_template']['o:id']=$data['resource_template'];
                    break;
                case 'resource_class':
                    $param['o:resource_class']['o:id']=$this->getIdByNSName($data['resource_class'],'resource_classes');;
                    break;
                case 'IIIF':
                    $param['o:media'][0]['dcterms:title'][0]['@value']= $v['title'];
                    $param['o:media'][0]['dcterms:title'][0]['property_id']= $this->getIdByName('title','properties');
                    $param['o:media'][0]['dcterms:title'][0]['type']= 'literal';
                    $param['o:media'][0]['o:source']= $v['IIIF'];    
                    $param['o:media'][0]['o:ingester']= 'iiif';    
                    $param['o:media'][0]['is_public']= $v['is_public'] ? $v['is_public'] : $this->is_public;   
                    break;      
                case 'ingest_url':
                    $param['o:media'][0]['dcterms:title'][0]['@value']= $v['title'];
                    $param['o:media'][0]['dcterms:title'][0]['property_id']= $this->getIdByName('title','properties');
                    $param['o:media'][0]['dcterms:title'][0]['type']= 'literal';
                    $param['o:media'][0]['ingest_url']= $v['source'];    
                    $param['o:media'][0]['o:ingester']= 'url';    
                    $param['o:media'][0]['is_public']= $v['is_public'] ? $v['is_public'] : $this->is_public;    
                    break;      
                default:
                    $i=0;
                    $p = $this->getPropByTerm($k);
                    $v = is_array($v) ? $v : array($v);
                    foreach ($v as $d) {
                        $param[$k][$i]['property_id']= $p['o:id'];
                        $param[$k][$i]['type']= $d['type'];
                        switch ($d['type']) {
                            case 'uri':
                                $param[$k][$i]['@id']= $d['uri'];
                                $param[$k][$i]['o:label']= $d['label'];
                                break;                                
                            case 'resource':
                                $param[$k][$i]['value_resource_id']= $d['value'];                    
                                break;      
                            default:
                                $param[$k][$i]['type']= 'literal';
                                $param[$k][$i]['@value']= $d;
                                $param[$k][$i]['@language']= $this->language ;
                                break;
                        }
                        $param[$k][$i]['is_public']= isset($d['is_public']) ? $d['is_public'] : $this->is_public;        
                        $i++;
                    }
                    break;
            }
        }

        return $param;

    }

    /**
     * Enregistre un item avec l'API
     *
     * @param array $data
     * @param boolean   $existe
     * @param boolean   $patch
     * @param boolean   $put
     *
     * @return array
     */
    function postItem($data, $existe=true, $patch=true, $put=false){

        $this->trace("DEBUT ".__METHOD__,$data);
        $param = $this->setParamAPI($data);
        $this->trace("PARAM ".__METHOD__,$param);
       
        $r = null;
        if($existe){
            $r = $this->searchByRef($data['dcterms:isReferencedBy'],'items',true)[0];
        }
        if($r==null)
            $r = $this->send('items','POST',$this->paramsAuth(),$param,'',true);
        elseif($patch)
            $r = $this->send('items','PATCH',$this->paramsAuth(),$param,'/'.$r['o:id'],true);
        elseif($put)
            $r = $this->send('items','PUT',$this->paramsAuth(),$param,'/'.$r['o:id'],true);
        
        $this->trace("RESULT ".__METHOD__,$r);

        /*
        //gestion de l'erreur
        if($r['errors'])
            throw new Exception(json_encode($r['errors']));
        */
        $this->trace("FIN ".__METHOD__);
        return $r;
    }

    /**
     * Enregistre une collection avec l'API
     *
     * @param array     $data
     * @param boolean   $existe
     * @return array
     */
    function postItemSet($data, $existe=true){
        $r = null;
        if($existe){
            $r = json_decode($this->search($data,'item_sets'),true)[0];
        }
        if($r==null){
            //initialisation des paramètres POST
            $param['o:resource_template']['o:id']="";
            $param['o:resource_class']['o:id'] = $data['resource_class'] ? $this->getIdByName($data['resource_class'],'resource_classes') : '';
            $param['dcterms:title'][0]['property_id']= $this->getIdByName('title','properties');//;
            $param['dcterms:title'][0]['type']='literal';
            $param['dcterms:title'][0]['@value'] = $data['title'];
            $param['dcterms:title'][0]['@language']= $this->language;
            $param['dcterms:title'][0]['is_public']= 1;
            $param['o:is_public']= 1;
            $param['o:is_open']= 1;
            $r = json_decode($this->send('item_sets','POST',$this->paramsAuth(),$param),true);
        }
        return $r;
    }

    /**
     * Enregistre une géolocalisation avec l'API
     *
     * @param array     $data
     * @return array
     */
    function postGeo($data){

        //initialisation des paramètres
        $param['o:resource_template']['o:id']=$this->getIdByName('Annotation','resource_templates');//6
        $param['o:resource_class']['o:id']=$this->getIdByName('Annotation','resource_classes');//136;

        $param['oa:motivatedBy'][0]['@value']= $data['motivatedBy'] ? $data['motivatedBy'] : 'tagging';
        $param['oa:motivatedBy'][0]['property_id']= $this->getIdByName('motivatedBy','properties');// 248;
        $param['oa:motivatedBy'][0]['type']= 'customvocab:'.$this->getIdByName('Annotation oa:motivatedBy','custom_vocabs');// 10;
        
        $param['oa:hasPurpose'][0]['@value']=$data['hasPurpose'] ? $data['hasPurpose'] : 'classifying';
        $param['oa:hasPurpose'][0]['property_id']=$this->getIdByName('hasPurpose','properties');//241;
        $param['oa:hasPurpose'][0]['type']='customvocab:'.$this->getIdByName('Annotation Body oa:hasPurpose','custom_vocabs');//10

        $param['oa:hasBody'][0]['rdf:value'][0]['@value']=$data['body'];
        $param['oa:hasBody'][0]['rdf:value'][0]['property_id']=$this->getIdByName('value','properties');//195;
        $param['oa:hasBody'][0]['rdf:value'][0]['type']='literal';
        $param['oa:hasBody'][0]['oa:hasPurpose'][0]['@value']=$data['hasPurpose'] ? $data['hasPurpose'] : 'classifying';
        $param['oa:hasBody'][0]['oa:hasPurpose'][0]['property_id']=$this->getIdByName('hasPurpose','properties');
        $param['oa:hasBody'][0]['oa:hasPurpose'][0]['type']='vocab:'.$this->getIdByName('Annotation Body oa:hasPurpose','custom_vocabs');

        /*
        $param['oa:hasSource'][0]['property_id']=$this->getIdByName('hasSource','properties');
        $param['oa:hasSource'][0]['type']='resource';
        $param['oa:hasSource'][0]['value_resource_id']=$data['hasSource'];
        $param['oa:hasSource'][0]['is_public']=$this->is_public;
        $param['dcterms:format'][0]['property_id']=$this->getIdByName('format','properties');
        $param['dcterms:format'][0]['type']='customvocab:'.$this->getIdByName('Annotation Body oa:hasPurpose','custom_vocabs');//10
        $param['dcterms:format'][0]['is_public']=$this->is_public;
        $param['dcterms:type'][0]['property_id']=$this->getIdByName('type','properties');
        $param['dcterms:type'][0]['type']='customvocab:'.$this->getIdByName('Annotation Target rdf:type','custom_vocabs');
        $param['dcterms:type'][0]['is_public']=$this->is_public;
        */

        $param['oa:hasTarget'][0]['oa:hasSource'][0]['property_id']=$this->getIdByName('hasSource','properties');//244;
        $param['oa:hasTarget'][0]['oa:hasSource'][0]['type']='resource';
        $param['oa:hasTarget'][0]['oa:hasSource'][0]['value_resource_id']=$data['hasSource'];
        $param['oa:hasTarget'][0]['dcterms:format'][0]['type']='customvocab:'.$this->getIdByName('Annotation Target dcterms:format','custom_vocabs');//13
        $param['oa:hasTarget'][0]['dcterms:format'][0]['property_id']=$this->getIdByName('format','properties');//191;
        $param['oa:hasTarget'][0]['dcterms:format'][0]['is_public']=1;
        $param['oa:hasTarget'][0]['dcterms:format'][0]['@value']='application/wkt';
        $param['oa:hasTarget'][0]['rdf:type'][0]['property_id']= $this->getIdByName('type','properties');//191;
        $param['oa:hasTarget'][0]['rdf:type'][0]['type']='customvocab:'.$this->getIdByName('Annotation Target rdf:type','custom_vocabs');//13
        $param['oa:hasTarget'][0]['rdf:type'][0]['@value']='oa:Selector';
        $param['oa:hasTarget'][0]['rdf:value'][0]['@value']=$data['value'] ? $data['value'] : 'POINT (-1.279615 46.144474)';
        $param['oa:hasTarget'][0]['rdf:value'][0]['property_id']=$this->getIdByName('value','properties');//195;
        $param['oa:hasTarget'][0]['rdf:value'][0]['type']='geometry:geography';
        $param['o:is_public']=$this->is_public;


        return $this->send('annotations','POST',$this->paramsAuth(),$param,'',true);
    }

    /**
     * Enregistre un template de ressource avec l'API
     *
     * @param   array       $data
     * @param   boolean     $existe
     * @param   boolean     $patch
     * @return array
     */
    function postResourceTemplate($data, $existe=true, $patch=false){

        if($existe){
            $r = json_decode($this->search($data,'resource_templates','label'),true)[0];
        }
        if($r==null){
            //initialisation des paramètres
            $param['o:label']=$data['label'];
            $param['o:resource_class']['o:id']=$this->getIdByName($data['class'],'resource_classes');
            foreach ($data['props'] as $p) {
                $idP = $this->getIdByName($p['label'],'properties');
                $param['o:resource_template_property'][$idP]['o:property']['o:id']=$idP;
                $param['o:resource_template_property'][$idP]['o:original_label']=$p['label'];
                $param['o:resource_template_property'][$idP]['o:is_required']=$p['required'];
                $param['o:resource_template_property'][$idP]['o:data_type']=$p['type'];
            }
            $r = json_decode($this->send('resource_templates','POST',$this->paramsAuth(),$param),true);
        }
        if($patch){
            $param['o:resource_class']['o:id']=$this->getIdByName($data['class'],'resource_classes');
            foreach ($data['props'] as $p) {
                $idP = $this->getIdByName($p['label'],'properties');
                $param['o:resource_template_property'][$idP]['o:property']['o:id']=$idP;
                $param['o:resource_template_property'][$idP]['o:original_label']=$p['label'];
                $param['o:resource_template_property'][$idP]['o:is_required']=$p['required'];
                $param['o:resource_template_property'][$idP]['o:data_type']=$p['type'];
            }
            $r = json_decode($this->send('resource_templates','PATCH',$this->paramsAuth(),$param,'/'.$r['o:id']),true);
        }

        return $r;
    }

    /**
     * cherche un objet
     *
     * @param array     $data
     * @param string    $type
     * @param string    $champ
     * @param boolean    $decode
     *
     */
    function search($data,$type='items',$champ='title', $decode=false){
        //initialisation des paramètres POST
        if($champ=='label' || $champ=='resource_class_label'){
            $param[$champ]=$data[$champ];
        }else{
            $param['property'][0]['property']= $this->getIdByName($champ,'properties');//1;
            $param['property'][0]['type']='eq';
            $param['property'][0]['text']=$data[$champ];
        }
        //TODO: ajouter une fonction searchAll
        $param['per_page']=100;
        $r = $this->send($type,'GET',$param,false,'',$decode);
        return $r;
    }

    /**
     * cherche un objet avec plusieurs critères
     * suivant les paramètre de l'API
property[{index}][joiner]: "and" OR "or" joiner with previous query
property[{index}][property]: property ID
property[{index}][text]: search text
property[{index}][type]: search type
eq: is exactly
neq: is not exactly
in: contains
nin: does not contain
ex: has any value
nex: has no value
res: is this RDF resource (by ID)
nres: is not this RDF resource (by ID)
     *
     * @param array     $data
     * @param string    $type
     * @param string    $champ
     * @param array     $params
     * @param boolean   $decode
     */
    function searchMulti($data,$type='items',$params=[],$decode=true){

        $i=0;
        foreach ($data as $k => $v) {
            if($k=='item_set_id'){
                if(!is_int($v))$v = $this->getColId($v);
                $param['item_set_id']=$v;
            }else{  
                //vérifie si le passage de paramètre est simple = property => valeur              
                if(!is_array($v)){
                    $data[$k] = array('t'=>'eq','s'=>'text','v'=>$v);
                }
                //défini les valeurs par défaut
                if(!$data[$k]['t'])$data[$k]['t']='eq';
                if(!$data[$k]['s'])$data[$k]['s']='text';
                if(!$data[$k]['v'])$data[$k]['v']=$v;
                //construction du paramètre
                $param['property'][$i]['property']= $this->getIdByNSName($k,'properties');
                $param['property'][$i]['type']=$data[$k]['t'];
                $param['property'][$i][$data[$k]['s']]=$data[$k]['v'];
            }
            $i++;
        }
        /*
Parameter	Description	Type	Default
sort_by	    Sort the result set by this field	string	created
sort_order	Sort the result set in this order, ascending ("asc") or descending ("desc")	string	desc
page	    The page number of the result set to return	integer	1
per_page	The number of results per page	integer	uses global "results per page" setting
limit	    The number of results to return	integer	0 (all)
offset	    The number offset of results to return	integer	0 (no offset)        
        */        
        foreach ($params as $k => $v) {
            $param[$k]=$v;
        }
        //property[0][property]=33&property[0][type]=res&property[0][text]=396182
        $r = $this->send($type,'GET',$param,false,'',$decode);
        return $r;
    }


    /**
     * cherche tout les objets
     *
     * @param array     $data
     * @param string    $type
     * @param string    $champ
     * @param array     $params
     * 
     * @return array
     */
    function searchAllMulti($data, $type='items',$params=false){
        if(!$params)$params = array("page"=>1);
        else $params["page"]++;

        $items = $this->searchMulti($data,$type,$params,true);
        if(count($items))$allitems=array_merge($items,$this->searchAllMulti($data,$type,$params));
        else $allitems=[];
        return $allitems;
    }


    /**
	 * cherche des items suivant des critères
     * 
     * @param array     $params
     * 
     * @return array
	 */
    function searchItems($params){
        return $this->searchMulti($params,'items',array('per_page'=>10000));
    }


    /**
     * cherche un objet par une référence
     *
     * @param string    $ref
     * @param string    $type
     * @param boolean   $decode
     */
    function searchByRef($ref,$type='items',$decode=false){
        //initialisation des paramètres POST
        $param['property'][0]['property']= $this->getIdByName('isReferencedBy','properties');
        $param['property'][0]['type']='eq';
        $param['property'][0]['text']=$ref;
        $r = $this->send($type,'GET',$param);
        if($decode)$r=json_decode($r,true);
        return $r;
    }

    /**
     * Récupère un vocabulaire
     *
     * @param string $prefix
     *
     * @return json
     */
    function getVocab($prefix){
        //initialisation des paramètres GET
        $param = $this->paramsAuth();
        $param['prefix']=$prefix;

        return $this->send('vocabularies','GET',$param);
    }

    /**
     * Enregistre un vocabulaire
     *
     * @param array $data
     *
     * @return json
     */
    function setVocab($data){

        //vérifie l'existence du vocab
        $v = $this->getVocab($data['prefix']);

        if($v=="[]"){
            $param['vocabulary-fetch']['url']= $data['url'];
            $param['vocabulary-fetch']['format']= $data['format'];
            $param['vocabulary-fetch']['lang']= $data['lang'];
            $param['o:prefix']= $data['prefix'];
            $param['o:namespace_uri']= $data['ns_uri'];
            $param['o:label']= $data['label'];
            $param['o:comment']= $data['comment'];
            $v = $this->send('vocabularies','POST',$this->paramsAuth(),$param);
        }
        return $v;
    }

    /**
     * Récupère les propriétés de vocabulaire
     *
     * @param array $data
     *
     * @return json
     */
    function getProps($data){
        //initialisation des paramètres GET
        $param = $this->paramsAuth();
        $param['vocabulary_prefix']=$data['prefix'];//"skos";

        return $this->send('properties','GET',$param);
    }

    /**
     * Récupère une propriété par son nom
     *
     * @param string $name
     *
     * @return json
     */
    function getPropByName($name){
        //initialisation des paramètres GET
        $param = $this->paramsAuth();
        $param['local_name']=$name;

        return $this->send('properties','GET',$param);
    }

    /**
     * Récupère un identifiant par son nom
     *
     * @param string $name
     * @param string $type
     * @param string $champ
     *
     * @return int
     */
    function getIdByName($name,$type,$champ='local_name'){
        //vérifie la présence de l'id
        if(!$this->idByTypeName[$type.'_'.$name]){
            //récupère l'id
            $param = $this->paramsAuth();
            $champ='local_name';
            if($type=='resource_templates' || $type=='custom_vocabs')$champ="label";
            $param[$champ]=$name;   
            $r = $this->send($type,'GET',$param,false,'',true);
            $this->idByTypeName[$type.'_'.$name]=$r[0]['o:id'];
        }
        return $this->idByTypeName[$type.'_'.$name];
    }

    /**
     * Récupère un identifiant par son nom avec name space
     *
     * @param string $nsName
     * @param string $type
     *
     * @return int
     */
    function getIdByNSName($nsName, $type="properties"){
        //vérifie la présence de l'id
        if(!$this->nsNameId[$nsName]){
            //récupère l'id
            $param = $this->paramsAuth();
            $champ='term';
            $param[$champ]=$nsName;   
            $r = $this->send($type,'GET',$param,false,'',true);
            $this->nsNameId[$nsName]=$r[0]['o:id'];
        }
        return $this->nsNameId[$nsName];
    }

    /**
     * Récupère une propriété par son term
     *
     * @param string $term
     *
     * @return array
     */
    function getPropByTerm($term){
        if(!$this->idByTypeName[$term]){
            //récupère l'id
            $param = $this->paramsAuth();
            $param['term']=$term;   
            $r = $this->send('properties','GET',$param,false,'',true);
            $this->idByTypeName[$term]=$r[0];
        }
        return $this->idByTypeName[$term];
    }

    /**
     * Envoie une requête à l'API
     *
     * @param string    $type
     * @param string    $methode
     * @param array     $get
     * @param array     $post
     * @param array     $id
     * @param boolean   $decode
     * 
     * @return array
     * 
     * POUR TESTER
     curl -XPOST -H "Content-type: application/json" -d '{"dcterms:title":[{"property_id":1,"type":"literal","@value":"test","@language":"fr","is_public":1}],"dcterms:isReferencedBy":[{"property_id":35,"type":"literal","@value":"test","@language":"fr","is_public":1}],"o:item_set":[14]}' 'http://192.168.20.232/genlod/api/items?key_identity=lqwqQkzUq2UZtoYmvKp5bs68YiWSFf0t&key_credential=sRikncbW7O6Whmlo4HJq9QOBy7cLuUu6'
     * 
     */
    function send($type, $methode, $get=false, $post=false,$id='',$decode=false){
        $this->trace("DEBUT ".__METHOD__.' '.$type.' '.$methode.' '.$get.' '.$post.' '.$id.' '.$decode);
        $client = new Zend_Http_Client($this->endpoint.$type.$id,array('timeout' => 100));
        $this->trace($this->endpoint.$type.$id,$post);
        if($get){
            $client->setParameterGet($get);
            $this->trace('GET',$get);
        }
        if($post){
            $js = json_encode($post);
            $this->trace($js);
            //pour forcer le header
            $client->setRawData($js, 'application/json');
        }
        if($methode=='PATCH')$client->setHeaders('Content-Type','application/json');
        $response = $client->request($methode);
        $body = $response->getBody();
        //$this->trace("RESULT ".__METHOD__.' '.$body);
        return $decode ? json_decode($body,true) : $body;

    }

    function paramsAuth(){
        //initialisation des paramètres GET pour l'authentification
        $p = array('key_identity' => $this->API_IDENT, 'key_credential' => $this->API_KEY);
        //$this->trace(__METHOD__,$p);
        return $p;
    }
    

    /**
     * Récupère toute les items d'une collection
     *
     * @param string    $param
     * @param int       $value
     * @param int       $page
     * 
     * @return array
     * 
     * 
     */
    function getAllItem($param, $value, $page=0){
        //récupère les items de la collection Stato fr	
        $url = $this->endpoint."items?".$param."=".$value."&page=".$page."&per_page=100&key_identity=".$this->API_IDENT."&key_credential=".$this->API_KEY;
		$json = $this->getUrlBodyContent($url);
        $items = json_decode($json,true);
        if(count($items))$allitems=array_merge($items,$this->getAllItem($param,$value,$page+1));
        else $allitems=[];
        return $allitems;
    }

     /**
     * Récupère un item
     *
     * @param int    $id
     * 
     * @return array
     * 
     * 
     */
    function getItem($id){
        $url = $this->endpoint."items/".$id."&key_identity=".$this->API_IDENT."&key_credential=".$this->API_KEY;
		$json = $this->getUrlBodyContent($url);
        return json_decode($json,true);
    }


    /**
     * Fonction pour récupérer la hierarchie complète d'une item
     *
     * @param string	$propSrc
     * @param string	$propDst
     * @param array	    $item
     * @param int	    $niv
	 * 
     * @return	array
     *
     */
    function getHierarchie($propSrc, $propDst, $item, $niv){
		 
		foreach ($item[$propSrc] as $sc) {
			if($sc['type']=="resource"){
				//ajoute l'item dans la hierarchie
				if(!$this->doublons[$sc['value_resource_id']]){
					$this->doublons[$sc['value_resource_id']]=1;
					//contruction des concepts plus large
					$this->param[$propDst][]=array('type'=>'resource','value'=>$sc['value_resource_id']);
					//pas nécessaire, le niveau = l'ordre de création $this->param['md:hierarchyLevel'][]=$niv."";
					//construction des concepts plus précis
					$this->hier[$sc['value_resource_id']][$propDst][$item['o:id']][]=$niv;
					//remonte la hiérarchie
					$this->id = $sc['value_resource_id'];
					$itemE = array_filter($this->items, function($v, $k) {
						return $v['o:id'] == $this->id;
					}, ARRAY_FILTER_USE_BOTH);
					//si pas trouvé on va le chercher dans omeka
					if(!count($itemE)){
						$itemE[] = $this->getItem($this->id);				
					} 
					foreach ($itemE as $i) {
						if($i[$propSrc]){
							$this->getHierarchie($propSrc, $propDst, $i, $niv+1);
						}				
					}
				}else{
					$this->doublons[$sc['value_resource_id']]++;
				}				
			}
		}
	}    

    /**
     * Fonction pour récupérer les liens sortant d'un item
     *
     * @param array	    $item
     * @param string	$prop
     * @param int	    $niv
	 * 
     * @return	array
     *
     */
    function getItemLiensSortant($item, $prop, $niv=0){
        

        $items = $this->searchAllMulti(array($prop=>array('v'=>$item['o:id'],'t'=>'res')));    

		foreach ($items as $i) {
            if(!$this->doublons[$i['o:id']]){
                //enregistre le noeud du lien 
                $this->reseau['nodes'][]=$i;
                $this->doublons[$i['o:id']]=1;                
            }else{
                $this->doublons[$i['o:id']] ++;                
            }
            //enregistre le lien
            $this->reseau['links'][]=array("source"=>$item['o:id'],"target"=>$i['o:id'],"niv"=>$niv,"value"=>1,"type"=>$prop);
            //recherche les liens de la target
            $this->getItemLiensSortant($i, $prop, $niv+1);
        }
        return $this->reseau;
	}        

    /**
     * Fonction pour récupérer les liens entrant d'un item
     *
     * @param array	    $item
     * @param string	$prop
     * @param int	    $niv
	 * 
     * @return	array
     *
     */
    function getItemLiensEntrant($item, $prop, $niv=0){

        

		foreach ($item[$prop] as $sc) {
			if($sc['type']=="resource"){

                //récupère l'item
                $i = $this->getItem($sc['value_resource_id']);
                if(!$this->doublons[$i['o:id']]){
                    //enregistre le noeud du lien 
                    $this->reseau['nodes'][]=$i;
                    $this->doublons[$i['o:id']]=1;                
                }else{
                    $this->doublons[$i['o:id']] ++;                
                }
                //enregistre le lien
                $this->reseau['links'][]=array("source"=>$i['o:id'],"target"=>$item['o:id'],"niv"=>$niv,"value"=>1,"type"=>$prop);
                $this->getItemLiensEntrant($i, $prop, $niv+1);
            }
        }
        return $this->reseau;

    }    
    

    /**
     * Fonction pour récupérer le réseau d'un item
     *
     * @param array	    $item
     * @param string	$prop
     * @param int	    $niv
     * @param boolean	$init
	 * 
     * @return	array
     *
     */
    function getItemReseau($item, $prop, $niv=0, $init=true){

        if($init){
            $this->doublons = array();
            $this->reseau = array('nodes'=>array(),'links'=>array());    
        }
        $this->reseau['nodes'][]=$item;
        $this->doublons[$item['o:id']]=1;     
        
        $this->getItemLiensEntrant($item, $prop, $niv);
        $this->getItemLiensSortant($item, $prop, $niv);
    
        return $this->reseau;

	}        




    /**
     * Fonction pour ajouter la hierarchie complète d'une item
     *
     * @param string	$propSrc
     * @param string	$propDst
     * @param   string  $qParam
     * @param   string  $qValue
     * 
     * @return	array
     *
     */
    function setHierarchie($propSrc,$propDst,$qParam,$qValue){
		 
		//récupère les items EC	
        $this->items = $this->getAllItem($qParam,$qValue);
		foreach ($this->items as $i) {
			//récupère la hiérarchie de l'item
			$this->param=[];
			$this->doublons=[];
			$this->getHierarchie($propSrc,$propDst,$i, 1);
			//$this->trace('hiérarchie '.$i['dc:title'],$this->param);
			//pour debug
			if($i['o:id']==7138)
				$t = 1;
			if(count($this->param)){
				$param = array_merge($i,$this->setParamAPI($this->param));
				$r = $this->send('items','PATCH',$this->paramsAuth(),$param,'/'.$i['o:id'],true);
				//gestion de l'erreur
				if($r['errors'])
					throw new Exception(json_encode($r['errors']).' : '.$i['dc:title'].' : '.$i['o:id']);
				$this->trace('hiérarchie $propSrc to $propDst  OK '.$i['dc:title'].' : '.$i['o:id']);	
			}else{
				$this->trace('hiérarchie $propSrc AUCUNNE '.$i['dc:title'].' : '.$i['o:id']);	
			}
		}
    }	    
    
    /**
	 * recupère l'identifiant d'une collection
     * 
     * @param string    $lib
     * 
     * @return int
	 */
    function getColId($lib){
        if(!$this->idsCol[$lib]) {
            $col = $this->postItemSet(array('title'=>$lib));
            $this->idsCol[$lib] = $col['o:id'];
        }
        return $this->idsCol[$lib];
    }


}
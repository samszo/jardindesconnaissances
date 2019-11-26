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
    var $language = 'fr';
    var $is_public = 1;
    var $API_IDENT;
    var $API_KEY;

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
                    $param['o:item_set']['o:id']=$v;
                    break;
                case 'resource_template':
                    $param['o:resource_template']['o:id']=$data['resource_template'];
                    break;
                case 'resource_class':
                    $param['o:resource_class']['o:id']=$this->getIdByName($data['resource_class'],'resource_classes');;
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


        $param = $this->setParamAPI($data);
       
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
        
        //gestion de l'erreur
        if($r['errors'])
            throw new Exception(json_encode($r['errors']));

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
     *
     * @return int
     */
    function getIdByName($name,$type){
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
     */
    function send($type, $methode, $get=false, $post=false,$id='',$decode=false){
        $client = new Zend_Http_Client($this->endpoint.$type.$id,array('timeout' => 100));
        if($get)$client->setParameterGet($get);
        if($post){
            //pour forcer le header
            $client->setRawData(json_encode($post), 'application/json');
        }
        if($methode=='PATCH')$client->setHeaders('Content-Type','application/json');
        $response = $client->request($methode);
        return $decode ? json_decode($response->getBody(),true) : $response->getBody();

    }

    function paramsAuth(){
        //initialisation des paramètres GET pour l'authentification
        return array('key_identity' => $this->API_IDENT, 'key_credential' => $this->API_KEY);
    }
    

}
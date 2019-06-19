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

	public function __construct($idBase=false, $bTrace=false)
    {
    	parent::__construct($idBase,$bTrace);
        
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
    }
    
    /**
     * Enregistre une annotation avec l'API
     *
     * @param array $data
     *
     */
    function postAnnotation($data){
        //initialisation des paramètres
        $param['o:resource_template']['o:id']=6;
        $param['o:resource_class']['o:id']=136;
        $param['oa:motivatedBy'][0]['@value']= 'tagging';
        $param['oa:motivatedBy'][0]['property_id']= 248;
        $param['oa:motivatedBy'][0]['customvocab']= 10;
        $param['oa:hasBody'][0]['rdf:value'][0]['@value']='test';
        $param['oa:hasBody'][0]['rdf:value'][0]['property_id']=195;
        $param['oa:hasBody'][0]['rdf:value'][0]['type']='literal';
        $param['oa:hasBody'][0]['oa:hasPurpose'][0]['@value']='classifying';
        $param['oa:hasBody'][0]['oa:hasPurpose'][0]['property_id']=241;
        $param['oa:hasBody'][0]['oa:hasPurpose'][0]['type']='customvocab:10';
        $param['oa:hasTarget'][0]['oa:hasSource'][0]['property_id']=244;
        $param['oa:hasTarget'][0]['oa:hasSource'][0]['type']='resource';
        $param['oa:hasTarget'][0]['oa:hasSource'][0]['value_resource_id']=1248;
        $param['oa:hasTarget'][0]['rdf:type'][0]['@value']='dctype:Text';
        $param['oa:hasTarget'][0]['rdf:type'][0]['property_id']= 191;
        $param['oa:hasTarget'][0]['rdf:type'][0]['type']='customvocab:13';
        $param['oa:hasTarget'][0]['rdf:value'][0]['@value']='test';
        $param['oa:hasTarget'][0]['rdf:value'][0]['property_id']=195;
        $param['oa:hasTarget'][0]['rdf:value'][0]['type']='literal';
        $param['o:is_public']= 1;

        return $this->send('annotations','POST',$this->paramsAuth(),$param);
    }

    /**
     * Enregistre un item avec l'API
     *
     * @param array $data
     * @param boolean   $existe
     * @param boolean   $patch
     *
     * @return array
     */
    function postItem($data, $existe=true, $patch=true){

        //initialisation des paramètres POST
        $param['o:resource_template']['o:id']=$data['resource_template'];
        if($data['resource_class'])$param['o:resource_class']['o:id']=$data['resource_class'];
        $param['dcterms:title'][0]['property_id']= 1;
        $param['dcterms:title'][0]['type']= 'literal';
        $param['dcterms:title'][0]['@value']= $data['title'];
        $param['dcterms:title'][0]['@language']= 'fr';
        $param['dcterms:title'][0]['is_public']= 1;
        $param['o:is_public']= 1;
        if($data['item_set'])$param['o:item_set'][0]= $data['item_set'];
        if($data['isReferencedBy']){
            $param['dcterms:isReferencedBy'][0]['property_id']= 35;
            $param['dcterms:isReferencedBy'][0]['type']= 'uri';
            $param['dcterms:isReferencedBy'][0]['@id']= $data['isReferencedBy']['uri'];
            $param['dcterms:isReferencedBy'][0]['o:label']= $data['isReferencedBy']['label'];
            $param['dcterms:isReferencedBy'][0]['is_public']= 1;    
        }
        if($data['type']){
            $param['rdf:type'][0]['property_id']= 191;
            $param['rdf:type'][0]['type']= 'literal';
            $param['rdf:type'][0]['@value']= $data['type'];
            $param['rdf:type'][0]['is_public']= 1;    
        }
        if($data['prefLabel']){
            $param['skos:prefLabel'][0]['property_id']= 392;
            $param['skos:prefLabel'][0]['type']= 'literal';
            $param['skos:prefLabel'][0]['@value']= $data['prefLabel'];
            $param['skos:prefLabel'][0]['is_public']= 1;    
        }
        if($data['inScheme']){
            $param['skos:inScheme'][0]['property_id']= 374;
            $param['skos:inScheme'][0]['type']= 'resource';
            $param['skos:inScheme'][0]['value_resource_id']= $data['inScheme']['id'];
            $param['skos:inScheme'][0]['is_public']= 1;    
            $param['skos:inScheme'][1]['property_id']= 374;
            $param['skos:inScheme'][1]['type']= 'literal';
            $param['skos:inScheme'][1]['@value']= $data['inScheme']['txt'];
            $param['skos:inScheme'][1]['is_public']= 1;    
        }        
        $r = null;
        if($existe){
            $r = json_decode($this->search($data,'items'),true)[0];
        }
        if($r==null)
            $r = json_decode($this->send('items','POST',$this->paramsAuth(),$param),true);
        elseif($patch)
            $r = json_decode($this->send('items','PATCH',$this->paramsAuth(),$param,'/'.$r['o:id']),true);
        
        /*
        dcterms:description[0][property_id]: 4
        dcterms:description[0][type]: literal
        dcterms:description[0][@language]: 
        dcterms:description[0][@value]: 
        dcterms:description[0][is_public]: 1
        */    
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
            $param['o:resource_class']['o:id']="";
            $param['dcterms:title'][0]['property_id']= 1;
            $param['dcterms:title'][0]['type']='literal';
            $param['dcterms:title'][0]['@value'] = $data['title'];
            $param['dcterms:title'][0]['@language']= '';
            $param['dcterms:title'][0]['is_public']= 1;
            $param['o:is_public']= 1;
            $param['o:is_open']= 1;
            $r = json_decode($this->send('item_sets','POST',$this->paramsAuth(),$param),true);
        }
        return $r;
    }

    /**
     * cherche un objet
     *
     * @param array     $data
     * @param string    $type
     *
     */
    function search($data,$type='items'){
        //initialisation des paramètres POST
        $param['property'][0]['property']= 1;
        $param['property'][0]['type']='eq';
        $param['property'][0]['text']=$data['title'];
        $r = $this->send($type,'GET',$param);
        return $r;
    }

    /**
     * Récupère un vocabulaire
     *
     * @param array $data
     *
     */
    function getVocab($data){
        //initialisation des paramètres GET
        $param = $this->paramsAuth();
        $param['prefix']="skos";

        return $this->send('vocabularies','GET',$param);
    }

    /**
     * Récupère les propriétés de vocabulaire
     *
     * @param array $data
     *
     */
    function getProps($data){
        //initialisation des paramètres GET
        $param = $this->paramsAuth();
        $param['vocabulary_prefix']=$data['prefix'];//"skos";

        return $this->send('properties','GET',$param);
    }


    /**
     * Envoie une requête à l'API
     *
     * @param string    $type
     * @param string    $methode
     * @param array     $get
     * @param array     $post
     * @param array     $id
     *
     */
    function send($type, $methode, $get=false, $post=false,$id=''){
        $client = new Zend_Http_Client($this->endpoint.$type.$id,array('timeout' => 30));
        if($get)$client->setParameterGet($get);
        if($post){
            //pour forcer le header
            $client->setRawData(json_encode($post), 'application/json');
        }
        if($methode=='PATCH')$client->setHeaders('Content-Type','application/json');
        $response = $client->request($methode);
        return $response->getBody();

    }

    function paramsAuth(){
        //initialisation des paramètres GET pour l'authentification
        return array('key_identity' => OMEKA_API_IDENT, 'key_credential' => OMEKA_API_KEY);
    }
    

}
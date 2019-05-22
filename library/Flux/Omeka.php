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
     *
     */
    function postItem($data){
        //initialisation des paramètres POST
        $param['o:resource_template']['o:id']="";
        $param['o:resource_class']['o:id']="";
        $param['dcterms:title'][0]['property_id']= 1;
        $param['dcterms:title'][0]['@value']= 'test';
        $param['dcterms:title'][0]['@language']= '';
        $param['dcterms:title'][0]['is_public']= 1;
        $param['o:is_public']= 1;

        /*
        dcterms:description[0][property_id]: 4
        dcterms:description[0][type]: literal
        dcterms:description[0][@language]: 
        dcterms:description[0][@value]: 
        dcterms:description[0][is_public]: 1
        */    
        return $this->send('items','POST',$this->paramsAuth(),$param);
    }

    /**
     * Envoie une requête à l'API
     *
     * @param string    $type
     * @param string    $methode
     * @param array     $get
     * @param array     $post
     *
     */
    function send($type, $methode, $get=false, $post=false){
        $client = new Zend_Http_Client($this->endpoint.$type,array('timeout' => 30));
        if($get)$client->setParameterGet($get);
        if($post){
            //pour forcer le header
            $client->setRawData(json_encode($post), 'application/json');
        }
        $response = $client->request($methode);
        return $response->getBody();

    }

    function paramsAuth(){
        //initialisation des paramètres GET pour l'authentification
        return array('key_identity' => OMEKA_API_IDENT, 'key_credential' => OMEKA_API_KEY);
    }
    

}
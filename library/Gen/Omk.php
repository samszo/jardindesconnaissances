<?php
/**
 * Gen_Omk
 * Classe qui gère le generateur dans omeka S
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Gen_Omk extends Flux_Site{

    var $dbOmk = false;
    var $omk = false;
    var $idsCol = [];

	
    /**
     * Constructeur de la classe
     *
     * @param  string   $idBase
     * @param  boolean  $bTrace
     * @param  boolean  $bCache
     * 
     */
	public function __construct($idBase=false, $bTrace=false, $bCache=true)
    {
        parent::__construct($idBase, $bTrace, $bCache);    	

    }

    /**
    * initialise omeka
    *   @param string   $endpoint
    *   @param string   $apiIdent
    *   @param string   $apiKey
    *   @return object

     */
    function initOmeka($endpoint, $apiIdent, $apiKey){
        $o = new Flux_Omeka();
        $o->endpoint = $endpoint;
        $o->API_IDENT = $apiIdent;
        $o->API_KEY = $apiKey;
        $this->omk = $o;
        return $o;
    }

    /**
     * initialise les vocabulaire
     */
    function initVocabulaires(){
        /*TODO*/return false;
        if(!$this->omk)$this->omk=new Flux_Omeka($this->dbOmk);
        /*TODO:l'importation par l'API ne marche pas
        $r[]=$this->omk->setVocab(array(
            'url'=>'https://www.w3.org/ns/ma-ont.rdf'
            ,'prefix'=>'ma'
            ,'format'=>'guess'
            ,'ns_uri'=>'http://www.w3.org/ns/ma-ont#'
            ,'label'=>'Ontology for Media Resources'
        ));
        $r[]=$this->omk->setVocab(array(
            'url'=>'https://semanticweb.cs.vu.nl/2009/11/sem/sem.rdf'
            ,'prefix'=>'sem'
            ,'format'=>'rdfxml'
            ,'ns_uri'=>'http://semanticweb.cs.vu.nl/2009/11/sem/'
            ,'label'=>'The Simple Event Model (SEM)'
        ));
        $r[]=$this->omk->setVocab(array(
            'url'=>'https://jardindesconnaissances.univ-paris8.fr/onto/jdc.ttl'
            ,'prefix'=>'jdc'
            ,'format'=>'guess'
            ,'ns_uri'=>'https://jardindesconnaissances.univ-paris8.fr/onto/'
            ,'label'=>'Jardin des connaissances'
        ));
        $r[]=$this->omk->setVocab(array(
            'url'=>'https://jardindesconnaissances.univ-paris8.fr/onto/ieml/ieml.ttl'
            ,'prefix'=>'ieml'
            ,'format'=>'guess'
            ,'ns_uri'=>'https://jardindesconnaissances.univ-paris8.fr/onto/ieml'
            ,'label'=>'Information Economic Meta Language'
        ));
        */

        //enregistre les templates
        $r[] = $this->omk->postResourceTemplate(array(
            'label'=>'Position sémantique'
            ,'class'=>'SemanticPosition'
            ,'props'=>array(
                array('label'=>'title','required'=>1,'type'=>'literal')
                ,array('label'=>'creationDate','required'=>1,'type'=>'literal')
                ,array('label'=>'hasCreator','required'=>1,'type'=>'literal')
                ,array('label'=>'hasRating','required'=>1,'type'=>'literal')
                ,array('label'=>'isRatingOf','required'=>1,'type'=>'resource:item')
                ,array('label'=>'ratingScaleMax','required'=>1,'type'=>'literal')
                ,array('label'=>'ratingScaleMin','required'=>1,'type'=>'literal')
                ,array('label'=>'hasRatingSystem','required'=>1,'type'=>'resource:item')
                ,array('label'=>'locationLatitude','required'=>1,'type'=>'literal')
                ,array('label'=>'locationLongitude','required'=>1,'type'=>'literal')
                ,array('label'=>'hasSource','required'=>1,'type'=>'resource:item')
                ,array('label'=>'frameHeight','required'=>1,'type'=>'literal')
                ,array('label'=>'frameWidth','required'=>1,'type'=>'literal')
                ,array('label'=>'isFragmentOf','required'=>1,'type'=>'resource:item')
                ,array('label'=>'distanceCenter','required'=>1,'type'=>'literal')
                ,array('label'=>'distanceConcept','required'=>1,'type'=>'literal')
                ,array('label'=>'hasActor','required'=>1,'type'=>'resource')
                ,array('label'=>'hasDoc','required'=>1,'type'=>'literal')//litteral pour la recherche
                ,array('label'=>'hasConcept','required'=>1,'type'=>'resource:item')
                ,array('label'=>'hasRapport','required'=>1,'type'=>'resource:item')
                ,array('label'=>'x','required'=>1,'type'=>'literal')
                ,array('label'=>'y','required'=>1,'type'=>'literal')
                ,array('label'=>'xRatingValue','required'=>1,'type'=>'literal')
                ,array('label'=>'yRatingValue','required'=>1,'type'=>'literal')
                ,array('label'=>'degradColors','required'=>1,'type'=>'literal')
                ,array('label'=>'degradName','required'=>1,'type'=>'literal')
            )
        ));
        return $r;
    }

    /**
	 * initialise les collections Gen
     * 
     * @return array
	 */
    function initCollectionGen(){
        $arr = array(
            array('titre'=>'gen_concept', 'class'=>'TermElement'),
            array('titre'=>'gen_syntagme', 'class'=>'TermElement'),
            array('titre'=>'gen_pronom_sujet_indefini', 'class'=>'TermElement'),
            array('titre'=>'gen_pronom_sujet', 'class'=>'TermElement'),
            array('titre'=>'gen_pronom_complement', 'class'=>'TermElement'),
            array('titre'=>'gen_negation', 'class'=>'TermElement'),
            array('titre'=>'gen_verbe', 'class'=>'TermElement'),
            array('titre'=>'gen_substantif', 'class'=>'TermElement'),
            array('titre'=>'gen_adjectif', 'class'=>'TermElement'),
            array('titre'=>'gen_generateur', 'class'=>'TermElement'),
            array('titre'=>'gen_negation', 'class'=>'TermElement'),
            array('titre'=>'gen_determinant', 'class'=>'TermElement'),            
        );
        foreach ($arr as $v) {
            $r[$v['titre']] = $this->omk->postItemSet(array('title'=>$v['titre'],'resource_class'=>$v['class']));
        }
        return $r;
    }

    /**
	 * recupère les collections 
     * 
     * @param string    $title
     * 
     * @return array
	 */
    function getCollection($title){

        $this->omk = $this->omk ? $this->omk : new Flux_Omeka($this->dbOmk);
        $r = json_decode($this->omk->search(array('title'=>$title),'item_sets'),true)[0]; 
        //initialise la collection si vide
        if(!$r['o:id']){
            $r = $this->initCollectionGen();
            $r = $r[$title];
        }
        $this->idsCol[$title]=$r['o:id'];             

        //renvoie le lien vers les item de la collection
        return $r['o:items']['@id'];

    }


    /**
	 * importe dans omeka les données de la base générateur
     * 
     * pour gérer la reprise d'importation
     * @param string    $refL 
     * @param int       $inext 
     * @param array     $arrC 
     * 
     * @return string
	 */
    function importBaseGen($refL="", $inext=-1, $arrC=false){    
        $this->trace("DEBUT ".__METHOD__);
        //execution infinie
        set_time_limit(0);
        //augmente la mémoire pour le cache
        ini_set("memory_limit",'6400M');

        /*pour le debugage
        error_reporting(E_ALL);
        $this->trace("Param OMK Auth ",$this->omk->paramsAuth());
        $r = $this->getConcept(array('refC'=>'test','title'=>'test'));
        return;
        */
        $this->omk->bTrace = false;//$this->bTrace;        


        $this->existe = false;//mettre true pour corriger une importation

        if(!$arrC){
            $this->trace("récupère tous les concepts et leurs class");
            $c = "getAllConceptDoublons";
            if(!$this->bCache)
                $this->cache->remove($c);
            $this->trace("recupère le cache");
            $arrC = $this->cache->load($c);
            if(!$arrC) {
                $this->trace("Requete Concepts et class");
                $dbC = new Model_DbTable_Gen_concepts($this->db);        
                $arrC = $dbC->getAllConceptDoublons();
                $this->cache->save($arrC, $c);
            }
            $this->trace("Concepts et class récupéré = ".count($arrC));
        }

        $oTitle = "";
        $i = 0;
        $this->trace("Boucle sur les concepts = ".count($arrC));
        foreach ($arrC as $c) {
            //pour la reprise de l'importation
            if($refL){                
                if($c['refL']==$refL){
                    $this->importBaseGen("", $i+1, $arrC); 
                    $this->trace("FIN TROUVE ".$refL);               
                }
            }else{
                if($i > $inext){
                    if($oTitle!=$c['title']){
                        //récupère l'item concept
                        $cpt = $this->getConcept($c);
                        $oTitle=$c['title'];                
                        $this->trace($i.' concept : '.$c['refC'].' '.$cpt['o:title'].' o:id='.$cpt['o:id']);
                    }
                    //création des class
                    $item = $this->addClass($c,$cpt);
                    $this->trace($i.' class : '.$c['refL'].' '.$item['o:title'].' o:id='.$item['o:id']);   
                }    
            }    
            $i++;
        }

		$this->trace("FIN ".__METHOD__);
    }

    /**
	 * ajoute une class à partir des données suivantes
    *    title
    *    , refC
    *    , refL
    *    , arrTypeConcept
    *    , arrTypeLien
    *    , description
    *    , lexTermElement 
    *    , lexTermElement_e
    *    , grammaticalGender
    *    , pluralNumberForm
    *    , pluralNumberForm_f
    *    , pluralNumberForm_fe
    *    , pluralNumberForm_m
    *    , pluralNumberForm_me
    *    , singularNumberForm
    *    , singularNumberForm_f
    *    , singularNumberForm_fe
    *    , singularNumberForm_m
    *    , singularNumberForm_me
    *    , aspect
     * 
     * @param array $v
     * @param array $cpt
     * 
     * @return string
	 */
    function addClass($v, $cpt=false){

		//$this->trace("DEBUT ".__METHOD__);

        if($v['description']=='' &&
            $v['lexTermElement']=='' &&
            $v['lexTermElement_e']=='' &&
            $v['grammaticalGender']=='' &&
            $v['pluralNumberForm']=='' &&
            $v['pluralNumberForm_f']=='' &&
            $v['pluralNumberForm_fe']=='' &&
            $v['pluralNumberForm_m']=='' &&
            $v['pluralNumberForm_me']=='' &&
            $v['singularNumberForm']=='' &&
            $v['singularNumberForm_f']=='' &&
            $v['singularNumberForm_fe']=='' &&
            $v['singularNumberForm_m']=='' &&
            $v['singularNumberForm_me']=='' &&
            $v['aspect']=='' 
        ) return 'Pas de données';

        if(!$this->omk)$this->omk=new Flux_Omeka($this->dbOmk);

        //création/récupération du concept
        if(!$cpt){
            $cpt =  $this->getConcept($v);
        }

        //création de la class
        $param = array(
            'resource_class'=>'gen_class'
            ,'dcterms:isReferencedBy'=>$v['refL']
            ,'dcterms:isPartOf'=>array(array('type'=>'resource','value'=>$cpt['o:id']))
            ,'dcterms:type'=>$v['arrTypeLien']
        );

        //mise à jour des données de la class suivant les valeurs renseignées
        $arrLien = explode(',',$v['arrTypeLien']);
        foreach ($arrLien as $l) {
            switch ($l) {
                case 'adjectif':
                    $param['dcterms:title'] =$v['lexTermElement'].$v['singularNumberForm_f'];
                    $param['lexinfo:termElement'] = $v['lexTermElement'];
                    $param['bibo:prefixName']=$v['lexTermElement_e'];
                    $param['item_set'][]=$this->getColId('gen_adjectif');
                    $param['lexinfo:pluralNumberForm'][]=$v['pluralNumberForm_f'];
                    $param['lexinfo:pluralNumberForm'][]=$v['pluralNumberForm_m'];
                    $param['lexinfo:singularNumberForm'][]=$v['singularNumberForm_f'];
                    $param['lexinfo:singularNumberForm'][]=$v['singularNumberForm_m'];
                    break;            
                case 'generateur':
                    $param['dcterms:title'] =uniqid('gen');
                    $param['dcterms:description']=$v['description'];
                    $param['item_set'][]=$this->getColId('gen_generateur');
                    break;            
                case 'pronom':
                    $param['dcterms:title']=$v['lexTermElement'].' - '.$v['lexTermElement_e'];
                    $param['item_set'][]=$this->getColId('gen_pronom_'.$l);
                    $param['dcterms:description']=$v['description'];
                    $param['lexinfo:termElement'][] = $v['lexTermElement'];
                    $param['lexinfo:termElement'][] = $v['lexTermElement_e'];
                    break;            
                case 'substantif':
                    $param['dcterms:title']=$v['lexTermElement'];
                    $param['lexinfo:termElement'] = $v['lexTermElement'];
                    $param['lexinfo:gender'] = $v['grammaticalGender'];
                    $param['lexinfo:singularNumberForm'] = $v['singularNumberForm'];
                    $param['lexinfo:pluralNumberForm'] = $v['pluralNumberForm'];
                    $param['bibo:prefixName']=$v['lexTermElement_e'];
                    $param['item_set']=$this->getColId('gen_substantif');
                    break;            
                case 'syntagme':
                    $param['dcterms:title']=$v['lexTermElement'];
                    $param['item_set'][]=$this->getColId('gen_syntagme');
                    $param['dcterms:description']=$v['description'];
                    break;            
                case 'verbe':
                    $param['dcterms:title']=$v['lexTermElement'];
                    $param['bibo:prefixName']=$v['lexTermElement_e'];
                    $param['item_set']=$this->getColId('gen_verbe');
                    $param['lexinfo:aspect']=$v['aspect'];                    
                    break;            
                case 'negation':
                    $param['dcterms:title']=$v['lexTermElement'];
                    $param['item_set']=$this->getColId('gen_negation');
                    $param['dcterms:description']=$v['description'];
                    break;  
                case 'determinant':
                    $param['dcterms:title'] = $v['singularNumberForm_f'].' - '.$v['pluralNumberForm_f'];
                    $param['item_set']=$this->getColId('gen_determinant');
                    $param['dcterms:description']=$v['description'];
                    $param['lexinfo:pluralNumberForm'][]=$v['pluralNumberForm_f'];
                    $param['lexinfo:pluralNumberForm'][]=$v['pluralNumberForm_m'];
                    $param['lexinfo:pluralNumberForm'][]=$v['pluralNumberForm_fe'];
                    $param['lexinfo:pluralNumberForm'][]=$v['pluralNumberForm_me'];
                    $param['lexinfo:singularNumberForm'][]=$v['singularNumberForm_f'];
                    $param['lexinfo:singularNumberForm'][]=$v['singularNumberForm_m'];
                    $param['lexinfo:singularNumberForm'][]=$v['singularNumberForm_fe'];
                    $param['lexinfo:singularNumberForm'][]=$v['singularNumberForm_me'];
                    break;                                    
            }
        }       
        return $this->omk->postItem($param, $this->existe);

    }

    /**
	 * recupère un concept
     * 
     * @param array    $v
     * 
     * @return array
	 */
    function getConcept($v){
        $this->trace("DEBUT ".__METHOD__.' '.$v['title']);
        $arr = array(
            'dcterms:title'=>trim($v['title'])
            ,'dcterms:isReferencedBy'=>$v['refC']
            ,'item_set'=>$this->getColId('gen_concept')
        );
        if($v['arrTypeConcept']=='carac'){
            $arr['dcterms:title']=$v['arrTypeConcept'].trim($v['title']);
            $arr['foaf:member']='carac';
        }
        return $this->omk->postItem($arr, $this->existe);    
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
            $col = $this->omk->postItemSet(array('title'=>$lib));
            $this->idsCol[$lib] = $col['o:id'];
        }
        return $this->idsCol[$lib];
    }

}
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

    var $temps = array(
        array('num'=>1,'lib'=>'indicatif présent'),
        array('num'=>2,'lib'=>'indicatif imparfait'),
        array('num'=>3,'lib'=>'passé simple'),
        array('num'=>4,'lib'=>'futur simple'),
        array('num'=>5,'lib'=>'conditionnel présent'),
        array('num'=>6,'lib'=>'subjonctif présent'),
        array('num'=>8,'lib'=>'participe présent'),
        array('num'=>9,'lib'=>'infinitif'),
    );
    var $termType = array('a','v','m','s');
    var $sepType = '-';
    var $sepTerm = '_';

	
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
            array('titre'=>'gen_conjugaison', 'class'=>'TermElement'),
            array('titre'=>'gen_conjugaison_temps', 'class'=>'TermElement'),                        
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
    function getCollection($title, $id=false){

        $this->omk = $this->omk ? $this->omk : new Flux_Omeka($this->dbOmk);
        $r = json_decode($this->omk->search(array('title'=>$title),'item_sets'),true)[0]; 
        //initialise la collection si vide
        if(!$r['o:id']){
            $this->omk->idsCol = $this->initCollectionGen();
            $r = $this->omk->idsCol[$title];
        }

        //renvoie le lien vers les item de la collection
        return $r['o:items']['@id'];

    }


    /**
	 * importe dans omeka les données de la base générateur
     * 
     * pour gérer la reprise d'importation
     * @param string    $ref 
     * @param int       $inext 
     * @param array     $arrC 
     * @param boolean   $bConceptOnly 
     * 
     * @return string
	 */
    function importBaseGen($ref="", $inext=-1, $arrC=false, $bConceptOnly=true){    
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

        $this->RT['genex_Concept']=$this->omk->getIdByName('genex_Concept','resource_templates');
        $this->RT['genex_Term']=$this->omk->getIdByName('genex_Term','resource_templates');
        $this->RT['genex_Generateur']=$this->omk->getIdByName('genex_Generateur','resource_templates');
                

        $this->existe = false;//mettre true pour corriger une importation

        $dbC = new Model_DbTable_Gen_concepts($this->db);        
        if(!$arrC){
            $this->trace("récupère tous les concepts et leurs class");
            $c = "getAllConceptDoublons".$bConceptOnly;
            if(!$this->bCache)
                $this->cache->remove($c);
            $this->trace("recupère le cache");
            $arrC = $this->cache->load($c);
            if(!$arrC) {
                $this->trace("Requete Concepts et class");
                if($bConceptOnly){
                    //on récupère uniquement les concept
                    $arrC = $dbC->getOnlyConceptDoublons();
                }else{
                    //on récupère les concept et leur définition
                    $arrC = $dbC->getAllConceptDoublons();
                }

                $this->cache->save($arrC, $c);
            }
            $this->trace("Concepts et class récupéré = ".count($arrC));
        }

        $oTitle = "";
        $i = 0;
        $this->trace("Boucle sur les concepts = ".count($arrC));
        foreach ($arrC as $c) {
            //pour la reprise de l'importation
            if($ref){                
                if($c['refC']==$ref){
                    $this->importBaseGen("", $i+1, $arrC, $bConceptOnly); 
                    $this->trace("FIN TROUVE ".$ref);               
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
                    if($bConceptOnly){
                        //on récupère uniquement les concept
                        $arrClass = $dbC->getClassConceptDoublons(trim($c['title']));
                        foreach ($arrClass as $cls) {
                            $item = $this->addClass($cls,$cpt);
                            $this->trace($i.' class : '.$cls['refL'].' '.$item['o:title'].' o:id='.$item['o:id']);   
                        }
                    }else{
                        $item = $this->addClass($c,$cpt);
                        $this->trace($i.' class : '.$c['refL'].' '.$item['o:title'].' o:id='.$item['o:id']);   
                    }
    
                }    
            }    
            $i++;
        }

		$this->trace("FIN ".__METHOD__);
    }



    /**
	 * importe dans omeka les données de conjugaison de la base générateur
     * 
     * pour gérer la reprise d'importation
     * @param string    $aspects 
     * @param int       $inext 
     * @param array     $arrC 
     * 
     * @return string
	 */
    function importBaseGenConj($aspects="", $inext=-1, $arrC=false){    
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
            $this->trace("récupère toutes les conjugaisons et leurs terminaisons");
            $c = "getAllConjDoublons";
            if(!$this->bCache)
                $this->cache->remove($c);
            $this->trace("recupère le cache");
            $arrC = $this->cache->load($c);
            if(!$arrC) {
                $this->trace("Requete Conjugaisons et terminaisons");
                $dbC = new Model_DbTable_Gen_conjugaisons($this->db);        
                $arrC = $dbC->getAllConjDoublons();
                $this->cache->save($arrC, $c);
            }
            $this->trace("Conjugaisons récupérées = ".count($arrC));
        }

        $oTitle = "";
        $i = 0;
        $j = 0;
        $this->trace("Boucle sur les conjugaisons = ".count($arrC));
        foreach ($arrC as $c) {
            //pour la reprise de l'importation
            if($aspects){                
                if($c['aspects']==$aspects){
                    $this->importBaseGenConj("", $i+37, $arrC); 
                    $this->trace("FIN TROUVE ".$aspects);
                    return true;               
                }
            }else{
                if($i > $inext){
                    if($oTitle!=$c['modele']){
                        //création du titre
                        $c['title'] = $c['modele'];
                        //récupère l'item concept                        
                        $conj = $this->getConjugaison($c);
                        if($conj['errors'])throw new Exception($conj['errors']['error']);
                        $oTitle=$c['modele'];                
                        $this->trace($i.' concept : '.$c['aspects'].' '.$conj['o:title'].' o:id='.$conj['o:id']);
                        //mis à jour des verbes avec l'identifiant de conjugaison
                        $idsConj = explode(',',$c['aspects']);
                        foreach ($idsConj as $id) {
                            //récupère les verbes avec cet aspect et sans la conjugaison
                            $arrV = $this->omk->searchItems(array(
                                'dcterms:type'=>array('v'=>'verbe')
                                ,'lexinfo:aspect'=>array('v'=>$id)
                                //,'lexinfo:aspect'=>array('t'=>'nres','v'=>$conj['o:id'])                                
                                ));
                            foreach ($arrV as $vrb) {
                                $p = $this->omk->setParamAPI(array('lexinfo:aspect'=>array(array('type'=>'resource','value'=>$conj['o:id']))));
                                $vrb['lexinfo:aspect'][]=$p['lexinfo:aspect'][0];
                                //ajout el'identifiant de conjugaison dans l'aspect
                                $p = $this->omk->send('items','PUT',$this->omk->paramsAuth(),$vrb,'/'.$vrb['o:id'],true);
                            }
                        }
                        
                    }
                    //vérifie qu'il n'y a pas d'erreur de position
                    if($c['num']>0)
                        $t = 1;
                    //création des terminaisons par temps
                    $posi = 0;
                    for ($k=0; $k < count($this->temps); $k++) { 
                        $t = $this->temps[$k];
                        $param=array();
                        $param['dcterms:title'] = $conj['o:title'].' '.$t['lib'];
                        $param['lexinfo:mood'] = $t['lib'];
                        $param['dcterms:isPartOf']=array(array('type'=>'resource','value'=>$conj['o:id']));
                        $param['dcterms:isReferencedBy'] = $conj['o:id'].'_'.$t['num'];
                        $param['item_set'][]=$this->getColId('gen_conjugaison_temps');
                        $param['lexinfo:firstPersonForm'][]=$arrC[$i+$posi]['lib'];
                        if($t['num'] < 8){
                            $param['lexinfo:firstPersonForm'][]=$arrC[$i+(1+$posi)]['lib'];
                            $param['lexinfo:secondPersonForm'][]=$arrC[$i+(2+$posi)]['lib'];
                            $param['lexinfo:secondPersonForm'][]=$arrC[$i+(3+$posi)]['lib'];
                            $param['lexinfo:thirdPersonForm'][]=$arrC[$i+(4+$posi)]['lib'];
                            $param['lexinfo:thirdPersonForm'][]=$arrC[$i+(5+$posi)]['lib'];        
                            $posi += 6;
                        }else
                            $posi += 1;

                        $item = $this->omk->postItem($param, $this->existe);
                        $this->trace($i.' temps : '.$item['o:title'].' o:id='.$item['o:id']);
                    }
                    //passe au modèle suivant
                    $inext = $i + 37;
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
            'dcterms:isReferencedBy'=>$v['refL']
            ,'genex:hasConcept'=>array(array('type'=>'resource','value'=>$cpt['o:id']))
        );

        //mise à jour des données de la class suivant les valeurs renseignées
        $arrLien = explode(',',$v['arrTypeLien']);
        foreach ($arrLien as $l) {
            switch ($l) {
                case 'adjectif':
                    $param['ressource_template'] = $this->RT['genex_Term'];
                    $param['resource_class'] = 'genex:Term';
                    $param['dcterms:title'] =$v['lexTermElement'].$v['singularNumberForm_f'];
                    $param['genex:hasPrefix']=$v['lexTermElement'];
                    $param['genex:hasElision']=$v['lexTermElement_e'];
                    //$param['item_set'][]=$this->getColId('gen_adjectif');
                    $param['lexinfo:pluralNumberForm'][]=$v['pluralNumberForm_f'];
                    $param['lexinfo:pluralNumberForm'][]=$v['pluralNumberForm_m'];
                    $param['lexinfo:singularNumberForm'][]=$v['singularNumberForm_f'];
                    $param['lexinfo:singularNumberForm'][]=$v['singularNumberForm_m'];
                    break;            
                case 'generateur':
                    $param['dcterms:title'] =uniqid('gen');
                    $param['dcterms:description']=$v['description'];
                    //$param['item_set'][]=$this->getColId('gen_generateur');
                    $param['ressource_template'] = $this->RT['genex_Generateur'];
                    $param['resource_class'] = 'genex:Generateur';
                    break;            
                case 'pronom':
                    $param['dcterms:title']=$v['lexTermElement'].' - '.$v['lexTermElement_e'];
                    //$param['item_set'][]=$this->getColId('gen_pronom_'.$l);
                    $param['dcterms:description']=$v['description'];
                    $param['genex:hasPrefix']=$v['lexTermElement'];
                    $param['genex:hasElision']=$v['lexTermElement_e'];
                    $param['resource_class'] = 'genex:Pronom';
                    break;            
                case 'substantif':
                    $param['dcterms:title']=$v['lexTermElement'];
                    $param['genex:hasPrefix']=$v['lexTermElement'];
                    $param['genex:hasElision']=$v['lexTermElement_e'];
                    $param['lexinfo:gender'] = $v['grammaticalGender'];
                    $param['lexinfo:singularNumberForm'] = $v['singularNumberForm'];
                    $param['lexinfo:pluralNumberForm'] = $v['pluralNumberForm'];
                    //$param['item_set']=$this->getColId('gen_substantif');
                    $param['ressource_template'] = $this->RT['genex_Term'];
                    $param['resource_class'] = 'genex:Term';
                    break;            
                case 'syntagme':
                    $param['dcterms:title']=$v['lexTermElement'];
                    $param['genex:hasPrefix']=$v['lexTermElement'];
                    $param['genex:hasElision']=$v['lexTermElement_e'];
                    //$param['item_set'][]=$this->getColId('gen_syntagme');
                    $param['dcterms:description']=$v['description'];
                    $param['ressource_template'] = $this->RT['genex_Term'];
                    $param['resource_class'] = 'genex:Term';
                    break;            
                case 'verbe':
                    $param['dcterms:title']=$v['lexTermElement'];
                    $param['genex:hasPrefix']=$v['lexTermElement'];
                    $param['genex:hasElision']=$v['lexTermElement_e'];
                    //$param['item_set']=$this->getColId('gen_verbe');
                    $param['genex:hasConjugaison']=$v['aspect'];                    
                    $param['ressource_template'] = $this->RT['genex_Term'];
                    $param['resource_class'] = 'genex:Term';
                    break;            
                case 'negation':
                    $param['dcterms:title']=$v['lexTermElement'];
                    //$param['item_set']=$this->getColId('gen_negation');
                    $param['dcterms:description']=$v['description'];
                    $param['resource_class'] = 'genex:Negation';
                    break;  
                case 'determinant':
                    $param['dcterms:title'] = $v['singularNumberForm_f'].' - '.$v['pluralNumberForm_f'];
                    //$param['item_set']=$this->getColId('gen_determinant');
                    $param['dcterms:description']=$v['description'];
                    $param['lexinfo:pluralNumberForm'][]=$v['pluralNumberForm_f'];
                    $param['lexinfo:pluralNumberForm'][]=$v['pluralNumberForm_m'];
                    $param['lexinfo:pluralNumberForm'][]=$v['pluralNumberForm_fe'];
                    $param['lexinfo:pluralNumberForm'][]=$v['pluralNumberForm_me'];
                    $param['lexinfo:singularNumberForm'][]=$v['singularNumberForm_f'];
                    $param['lexinfo:singularNumberForm'][]=$v['singularNumberForm_m'];
                    $param['lexinfo:singularNumberForm'][]=$v['singularNumberForm_fe'];
                    $param['lexinfo:singularNumberForm'][]=$v['singularNumberForm_me'];
                    $param['resource_class'] ='genex:Determinant';
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
            'dcterms:title'=>$v['arrTypeConcept'].$this->sepType.trim($v['title'])
            ,'dcterms:isReferencedBy'=>$v['refC']
            //,'item_set'=>$this->getColId('gen_concept')
            ,'resource_template'=>$this->RT['genex_Concept']
            ,'resource_class'=> 'genex:Concept'   
        );
        //récupère les types
        $arrType = explode(',',$v['arrTypeConcept']);
        foreach ($arrType as $t) {
            if($t=='carac'){
                $arr['genex:isCaract']=1;
                $arr['dcterms:description'][]=$t.trim($v['title']);
            }
            $arr['genex:hasType'][]=$t;
            if(in_array($t,$this->termType))
                $arr['dcterms:description'][]=$t.$this->sepTerm.trim($v['title']);
            else
                $arr['dcterms:description'][]=$t.$this->sepType.trim($v['title']);
        }
        return $this->omk->postItem($arr, $this->existe);    
    }

    /**
	 * recupère une conjugaison
     * 
     * @param array    $v
     * 
     * @return array
	 */
    function getConjugaison($v){
        $this->trace("DEBUT ".__METHOD__.' '.$v['modele']);
        $arr = array(
            'dcterms:title'=>$v['title']
            ,'dcterms:description'=>trim($v['modele'])
            ,'dcterms:isReferencedBy'=>$v['aspects']
            ,'item_set'=>$this->getColId('gen_conjugaison')
        );
        return $this->omk->postItem($arr, $this->existe);    
    }

    /**
	 * corrige les doublons de l'importation
     * 
     * 
     * @return int
	 */
    function corrigeDoublonsImport(){

        $this->trace("DEBUT ".__METHOD__);

        //récupère les items avec un titre identique
        $c = "corrigeDoublonsImport";
        //if(!$this->bCache)
            $this->cache->remove($c);
        $rs = $this->cache->load($c);
        if(!$rs) {
            $sql = "SELECT 
                    COUNT(*) nb, v.value, group_concat(v.resource_id) ids
                FROM
                    value v
                WHERE
                    v.property_id = 1
                GROUP BY v.value
                HAVING COUNT(*) > 1
                ORDER BY nb DESC ";
            $dbD = new Model_DbTable_Flux_Doc($this->omk->db);
            $rs = $dbD->exeQuery($sql);
            $this->cache->save($rs, $c);
        }
        $i = 0;
        foreach ($rs as $v) {
            //récupère les identifiants
            $ids = explode(',',$v['ids']);            
            if(is_numeric($v['value'])){
                for ($j=0; $j < count($ids); $j++) { 
                    $rl = $this->omk->send('items','DELETE',$this->omk->paramsAuth(),false,'/'.$ids[$j],true);
                    $this->trace('DELETE INT '.$i.' '.$j.' '.$v['value'].' = '.$ids[$j]);
                }
            /*on traite les ids au fur et à mesure
            }elseif (count($ids)!=$v['nb']) {
                $this->trace('ERROR '.$i.' '.count($ids).'!='.$v['nb']);
            */
            }else{
                //récupère la première item
                $donnees = $this->omk->getItem($ids[0]);
                for ($j=1; $j < count($ids); $j++) { 
                    //récupère la première item
                    $maj = $this->omk->getItem($ids[$j]);
                    //ajoute les éléments au données
                    $donnees['dcterms:isReferencedBy'][]=$maj['dcterms:isReferencedBy'][0];
                    //récupère les items liées                                        
                    $arrLien = $this->omk->searchItems(array(
                        'dcterms:isPartOf'=>array('t'=>'res','v'=>$maj['o:id'])
                        ));
                    //modifie l'identifiant du lien
                    foreach ($arrLien as $l) {
                        $nId = str_replace($l['dcterms:isPartOf'][0]['value_resource_id'], $donnees['o:id'], $l['dcterms:isPartOf'][0]['@id']);
                        $l['dcterms:isPartOf'][0]['@id']=$nId;
                        $l['dcterms:isPartOf'][0]['value_resource_id']=$donnees['o:id'];
                        $rl = $this->omk->send('items','PATCH',$this->omk->paramsAuth(),$l,'/'.$l['o:id'],true);
                        $this->trace('LIEN modifié '.$i.' '.$j.' '.$l['o:title'].' = '.$donnees['@id']);                    
                    }
                    //supprime l'item
                    $rl = $this->omk->send('items','DELETE',$this->omk->paramsAuth(),false,'/'.$maj['o:id'],true);
                    $this->trace('ITEM supprimée '.$i.' '.$j.' '.$maj['o:title'].' = '.$donnees['@id']);                    
                }
                //éxécution de la mise à jour
                $r = $this->omk->send('items','PATCH',$this->omk->paramsAuth(),$donnees,'/'.$i['o:id'],true);
                $this->trace('OK '.$i.' '.$j.' '.$donnees['o:title'].' = '.$donnees['@id']);
            }
            $i ++;
        }
        
        $this->trace("FIN ".__METHOD__.' '.$i);
        
    }

    /**
	 * recupère le réseau d'un concept
     * 
     * @param string $cpt
     * 
     * 
     * @return array
	 */
    function getConceptReseau($cpt){


        $c = "getConceptReseau".md5($cpt);
        //if(!$this->bCache)
        //    $this->cache->remove($c);
        $this->omk->reseau = $this->cache->load($c);
        if(!$this->omk->reseau) {

            if(!$cpt)
                throw new Exception('Impossible de récupérer le réseau. Veuillez préciser le nom du concept');

            //récupère l'item concept
            $items = $this->omk->searchMulti(array('dcterms:title'=>$cpt,'item_set_id'=>'gen_concept'));

            if(count($items)>1)
                throw new Exception("Impossible de récupérer le réseau. Plusieurs concepts ont été trouvétrouvé");

            if($items[0]['o:title']!=$cpt)
                throw new Exception("Impossible de récupérer le réseau. Le concept n'a pas été trouvé");
            
            //récupère le reseau des isPartOf
            $this->omk->getItemReseau($items[0],"dcterms:isPartOf");

            $this->cache->save($this->omk->reseau, $c);
        }

        //récupère les générateurs inclu dans les items
        $nb = count($this->omk->reseau['nodes']);
        for ($i=0; $i < $nb; $i++) { 
            if(isset($this->omk->reseau['nodes'][$i]['dcterms:description'])){
                $genFlux = $this->getGenFlux($this->omk->reseau['nodes'][$i]['dcterms:description'][0]['@value']);
                $this->omk->reseau['nodes'][$i]['flux']=$genFlux;
                //calcul le réseau de générateurs inclus

            }
        }
    
        return $this->omk->reseau;
    }

    /**
	 * contruction du flux de génération
     * @param string $exp
     * 
     * @return array
	 */
    function getGenFlux($exp){

        //récupère les générateur du texte
        $arrGen = $this->getGenInTxt($exp);

        //construction du flux
        $arrFlux = array();
        $posi = 0;
        foreach ($arrGen[0] as $i => $gen) {
            //retrouve la position du gen
            $deb = strpos($exp, $gen);
            $fin = strlen($gen)+$deb;
            if($deb>$posi)$arrFlux[]=array('deb'=>$posi,'fin'=>$deb,'txt'=>substr($exp, $posi, $deb-$posi));
            //décompose le générateur
            $genCompo = $this->getGenCompo($arrGen[1][$i]);
            $arrFlux[]=array('deb'=>$deb,'fin'=>$fin,'gen'=>$arrGen[1][$i],'compo'=>$genCompo);
            $posi = $fin;
        }
        //vérifie s'il faut ajouter la fin du texte
        if($posi<strlen($exp))$arrFlux[]=array('deb'=>$posi,'fin'=>strlen($exp),'txt'=>substr($exp, $posi));

        return $arrFlux;

    }

    /**
	 * récupère la décomposition du générateur
     * @param string $gen
     * 
     * @return array
	 */
    function getGenCompo($gen){

        $genCompo = array();

        //on récupère le déterminant
        $arrGen = explode("|",$gen);
        
        if(count($arrGen)==1){
            $genCompo['class']=$arrGen[0];
            return $genCompo;
        }

        $genCompo['det']=$arrGen[0];
        $genCompo['class']=$arrGen[1];

		//gestion du changement de position de la classe
		$genCompo['posi']=explode("@", $genCompo['class']);
        
		//vérifie si la class est un déterminant
		if(is_numeric($genCompo['class']))$genCompo['classDet']=$genCompo['class'];

		//vérifie si la class possède un type de class
		if(strpos($genCompo['class'],"_")){
            $arr = explode("_",$genCompo['class']);			
            $genCompo['type']=$arr[0];
            $genCompo['gen']=$arr[1];
		}elseif(substr($genCompo['class'],0,5)=="carac"){
			//la class est un caractère
            $genCompo['caract'] = str_replace("carac", "carac_", $genCompo['class']);
		}elseif(strpos($genCompo['class'],"#")){
				$genCompo['gen'] = str_replace("#","determinant",$genCompo['class']); 
        }elseif(substr($genCompo['class'],0,1)=="=" && is_numeric(substr($genCompo['class'],1,1))){
            $genCompo['bloc'] = substr($genCompo['class'],1);
        }elseif(substr($genCompo['class'],0,1)=="=" && substr($genCompo['class'],1,1)=="x"){
            $genCompo['bloc'] = substr($genCompo['class'],1);
        }elseif(strpos($genCompo['class'],"-")){
            $genCompo['gen']=$genCompo['class'];
        }			

        return $genCompo;

    }

    

    /**
	 * recupère les générateurs d'une expression textuelle
     * merci à https://stackoverflow.com/questions/10104473/capturing-text-between-square-brackets-in-php
     * @param string $exp
     * 
     * @return array
	 */
    function getGenInTxt($exp){

        preg_match_all("/\[([^\]]*)\]/", $exp, $matches);
        return $matches;

    }


}
<?php
/**
 * Flux_Sonar
 * Classe qui gère les flux de l'application Sonar
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Sonar extends Flux_Site{

    var $dbOmk = false;
    var $omk = false;
    var $titleColCrible = 'Cribles conceptuels';
    var $titleColIIIF = 'Collection IIIF';
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

        //on récupère la racine des documents
        $this->initDbTables();
        $this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
        $this->idDocStruc = $this->dbD->ajouter(array("titre"=>'structures','parent'=>$this->idDocRoot));
        $this->idDocCol = $this->dbD->ajouter(array("titre"=>'Collection IIIF','parent'=>$this->idDocRoot));
        $this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
        $this->idTagRoot = $this->dbT->ajouter(array("code"=>__CLASS__));
        $this->idTagTypeFlux = $this->dbT->ajouter(array("code"=>'type flux','parent'=>$this->idTagRoot));
        $this->idTagRep = $this->dbT->ajouter(array('code'=>'réponse', 'parent'=>$this->idTagRoot));
        $this->idTagChoixRep = $this->dbT->ajouter(array('code'=>'choix', 'parent'=>$this->idTagRoot));
        $this->idTagNonChoixRep = $this->dbT->ajouter(array('code'=>'non choix', 'parent'=>$this->idTagRoot));
        $this->idTagPosChoixRep = $this->dbT->ajouter(array('code'=>'possibilité de choix', 'parent'=>$this->idTagRoot));
        $this->idTagProc = $this->dbT->ajouter(array('code'=>'processus', 'parent'=>$this->idTagRoot));
        $this->idTagStruc = $this->dbT->ajouter(array('code'=>'structure', 'parent'=>$this->idTagRoot));

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
        return $o;
    }

    /**
     * initialise les vocabulaire
     */
    function initVocabulaires(){
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
	 * initialise les collections IIIF
     * 
     * @return array
	 */
    function initCollectionIIIF(){
        $arr = array(
            array('titre'=>'Visages SMEL','ancre'=>'SMEL','url'=>'https://jardindesconnaissances.univ-paris8.fr/smel/omk/iiif/collection/1496','parent'=>$this->idDocCol),
            array('titre'=>'Tout SMEL','ancre'=>'SMEL','url'=>'https://jardindesconnaissances.univ-paris8.fr/smel/omk/iiif/collection/1','parent'=>$this->idDocCol),
            array('titre'=>'Affiche de mai 68','ancre'=>'octaviana','url'=>'https://octaviana.fr/iiif/collection/346','parent'=>$this->idDocCol),
            array('titre'=>'Illustrations de thèse','ancre'=>'ADACEMU','url'=>'https://jardindesconnaissances.univ-paris8.fr/ADACEMU/omk/iiif/collection/63','parent'=>$this->idDocCol),
            array('titre'=>'Reportages photographiques des présidences de la république française','ancre'=>'ValArNum','url'=>'https://jardindesconnaissances.univ-paris8.fr/ValArNum/omks/iiif/collection/151682','parent'=>$this->idDocCol)
            //,array('titre'=>'Tests locaux','ancre'=>'SMEL local','url'=>'../../data/SMEL/iiif-smel1.json','parent'=>$this->idDocCol)    
        );
        foreach ($arr as $v) {
            if($this->dbOmk){
                $this->addIIIF($v);
                $r = $this->omk->postItemSet(array('title'=>$this->titleColIIIF,'resource_class'=>'MediaResource'));
            }else        
                $r = $this->dbD->ajouter($v);
        }
        return $r;
    }

    /**
	 * ajoute une collection IIIF
     * 
     * @param array $v
     * 
     * @return string
	 */
    function addIIIF($v){
        if(!$this->omk)$this->omk=new Flux_Omeka($this->dbOmk);
        if(!$this->idsCol[$this->titleColIIIF]) {
            //'Collection IIIF'
            $col = $this->omk->postItemSet(array('title'=>$this->titleColIIIF));
            $this->idsCol[$this->titleColIIIF] = $col['o:id'];
        }
        return $this->omk->postItem(array(
            'dcterms:title'=>$v['titre']
            ,'dcterms:isReferencedBy'=>array(array('type'=>'uri','uri'=>$v['url'],'label'=>$v['ancre']))
            ,'resource_class'=>$this->omk->getIdByName('Collection','resource_classes')//23
            ,'item_set'=>$this->idsCol[$this->titleColIIIF]
        ));
    }

    /**
	 * recupère l'url pour rechercher les items d'une collection
     *   
     * @return string
	 */
    function getUrlFindItemByCol(){
        if(!$this->omk)$this->omk=new Flux_Omeka($this->dbOmk);
        $r = json_decode($this->omk->getPropByName('inScheme'),true);
        return $this->omk->endpoint.'items?property[0][property]='.$r[0]['o:id'].'&property[0][type]=eq&property[0][text]=';
    }

    /**
	 * recupère les collections IIIF
     * 
     * @param string    $title
     * 
     * @return array
	 */
    function getCollection($title){
        if($this->dbOmk){
            $this->omk = $this->omk ? $this->omk : new Flux_Omeka($this->dbOmk);
            $r = json_decode($this->omk->search(array('title'=>$title),'item_sets'),true)[0]; 
            //initialise la collection si vide
            if(!$r['o:id']){
                switch ($title) {
                    case $this->titleColIIIF:
                        $r = $this->initCollectionIIIF();
                        break;
                    case $this->titleColCrible:
                        $r = $this->initCrible();
                        break;
                }
            }
            $this->idsCol[$title]=$r['o:id'];             

            //renvoie le lien vers les item de la collection
            return $r['o:items']['@id'];
        }else
    		return $this->dbD->findByParent($this->idDocCol);
    }

    /**
	 * initialise les cribles
     * 
     * @return array
	 */
    function initCrible(){

        $arr = array(
            array('langue'=>'fr','titre'=>'bon et mauvais','data'=>'bon,mauvais','parent'=>$this->idDocStruc),
            array('langue'=>'fr','titre'=>'IEML : élémentaire','data'=>'vide,actuel,virtuel,signe,être,chose','parent'=>$this->idDocStruc),
            array('langue'=>'fr','titre'=>'Yi Jing : élémentaires','data'=>'Ciel,Tonnerre,Eau,Montagne,Terre,Vent,Feu,Brume','parent'=>$this->idDocStruc),
            array('langue'=>'fr','titre'=>'Yi Jing : hexagrammes','data'=>"le Créatif - le Ciel,le Réceptif,la Difficulté initiale,la Folie juvénile,l'Attente,le Conflit,l'Armée,la Solidarité-  l'Union,le Pouvoir d'apprivoisement du petit,la Marche,la Paix,la Stagnation,la Communauté avec les hommes,le Grand Avoir,l'Humilité,l'Enthousiasme,La Suite,le Travail sur ce qui est corrompu,l'Approche,la Contemplation,Mordre au travers,la Grâce,l'Éclatement,le Retour,l'Innocence,le Pouvoir d'apprivoisement du grand,les Commissures des lèvres,la Prépondérance du grand,l'Insondable,le Feu,l'Influence,la Durée,la Retraite,la Puissance du grand,le Progrès,l'Obscurcissement de la lumière,la Famille,l'Opposition,l'Obstacle,la Libération,la Diminution,l'Augmentation,la Percée,Venir à la rencontre,le Rassemblement,la Poussée vers le haut,l'Accablement,le Puits,la Révolution,le Chaudron,l'Ébranlement,la Montagne,le Développement,l'Épousée,Abondance,le Voyageur,le Doux,le Lac,La Dispersion,la Limitation,la Vérité intérieure,la Prépondérance du Petit,Après l'accomplissement,Avant l'accomplissement",'parent'=>$this->idDocStruc),
            array('langue'=>'cn','titre'=>'易静：小学','data'=>"乾,坤,震,坎,艮,巽,離,兌",'parent'=>$this->idDocStruc)
        );
        foreach ($arr as $v) {
            if($this->dbOmk){
                $this->addCrible($v);
                $r = $this->omk->postItemSet(array('title'=>$this->titleColCrible));
            }else        
                $r = $this->dbD->ajouter($v);
        }
        return $r;
    }

    /**
	 * ajoute un crible
     * 
     * @param array $v
     * 
     * @return string
	 */
    function addCrible($v){

        if(!$this->omk)$this->omk=new Flux_Omeka($this->dbOmk);
        //récupère la Collection crible'
        if(!$this->idsCol[$this->titleColCrible]) {
            $col = $this->omk->postItemSet(array('title'=>$this->titleColCrible));
            $this->idsCol[$this->titleColCrible] = $col['o:id'];
        }
        //création du crible
        $s =  $this->omk->postItem(array(
            'dcterms:title'=>$v['titre']
            ,'resource_class'=>'Crible'
            ,'rdf:type'=>'skos:ConceptScheme'
            ,'item_set'=>$this->idsCol[$this->titleColCrible]
            ),false);
        
        //création des concepts du crible
        $l = explode(',',$v['data']);
        foreach ($l as $c) {
            $i = $this->omk->postItem(array('title'=>$c
            ,'resource_class'=>'Concept'
            ,'rdf:type'=>'skos:Concept'
            ,'skos:prefLabel'=>$c
            ,'skos:inScheme'=>array(array('type'=>'resource','value'=>$s['o:id']),$s['dcterms:title'][0]['@value'])
            ),false);

        }
    }


    /**
	 * recupère les structure
     * 
     * @return array
	 */
    function getStructure(){
		return $this->dbD->findByParent($this->idDocStruc);
    }
    
    /**
	 * récupère la liste des flux
     * 
     * @return array
	 */
	function getListeFlux(){
        $sql = "SELECT 
                d.doc_id,
                d.url,
                d.type,
                GROUP_CONCAT(dp.titre) dpTitres,
                GROUP_CONCAT(dp.url) dpUrls,
                GROUP_CONCAT(dp.doc_id) dpIds
            FROM
                flux_doc d
                    INNER JOIN
                flux_doc dp ON d.lft BETWEEN dp.lft AND dp.rgt
            WHERE
                d.tronc = 'flux'
                    AND dp.niveau BETWEEN 2 AND d.niveau - 1
            GROUP BY d.doc_id";
        $rs = $this->dbD->exeQuery($sql);
        foreach ($rs as $id => $d) {
            $t = explode(',',$d['dpTitres']);
            $u = explode(',',$d['dpUrls']);
            $i = explode(',',$d['dpIds']);
            unset($rs[$id]['dpTitres']);
            unset($rs[$id]['dpUrls']);
            unset($rs[$id]['dpIds']);
            $rs[$id]['parents'] = [];
            foreach ($t as $k => $v) {
                $rs[$id]['parents'][] = array('titre'=>$v,'url'=>$u[$k],'id'=>$i[$k]);
            }
        }
        return $rs;
    }


    /**
	 * enregistre la position
     * 
     * @param array     $data
     * @param string    $type
     * @param array     $geo
     * @param array     $uti
     * 
     * @return array
	 */
	function savePosi($data, $type, $geo, $uti){

        //enregistre la geo
        if($geo){
            $this->idGeo = $this->dbG->ajouter(array(
                'lat'=>$geo['coords']['latitude'],
                'lng'=>$geo['coords']['longitude'],
                'alt'=>$geo['coords']['altitude'] ? $geo['coords']['altitude'] : 0,
                'pre'=>$geo['coords']['accuracy']
            ));
        }
        
        //enregistrement de la structure
        $titreStruc = "";
        foreach ($data['structure'] as $s) {
            $titreStruc .= $s['t']." - ";
        }
        $titreStruc = substr($titreStruc,0,-3);
        $idStruc = $this->dbD->ajouter(array('titre'=>$titreStruc
            ,'tronc'=>'structure'
            ,'parent'=>$this->idDocStruc));

        //enregistre le document
        switch ($type) {
            case 'IIIF':
                $idDoc = $this->dbD->ajouter(array('titre'=>'image IIIF'
                ,'tronc'=>$type
                ,'parent'=>$this->idDocRoot
                ,'url'=>$data['id']));
                break;
        }

        //enregistre l'évaluation de la structure
        // src = le document
        // dst = la structure
        // pre = l'auteur de l'évaluation
        $idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
            ,"src_id"=>$idDoc,"src_obj"=>"doc"
            ,"dst_id"=>$idStruc,"dst_obj"=>"doc"
            ,"pre_id"=>$uti['id_uti'],"pre_obj"=>"uti"
            ,"valeur"=>json_encode(array("x"=>$data['x'],"y"=>$data['y'],"numX"=>$data['numX'],"numY"=>$data['numY']
                ,"degrad"=>$data['degrad']
                ,"distance"=>$data['distance']
                ))
        ));

        //enregistre l'évaluation de la structure
        // src = le document
        // dst = le tag de la structure
        // pre = le rapport avec la structure
        foreach ($data['structure'] as $s) {
            //récupère le tag
            $idTag = $this->dbT->ajouter(array('code'=>$s['t'],'parent'=>$this->idTagStruc));

            $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                ,"src_id"=>$idDoc,"src_obj"=>"doc"
                ,"dst_id"=>$idTag,"dst_obj"=>"tag"
                ,"pre_id"=>$idRap,"pre_obj"=>"rapport"
                ,"niveau"=>floatval($s['p'])
            ));
        }



    }    

    /**
	 * enregistre la position
     * 
     * @param array     $data
     * @param array     $uti
     * 
     * @return array
	 */
	function savePosiOmk($d, $uti){

        if(!$this->omk)$this->omk=new Flux_Omeka($this->dbOmk);

        //enregistre/récupère les collections
        $isRA = $this->omk->postItemSet(array(
            'title'=>'Ressources annotées'
        ));
        $isPS = $this->omk->postItemSet(array(
            'title'=>'Positions sémantiques'
        ));
        /*
        $isPOV = $this->omk->postItemSet(array(
            'title'=>'Point de vue'
        ));
        */

        //récupère la ressource annotée
        //$item = $this->omk->searchByRef($d['ma:hasSource']);
        $itemTitle = $d['ma:isFragmentOf']['title'].' : fragment';
        $item = $this->omk->searchByRef($d['ma:hasSource'],'items',true)[0];
        if($item==null){
            //enregistre le parent
            if($d['ma:isFragmentOf']){
                $itemParent = $this->omk->searchByRef($d['ma:isFragmentOf']['IIIF']);
                if($itemParent=="[]"){
                    $itemParent = $this->omk->postItem(array(
                        'dcterms:title'=>$d['ma:isFragmentOf']['title']
                        ,'dcterms:isReferencedBy'=>$d['ma:isFragmentOf']['IIIF']
                        ,'dcterms:isPartOf'=>$d['dcterms:isPartOf']
                        ,'item_set'=>$isRA['o:id']
                        ,'resource_class'=>'Image'
                        ,'IIIF'=>$d['ma:isFragmentOf']
                    ));        
                }
            }
            //enregistre la ressource
            $item = $this->omk->postItem(array(
                'dcterms:title'=>$itemTitle
                ,'resource_class'=>'Image'
                ,'dcterms:isReferencedBy'=>$d['ma:hasSource']
                ,'item_set'=>$isRA['o:id']
                ,'dcterms:isPartOf'=>array(array('type'=>'resource','value'=>$itemParent['o:id']))
                ,'ingest_url'=>array('title'=>$d['ma:isFragmentOf']['title'],'source'=>$d['ma:hasSource'])
            ));    
        }

        //enregistre la position sémantique
        $d['ma:isFragmentOf']=null;
        $d['dcterms:isPartOf']=null;
        $d['ma:hasSource']=array(array('type'=>'resource','value'=>$item['o:id']));
        //met un type litteral pour le retrouver à la recherche
        $d['jdc:hasDoc']=''.$item['o:id'].'';

        $itemPosi = $this->omk->postItem($d,false);    

        //enregistre la geo de l'annotateur
        if($d['ma:locationLatitude']){
            $geo = $this->omk->postGeo(array(
                'value'=>'POINT ('.$d['ma:locationLongitude'].' '.$d['ma:locationLatitude'].')'
                ,'body'=>"géolocalisation de l'annotateur"
                ,'motivatedBy'=>'identifying'
                ,'hasPurpose'=>'moderating'
                ,'hasSource'=>$itemPosi['o:id']
            ));
        }
        
        //enregistre l'annotation
        $a = $this->omk->postAnnotation(array(
            'body'=>'Point de vue de : '.$d['ma:hasCreator'].' sur : '.$itemTitle
            ,'motivatedBy'=>'tagging'
            ,'hasPurpose'=>'moderating'
            ,'hasSource'=>$item['o:id']
            ,'hasTarget'=>array('id'=>$itemPosi['o:id'])
        ));

    }    


    /**
	 * récupère les évaluations
     * 
     * @param string    $type
     * @param string    $id
     * 
     * @return array
	 */
	function getEvals($type, $id){


        //construction de la requête
        switch ($type) {
            case 'IIIF':
                $sql = "SELECT 
                d.doc_id, d.url
                , r.valeur, r.maj
                , u.uti_id, u.login
                ,ds.doc_id idStruc , ds.data struc
                ,g.lat, g.lng, g.alt
            FROM
                flux_doc d
                    INNER JOIN
                flux_rapport r ON r.src_id = d.doc_id
                    AND r.src_obj = 'doc' AND r.pre_obj = 'uti'
                    INNER JOIN
                flux_uti u ON u.uti_id = r.pre_id
                    INNER JOIN
                flux_doc ds ON ds.doc_id = r.dst_id
                    INNER JOIN
                flux_geo g ON g.geo_id = r.geo_id
            WHERE
                d.url = '".$id."'";
                break;
        }

        $rs = $this->dbD->exeQuery($sql);
        return $rs;

    }        

    /**
	 * récupère les évaluations dans omk
     * 
     * @param string    $inScheme
     * @param string    $id
     * 
     * @return array
	 */
	function getEvalsOmk($inScheme, $id){
        //inScheme possibilité de filtre non implémenté = fait sur le client
        $rs=array();
        $item = $this->omk->searchByRef($id,'items',true)[0];
        if($item){
            $rs = $this->omk->search(array('hasDoc'=>$item['o:id']),'items','hasDoc',true);
        }
        return $rs;

    }        

    /**
	 * récupère les geolocalisations au format  WebGL Globe
     * 
     * @param array    $params
     * 
     * @return array
	 */
	function getEvalsWebGLGlobe($params=false){
        //TODO: utiliser params poru ajouter des conditions
        //récupère les positions semantiques
        //https://github.com/dataarts/webgl-globe
        /*
        var data = [
            [
            'seriesA', [ latitude, longitude, magnitude, latitude, longitude, magnitude, ... ]
            ],
            [
            'seriesB', [ latitude, longitude, magnitude, latitude, longitude, magnitude, ... ]
            ]
        ];        
        */
        $rs= $this->omk->search(array('resource_class_label'=>'SemanticPosition'),'items','resource_class_label',true);
        $wgl = array();
        $series = array();
        foreach ($rs as $r) {
            $doc = $r['ma:hasSource'][0];
            //construction des séries par doc
            if(!$series[$doc['value_resource_id']]){
                $series[$doc['value_resource_id']]=array('i'=>count($series),'details'=>$doc);
                $wgl[]=array($doc['display_title'],array());
            }
            $idSerie = $series[$doc['value_resource_id']]['i'];
            $wgl[$idSerie][1][] = $r["ma:locationLatitude"][0]['@value'];
            $wgl[$idSerie][1][] = $r["ma:locationLongitude"][0]['@value'];
            $wgl[$idSerie][1][] = $r["jdc:distanceCenter"][0]['@value'];
        }
        return array('wdl'=>$wgl,'series'=>$series);

    }        


}
<?php
/**
 * FluxSMEL
 * Classe qui gère les flux du projet SMEL
 * 
 * REFERENCES
 * http://collections.musee-mccord.qc.ca/scripts/search_results.php?keywords=houdini&Lang=2
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Scraping
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Smel extends Flux_Site{

	var $formatResponse = "json";
	var $searchUrl = '';
	var $rs;
	var $doublons;
	var $idDocRoot;
	var $idMonade;
	var $idBaseDefault="flux_smel";
	
    /**
     * Constructeur de la classe
     *
     * @param  string   $idBase
     * @param  boolean  $bTrace
     * @param  boolean  $bCache
     * 
     */
	public function __construct($idBase=false, $bTrace=true, $bCache=true)
    {
            if(!$idBase)$idBase=$this->idBaseDefault;
    		parent::__construct($idBase, $bTrace, $bCache);    	

            //on récupère la racine des documents
            $this->initDbTables();
            $this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
            $this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
            $this->idTagRoot = $this->dbT->ajouter(array("code"=>__CLASS__));
            //on enregistre les tags utiles
            $this->idTagAuteur = $this->dbT->ajouter(array("code"=>'auteur',"parent"=>$this->idTagRoot));
            $this->idTagType = $this->dbT->ajouter(array("code"=>'type',"parent"=>$this->idTagRoot));
            $this->idTagDate = $this->dbT->ajouter(array("code"=>'date',"parent"=>$this->idTagRoot));
            $this->idTagSiecle = $this->dbT->ajouter(array("code"=>'siècle',"parent"=>$this->idTagRoot));
            $this->idTagRef = $this->dbT->ajouter(array("code"=>'référence',"parent"=>$this->idTagRoot));

	    	
    }

     /**
     * Enregistre les informations d'une recherche dans le site McCord
     *
     * @param   string  $query
     * @param   int     $curset
     *
     * @return array
     */
    public function saveMcCordSearch($query,$curset=1)
    {
        $this->trace(__METHOD__." - ".$query." - ".$curset);

        //enregistre l'action        
        $idAct = $this->dbA->ajouter(array('code'=>__METHOD__));

        //enregistre le mot clef de recherche
        $idTagQ = $this->dbT->ajouter(array('code'=>$query,'parent'=>$this->idTagRoot));

        //récupère la réponse
        $url = 'http://collections.musee-mccord.qc.ca/scripts/search_results.php?Lang=2'
            .'&order=1&curset='.$curset
            .'&keywords='.$query;            
        $html = $this->getUrlBodyContent($url,false,$this->bCache);

        //enregistre le doc
        $idDoc = $this->dbD->ajouter(array('titre'=>'McCord Search '.$query,'url'=>$url, 'data'=>$html, 'parent'=>$this->idDocRoot));

        //enregistre le rapport
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idTagQ,"src_obj"=>"tag"
				,"dst_id"=>$idDoc,"dst_obj"=>"doc"
				,"pre_id"=>$idAct,"pre_obj"=>"acti"
    		));						

        $i=0;
        if($html){
            $dom = new Zend_Dom_Query($html);	    

            //récupère la liste des réponses
            $xPath = '//div[@class="groupe_image"]';
            $results = $dom->queryXpath($xPath);

            foreach ($results as $result) {
                $this->trace($result->nodeValue);
                foreach($result->childNodes as $cn){
                    $a = $cn->getAttribute('class');
                    $this->trace($a);
                    if($a=='image'){
                        //récupère les informations de ref
                        $ref = explode(" | ",$cn->firstChild->getAttribute('title'));
                        $this->trace('ref:'.$ref[0].' - titre:'.$ref[1].' - type:'.$ref[2].' - auteur:'.$ref[3]);
                    }
                    if($a=='groupe_image_droite'){
                        $ima = $cn->firstChild->nodeValue;
                        //récupère la date en supprimant les informations de ref
                        foreach ($ref as $r) {
                            $ima = str_replace($r,"",$ima);
                        }
                        $ima = str_replace('© Musée McCord',"",$ima);
                        $dates = explode(',',$ima);
                        foreach ($dates as $d) {
                            $this->trace($d);
                        }
                    }                        
                }
                //récupère l'image en grand format
                $taille = 'large';
                $rsDocTof = $this->getMcCordImage($ref[0],$taille,false,$idRap);
                //création du rapport entre les résultat de la recherchr, la photo et l'activité
                $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                        ,"src_id"=>$rsDocTof["parent"],"src_obj"=>"doc"
                        ,"dst_id"=>$rsDocTof["doc_id"],"dst_obj"=>"doc"
                        ,"pre_id"=>$idRap,"pre_obj"=>"rapport"
                    ));		
                //enregistre l'auteur
                if($ref[3]){
                    $idExi = $this->dbE->ajouter(array("nom"=>$ref[3]));
                    //création du rapport entre l'image est l'auteur
                    $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                            ,"src_id"=>$rsDocTof["parent"],"src_obj"=>"doc"
                            ,"dst_id"=>$idExi,"dst_obj"=>"exi"
                            ,"pre_id"=>$this->idTagAuteur,"pre_obj"=>"tag"
                        ));						
                }                    
                //création des rapports
                if($dates[0]){
                        $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                        ,"src_id"=>$rsDocTof["parent"],"src_obj"=>"doc"
                        ,"dst_id"=>$this->idTagDate,"dst_obj"=>"tag"
                        ,"valeur"=>$dates[0]
                    ));						
                }
                if($dates[1]){
                    $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                        ,"src_id"=>$rsDocTof["parent"],"src_obj"=>"doc"
                        ,"dst_id"=>$this->idTagSiecle,"dst_obj"=>"tag"
                        ,"valeur"=>$dates[1]
                    ));						
                }
                $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                    ,"src_id"=>$rsDocTof["parent"],"src_obj"=>"doc"
                    ,"dst_id"=>$this->idTagRef,"dst_obj"=>"tag"
                    ,"valeur"=>$ref[0]
                ));						
                $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                    ,"src_id"=>$rsDocTof["parent"],"src_obj"=>"doc"
                    ,"dst_id"=>$this->idTagType,"dst_obj"=>"tag"
                    ,"valeur"=>$ref[2]
                ));						

                $i++;
            }	    
            if($i)$this->saveMcCordSearch($query,$curset+1);
        }
        $this->trace("END ".__METHOD__);

    }

     /**
     * Récupère les d'une référence du site McCord
     *
     * @param   string  $ref
     * @param   string  $taille
     * @param   int     $idDocParent
     * @param   int     $idRapport
     *
     * @return array
     */
    public function getMcCordImage($ref,$taille='large',$idDocParent=false, $idRapport=0)
    {
        $this->trace(__METHOD__." - ".$ref." - ".$taille);

        if(!$idDocParent)$idDocParent=$this->idDocRoot;

        //enregistre l'action        
        $idAct = $this->dbA->ajouter(array('code'=>__METHOD__));

        //récupère la réponse
        $url = 'http://collections.musee-mccord.qc.ca/fr/collection/artefacts/'
            .$ref;            
        $html = $this->getUrlBodyContent($url,false,$this->bCache);

        //enregistre le doc
        $idDoc = $this->dbD->ajouter(array('titre'=>'McCord Artefact '.$ref, 'tronc'=>"artefact", 'note'=>$ref, 'url'=>$url, 'data'=>$html, 'parent'=>$idDocParent));

        //enregistre le rapport
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idRapport,"src_obj"=>"rapport"
				,"dst_id"=>$idDoc,"dst_obj"=>"doc"
				,"pre_id"=>$idAct,"pre_obj"=>"acti"
            ));						

        if($html){
            $dom = new Zend_Dom_Query($html);	    

            //récupère le titre du doc
            $xPath = '/html/head/title';
            $results = $dom->queryXpath($xPath);
            foreach ($results as $result) {
                $titre = $result->nodeValue;
            }
            if($titre)$this->dbD->edit($idDoc,array('titre'=>$titre));

            //récupère l'adresse de l'artefact
            $xPath = '//*[@class="download"]/a/@href';
            $results = $dom->queryXpath($xPath);
            foreach ($results as $result) {
                $urlDld = $result->nodeValue;
            }
            if($urlDld){
                //création du mien de téléchargement
                $urlDld = "http://collections.musee-mccord.qc.ca".$urlDld."&format=".$taille;
                $htmlDld = $this->getUrlBodyContent($urlDld,false,$this->bCache);

                //enregistre le doc
                $idDocDld = $this->dbD->ajouter(array('titre'=>'McCord Download '.$ref,'url'=>$urlDld, 'tronc'=>"download", 'note'=>$ref, 'data'=>$html, 'parent'=>$idDoc));
        
                //enregistre le rapport
                $idRapDld = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                        ,"src_id"=>$idRap,"src_obj"=>"rapport"
                        ,"dst_id"=>$idDocDld,"dst_obj"=>"doc"
                        ,"pre_id"=>$idAct,"pre_obj"=>"acti"
                    ));						
        
                if($htmlDld){
                    $domDld = new Zend_Dom_Query($htmlDld);	                
                    //récupère le titre du doc
                    $xPath = '/html/head/title';
                    $results = $dom->queryXpath($xPath);
                    foreach ($results as $result) {
                        $titre = $result->nodeValue;
                    }
                    if($titre)$this->dbD->edit($idDocDld,array('titre'=>$titre));

                    //récupère l'adresse de l'artefact
                    $xPath = '//img/@src';
                    $results = $domDld->queryXpath($xPath);
                    foreach ($results as $result) {
                        //la dernière image est la bonne
                        $urlImage = $result->nodeValue;
                    }                   
                    $urlImage = "http://collections.musee-mccord.qc.ca".$urlImage;
                    //enregistre l'image
                    $img = ROOT_PATH.'/data/SMEL/McCord/photos/'.$ref."_".$taille.".jpg";
                    if (!file_exists($img)){
                        $res = file_put_contents($img, file_get_contents($urlImage));
                        $this->trace($res." ko : Fichier créé ".$urlImage." ".$img);
                    }
                    //ajoute la photo
                    $rsDocTof = $this->dbD->ajouter(array("url"=>$urlImage
                        ,"titre"=>$img
                        ,"type"=>1
                        ,"note"=>$ref
                        ,"tronc"=>'image'
                        ,"parent"=>$idDocDld),true,true);
                }
            }

        }
        $this->trace("END ".__METHOD__.' '.$urlImage);        
        return $rsDocTof;

    }

     /**
     * Récupère les analyse de google vision
     *
     * @param   int     idDoc
     * @param   boolean $all
     *
     * @return array
     */
    public function getGoogleVisionAnalyse($idDoc, $all){
        $this->trace(__METHOD__.' - '.$idDoc);        
        if($all){
            $arr = $this->dbD->findByTronc('image');
        }else{
            $arr = $this->dbD->findBydoc_id($idDoc);
        }
        $gVision = new Flux_Gvision($this->idBase,$this->bTrace);
        $gVision->saveAnalyses($arr,'titre');    

        $this->trace("END ".__METHOD__);        
    }



}
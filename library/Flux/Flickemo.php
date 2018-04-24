<?php

/**
 * Flux_Flickemo
 * 
 * Classe qui gère les flux des Flickemo
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Flux_Flickemo extends Flux_Site{

    var $flikr;
    var $idTagProp;
    var $idTagOwner;
    var $idTagMachine;
    
    /**
     * Construction du gestionnaire de flux.
     *
     * @param string $idBase
     *
     * 
     */
    public function __construct($idBase=false, $bTrace=false)
    {        
        parent::__construct($idBase, $bTrace);
        
        //on récupère la racine des documents
        $this->initDbTables();
        $this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
        $this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
        $this->idTagRoot = $this->dbT->ajouter(array("code"=>__CLASS__));
        $this->idExiRoot = $this->dbE->ajouter(array("nom"=>__CLASS__));
        
        //ajoute les mot clefs nécessaires
        $this->idTagProp = $this->dbT->ajouter(array("code"=>"propriétaire","parent"=>$this->idTagRoot));
        $this->idTagOwner = $this->dbT->ajouter(array("code"=>"Tags propriétaire","parent"=>$this->idTagRoot));
        $this->idTagMachine = $this->dbT->ajouter(array("code"=>"Tags machine","parent"=>$this->idTagRoot));
        
        
        $this->flikr = new phpFlickr(KEY_FLIKR);
        $app = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH.'/configs/application.ini');
        $config = $app->bootstrap()->getOptions();
        $db = $config['resources']['db']['params'];
        $this->flikr->enableCache("db", "mysql://".$db["username"].":".$db["password"]."@".$db["host"]."/".$this->idBase."");
        
        $this->mc = new Flux_MC($idBase, $bTrace);
        
    }

    /**
     * Récupère ou crée les information de l'utilisateur
     *
     * @param string $login
     * @param string $flux
     *
     * @return array
     *
     */
    public function getUti($login, $flux)
    {
        //enregistre l'utilisateur
        $idUti = $this->dbU->ajouter(array('login'=>$login,'flux'=>$flux), true, false);
        //enregistre l'existence        
        $idExi = $this->dbE->ajouter(array('nom'=>$login,'uti_id'=>$idUti,'parent'=>$this->idExiRoot));  
        
        return array('idUti'=>$idUti,'idExi'=>$idExi,'idBase'=>$this->idBase);
    }            

    /**
     * Récupère les groupes d'un utilisateur
     *
     * @param int $idUti
     *
     * @return array
     *
     */
    public function getObjetsForUti($idUti)
    {
        $sql = "SELECT 
                d.doc_id, d.titre, d.url, d.tronc, d.note
            FROM
                flux_doc d
                    INNER JOIN
                flux_rapport r ON r.dst_id = d.doc_id
                    AND r.dst_obj = 'doc'
                    AND r.src_id = ".$idUti."
                    AND r.src_obj = 'uti'
            ORDER BY d.tronc";
        $arr = $this->dbD->exeQuery($sql);
        
        return $arr;
    }
    
    /**
     * Ajoute un groupe pour l'utilisateur
     *
     * @param int       $idUti
     * @param string    $idObj
     * @param string    $typeObj
     *
     * @return array
     *
     */
    public function setObjetForUti($idUti,$idObj,$typeObj)
    {
        $idActi = $this->dbA->ajouter(array('code'=>__METHOD__."_".$typeObj));
        $this->trace(__METHOD__."_".$typeObj);
        
        //récupère les infos
        switch ($typeObj) {
            case "group":
                $data = $this->flikr->groups_getInfo($idObj);
                break;
            case "galleries":
                $data = $this->flikr->galleries_getInfo($idObj);
                break;
            case "photosets":
                $data = $this->flikr->photosets_getInfo($idObj);
                break;
                
        }
        //creation de l'objet
        $arr =  $this->dbD->ajouter(array("titre"=>$data[$typeObj]['name']['_content']
            ,"url"=>$data[$typeObj]['id']
            ,"parent"=>$this->idDocRoot
            ,"tronc"=>$typeObj
            ,"note"=>$data[$typeObj]['description']['_content']
            ,"data"=>json_encode($data[$typeObj])
        ),true, true);
                                
        //création des rapports entre
        // src = l'utilisateur
        // dst = l'objet
        // pre = l'activité
        $idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
            ,"src_id"=>$idUti,"src_obj"=>"uti"
            ,"dst_id"=>$arr["doc_id"],"dst_obj"=>"doc"
            ,"pre_id"=>$idActi,"pre_obj"=>"acti"
        ));
        
        return $arr;
    }
    
    
    /**
     * Récupère les photos d'une source
     *
     * @param int       $idDoc
     *
     * @return array
     *
     */
    public function getPhotosFromDoc($idDoc)
    {
        $idActi = $this->dbA->ajouter(array('code'=>__METHOD__));
        
        $arrDoc = $this->dbD->findBydoc_id($idDoc);
        $arrTofs = array();
        $extra = "original_format,date_taken,owner_name,geo,tags,machine_tags,url_sq,url_t,url_z,url_m,url_o,o_dims";
        switch ($arrDoc['tronc']) {
            case "group":
                //on ne prend que les 100 premières photos
                $data = $this->flikr->groups_pools_getPhotos($arrDoc['url'],NULL,NULL,NULL,$extra,100);
                break;
            case "galleries":
                $data = $this->flikr->galleries_getPhotos($arrDoc['url']);
                break;
            case "photosets":
                $data = $this->flikr->photosets_getPhotos($arrDoc['url']);
                break;
        }
        return $data;
    }
        
    /**
     * Enregistre les photos d'une source
     * ATTENTION : trop long pour le faire en live !
     *
     * @param int       $idDoc
     *
     * @return array
     *
     */
    public function setPhotosFromDoc($idDoc)
    {
        $idActi = $this->dbA->ajouter(array('code'=>__METHOD__));
                
        //récupère les infos
        $arrDoc = $this->dbD->findBydoc_id($idDoc);
        $arrTofs = array();
        $extra = "original_format,date_taken,owner_name,geo,tags,machine_tags,url_sq,url_t,url_z,url_m,url_o,o_dims";
        switch ($arrDoc['tronc']) {
            case "group":
                $data = $this->flikr->groups_pools_getPhotos($arrDoc['url'],NULL,NULL,NULL,$extra);
                //vérifie si les photo sont déjà extraites
                $arrPhoto = $this->dbD->findByParent($idDoc);
                if(count($arrPhoto) >= $data["photos"]["pages"]) return $arrPhoto;
                //extraction des photos
                $arrTofs = $data["photos"]["photo"];
                if($data["photos"]["pages"]>1){
                    for ($i = 2; $i <= $data["photos"]["pages"]; $i++) {
                        $newdata = $this->flikr->groups_pools_getPhotos($arrDoc['url'],NULL,NULL,NULL,$extra,NULL,$i);
                        $arrTofs = array_merge($arrTofs, $newdata["photos"]["photo"]);
                    }
                }
                break;
            case "galleries":
                $data = $this->flikr->galleries_getPhotos($arrDoc['url']);
                break;
            case "photosets":
                $data = $this->flikr->photosets_getPhotos($arrDoc['url']);
                break;
        }
        $arr = array();
        foreach ($arrTofs as $p) {
            $arr[] = $this->savePhoto($p, $idDoc);       
        }
        
        return $arr;
    }
    
    /**
     * Enregistre les émotions pour une photo
     *
     * @param array     $doc
     * @param array     $e
     * @param array     $idUti
     *
     * @return array
     *
     */
    public function saveEmo($doc, $e, $idUti){
        
        //enregistre la photo
        $arrT =  $this->savePhoto($doc['data'], $doc['idSource']);

        //récupère le document du fragment
        $idDocFrag = $this->dbD->ajouter(array("titre"=>'fragment',"tronc"=>$doc['data']['id'],"parent"=>$arrT['doc_id']
            ,"url"=>$e['img'], 'note'=>json_encode(array("x"=>$e['x'],"y"=>$e['y'],"w"=>$e['w'],"h"=>$e['h']))));
        //Traitement de l'évaluation
        //récupère le tag
        $idTagParent = $this->dbT->ajouter(array("code"=>"Roue des émotions","parent"=>$this->idTagRoot));
        $idTag = $this->dbT->ajouter(array("code"=>$e["d"],"parent"=>$idTagParent,"type"=>$e["color"]));
        //récupère l'action
        //$idAct = $s->dbA->ajouter(array("code"=>'évaluation émotionnelle'));
        //enregistre le rapport entre le document, l'utilisateur et son évaluation
        $idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
            ,"src_id"=>$idDocFrag,"src_obj"=>"doc"
            ,"pre_id"=>$idUti,"pre_obj"=>"uti"
            ,"dst_id"=>$idTag,"dst_obj"=>"tag"
            ,"niveau"=>$e['r']
            ,"valeur"=>json_encode(array("cx"=>$e['cx'],"cy"=>$e['cy'],"r"=>$e['r'],"d"=>$e['d'],"color"=>$e['color']))
        ));
        return $arrT;
    }
    
    /**
     * Enregistre les émotions pour une photo
     *
     * @param array     $p
     * @param int       $idDoc
     *
     * @return array
     *
     */
    public function savePhoto($p, $idDoc){
                
        //creation de la photo
        $arrTof =  $this->dbD->ajouter(array("titre"=>$p['title']
            ,"url"=>$p['url_o']
            ,"parent"=>$idDoc
            ,"tronc"=>"photo"
            ,"note"=>$p['tags']
            ,"data"=>json_encode($p)
        ),true, true);
        //création du propriétaire
        $idExi = $this->dbE->ajouter(array("nom"=>$p['ownername'],"url"=>$p['owner']));
        $arrTof["idExi"]=$idExi;
        //lien entre propriétaire et photo
        // src = le propriétaire
        // dst = la photo
        // pre = propriétaire
        $idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
            ,"src_id"=>$idExi,"src_obj"=>"exi"
            ,"dst_id"=>$arrTof["doc_id"],"dst_obj"=>"doc"
            ,"pre_id"=>$this->idTagProp,"pre_obj"=>"tag"
        ));
        //création des tags du propriétaire
        $arrTof["tags"]=[];
        $arrTags = explode(" ",$p['tags']);
        foreach ($arrTags as $t) {
            $arrT = $this->dbT->ajouter(array("code"=>$t,"parent"=>$this->idTagOwner), true, true);
            //lien entre le propriétaire, le tag et la photo
            // src = le propriétaire
            // dst = la photo
            // pre = propriétaire
            $idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                ,"src_id"=>$idExi,"src_obj"=>"exi"
                ,"dst_id"=>$arrT['tag_id'],"dst_obj"=>"tag"
                ,"pre_id"=>$arrTof["doc_id"],"pre_obj"=>"doc"
            ));
            $arrTof["tags"][]=$arrT;
        }
        //création des tags de la machine
        $arrTags = explode(" ",$p['machine_tags']);
        foreach ($arrTags as $t) {
            $arrT = $idT = $this->dbT->ajouter(array("code"=>$t,"parent"=>$this->idTagMachine), true, true);
            //lien entre le propriétaire, le tag et la photo
            // src = le propriétaire
            // dst = la photo
            // pre = propriétaire
            $idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                ,"src_id"=>$idExi,"src_obj"=>"exi"
                ,"dst_id"=>$arrT['tag_id'],"dst_obj"=>"tag"
                ,"pre_id"=>$arrTof["doc_id"],"pre_obj"=>"doc"
            ));
            $arrTof["tags"][]=$arrT;
        }      
        return $arrTof;        
    }

}
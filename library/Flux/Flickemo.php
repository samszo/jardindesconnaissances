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
        
        return array('idUti'=>$idUti,'idExi'=>$idExi);
    }            

    /**
     * Récupère les groupes d'un utilisateur
     *
     * @param int $idUti
     *
     * @return array
     *
     */
    public function getGroupes($idUti)
    {
        
        return array('idUti'=>$idUti,'idExi'=>$idExi);
    }
    
    /**
     * Ajoute un groupe pour l'utilisateur
     *
     * @param int   $idUti
     * @param array $data
     *
     * @return array
     *
     */
    public function setGroupeForUti($idUti,$data)
    {
        $idActi = $this->dbA->ajouter(array('code'=>__METHOD__));
        
        //creation du groupe
        $idDoc =  $this->dbD->ajouter(array("titre"=>$data['name']
            ,"url"=>"https://www.flickr.com/groups/".$data['id']
            ,"parent"=>$this->idDocRoot
            ,"tronc"=>"groupe FlickR"
            ,"note"=>$data['id']
        ));
                
        //création des rapports entre
        // src = l'utilisateur
        // dst = le groupe
        // pre = l'activité
        // valeur = la date
        $idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
            ,"src_id"=>$idUti,"src_obj"=>"uti"
            ,"dst_id"=>$idDoc,"dst_obj"=>"doc"
            ,"pre_id"=>$idActi,"pre_obj"=>"acti"
        ));
        
        return array($idDoc, $idRap);
    }
    
    /**
     * Ajoute les informations du groupe
     *
     * @param string  $id
     *
     * @return array
     *
     */
    public function setGroupeInfo($id)
    {
        $idActi = $this->dbA->ajouter(array('code'=>__METHOD__));
        
        
        //creation du groupe
        $idDoc =  $this->dbD->ajouter(array("titre"=>$data['name']
            ,"url"=>"https://www.flickr.com/groups/".$data['id']
            ,"parent"=>$this->idDocRoot
            ,"tronc"=>"groupe FlickR"
            ,"note"=>$data['id']
        ));
        
        //création des rapports entre
        // src = l'utilisateur
        // dst = le groupe
        // pre = l'activité
        // valeur = la date
        $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
            ,"src_id"=>$idUti,"src_obj"=>"uti"
            ,"dst_id"=>$idDoc,"dst_obj"=>"doc"
            ,"pre_id"=>$idActi,"pre_obj"=>"acti"
        ));
        
        return array('idUti'=>$idUti,'idExi'=>$idExi);
    }
    
}
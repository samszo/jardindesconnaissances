<?php
/**
 * ValarnumController
 *
 * Controller pour la valorisation des archives numériques
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class ValarnumController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->initInstance();        
    }

    /**
     * action pour afficher un explorateur d'emotion
     *
     * @param  	string 		$q = pour afficher un type de collection
     * @example 'getOmkCollection' = une collection à partir d'un ItemSet d'Omeka S + IIIF
     * @example 'getCollectionFaces' = une collection de visage à partir d'un ItemSet d'Omeka S + IIIF + Google Vision
     *
     * @param  	int  		$idCol = identifiant de l'ItemSet Omeka
     *
     */
    public function exploemoAction(){
        
        $this->initInstance();
        
        $this->view->q =  $this->_getParam('q', "getOmkCollection");
        $this->view->idCol =  $this->_getParam('idCol', 1572);
        
        
    }
    
    public function evalacteursAction(){
        
        $this->initInstance();
    
    }

    public function keshifAction(){
        
        
    }
    
    public function sauveAction() {
        $this->initInstance();
        if($this->_getParam('q', 0)){
            switch ($this->_getParam('q')){
                case "emo":
                    $this->saveEmo($this->_getParam('doc'),$this->_getParam('eval'));
                    $this->view->data =  array('message'=>'OK', 'idBase'=>$this->idBase);
                break;
            }
        }
    }
    
    
    
    function saveEmo($doc, $e){
        $s = new Flux_Site($this->idBase);
        $s->dbT = new Model_DbTable_Flux_Tag($s->db);
        $s->dbD = new Model_DbTable_Flux_Doc($s->db);
        $s->dbR = new Model_DbTable_Flux_Rapport($s->db);
        $s->dbM = new Model_DbTable_Flux_Monade($s->db);
        $s->dbA = new Model_DbTable_Flux_Acti($s->db);
        
        //
        //récupère les identifiants de référence
        $idMonade = $s->dbM->ajouter(array("titre"=>"valarnum"),true,false);
        $docSource = $s->dbD->findBydoc_id($doc['idDoc']);
        //vrifie s'il faut mettre à jour les notes
        if($docSource['note']=="")$s->dbD->edit($docSource['doc_id'], array("note"=>json_encode($doc)));
        //récupère le document du fragment
        $idDocFrag = $s->dbD->ajouter(array("titre"=>'fragment',"tronc"=>$doc['idOmk'],"parent"=>$docSource['doc_id']
            ,"url"=>$e['img'], 'note'=>json_encode(array("x"=>$e['x'],"y"=>$e['y'],"w"=>$e['w'],"h"=>$e['h']))));
        //,,"cx":"1691","cy":"1592","r":"120","d":"excitation"}
        $idGeo = 0;
        
        //Traitement de l'évaluation
        //récupère le tag
        $idTagParent = $s->dbT->ajouter(array("code"=>"Roue des émotions"));
        $idTag = $s->dbT->ajouter(array("code"=>$e["d"],"parent"=>$idTagParent,"type"=>$e["color"]));
        //récupère l'action
        //$idAct = $s->dbA->ajouter(array("code"=>'évaluation émotionnelle'));
        //enregistre le rapport entre le document, l'utilisateur et son évaluation
        $idRap = $s->dbR->ajouter(array("monade_id"=>$idMonade,"geo_id"=>$idGeo
            ,"src_id"=>$idDocFrag,"src_obj"=>"doc"
            ,"pre_id"=>$this->ssUti->uti['uti_id'],"pre_obj"=>"uti"
            ,"dst_id"=>$idTag,"dst_obj"=>"tag"
            ,"niveau"=>$e['r']
            ,"valeur"=>json_encode(array("cx"=>$e['cx'],"cy"=>$e['cy'],"r"=>$e['r'],"d"=>$e['d']))
        ));
    }
    
    
    
    function initInstance(){
        $this->view->ajax = $this->_getParam('ajax');
        $this->view->idBase = $this->idBase = $this->_getParam('idBase', $this->idBase);
        
        $auth = Zend_Auth::getInstance();
        $this->ssUti = new Zend_Session_Namespace('uti');
        if ($auth->hasIdentity()) {
            // l'identité existe ; on la récupère
            $this->view->identite = $auth->getIdentity();
            $this->ssUti->uti['mdp'] = '***';
            $this->view->login = $this->ssUti->uti['login'];
            $this->view->uti = json_encode($this->ssUti->uti);
        }else{
            //$this->view->uti = json_encode(array("login"=>"inconnu", "id_uti"=>0));
            $this->ssUti->redir = "/valarnum";
            $this->ssUti->dbNom = $this->idBase;
            if($this->view->ajax)$this->redirect('/auth/finsession');
            else $this->redirect('/auth/login');
        }
        
    }
    
    
}




<?php
/**
 * FlickEmoController
 *
 * Controller pour l'exploration emotionnelle de FlickR
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class FlickemoController extends Zend_Controller_Action
{
    var $idBase = "flux_flickemo";
    
    public function indexAction()
    {
                
    }

    public function utiAction()
    {
        $this->initInstance();
        $this->view->uti = $this->s->getUti($login, $flux);
        
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
        $this->view->nb =  $this->_getParam('nb', 100);//nombre de photo récupérée de la base de données
        $this->view->nbTof =  $this->_getParam('nbTof', 10);//nombre de photo dans la mosaïque
        $s = new Flux_Site($this->idBase);
        $s->dbM = new Model_DbTable_Flux_Monade($s->db);        
        $this->view->idMonade = $s->dbM->ajouter(array("titre"=>"valarnum"),true,false);
        
        
    }
    
    public function evalacteursAction(){
        
        $this->initInstance();
    
    }

    public function identacteursAction(){
        
        $this->initInstance();
        //récupère le nombre total de photo
        $an = new Flux_An($this->idBase);
        //ATTENTION nb de Data != Nb de Visage car il manque des dates
        //$arr = $an->getNbVisage();
        $arr = $an->getVisagesDatas("","",true);
        
        $this->view->nbTof = $arr[0]['nbVisage'];
        
        
    }
    
    public function photofacettesAction(){
        
        $this->initInstance();
        
    }

    public function visagefacettesAction(){
        
        
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
    
    public function editacteurAction() {
        $this->initInstance();
        //initialise les objets
        $s = new Flux_Site($this->idBase, $this->_getParam('trace'));
        $s->dbE = new Model_DbTable_Flux_Exi($s->db);

        //récupère les paramètres
        $params = $this->_request->getParams();
        $s->trace("params",$params);
        $params = $this->cleanParamZend($params);
        
        //construction des données
        $id = $params['recid'];
        unset($params['recid']);
        if($params['nait']=='')unset($params['nait']);
        if($params['mort']=='')unset($params['mort']);
        unset($params['confiance']);
        
        
        //met à jour les données
        $this->view->rs = $s->dbE->edit($id, $params);
        $this->view->message = "L'acteur est mis à jour";
    }        
    
    public function ajoutacteurlienAction() {
        $this->initInstance();
        //initialise les objets
        $s = new Flux_An($this->idBase, $this->_getParam('trace'));
        $s->dbE = new Model_DbTable_Flux_Exi($s->db);
        $s->dbR = new Model_DbTable_Flux_Rapport($s->db);
        
        //récupère les paramètres
        $params = $this->_request->getParams();
        $s->trace("params",$params);
        $data = $params['data'];
        
        //construction des données
        if($data['nait']=='')unset($data['nait']);
        if($data['mort']=='')unset($data['mort']);
        $confiance = $data['confiance'] ? $data['confiance'] : 1;
        
        if($data['recid']){
            $idExi = $data['recid'];            
        }else{
            unset($data['recid']);
            unset($data['confiance']);
            //ajoute l'acteur
            $idExi = $s->dbE->ajouter($data);
        }
        
        $this->view->id = $idExi;        
        
        //enregistre le rapport
        $s->dbR->ajouter(array("monade_id"=>$s->idMonade,"geo_id"=>$s->idGeo
            ,"src_id"=>$params['idDoc'],"src_obj"=>"doc"
            ,"dst_id"=>$idExi,"dst_obj"=>'exi'
            ,"pre_id"=>$params['idUti'],"pre_obj"=>"uti"
            ,"niveau"=>$confiance
        ));
        
        //récupère les stats de la photo
        $this->view->rs = $s->getActeursContexte($params['gpId'],"statTof",$params['pId'],$params['tId']);
        
        $this->view->message = "Le lien est ajouté.";
        
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
        //verifie s'il faut mettre à jour les notes
        if($docSource['note']=="")$s->dbD->edit($docSource['doc_id'], array("note"=>json_encode($doc)));
        //récupère le document du fragment
        $idDocFrag = $s->dbD->ajouter(array("titre"=>'fragment',"tronc"=>$doc['idOmkMedia'],"parent"=>$docSource['doc_id']
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
            ,"valeur"=>json_encode(array("cx"=>$e['cx'],"cy"=>$e['cy'],"r"=>$e['r'],"d"=>$e['d'],"color"=>$e['color']))
        ));
    }
    
    
    
    function initInstance(){
        $this->view->idBase = $this->idBase = $this->_getParam('idBase', $this->idBase);
        $this->s = new Flux_Flickemo($this->idBase);        
    }
    
    function cleanParamZend($params){
        //enlève les paramètres Zend
        unset($params['controller']);
        unset($params['action']);
        unset($params['module']);
        unset($params['idBase']);
        
        return $params;
        
    }
    
}




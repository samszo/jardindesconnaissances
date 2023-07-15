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

    /**
     * action pour récupérer les informations d'un utilisateur
     *
     * @param  	int 		$idUti = l'identifiant de l'utilisateur
     * @param  	int  		$idGallerie = identifiant de la gallerie
     * @param  	int  		$idGroupe = identifiant du groupe
     *
     */
    public function utiAction()
    {
        $this->initInstance();
        $this->view->uti = $this->s->getUti($this->_getParam('login'), $this->_getParam('flux'));        
        $this->view->objets = $this->s->getObjetsForUti($this->view->uti["idUti"]);
    }
    
    /**
     * action pour enregistrer un objet flickR pour un utilisateur
     *
     * @param  	int 		$idUti = l'identifiant de l'utilisateur
     * @param  	int  		$idObjet = identifiant de l'objet 
     * @param  	string  	$objet = type d'objet : gallerie, groupe
     *
     */
    public function setobjutiAction()
    {
        $this->initInstance();
        $this->view->data = $this->s->setObjetForUti($this->_getParam('idUti'),$this->_getParam('id'),$this->_getParam('objet'));
    }
    

    /**
     * action pour récupérer la définition d'une source
     *
     * @param  	int  		$id = identifiant de la source
     *
     */
    public function getsourceAction()
    {
        if($this->_getParam('id')){
            $this->initInstance();        
            $this->view->data = $this->s->getPhotosFromDoc($this->_getParam('id'));
        }else{
            $this->view->data = array("error"=>"Aucun identfiant de source");
        }
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
    
    
    public function sauveAction() {
        $this->initInstance();
        if($this->_getParam('q', 0)){
            switch ($this->_getParam('q')){
                case "emo":
                    $this->s->saveEmo($this->_getParam('doc'),$this->_getParam('eval'),$this->_getParam('idUti'));
                    $this->view->data =  array('message'=>'OK', 'idBase'=>$this->idBase);
                break;
            }
        }
    }
    
    function initInstance(){
        $this->view->idBase = $this->idBase = $this->_getParam('idBase', $this->idBase);
        $this->s = new Flux_Flickemo($this->idBase,$this->_getParam('trace'));        
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




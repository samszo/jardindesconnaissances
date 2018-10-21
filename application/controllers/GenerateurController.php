<?php
/**
 * GenerateurController
 *
 * Porte d'entrée du jardin des connaissances
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class GenerateurController extends Zend_Controller_Action
{
    var $idBase = "generateur";

    public function init()
    {
    	
    }

    public function indexAction()
    {

    	
    }

    public function importAction()
    {
        $oDico = new Gen_Dico();
        if($this->_getParam('csv') && $this->_getParam('csv') && $this->_getParam('path')){
            //importation d'un fichier csv
            $oDico->importCSV($this->_getParam('csv'), $this->_getParam('path'));	
        }

    	
    }

    public function gestionAction()
    {
        $this->initInstance("/gestion");        
    	
    }

    public function creaimportAction()
    {
    	
    }

    function initInstance($redir){
        $this->view->ajax = $this->_getParam('ajax');
        $this->view->idBase = $this->idBase = $this->_getParam('idBase', $this->idBase);
        
        $auth = Zend_Auth::getInstance();
        $this->ssUti = new Zend_Session_Namespace('uti');
        $ssGoogle = new Zend_Session_Namespace('google');
        
        if ($auth->hasIdentity() || isset($this->ssUti->uti)) {
            //utilisateur authentifier
            $this->ssUti->uti['mdp'] = '***';
            $this->view->login = $this->ssUti->uti['login'];
            $this->view->uti = json_encode($this->ssUti->uti);
        }elseif($this->_getParam('idUti') ){
            //authentification CAS ou google
            $s = new Flux_Site($this->idBase);
            $dbUti = new Model_DbTable_Flux_Uti($s->db);
            $uti = $dbUti->findByuti_id($this->_getParam('idUti'));
            $this->ssUti->uti = $uti;
            $this->ssUti->uti['mdp'] = '***';
            $this->view->login = $this->ssUti->uti['login'];
            $this->view->uti = json_encode($uti);                        
        }else{
            //$this->view->uti = json_encode(array("login"=>"inconnu", "id_uti"=>0));
            $this->ssUti->redir = "/generateur".$redir;
            $this->ssUti->dbNom = $this->idBase;
            if($this->view->ajax)$this->redirect('/auth/finsession');
            else $this->redirect('/auth/connexion');
        }
        
    }
        
    
}




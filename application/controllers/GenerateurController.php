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
    var $idBaseGen = "generateur";
    var $maxExeTime = 180;

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
        $sGen = new Flux_Site($this->idBaseGen);
    	
    }

    public function creaimportAction()
    {
    	
    }

    public function apiAction()
    {   
        $this->initInstance("/gestion");        
        $sGen = new Flux_Site($this->idBaseGen);
        $o = $this->_getParam('o');
        switch ($this->_getParam('v')) {
            case 'test':
                set_time_limit($this->maxExeTime);
                //initialisation du moteur
                $m = new Gen_Moteur($this->idBaseGen);                        
                //paramétrage du moteur
                $arrDico = $m->getDicosOeuvre($this->_getParam('idOeu'));
                $arrText = $this->_getParam('txts',[]);
                $this->view->r = $m->Tester($arrText,$arrDico,$this->_getParam('trace',false));
                break;
            case 'gen':
                set_time_limit($this->maxExeTime);
                //initialisation du moteur
                $m = new Gen_Moteur($this->idBaseGen);                        
                //paramétrage du moteur
                $m->arrDicos = $m->getDicosOeuvre($this->_getParam('idOeu'));
                $m->showErr = $this->_getParam('err',false);
                $m->bTrace = $this->_getParam('trace',false);
                $m->forceCalcul = $this->_getParam('force',true);
                $m->coupures = $this->_getParam('coupures',false);
                $m->finLigne = $this->_getParam('rtn','\n');
                $txtGen = $this->_getParam('txt');
                $nb = $this->_getParam('nb',1);
                $this->view->r = array();
                for ($i = 0; $i < $nb; $i++) {
                    $txt = $m->Generation($txtGen);
                    //if($rtn == "\n")$txt = str_replace("<br/>", "\n", $txt);
                    $this->view->r[] = array('txt'=>$txt,'detail'=>$m->arrClass);
                }       
                break;
            case 'c':
                # code...
                break;
            case 'r':
                switch ($o) {
                    case 'oeuvre':
                        $db = new Model_DbTable_Gen_Oeuvres($sGen->db);
                        $this->view->r = $db->getAll();
                        break;
                    case 'uti':
                        $db = new Model_DbTable_Flux_uti($sGen->db);
                        $this->view->r = $db->getAll();
                        break;
                    case 'conj':                    
                        $db = new Model_DbTable_Gen_conjugaisons($sGen->db);
                        $this->view->r = $db->getAll();
                        break;
                    case 'dico':
                        if($this->_getParam('idOeu')){
                            $db = new Model_DbTable_Gen_oeuvresxdicosxutis($sGen->db);
                            $this->view->r=$db->findByIdOeu($this->_getParam('idOeu'));
                        }
                        if($this->_getParam('idDico')){
                            $db = new Model_DbTable_Gen_concepts($sGen->db);
                            $this->view->r=$db->getAllByDico($this->_getParam('idDico'),$this->_getParam('type'));
                        }
                        if($this->_getParam('idConcept')){
                            $dbG = new Model_DbTable_Gen_generateurs($sGen->db);
                            $dbC = new Model_DbTable_Gen_concepts($sGen->db);
                            $r['gen']=$dbG->findByIdConcept($this->_getParam('idConcept'));
                            $r[$this->_getParam('type')]=$dbC->getItemByType($this->_getParam('idConcept'),$this->_getParam('type'));                            
                            $this->view->r = $r;
                        }
                        break;                    
                    default:
                        $this->view->r=array('erreur'=>"Pas d'objet");
                        break;
                }
                break;
            case 'u':
                # code...
                break;
            case 'd':
                # code...
                break;
            default:
                $this->view->r=array('erreur'=>'Aucun verbe');
                break;
        }
    	
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




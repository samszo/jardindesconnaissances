<?php

/**
 * FluxController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class FluxController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->view->title = "Flux disponibles";
	    $this->view->headTitle($this->view->title, 'PREPEND');
	    $this->view->flux = array("tags pour un utilisateur", "utilisateurs en relation");
	}

    public function toustagsAction()
    {
	    if ($this->getRequest()->isPost()) {
	        $calculer = $this->getRequest()->getPost('calculer');
	    } else {
	        $dbTags = new Model_DbTable_Flux_Tag();
	        $this->view->tags = $dbTags->getAll();
	    }
    }	
    
    public function tagsAction()
    {
	    $dbTags = new Model_DbTable_Flux_UtiTag();
	    if($this->_getParam('uti', 0)){
		    $this->view->tags = $dbTags->findTagByUti($this->_getParam('uti', 0));	    	
	    }else{
		    $this->view->tags = $dbTags->findTagUti();	    	
	    }
    }	

    public function docsAction()
    {
		$rs = array();
		if($this->_getParam('tag', 0) && $this->_getParam('liste', 0)){
			$idBase = $this->_getParam('idBase', false);
			$s = new Flux_Site($idBase);
			$dbD = new Model_DbTable_flux_tagdoc($s->db);
			$racine = $this->_getParam('racine', false);
			if($idBase=="flux_zotero" && $racine){
				$rs = $dbD->findDocTroncByTagId($this->_getParam('tag', 0),$this->_getParam('tags', 0));
			}else{
				$rs = $dbD->findByTagId($this->_getParam('tag', 0), $this->_getParam('tags', 0), $racine);
			}
		}
	    if($this->_getParam('tag', 0) && $this->_getParam('uti', 0)){
	    	$dbUD = new Model_DbTable_Flux_UtiDoc();
	    	$arr = $dbUD->findDocByUtiTag($this->_getParam('uti', 0),$this->_getParam('tag', 0));
		    foreach ($arr as $d){
		    	$arrT = $dbUD->findTagByDocUti($d["doc_id"],$d["uti_id"]);
		    	//formatage du tableau des tags
		    	$tags = array();
		    	foreach ($arrT as $t){
		    		$tags[] = $t['code'];
		    	}
		    	$r = array("d"=>$d["titre"],"dt"=>$d["pubDate"],"u"=>$d["url"],"t"=>$tags,"oD"=>$d,"oT"=>$arrT);
		    	$rs[] = $r;
		    }
	    }
	    $this->view->docs = $rs;		    
    }
    	
	public function ajoututitagAction() {
		try {
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
			    // l'identité existe ; on la récupère
			    $this->view->identite = $auth->getIdentity();
				$ssUti = new Zend_Session_Namespace('uti');
				//enregistre les positions
				$o = new Flux_Site($this->_getParam('db', 0));
				$o->user = $ssUti->idUti;
				$d = new Zend_Date();
				$this->view->data = $o->saveTag($this->_getParam('tag', 0), $this->_getParam('idDoc', 0), $this->_getParam('poids', 0),$d->get("c"));
				
				$dbUTD = new Model_DbTable_Flux_UtiTagDoc($o->db);		
				$this->view->data = $dbUTD->GetUtiTagDoc($ssUti->idUti, $this->_getParam('idDoc', 0));
				
			}else{
			    $this->_redirect('/auth/login');
			}
			
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}    
	
	public function gettutitagsAction() {
		try {
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
			    // l'identité existe ; on la récupère
				$o = new Flux_Site($this->_getParam('db', 0));
				$dbUTD = new Model_DbTable_Flux_UtiTagDoc($o->db);		
				$this->view->data = $dbUTD->GetUtiTags($this->_getParam('idUti', 0),"LENGTH(t.code) > 3", "SUM(td.poids) >= 1");
				
			}else{
			    $this->_redirect('/auth/login');
			}
			
		}catch (Zend_Exception $e) {
	          echo "Récupère exception: " . get_class($e) . "\n";
	          echo "Message: " . $e->getMessage() . "\n";
		}
	}    
	
}
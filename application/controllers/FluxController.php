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
		$s = new Flux_Site($this->_getParam('idBase', false));	    	
    		$dbTags = new Model_DbTable_Flux_UtiTag($s->db);
    		if($this->_getParam('uti', 0)){
			$this->view->tags = $dbTags->findTagByUti($this->_getParam('uti', 0),$this->_getParam('flux', ""));
    		}elseif($this->_getParam('idUti', 0)){	    	
			$this->view->tags = $dbTags->findTagByUtiId($this->_getParam('idUti', 0),$this->_getParam('flux', ""));
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
	    	$dbUTD = new Model_DbTable_Flux_UtiTagDoc();
	    	$arr = $dbUTD->findByTagLogin($this->_getParam('tag', 0),$this->_getParam('uti', 0));
		    foreach ($arr as $d){
		    	$arrT = $dbUTD->GetUtiTagDoc($d["uti_id"],$d["doc_id"]);
		    	//formatage du tableau des tags
		    	$tags = array();
		    	foreach ($arrT as $t){
		    		$tags[] = $t['code'];
		    	}
		    	$r = array("d"=>$d["titre"],"dt"=>$d["pubDate"],"u"=>$d["url"],"t"=>$tags,"oD"=>$d,"oT"=>$arrT);
		    	$rs[] = $r;
		    }
	    }
	    if($this->_getParam('idsDoc', 0)){
			$idBase = $this->_getParam('idBase');
			$s = new Flux_Site($idBase);
		    	$dbUTD = new Model_DbTable_Flux_UtiTagDoc($s->db);
		    	$rs = $dbUTD->GetDocTags($this->_getParam('idsDoc'));
	    }
	    $this->view->html = $this->_getParam('html', false);		    
	    $this->view->docs = $rs;		    
    }
    	
	public function ajoututitagAction() {
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
			    // l'identité existe ; on la récupère
			    $this->view->identite = $auth->getIdentity();
				$ssUti = new Zend_Session_Namespace('uti');
				//enregistre les positions
				$o = new Flux_Site($this->_getParam('db', 0));
				$o->user = $ssUti->idUti;
				$d = new Zend_Date();
				$this->view->data = $o->saveTag($this->_getParam('tag', 0), $this->_getParam('idDoc', 0), $this->_getParam('poids', 0)
					,$d->get("c"), -1, $this->_getParam('existe', true));
				
				$dbUTD = new Model_DbTable_Flux_UtiTagDoc($o->db);		
				$this->view->data = $dbUTD->GetUtiTagDoc($ssUti->idUti, $this->_getParam('idDoc', 0));
				
			}else{
			    $this->_redirect('/auth/login');
			}
	}       
		
	public function gettutitagsAction() {
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
		    // l'identité existe ; on la récupère
			$o = new Flux_Site($this->_getParam('db', 0));
			$dbUTD = new Model_DbTable_Flux_UtiTagDoc($o->db);		
			$this->view->data = $dbUTD->GetUtiTags($this->_getParam('idUti', 0),"LENGTH(t.code) > 3", "SUM(td.poids) >= 1");
			
		}else{
		    $this->_redirect('/auth/login');
		}
	}    

	public function showshorturlAction() {
			$o = new Flux_Gurl(null, null, "flux_urlcourtes");
			$this->view->docs = $o->getUrlSave();
			$this->view->html=true;			
	}    

	public function paroleAction() {
		    if($this->_getParam('txt', 0)){			
				$o = new Flux_Audio();
				$this->view->file = $o->getGoogleParole($this->_getParam('txt'));
		    }
	}    

	public function savekwurlAction() {
	    if($this->_getParam('idBase', 0) && $this->_getParam('url', 0)){			

	    }
	}    

    /**
     * Enregistre le flux d'une source
     *
     */
    public function savefluxAction()
    {
    		if($this->_getParam('flux')=="diigo" && $this->_getParam('login', 0)){
    			$d = new Flux_Diigo($this->_getParam('login', 0),"",$this->_getParam('idBase'));
    			$this->view->data = 	$d->saveUserTag($this->_getParam('login'));
    		}
    		
    }

	public function sauvedocAction()
    {
    		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			//vérifie les paramètres
			if($this->_getParam('tronc') && $this->_getParam('data') && $this->_getParam('titre') 
				&& $this->_getParam('idUti')){
		    		$s = new Flux_Site($this->_getParam('idBase'));
		    		$dbDoc = new Model_DbTable_Flux_Doc($s->db);
		    		//vérifie s'il faut jsonner les data
		    		//ATTENTION JSON_NUMERIC_CHECK ne marche qu'avec PHP >= 5.3.3
		    		if(is_array($this->_getParam('data')))$data=json_encode($this->_getParam('data'),JSON_NUMERIC_CHECK);
		    		else $data = $this->_getParam('data');
		    		if($this->_getParam('idDoc')){
					$dbDoc->edit($this->_getParam('idDoc'), array(
				    			"titre"=>$this->_getParam('titre')
				    			,"data"=>$data
				    			,"tronc"=>$this->_getParam('tronc')
				    		),false);
			    		$rs = array("test"=>"oui");
		    		}else{
					$rs = $dbDoc->ajouter(array(
				    			"titre"=>$this->_getParam('titre')
				    			,"data"=>$data
				    			,"tronc"=>$this->_getParam('tronc')
				    		),false, true);			    		
		        		$dbUtiDoc = new Model_DbTable_Flux_UtiDoc($s->db);		    		
			    		$dbUtiDoc->ajouter(array("doc_id"=>$rs["doc_id"],"uti_id"=>$this->_getParam('idUti')));
		    		}		    		
 		    			
		        $this->view->rs = $rs;
			}else{
			    $this->view->erreur = "Il manque des paramètres.";				
			}			
		}else{			
		    $this->view->erreur = "aucun utilisateur connecté";
		}   	        
    }
    
	public function getdocsAction()
    {
    		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			$s = new Flux_Site($this->_getParam('idBase'));
		    	$dbDoc = new Model_DbTable_Flux_Doc($s->db);			
			//recherche par tronc
			if($this->_getParam('tronc')){
				$this->view->rs = $dbDoc->findByTronc($this->_getParam('tronc'),true);
		    	}
			//recherche par utilisateur
		    	if($this->_getParam('idUti')){
				$this->view->rs = $dbDoc->findByUti($this->_getParam('idDoc'),true);
	    		}		    		
		    	if(!$this->view->rs){
	    		    $this->view->erreur = "Il manque des paramètres.";				
			}						
		}else{			
		    $this->view->erreur = "aucun utilisateur connecté";
		}   	        
    }    
	public function databnftermAction()
    {
    		$s = new Flux_Site();
		$this->view->reponse = $s->getUrlBodyContent("http://data.bnf.fr/search-letter/?term=".urlencode($this->_getParam('term')));
    }

	public function databnfbioAction()
    {
    		$s = new Flux_Site();
	   	$format = 'json';	 
	   	
	   	//récupère les infos de data bnf
	   	$query =
		'SELECT DISTINCT ?idArk ?nom ?prenom ?nait ?mort  WHERE
		{
		?idArk bnf-onto:FRBNF "'.$this->_getParam('idBNF').'"^^xsd:integer;
		foaf:focus ?identity.
		?identity foaf:familyName ?nom;
		foaf:givenName ?prenom.
		OPTIONAL {?identity bio:birth ?nait.}
		OPTIONAL {?identity bio:death ?mort.} 
		}';	   			   	
	   	$searchUrl = 'http://data.bnf.fr/sparql?'
	    	.'query='.urlencode($query)
	      	.'&format='.$format;
		$result = $s->getUrlBodyContent($searchUrl,false,false);
		$obj1 = json_decode($result);
		//construction de la réponse
		$objResult->idArk = $obj1->results->bindings[0]->idArk->value;				
		$objResult->nom = $obj1->results->bindings[0]->nom->value;				
		$objResult->prenom = $obj1->results->bindings[0]->prenom->value;				
		$objResult->nait = $obj1->results->bindings[0]->nait->value;				
		$objResult->mort = $obj1->results->bindings[0]->mort->value;				
		
		//récupère les données liées
		$query =
		'SELECT DISTINCT ?p ?o WHERE {
		<'.$obj1->results->bindings[0]->idArk->value.'> ?p ?o.
		}';	   	
	   	$searchUrl = 'http://data.bnf.fr/sparql?'
	    	.'query='.urlencode($query)
	      	.'&format='.$format;
		$result = $s->getUrlBodyContent($searchUrl,false,false);
		$obj2 = json_decode($result);		
		//construction de la réponse
		$objResult->liens = array();
		foreach ($obj2->results->bindings as $val) {
			//ajoute les liens direct
			if($val->p->value=="http://www.w3.org/2004/02/skos/core#exactMatch")
				array_push($objResult->liens, $val->o->value);
			//ajout l'isni
			if($val->p->value=="http://isni.org/ontology#identifierValid")
				$objResult->isni = $val->o->value;				
		}
		$this->view->reponse = json_encode($objResult);
    }
    
	public function googleAction()
    {
		$ssGoogle = new Zend_Session_Namespace('google');
		$ssGoogle->type = $this->_getParam('type');
		$ssGoogle->gDocId = $this->_getParam('gDocId');
		if(!$ssGoogle->client || $this->verifExpireToken($ssGoogle)){
			$this->_redirect('/auth/google?scope='.$this->_getParam('scope',"Drive"));
		}elseif ($this->_getParam('logout')){
			$this->_redirect('/auth/google?logout=1');			
		}else{
			if($ssGoogle->type == 'css' && $ssGoogle->gDocId){
				$gDrive = new Flux_Gdrive($ssGoogle->token);
				$this->view->content =  $gDrive->downloadFile($ssGoogle->gDocId,'text/csv');
			}else{
				$this->view->google = $ssGoogle;
			}
		}
    }

	private function verifExpireToken($ss){
		$ss->client->setAccessToken($ss->token);
		if ($ss->client->isAccessTokenExpired()) {
		    $ss->token=false;
		}
	}    
}

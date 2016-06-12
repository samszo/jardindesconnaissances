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
    		}elseif($this->_getParam('parent', 0)){	    	
    			$dbTags = new Model_DbTable_Flux_Tag($s->db);
    			$this->view->tags = $dbTags->getFullChild($this->_getParam('parent'),"code");			
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
			if($this->_getParam('exi', 0)){
				$dbETD = new Model_DbTable_Flux_ExiTagDoc($o->db);
				$where = "";
				if($this->_getParam('parent', 0))$where = "t.parent=".$this->_getParam('parent');		
				$this->view->data = $dbETD->GetUtiTags($this->_getParam('idUti', 0),$where);
			}else{
				$dbUTD = new Model_DbTable_Flux_UtiTagDoc($o->db);		
				$this->view->data = $dbUTD->GetUtiTags($this->_getParam('idUti', 0),"LENGTH(t.code) > 3", "SUM(td.poids) >= 1");
			}			
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
    
	public function sudocAction()
    {
    		$s = new Flux_Databnf();
    		if($this->_getParam('isbn')){
			$this->view->reponse = json_encode($s->getSudocAutoriteByISBN($this->_getParam('isbn')));    			
    		} 
    }
    

	public function databnfAction()
    {
    		$bnf = new Flux_Databnf();
	   	
	   	switch ($this->_getParam('obj')) {
	   		case 'term':
				$this->view->reponse = $bnf->getUrlBodyContent("http://data.bnf.fr/search-letter/?term=".urlencode($this->_getParam('term')));
	   			break;	   		
	   		case 'bio':
				$this->view->reponse = $bnf->getBio($this->_getParam('idBNF'));
	   			break;	   		
	   		case 'rameau':
				$this->view->reponse = json_encode($bnf->getRameauByIdBnf($this->_getParam('idBNF')));
	   			break;	   		
	   		default:
	   			break;
	   	}
    }

	public function bup8Action()
    {
    		$buP8 = new Flux_Bup8($this->_getParam('idBase'));
	   	$buP8->bTrace = false;
	   	$buP8->trace(__METHOD__." : ".$this->_getParam('obj'));
	   	
	   	switch ($this->_getParam('obj')) {
	   		case 'getListe':
				$this->view->reponse = $bnf->getUrlBodyContent("http://data.bnf.fr/search-letter/?term=".urlencode($this->_getParam('term')));
	   			break;	   		
	   		case 'setListe':
	   			$buP8->trace('idListe='.$this->_getParam('idListe'));
	   			$arr = $buP8->setListe($this->_getParam('idListe'));
	   			$buP8->trace('reponse',$arr);
	   			$this->view->reponse = json_encode($arr);
				break;	   		
	   		case 'getLivre':
				$this->view->reponse = $bnf->getUrlBodyContent("http://data.bnf.fr/search-letter/?term=".urlencode($this->_getParam('term')));
	   			break;	   		
	   		case 'setLivre':
				$this->view->reponse = $buP8->setInfoPageLivre($this->_getParam('idLivre'));
	   			break;	   		
	   		default:
	   			break;
	   	}
	   	$buP8->trace('FIN');
    }

    public function rmnAction()
    {
    		$rmn = new Flux_Rmngp();
	   	
	   	switch ($this->_getParam('obj')) {
	   		case 'autocomplete':
				$this->view->reponse = $rmn->getAutocomplete($this->_getParam('q'));
	   			break;
	   		case 'suggestion':
				$this->view->reponse = $rmn->getSuggestion($this->_getParam('id'));
	   			break;
	   		default:
	   			break;
	   	}
    }
    
	public function googleAction()
    {
    		//service n'ayant pas besoin d'une authentification cliente
    		switch ($this->_getParam('type')) {
    			case "trouveLivre":
	    			$g = new Flux_Gbooks();
	    			$arr = $g->findBooks($this->_getParam('q'));
	    			$this->view->content =  json_encode($arr);
				break;
    			case "cssOpen":
	    			$s = new Flux_Site();
	    			$this->view->content =  $s->getUrlBodyContent("https://docs.google.com/spreadsheets/d/".$this->_getParam('gDocId')."/pub?gid=0&single=true&output=csv",false,false);
				break;				
    			default:
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
				break;
    		}
    }

	public function googlekgAction()
    {
		$g = new Flux_Gknowledgegraph();
		if($this->_getParam('q'))$this->view->reponse = $g->getQuery($this->_getParam('q'));
		else $this->view->reponse = "Il manque un paramètre.";
    }    
    
	public function dbpediaAction()
    {
    		$dbp = new Flux_Dbpedia();
	   	$dbp->bTrace = false;
	   	switch ($this->_getParam('obj')) {
	   		case 'bio':
				$this->view->reponse = $dbp->getBio($this->_getParam('res'));
	   			break;	   		
	   		default:
	   			break;
	   	}
    }

	public function skosAction()
    {
	   	
	   	switch ($this->_getParam('obj')) {
	   		case 'saveToSpip':
	   			if($this->_getParam('idBase')){
			    		$s = new Flux_Skos($this->_getParam('idBase'),true);
    					$s->dbGM = new Model_DbTable_Spip_groupesxmots($s->db);
			    		if($this->_getParam('csv')){
	   					$arrMC = $s->csvToArray($this->_getParam('csv'));
	   					for ($i = 1; $i < count($arrMC); $i++) {
	   						//foreach ($arrMC as $mc) {
	   						$mc = $arrMC[$i];
	   						$s->trace("mot-clef csv",$mc);
	   						//recherche le groupe de mot
	   						$gm = $s->dbGM->ajouter(array('titre'=>$mc[1]),true,true);
	   						//récupère l'uri du concept
	   						$uri = $s->getUriByLabel($mc[0]);
	   						if($uri){
		   						//enregistre le mot clef
		   						$s->sauveToSpip($uri."/json", $gm);
	   						}else $s->trace("PAS DANS SKOS",$mc[0]);
	   					}
	   				}
	   			}else $this->view->reponse = 'Il manque des paramètres';
	   			break;	   		
	   		default:
	   			break;
	   	}
    }
    
	private function verifExpireToken($ss){
		$ss->client->setAccessToken($ss->token);
		if ($ss->client->isAccessTokenExpired()) {
		    $ss->token=false;
		}
	}    
}


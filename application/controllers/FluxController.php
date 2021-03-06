<?php

/**
 * FluxController
 * 
 * Pour gérer les flux d'informations
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\Controller\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
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

	public function ajaxAction() {
		$s = new Flux_Site(false,false);
		$u = $this->_getParam('u', "http://www.google.com");
		$s->trace($u);
		$ch = curl_init ($u) ;
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1) ;
        $res = curl_exec ($ch) ;
        curl_close ($ch) ;
		$this->view->content = $res;
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

		
	public function csvtojsonAction() {
		$s = new Flux_Site();
		$this->view->result = $s->csvToJson($this->_getParam('url'));
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
		switch ($this->_getParam('q')) {
			case 'getFolderImg':
				$path = '/Users/samszo/Sites/jdc/public/img/rethofalla/';
				$url = 'http://localhost/jdc/public/img/rethofalla/';
				$files=scandir($path);
				$i = 0;
				$rs = array();
				foreach ($files as $f) {
					if (!in_array($f,array(".",".."))) {
						$rs[]= array('src'=>$url.$f, 'id'=>$i);
						$i++;
					}
				}
				if($this->_getParam('csv')){
					$s = new Flux_Site();
					foreach ($rs as $v) {
						if(!$this->view->rs)$this->view->rs = $s->arrayToCsv(array_keys($v),",").PHP_EOL;
						$this->view->rs .= $s->arrayToCsv($v,",").PHP_EOL;
					}
				}else
					$this->view->rs = json_encode($rs);		
				break;			
			default:
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
					if(!$this->view->rs)
						$this->view->erreur = "Il manque des paramètres.";	
					else
						$this->view->rs = json_encode($this->view->rs);			
				}else{			
					$this->view->erreur = "aucun utilisateur connecté";
				}   	        				
				break;
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
		$bnf = new Flux_Databnf($this->_getParam('idBase','flux_databnf'),$this->_getParam('trace'));
		$bnf->bTraceFlush = true;
	   	switch ($this->_getParam('obj')) {
	   		case 'term':
				$this->view->reponse = $bnf->getUrlBodyContent("http://data.bnf.fr/search-letter/?term=".urlencode($this->_getParam('term')));
	   			break;	   		
	   		case 'bio':
				$this->view->reponse = $bnf->getBio($this->_getParam('idBNF'));
	   			break;	   		
	   		case 'rameau':
				$this->view->reponse = json_encode($bnf->getRameau($this->_getParam('idBNF',''),$this->_getParam('label',''),$this->_getParam('niv',0),$this->_getParam('nivMax',1)));
				break;	   		
			case 'saveAudio':
				$query = '(dc.creator all "Deleuze, Gilles" or dc.contributor all "Deleuze, Gilles" )';
				$this->view->reponse = $bnf->saveAudio($this->_getParam('q',$query),201);			
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
	   		case 'getListeLivre':
	   			$this->view->reponse = $buP8->getListeLivre($this->_getParam('idListe'));
	   			break;	   		
   			case 'getListe':
	   			$this->view->reponse = json_encode($buP8->getListe());
	   			//$this->view->reponse = $bnf->getUrlBodyContent("http://data.bnf.fr/search-letter/?term=".urlencode($this->_getParam('term')));
	   			break;	   				 
	   		case 'setListe':
	   			$buP8->trace('idListe='.$this->_getParam('idListe'));
	   			$arr = $buP8->setListe($this->_getParam('idListe'));
	   			$buP8->trace('reponse',$arr);
	   			$this->view->reponse = json_encode($arr);
				break;	   		
	   		case 'getLivre':
				$this->view->reponse = $buP8->getUrlBodyContent("http://data.bnf.fr/search-letter/?term=".urlencode($this->_getParam('term')));
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
    			case "verifUrl":
    				$s = new Flux_Site();
    				$url = "https://searchconsole.googleapis.com/v1/urlTestingTools/mobileFriendlyTest:run";
    				$url .= "?fields=mobileFriendliness,mobileFriendlyIssues,resourceIssues,screenshot,testStatus"
    						."&key=".KEY_GOOGLE_SERVER;
    				$param = array(
    						"requestScreenshot"=>"true"
    						,"url"=>$this->_getParam('url') 
    				); 
    				$this->view->content = $s->getUrlBodyContent($url,$param,false,Zend_Http_Client::POST);
    				break;
    			case "analyseImage":
    			    $g = new Flux_Gvision();
    			    $this->view->content = $g->analyseImage($this->_getParam('q','http://localhost/jdc/data/an/photos/FRAN_0023_00033_L-medium.jpg'));
    			    break;
    			case "analyseTexte":
                break;    			    
    			case "trouveLivre":
	    			$g = new Flux_Gbooks();
	    			$arr = $g->findBooks($this->_getParam('q'));
	    			$this->view->content =  json_encode($arr);
				break;
    			case "csvOpen":
	    			$s = new Flux_Site();
	    			$url = "https://docs.google.com/spreadsheets/d/".$this->_getParam('gDocId')."/pub?gid=".$this->_getParam('gid',0)."&single=true&output=csv";
	    			$this->view->content = $s->getUrlBodyContent($url,false,false);
				break;				
			case "album":
					//attention il faut rendre les albums public cf. https://casper.baghuis.nl/google-photos/Google-Photos-RSSerator.html
					//IMPOSSIBLE DEPUIS Février 2017
					$s = new Flux_Site();
					$url = "http://photos.googleapis.com/data/feed/api/user/".$this->_getParam('userId')."/albumid/".$this->_getParam('albumId')."?alt=json";
					$this->view->content =  $s->getUrlBodyContent($url);
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
						if($ssGoogle->type == 'csv' && $ssGoogle->gDocId){
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
        $g = new Flux_Gknowledgegraph($this->_getParam('idBase'),$this->_getParam('trace'));
        if($this->_getParam('q'))$this->view->reponse = $g->getQuery($this->_getParam('q'));
        elseif($this->_getParam('id'))$this->view->reponse = $g->getId($this->_getParam('id'));
        else $this->view->reponse = "Il manque un paramètre.";
		
    }    
    
	public function dbpediaAction()
    {
        $dbp = new Flux_Dbpedia($this->_getParam('idBase'),$this->_getParam('trace'));
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

    public function orcidAction()
    {
    	 
    	
    }

    public function omkAction()
    {
		$omk = new Flux_Omeka($this->_getParam('idBase',""),$this->_getParam('trace', true));
		switch ($this->_getParam('q')) {
			case 'setAnno':
				$this->view->content = $omk->postAnnotation(array());
				break;
			case 'setItem':
				$this->view->content = $omk->postItem(array());
				break;
			case 'setItemSet':
				$this->view->content = $omk->postItemSet($this->_request->getParams());
				break;			
			case 'getProps':
				$this->view->content = $omk->getProps($this->_request->getParams());
				break;
			case 'setHierarchie':
				$omkParams = OMK_PARAMS['omk_gestforma'];
				$o = $omk->initOmeka($omkParams["ENDPOINT"], $omkParams["API_IDENT"], $omkParams["API_KEY"]);
				$o->setHierarchie("dcterms:isPartOf","dcterms:isPartOf",'resource_class_id',112);						
			break;
		}
	}
    public function diigoAction()
    {
		$diigo = new Flux_Diigo($this->_getParam('login',""),$this->_getParam('mdp',""),$this->_getParam('idBase', "flux_diigo"),$this->_getParam('trace', true));
		$diigo->bTraceFlush = $this->_getParam('trace', true);
		switch ($this->_getParam('q')) {
			case 'evalActi':
				$data = $diigo->evalActi($this->_getParam('user'));
				break;
			case 'getImages':
				$data = $diigo->getImages();
				break;
			case 'getCsvToOmeka':
				$diigo->getCsvToOmeka('/Users/samszo/Sites/jdc/data/diigo/importBookmark.csv');
				break;
			case "saveOutlinerImage":
				$diigo->saveOutlinerImage('http://localhost/jdc/data/diigo/imageTheseTot.html');
				break;    		
			case "saveRecent":
				$diigo->saveRecent($this->_getParam('login'));
				break;    		
			case "saveAll":
				$diigo->saveAll($this->_getParam('login'),$this->_getParam('tags'));
				break;
			case "performance":
					$data = $diigo->getPerformance($this->_getParam('deb',''),$this->_getParam('fin',''));
				break;
			case "getTagHisto":
				$data = $diigo->getTagHisto($this->_getParam("dateUnit", '%Y-%m')
						, $this->_getParam("idUti"), $this->_getParam("idMonade")
						, $this->_getParam("idActi"), $this->_getParam("idParent")
						, $this->_getParam("arrTags"), $this->_getParam("req")
						, $this->_getParam("dates"), $this->_getParam("for"));
				break;
			case "getHistoTagLies":
				$data = $diigo->getHistoTagLies($this->_getParam("idTag")
						, $this->_getParam("dateUnit", '%Y-%m')
						, $this->_getParam("idUti"), $this->_getParam("idMonade")
						, $this->_getParam("idActi"), $this->_getParam("idParent")
						, $this->_getParam("dates"), $this->_getParam("for")
						, $this->_getParam("tags"), $this->_getParam("nbLimit",1), $this->_getParam("nbMin",0));
				break;				
			case "getStatutUrl":
				$data = $diigo->getStatutUrl($this->_getParam("dateUnit", '%Y')
						, $this->_getParam("idActi",7)
						, $this->_getParam("deb"), $this->_getParam("fin")
						, $this->_getParam("for","multiligne")
						, $this->_getParam("statuts",false));
				break;
			case "getHistoUti":
					$data = $diigo->getHistoUti();
				break;					
		}    	 
		if($this->_getParam('csv')){
			foreach ($data as $v) {
				if(!$this->view->content)$this->view->content = $diigo->arrayToCsv(array_keys($v),",").PHP_EOL;
				$this->view->content .= $diigo->arrayToCsv($v,",").PHP_EOL;
			}
		}else
			$this->view->content = json_encode($data);
					
    }

	public function euAction()
    {
		$eu = new Flux_Eu($this->_getParam('idBase', 'flux_eu'),$this->_getParam('trace'),$this->_getParam('cache',true));
		$eu->bTraceFlush = $this->_getParam('trace');    	 
		switch ($this->_getParam('f')) {
			case "setDossierObsLegi":
				$eu->setDossierObsLegi($this->_getParam('idDossier'));
				break;
			case "getParlTrackResult":
				$data = $eu->getParlTrackResult($this->_getParam('q'));				
				if($this->_getParam('set')){
					$repriseProcedure = $this->_getParam('reproc',0);
					$i=0;
					foreach ($data['procedures'] as $r => $p) {
						if($i >= $repriseProcedure)
							$eu->setDossierObsLegi($r);
						$i++;
					}
					$repriseLiens = $this->_getParam('relien',0);
					$i=0;
					foreach ($data['liens'] as $r) {
						if($i >= $repriseLiens){
							$eu->setDossierObsLegi($r['reference']
								, array('sujets'=>true,'activities'=>true,'amendements'=>false,'comeets'=>true,'votes'=>true));
						}
						$i++;
					}
				}
				break;
		}		
		$this->view->content = json_encode($data);

	}
	
	public function iemlAction()
    {
		$ieml = new Flux_Ieml($this->_getParam('idBase', 'flux_ieml'),$this->_getParam('trace'),$this->_getParam('cache',true));
		$ieml->bTraceFlush = $this->_getParam('trace');    	 
		switch ($this->_getParam('f')) {
			case "getDicoItem":
				$data = $ieml->getDicoItem($this->_getParam('ieml'));
				break;
			case "getDico":
				$data = $ieml->getDico($this->_getParam('version'));
				break;
			case "getDicoPlus":
				$data = $ieml->getDicoPlus($this->_getParam('version'));
				break;
			case "getCSV":
				$rs = $ieml->getCSV($this->_getParam('code'));
				$csv = "";
				$s = new Flux_Site();
				foreach ($rs as $v) {
					$csv .= $s->arrayToCsv($v,",").PHP_EOL;
				}
				break;

		}
		if($csv){
			header('Content-type: text/csv');
			$this->view->content = $csv;		
		}
		else $this->view->content = json_encode($data);

	}	

    public function ensuprefrAction()
    {
	    	$ensuprefr = new Flux_Ensuprefr($this->_getParam('idBase', 'flux_ecosystem'),$this->_getParam('trace'));
	    	$ensuprefr->bTraceFlush = $this->_getParam('trace');    	 
	    	switch ($this->_getParam('q')) {
	    		case "getPubli":
	    			$this->view->content =$ensuprefr->getPubli($this->_getParam('req'));
	    			break;
	    		case "saveMonade":
	    			$ensuprefr->getUser(array("login"=>$this->_getParam('user','samszo')));
	    			$this->view->content = $ensuprefr->saveMonade($this->_getParam('req'));
	    			break;
				case "getTagHisto":
	    			$data = $ensuprefr->getTagHisto($this->_getParam("dateUnit", '%Y')
	    				, $this->_getParam("idUti"), $this->_getParam("idMonade")
	    				, $this->_getParam("idActi"), $this->_getParam("idParent")
	    				, $this->_getParam("arrTags"), $this->_getParam("req")
	    				, $this->_getParam("dates"), $this->_getParam("for"), $this->_getParam("query"));
	    			$this->view->content = json_encode($data);
	    			break;    				 
	    	}
    
    }
    
    public function okapiAction()
    {
		$okapi = new Flux_Okapi("flux_okapi");
		switch ($this->_getParam('v')) {
			case "chercherMedia":
				$this->view->reponse = $okapi->chercherMedia($this->_getParam('q'));				
			break;
		}
    }

    
    public function isidoreAction()
    {
		$idBase = $this->_getParam('idBase');
		$isidore = new Flux_Isidore($idBase,$this->_getParam('trace'));
		$isidore->bTraceFlush = $this->_getParam('trace');
		$isidore->bCache = true;
		switch ($this->_getParam('q')) {
			case "getRefHistoDiscipline":
				$data = $isidore->getRefHistoDiscipline("stream");
				$this->view->content = json_encode($data);
				break;
			case "getHistoDiscipline":
				$data = $isidore->getHistoDiscipline($this->_getParam('req'),"stream");
				$this->view->content = json_encode($data);
				break;
			case "getDoc":
				$data = $isidore->getDoc($this->_getParam('req'),$this->_getParam('params'));
				$this->view->content = json_encode($data);
				break;
		}
    
    }

    public function smelAction()
    {
		$s = new Flux_Smel(false,intval($this->_getParam('trace',true)));
		$s->trace("DEBUT ".__METHOD__);
		switch ($this->_getParam('f')) {
			case 'saveMcCordSearch':
				$s->saveMcCordSearch($this->_getParam('q','houdini'),$this->_getParam('c',1));
				break;
			case 'corrigeGoogleVisionAnalyse':
				$docs=array(242,275,284,287,290,293,1187,1263);
				foreach ($docs as $d) {
					$s->getGoogleVisionAnalyse($d,0);
				}
				break;
			case 'getGoogleVisionAnalyse':
				$s->getGoogleVisionAnalyse($this->_getParam('idDoc',0), $this->_getParam('all',0));
				break;
			case 'supprimeDoublons':
				$s->supprimeDoublons();
				break;
			case 'getCsvToOmeka':
				$s->getCsvToOmeka('/Users/samszo/Sites/jdc/data/SMEL/importMcCord.csv');
				break;
			case 'getCsvGoogleVisageToOmk':
				$s->getCsvGoogleVisageToOmk('https://jardindesconnaissances.univ-paris8.fr/smel/omk/iiif-img/','/Users/samszo/Sites/jdc/data/SMEL/importMcCordVisage.csv');
				break;
			case 'getImageByComplexe':
				$rs = $s->getArtefactInfos("#",' sum_complex ASC');
				$s = new Flux_Site();
				foreach ($rs as $v) {
					if(!$this->view->content)$this->view->content = $s->arrayToCsv(array_keys($v),",").PHP_EOL;
					$this->view->content .= $s->arrayToCsv($v,",").PHP_EOL;
				}
				break;
		}        
	}

    public function anAction()
    {
        $an = new Flux_An($this->_getParam('idBase','flux_valarnum'),$this->_getParam('idBaseOmk',"omk-valarnum"),$this->_getParam('trace'));
		$ice = new Flux_Ice($this->_getParam('idBase','flux_valarnum'));
        $an->bTraceFlush = $an->bTrace;
        $an->trace("DEBUT ".__METHOD__);        
        switch ($this->_getParam('q')) {
            case "compareComplexEcosystemUrl":
                $idsBase = $this->_getParam('idsBase');
                $rs = array();
                foreach ($idsBase as $idBase) {
                    //récupère l'identifiant du document
                    $dbD = new Model_DbTable_Flux_Doc($an->db);
                    $doc = $dbD->findByUrl($this->_getParam('url'),true);
                    if($doc){
                        $data = $ice->getComplexEcosystem($doc['doc_id']
                            ,$this->_getParam('idTag'),$this->_getParam('idExi')
                            , $this->_getParam('idGeo'), $this->_getParam('idMonade'), $this->_getParam('idRapport'), $this->_getParam('cache',true));
                        $rs = array_merge($rs, $data["details"]);
                    }
                }
                $this->view->content = json_encode($rs);
                break;
            case "compareComplexEcosystem":
                $idsBase = $this->_getParam('idsBase');
                $rs = array();
                foreach ($idsBase as $idBase) {
                    $data = $ice->getComplexEcosystem($this->_getParam('idDoc'),$this->_getParam('idTag'),$this->_getParam('idExi')
                        , $this->_getParam('idGeo'), $this->_getParam('idMonade'), $this->_getParam('idRapport'), $this->_getParam('cache',true));
                    $rs = array_merge($rs, $data["details"]);
                }
                $this->view->content = json_encode($rs);
                break;
            case "getComplexEcosystem":
                $data = $ice->getComplexEcosystem($this->_getParam('idDoc'),$this->_getParam('idTag'),$this->_getParam('idExi')
                , $this->_getParam('idGeo'), $this->_getParam('idMonade'), $this->_getParam('idRapport'),  $this->_getParam('cache',true));
                $this->view->content = json_encode($data);
                break;
            case "getTreemapPhoto":
                $data = $an->getTreemapPhoto($this->_getParam('idDoc'));
                $this->view->content = json_encode($data);
                break;
            case "getActeursContexte":
                $data = $an->getActeursContexte($this->_getParam('idDoc'),false,$this->_getParam('idTheme'),$this->_getParam('idVisage'));
                $this->view->content = json_encode($data);
                break;
            case "getAleaTofs":
                $an->iiif = new Flux_Iiif($this->_getParam('iif',"http://gapai.univ-paris8.fr/ValArNum/omks/iiif"),$an->idBase,$an->bTrace);
                $an->iiif->bTraceFlush = $an->bTrace;
                $data = $an->getAleaTofs($this->_getParam('nb',10));
                $this->view->content = json_encode($data);                
                break;
            case "getVisagesDatas":
                //utiliser avec la base flux_valarnum
                $data = $an->getVisagesDatas($this->_getParam('deb',""),$this->_getParam('nb',""),$this->_getParam('count',""));
                $this->view->content = json_encode($data);
                if($this->_getParam('sauve'))$an->sauveJson(ROOT_PATH."/data/AN/getVisageData_".$this->_getParam('deb',"")."_".$this->_getParam('nb',"").".json", $data);                
                break;
            case "getEvalsMonade":
                $data = $ice->getEvalsMonade($this->_getParam('idMonade',3));
                $this->view->content = json_encode($data);
                break;
            case "getEvalsMonadeHistoTag":
                //récupère les évaluations par tag
                $sta = new Flux_Stats();
                $data = $ice->getEvalsMonadeHistoByTag($this->_getParam('idMonade',3),$this->_getParam("dateUnit", '%Y-%m-%d'));
                if($this->_getParam('stream')){
                    //calcul les données pour le stream
                    $data = $sta->array_orderby($data, 'type', SORT_ASC, 'temps', SORT_ASC);
                    $data = $sta->getDataForStream($data, $this->_getParam("dateUnit", '%Y-%m-%d'));
                }
                $this->view->content = json_encode($data);
                break;
            case "getEvalsMonadeHistoUti":
                //récupère les évaluations par utilisateur
                $data = $ice->getEvalsMonadeHistoByUti($this->_getParam('idMonade',3),$this->_getParam("dateUnit", '%Y-%m-%d %H:%i')
                    ,$this->_getParam("dateType", 'dateChoix'),$this->_getParam("idTag", false));
                if($this->_getParam('stream')){
                    //calcul les données pour le stream
                    $data = $sta->array_orderby($data, 'type', SORT_ASC, 'temps', SORT_ASC);
                    $data = $sta->getDataForStream($data, $this->_getParam("dateUnit", '%Y-%m-%d %H:%i'));
                }
                $this->view->content = json_encode($data);
                break;
            case "getEvalsMonadeHistoDoc":
                //récupère les évaluations par document
                $data = $ice->getEvalsMonadeHistoByDoc($this->_getParam('idMonade',3),$this->_getParam("dateUnit", '%Y-%m-%d %H:%i'));
                if($this->_getParam('stream')){
                    //calcul les données pour le stream
                    $data = $sta->array_orderby($data, 'type', SORT_ASC, 'temps', SORT_ASC);
                    $data = $sta->getDataForStream($data, $this->_getParam("dateUnit", '%Y-%m-%d %H:%i'));
                }                
                $this->view->content = json_encode($data);
                break;
        }
        
    }
    
    public function iiifAction()
    {
        $iiif = new Flux_Iiif($this->_getParam('serveur',"http://gapai.univ-paris8.fr/ValArNum/omks/iiif"),$this->_getParam('idBase'));
        $iiif->bTrace=false;
        switch ($this->_getParam('q')) {
            case "getOmkCollection":
                $this->view->content = json_encode($iiif->getOmkCollection($this->_getParam('idCol',1572)));
                break;
            case "getCollectionFaces":
                $g = new Flux_Gvision();                
                $g->bTrace = false;
                //récupère la collection
                $arr = $iiif->getOmkCollection($this->_getParam('idCol',1572));
                $i=1;
                $rs[] = array("label"=>$col->label,"id"=>"root","value"=>"");
                foreach ($arr as $t) {
                    if ($t['id']!='root'){
                        //récupère les données de google
                        $data = json_decode($g->analyseImage($t['original']));
                        //$g->trace("",$data);
                        foreach ($data->responses[0]->faceAnnotations as $fa) {
                            $g->trace("",$fa);
                            //calcule la position de l'image
                            $v = $fa->boundingPoly->vertices;
                            //ajoute l'image à la collection
                            $rs[] = array("id"=>"root.".$i,"value"=>"","label"=>"Fragment de ".$t['label'],"idOmk"=>$t['idOmk'],"imgOmk"=>$t['imgOmk']
                                ,"original"=>$t['original']
                                ,"imgFull"=>$t['imgOmk'].'/'.$v[0]->x.','.$v[0]->y.','.($v[1]->x - $v[0]->x).','.($v[2]->y - $v[0]->y).'/full/0/default.jpg'
                                ,"width"=>($v[1]->x - $v[0]->x), "height"=> ($v[2]->y - $v[0]->y) );
                            $i++;
                        }
                    }
                }
                //ATTENTION les url iiif possèdent de ,               
                if($this->_getParam('csv')){
                    foreach ($rs as $v) {
                        if ($v['id']!='root'){                            
                            if(!$this->view->content)$this->view->content = 'id,src'.PHP_EOL;
                            $this->view->content .= $v["id"].",".$v["imgFull"].PHP_EOL;
                        }
                    }
                }else                       
                    $this->view->content = json_encode($rs);
                break;
                
        }
        
	}
	
    public function zoteroAction()
    {
		$z = new Flux_Zotero($this->_getParam('login'),$this->_getParam('idBase',"flux_zotero"));
		$z->bTrace=true;
		switch ($this->_getParam('q')) {
			case 'saveAll':
				$z->saveAll();
				break;
				case 'saveAllToOmk':
					$omkParams = OMK_PARAMS[$this->_getParam('idBaseOmk','omk_samszo')];
					$z->initOmeka($omkParams["ENDPOINT"], $omkParams["API_IDENT"], $omkParams["API_KEY"]);		
					$z->saveAllToOmk();
					break;
			}
	}

    public function diplomatiegouvfrAction()
    {
		$dv = new Flux_Diplomatiegouvfr($this->_getParam('idBase',"flux_veille_diplo"),$this->_getParam('trace',true));
		switch ($this->_getParam('q')) {
			case 'saveAll':
				foreach ($dv->pays as $p) {
					$dv->saveVeillePays($p);
					$dv->nbArticleParPage=0;
				}
				break;
			case 'savePays':
				$dv->saveVeillePays($this->_getParam('pays'));
				break;
			}
	}

    public function ontostatAction()
    {
		$o = new Flux_Ontostat($this->_getParam('idBase','flux_ontostats'));
		$o->bTrace = $this->_getParam('trace',true);
		$o->bTraceFlush = $o->bTrace;
		$omkParams = OMK_PARAMS['omk_ontostat'];
		$o->initOmeka($omkParams["ENDPOINT"], $omkParams["API_IDENT"], $omkParams["API_KEY"]);		
		switch ($this->_getParam('q')) {
			case 'setConceptHierarchie':
				$o->setConceptHierarchieToOmeka();
				break;
		}
	}
	
    public function gorafiAction()
    {
		$g = new Flux_Gorafi($this->_getParam('idBase',"flux_gorafi"),$this->_getParam('trace',true));
		switch ($this->_getParam('q')) {
			case 'saveAll':
				foreach ($g->categories as $c) {
					$g->nbPagination = 0;
					$g->saveCategorie($c);
				}
				break;
		}
	}

	function verifExpireToken($ss){
		$ss->client->setAccessToken($ss->token);
		if ($ss->client->isAccessTokenExpired()) {
		    $ss->token=false;
		}
	}    
}


<?php
/**
 * Classe qui gère les flux Zotero
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Zotero extends Flux_Site{

	
	const API_URI = "https://api.zotero.org";
	var $libraryID = '';
	var $url = '';
	var $idTagMostRecent;
	var $latestEdition;
	var $idTagLatestEdition;	
	
	public function __construct($login, $idBase="flux_zotero", $libraryID=ZOTERO_ID_LIB)
    {
    	parent::__construct($idBase);
    	$this->libraryID = $libraryID;
    	$this->login = $login;
    }
		
    function getRequest($url, $params){

		$c = str_replace("::", "_", __METHOD__)."_".$this->getParamString($params); 
	   	$flux = $this->cache->load($c);
        if(!$flux){
	    	$params['key'] = KEY_ZOTERO;
	    	$params['content'] = "json";
	    	$params['order'] = "dateAdded";
	    	if(!isset($params['sort']))$params['sort'] = "asc";
	    	$params['format'] = "atom";
	    	$atom = $this->getUrlBodyContent($url, $params);
	    	$flux = Zend_Feed::importString($atom);	        
			$this->cache->save($flux, $c);
        }        
        return $flux;
    	
    }	

    function saveAll(){

    	$this->getUser(array("login"=>$this->login,"flux"=>"zotero"));
		//initialise les gestionnaires de base de données
    	if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
    	$i = 1;
    	$this->url = self::API_URI."/users/".$this->libraryID."/items";
		while ($i>0) {
    		$flux = $this->getRequest($this->url,array("limit"=>99, "start"=>$i, "sort"=>"desc"));
    		foreach ($flux as $item){
    			$this->saveItem($item);
    		}
    		$i = $i+count($flux);
		}
    	//$this->lucene->index->optimize();
    }

    function saveItem($i){

    	
		//TODO ajouter les db pour graine utigraine et grainedoc 
		//$this->getGraine(array("titre"=>"Diigo","class"=>"Flux_Diigo","url"=>"http://www.diigo.com/"));
		//TODO ajouter la utigraine 

    	$item['url'] = $i->id();
    	$item['titre'] = $i->title();
    	$item['tronc'] = 0;
    	$item['pubDate'] = $i->updated();
    	$item['note'] = $i->content();
		$content = json_decode($item['note']);
    	$item['type'] = $content->itemType;
    	
    	//gestion des notes
    	if($item['type']=="note"){
    		$item['note'] = $content->note;
    		//récupération du parent
    		$lienParent = $i->link("up");
    		$lienParent = str_replace("?content=json", "", $lienParent);
    		$idParent = substr($lienParent, strlen($this->url)+1);
    		$docParent = $this->dbD->findLikeUrl($idParent);
    		if($docParent[0]['doc_id'])
	    		$item['tronc'] = $docParent[0]['doc_id'];
	    	else
	    		$item['tronc'] = $idParent;
    	}
    	
		
		//ajouter un document correspondant au lien
		$idD = $this->dbD->ajouter($item);
		//index le document
		//$this->lucene->addDoc($item['url']);
		
		//TODO ajouter la grainedoc 

		//traitement des auteurs
		$arrAuteurs = $content->creators;
		foreach ($arrAuteurs as $auteur){
			$idU = $this->getUser(array("login"=>$auteur->firstName." ".$auteur->lastName,"flux"=>"zotero","role"=>$auteur->creatorType),true);
			$this->dbUD->ajouter(array("uti_id"=>$idU, "doc_id"=>$idD, "poids"=>0));										    
		}

		//traitement des mots clefs
		$arrTags = $content->tags;
		foreach ($arrTags as $tag){
			$this->saveTag($tag->tag, $idD, 1, $item['pubDate']);	    			
			$i++;
		}						

		//ajoute un lien entre l'utilisateur et le document avec un poids correspondant au nombre de tag
		$this->dbUD->ajouter(array("uti_id"=>$this->user, "doc_id"=>$idD, "poids"=>$i));										    
		//TODO ajouter la grainedoc 
		
    } 

    /**
     * sauveAmazonInfo
     *
     * enregistre les information d'amazon pour les document avec un titre
	 * 
     * 
     */
	function sauveAmazonInfo(){

    	if(!$this->dbD)$this->dbD = new Model_DbTable_flux_doc($this->db);
    	
    	$fA = new Flux_Amazon($this->idBase);
    	
    	//initialise l'utilisateur
    	$fA->getUser(array("login"=>"Flux_Amazon"));
    	
    	//récupère les documents
		$rs = $this->dbD->findByTronc("0");
    	
		foreach ($rs as $r) {
			if($r['type']=="book"){
				//vérifie si les infos amazon sont déjà enregistrées
				$doc = $this->dbD->findFiltre("tronc=".$r["doc_id"]." AND type=39 AND note !='' AND data != ''", "doc_id");
				if(count($doc)==0){
					//récupère la note json
					$obj = json_decode($r['note']);
					//création de la requête amazon
					$search = array('SearchIndex' => 'Books');
					if(isset($obj->title))$search['Title'] = $obj->title;
					//if(isset($obj->creators[0]->firstName))$search['Author'] = $obj->creators[0]->firstName." ".$obj->creators[0]->lastName;
					//if(isset($obj->creators[0]->name))$search['Author'] = $obj->creators[0]->name;
					//if(isset($obj->publisher))$search['Publisher'] = $obj->publisher;
					$fA->sauveSearch($search, $r["doc_id"]);
					echo "<br/>".$r["doc_id"]." - ".$obj->title."<br/>";
				}
			}
		}
    	
	}    
	
    /**
     * sauveOCLCInfo
     *
     * enregistre les information d'OCLC pour les document avec un ISBN
	 * http://www.oclc.org/ca/fr/default.htm
     * 
     */
	function sauveOCLCInfo(){

    	if(!$this->dbD)$this->dbD = new Model_DbTable_flux_doc($this->db);
    	if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
    	if(!$this->dbTT)$this->dbTT = new Model_DbTable_Flux_TagTag($this->db);
    	
    	//initialise les utilisateurs
    	$this->idUserOCLC = $this->getUser(array("login"=>"oclc"));
    	$this->idUserDewey = $this->getUser(array("login"=>"www.dewey.info"));
    	
    	//initialise les mots clefs
    	$this->idTagMostPop = $this->dbT->ajouter(array("code"=>"mostPopular"));
		$this->idTagMostRecent = $this->dbT->ajouter(array("code"=>"mostRecent"));
		$this->idTagLatestEdition = $this->dbT->ajouter(array("code"=>"latestEdition"));	
    	
    	//récupère les documents
		$rs = $this->dbD->findByTronc("0");
		
		foreach ($rs as $r) {
			//récupère la date courante
			if($r['type']=="book"){
				//récupère la note json
				$obj = json_decode($r['note']);
				//vérification de l'isbn
				$arrISBN = explode(" ", $obj->ISBN);
				if($arrISBN[0]!=""){
					//récupération de la recommandation avec le premier isbn
					$oclc = $this->getOCLCRecommandations($arrISBN[0]);
					if($oclc[0]){
						//on stocke l'information dans un nouveau document
						$xmlOCLC = $oclc[0];
					    $arrDoc = array("url"=>$oclc[0], "tronc"=>$r['doc_id'], "data"=>$xmlOCLC->asXML(), "type"=>60);
						$idDocOCLC = $this->dbD->ajouter($arrDoc);
						//on récupère la définition du code dewey
						foreach ($xmlOCLC->recommendations->ddc as $dcc) {
							if($dcc->mostPopular){
								$idTagOCLC = $this->sauveOCLCTag($dcc->mostPopular, $idDocOCLC);
								//enregistre le type de mot
								$this->dbTT->ajouter(array("tag_id_src"=>$this->idTagMostPop, "tag_id_dst"=>$idTagOCLC));
							}
							if($dcc->mostRecent){
								$idTagOCLC = $this->sauveOCLCTag($dcc->mostRecent, $idDocOCLC);
								//enregistre le type de mot
								$this->dbTT->ajouter(array("tag_id_src"=>$this->idTagMostRecent, "tag_id_dst"=>$idTagOCLC));
							}
							if($dcc->latestEdition){
								$idTagOCLC = $this->sauveOCLCTag($dcc->latestEdition, $idDocOCLC);
								//enregistre le type de mot
								$this->dbTT->ajouter(array("tag_id_src"=>$this->idTagLatestEdition, "tag_id_dst"=>$idTagOCLC));
							}
						}						
					}
					echo "<br/>".$r["doc_id"]." - ".$r["titre"]."<br/>";
				}
			}
		}
    	
	}  

    /**
     * sauveOCLCTag
     *
     * enregistre les tags récupéré dans OCLC
     * 
     * @param xml $dcc
     * @param int $idDoc
     * 
     * @return int
     * 
     */
	function sauveOCLCTag($dcc, $idDoc){
		
		$d = new Zend_Date();
		
		//enregistre le tag pour le document
		$nota = $dcc['sfa']."";
		$idTagOCLC = $this->saveTag($nota, $idDoc, $dcc['holdings'], $d->get("c"), $this->idUserOCLC);
		//on récupère les informations de dewey
		$arrDewey = $this->getDeweyAbout($nota, "en");
		$idTagDewey = $this->sauveDeweyAbout($arrDewey, $idDoc);
		return $idTagOCLC; 
	}
	
    /**
     * getOCLCRecommandations
     *
     * récupère les recommandations d'OCLC pour un ISBN
	 * http://www.oclc.org/ca/fr/default.htm
	 * pour tester : http://classify.oclc.org/classify2/api_docs/classify.html
     * 
     * @param string $isbn
     * 
     * @return array
     * 
     */
	function getOCLCRecommandations($isbn){
		//création de la requête OCLC
		$url = "http://classify.oclc.org/classify2/Classify?isbn=".$isbn."&summary=true";
		if($xml = simplexml_load_string($this->getUrlBodyContent($url))){
			//vérifie si une recommandation existe
			if(!$xml->recommendations){
				//on fait une nouvelle recherche à partir du premier swid
				$swid = $xml->works->work[0]['swid']; 
				$url = "http://classify.oclc.org/classify2/Classify?swid=".$swid."&summary=true";
				$xml = simplexml_load_string($this->getUrlBodyContent($url));
			}
		}
		return array($xml, $url);
	}
	

    /**
     * getDeweyAbout
     *
     * récupère les informations concernant une class Dewey
	 * http://oclc.org/developer/documentation/dewey-web-services/using-api
	 * pour tester : http://dewey.info/sparql.php
     * 
     * @param string $notation
     * @param string $langue
     * 
     * @return array
     * 
     */
	function getDeweyAbout($notation, $langue="fr"){
		//création de la requête dewey
		$query =
		"PREFIX skos: <http://www.w3.org/2004/02/skos/core#> 
		PREFIX dct: <http://purl.org/dc/terms/>
		
		SELECT *
		WHERE {
		  ?x skos:prefLabel ?prefLabel ;
		     skos:notation ?notation .
		  FILTER ((?notation = '".$notation."') && langMatches( lang(?prefLabel), '".$langue."' ))
		}";
		$url = "http://dewey.info/sparql.php";
		$xml = simplexml_load_string($this->getUrlBodyContent($url, array("output"=>"xml", "query"=>$query), false));
		return array($xml, $url);
	}
    
    /**
     * sauveDeweyAbout
     *
     * enregistre les information Dewey pour une notation
     * 
     * @param array $dewey
     * @param int $idDoc
     * @param int $idTagParent
     * 
     * @return array
     * 
     */
	function sauveDeweyAbout($dewey, $idDoc, $idTagParent=-1){
		
		try {
			$idTag = false;
			if($dewey[0]){
				//initialise l'utilisateur
				$d = new Zend_Date();
	    		
				//on stocke l'information dans un nouveau document
				$xmlDewey = $dewey[0]->results->result[0];
				if($xmlDewey){
					//on stocke l'information dans un nouveau document
					$arrDoc = array("url"=>$xmlDewey->binding[0]->uri."", "tronc"=>$idDoc, "data"=>$xmlDewey->asXML(), "type"=>60);
					$idDocDewey = $this->dbD->ajouter($arrDoc);
					//enregistre le tag pour le document
					$data['desc'] = $xmlDewey->binding[1]->literal."";
					$data['code'] = $xmlDewey->binding[2]->literal."";
					if($idTagParent!=-1) $data['parent'] = $idTagParent;
					$idTag = $this->saveTag($data, $idDocDewey, 0, $d->get("c"));
				}else{
					$idTag=-1;
					$idDocDewey=-1;
				}
			}	
		}catch (Zend_Exception $e) {
			echo "Récupère exception: " . get_class($e) . "\n";
		    echo "Message: " . $e->getMessage() . "\n";
		}
			
		return array($idTag,$idDocDewey);
		
	}
	
    /**
     * sauveDeweyHierarchie
     *
     * calcule la hiérarchie d'une classification Dewey
     * 
     * @param string $dewey
     * @param int $idDoc
     * 
     * @return int
     * 
     */
	function sauveDeweyHierarchie($dewey, $idDoc=0){

    	if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
    	if(!$this->dbD)$this->dbD = new Model_DbTable_flux_doc($this->db);
    	if(!$this->user)$this->user = $this->getUser(array("login"=>"www.dewey.info"));
    	
    	//récupère le tag racine
    	$idTagRacine = $this->dbT->ajouter(array("code"=>"Classification Dewey"));
    	
    	//parcourt l'ensemble de la chaine
    	$idTagParent = $idTagRacine;
		for($i = 1; $i <= strlen($dewey); $i++)
        {
        	$c = substr($dewey, 0, $i);
        	if(substr($c, -1)=="."){
        		$i++;
        		$c = substr($dewey, 0, $i);
        	}         	
        	//recherche la codification dans la base
        	$tag = $this->dbT->findByCode($c);
        	if(count($tag)==0){
        		// on cherche en anglais
				$arrDewey = $this->getDeweyAbout($c, "en");	
        		//on enregistre le nouveau code
				$idTagDoc = $this->sauveDeweyAbout($arrDewey, $idDoc, $idTagParent);
				if($idTagDoc[0] != -1)
					$idTagParent = $idTagDoc[0];
				echo $c." : ".$idTagDoc[0]." - ".$idTagDoc[1]."<br/>";				
        	}else{
        		//vérifie que la hiérarchie est définie
        		if($tag["lft"]==-1){
        			$tag["parent"] = $idTagParent;
        			$tag = $this->dbT->updateHierarchie($tag);
        			$this->dbT->edit($tag["tag_id"], $tag);
        		}
				$idTagParent = $tag["tag_id"];
        	}
        	
        }
	}
        
    /**
     * setDeweyTagDocHierarchie
     *
     * calcule les tags d'un document par rapport à la hiérarchie Dewey
     * 
     * @param int $idDoc
     * 
     * @return array
     * 
     */
	function setDeweyTagDocHierarchie($idDoc){

    	if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
    	if(!$this->dbD)$this->dbD = new Model_DbTable_flux_doc($this->db);
    	if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
    	if(!$this->user)$this->user = $this->getUser(array("login"=>"www.dewey.info"));
    	
    	//récupère la classification du document par OCLC
		$sql = "SELECT
					tcode.tag_id idTag, tcode.code
					, tsrc.tag_id idTagCat, tsrc.code
					, d.doc_id
				FROM flux_tagtag tt
				INNER JOIN flux_tag tcode ON tcode.tag_id = tt.tag_id_dst
				INNER JOIN flux_tag tsrc ON tsrc.tag_id = tt.tag_id_src
				INNER JOIN flux_utitagdoc utd ON utd.tag_id = tcode.tag_id
				INNER JOIN flux_uti u ON u.uti_id = utd.uti_id
				INNER JOIN flux_doc dOCLC ON dOCLC.doc_id = utd.doc_id 
				INNER JOIN flux_doc d ON d.doc_id = dOCLC.tronc 
				WHERE tt.tag_id_src IN (845, 846, 848)  
						AND d.titre != ''
						AND d.doc_id = ".$idDoc."
				GROUP BY d.doc_id
				ORDER BY d.doc_id";    
    	$result = $this->db->fetchAll($sql);    	
		
    	
    	//parcours les classification OCLC
		$d = new Zend_Date();
		foreach ($result as $class) {
    		//récupère la hiérarchie de la classification
    		$arrH = $this->dbT->getFullPath($class['idTag']);
    		//création du tag pour chaque élément
    		foreach ($arrH as $classH) {
    			$this->saveTag($classH['code'], $idDoc, 1, $d->get("c"));
    		}
    	}
        	
	}
        
    /**
     * getDeweyTagDoc
     *
     * renvoie la hiérarchie Dewey avec les document associé
     * 
     * @param int $idUtiDewey
     * @param int $idTagRacineDewey
     * 
     * @return array
     * 
     */
	function getDeweyTagDoc($idUtiDewey=638, $idTagRacineDewey=1811){

		$c = str_replace("::", "_", __METHOD__)."_".$idUtiDewey."_".$idTagRacineDewey; 
	   	$flux = false;//$this->cache->load($c);
        if(!$flux){
		
			if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
			if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
			
			$arr = $this->dbUTD->getDeweyTagDoc($idUtiDewey);
			
			//construction de la hiérarchie
			$result[] = array("desc"=>"Classification Dewey", "niveau"=>0, "tag_id"=>$idTagRacineDewey);
			foreach ($arr as $d) {
				//récupère la hiérarchie
				// problème de performance
				//$arrParent = explode(",", $d['idsTagParent']);
				$arrParent = $this->dbT->getFullPath($d['tag_id']);
				$result = $this->getArrHier($d, $arrParent, $result);
			}
			$flux = $result[0];
			$this->cache->save($flux, $c);
        }
		return $flux;
	}	
    /**
     * getDocDetail
     *
     * renvoie le détail des documents
     * 
     * @param int $idsDoc
     * 
     * @return array
     * 
     */
	function getDocDetail($idsDoc){

		$c = str_replace("::", "_", __METHOD__)."_".$idsDoc; 
	   	$flux = false;//$this->cache->load($c);
        if(!$flux){
		
			$dbD = new Model_DbTable_Flux_Doc($this->db);

	        $query = $dbD->select()
	        	->setIntegrityCheck(false)
	        	->from(array("d" => "flux_doc"),array("doc_id","url","titre","type"))
	        	->joinInner(array('dAmz' => 'flux_doc'),'dAmz.tronc = d.doc_id AND dAmz.type = 39'
	            	,array('dAmzTitre'=>'dAmz.titre', 'dAmzUrl'=>'dAmz.url'))
	            ->joinInner(array('dTof' => 'flux_doc'),'dTof.tronc = dAmz.doc_id'
	            	,array('dTofTitre'=>'dTof.titre', 'dTofUrl'=> 'dTof.url'))
				->joinInner(array('ud' => 'flux_utidoc'), "ud.doc_id = d.doc_id",
					array("idsUti"=>"GROUP_CONCAT(DISTINCT ud.uti_id)"))
	            ->group("d.doc_id")
				->where("d.doc_id IN (".$idsDoc.")");
			$flux = $dbD->fetchAll($query)->toArray(); 					
			$result = array();
			$result = array("titre"=>"documents", "nbDoc"=>0, "children"=>array(), "idsUti"=>"", "type"=>"racine");
			foreach ($flux as $book) {
				//récupère les note pour le livre
				$notes = $dbD->findByParams(array("tronc"=>$book['doc_id'], "type"=>"note"));
				//met à jour le livre
				$book['nbDoc'] = count($notes);
				//ajoute le nombre mot pour la note
				for ($i = 0; $i < $book['nbDoc']; $i++) {
					$notes[$i]["nbDoc"] = str_word_count($notes[$i]["note"]);
					$notes[$i]["note"] = strip_tags($notes[$i]["note"]); 
				}
				if($book['nbDoc'] > 0){
					$book['children']=$notes;
					//rassemble les résultat dans la racine
					$result['nbDoc'] += $book['nbDoc'];
					$result['idsUti'] .= $book['idsUti'];
				}
				$result["children"][] = $book;
			}
			
			//$this->cache->save($flux, $c);
        }
		return $result;
	}		
}
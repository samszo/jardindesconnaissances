<?php
/**
 * Classe qui gère les flux Gdata
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Ontostat extends Flux_Site{

	const API_URI = "https://secure.diigo.com";
    const API_URL = '/api/v2/bookmarks';
    const RSS_GROUP_URL = 'http://groups.diigo.com/group/';
    var $LUCENE_INDEX;
    var $setLucene = false;
    var $mc;
    
    /**
     * Zend_Service_Rest instance
     *
     * @var Zend_Service_Rest
     */
    protected $rest;
	
	public function __construct($login="", $pwd="", $idBase=false, $bTrace=false)
    {
    	parent::__construct($idBase,$bTrace);

    	$this->LUCENE_INDEX = ROOT_PATH.'/data/diigo-index';
    	
    	//on récupère la racine des documents
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	if(!$this->dbM)$this->dbM = new Model_DbTable_Flux_Monade($this->db);
    	$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
    	$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
    	 
    	
		$this->login = $login;
    	if($login && $pwd){
	    	$this->pwd = $pwd;
    		$this->rest = new Zend_Rest_Client();
	        $this->rest->getHttpClient()->setAuth($login, $pwd);
	        $this->rest->setUri(self::API_URI);
       	}
       	
       	$this->mc = new Flux_MC($idBase, $bTrace);
       	
    }
    
    /**
     * Fonction pour effectuer une requête sur l'API
     *
     * @param 	array	$params
     * @param	boolean	$cache
     * 
     * @return array
     */
    function getRequest($params, $cache=true){

		$c = str_replace("::", "_", __METHOD__)."_".$this->getParamString($params); 
	   	$arr = $cache ? $this->cache->load($c) : false;
        if(!$arr){
		    $params['key'] = KEY_DIIGO_API;
		    $response = $this->rest->restGet(self::API_URL, $params);
			//$this->trace($response);	
	        if (!$response->isSuccessful()) {
				$this->trace("ERREUR : ".$response->getMessage());	
	        		//throw new Zend_Service_Exception("Http client reported an error: '{$response->getMessage()}'");
	        		return false;
	        }	        
	        $json = $response->getBody();
		    $arr = json_decode($json);
	        //le decode de zend est beaucoup trop long
	        //$arr = Zend_Json_Decoder::decode($json);
	        
			$this->cache->save($arr, $c);
        }        
        return $arr;
    	
    }
    
    
	/**
	 * enegistre le bookmark d'un compte
	 *
	 * @param string 	$login
	 *
	 * @return array
	 */
    function saveAll($login=false){
    	
		$this->trace("DEBUT ".__METHOD__);				
		
		set_time_limit(0);
		
  		if(!$login)$login=$this->login;
    		$this->getUser(array("login"=>$login,"flux"=>"diigo"));
    		
		$this->trace("LOGIN ".$login." : ".$this->user);				
    		
		//initialise les gestionnaires de base de données
		$this->initDbTables();
		$this->trace("Tables initialisées");
		
		//initialise le moteur d'indexation
		if($this->setLucene && !$this->lucene){
			$this->lucene = new Flux_Lucene($this->login, $this->pwd, $this->idBase, false, $this->LUCENE_INDEX);
			$this->lucene->classUrl = $this;
			$this->lucene->index->optimize();	
			$this->trace("moteur d'indexation initialisé");
		}
		
		//
		$i = 1;
		$count = 100;
		while ($i>0) {
    		$arr = $this->getRequest(array("user"=>$login,"count"=>$count, "start"=>$i));
    		if(!$arr){
	    		$i=-1;	
    		}else{				
    			$j = 0;
    			$this->trace("getRequest   ".$i." : ".count($arr));
    			foreach ($arr as $item){
					$this->trace($i."   ".$j." : ".$item->url);				
	    			$this->saveItem($item);
	    			$j++;
	    		}
				if(count($arr)==0)$i=-1;
				else $i += $j;
    		}
    		//$i=-1;
    	}
    	if($this->setLucenee)$this->lucene->index->optimize();
	    //
		
		$this->trace("FIN ".__METHOD__);				
    }    

    /**
     * enegistre les derniers bookmarks d'un compte
     *
     * @param string 	$login
     *
     * @return array
     */
    function saveRecent($login=false){
    	 
    	$this->trace("DEBUT ".__METHOD__);
    	if(!$login)$login=$this->login;
    	$this->getUser(array("login"=>$login,"flux"=>"diigo"));
    
    	$this->trace("LOGIN ".$login." : ".$this->user);
    
    	//initialise les gestionnaires de base de données
    	$this->initDbTables();
    	$this->trace("Tables initialisées");
    
    	//initialise le moteur d'indexation
    	if($this->setLucene && !$this->lucene){
    		$this->lucene = new Flux_Lucene($this->login, $this->pwd, $this->idBase, false, $this->LUCENE_INDEX);
    		$this->lucene->classUrl = $this;
    		$this->lucene->index->optimize();
    		$this->trace("moteur d'indexation initialisé");
    	}
        	 
    	//
    	$i = 1;
    	$count = 100;
    	while ($i>0) {
    		$arr = $this->getRequest(array("user"=>$login,"count"=>$count, "start"=>$i),false);
    		if(!$arr){
    			$i=-1;
    		}else{
    			$j = 0;
    			$this->trace("   ".$i." : ".count($arr));
    			foreach ($arr as $item){
    				$this->trace($i."   ".$j." : ".$item->url);
    				//vérifie que l'item existe
    				$doc = $this->dbD->findByUrl($item->url);
    				//$this->trace("vérifie que l'item existe ",$doc);
    				if($doc) {
    					$arr = array();
    					break;
    				}
    				else $this->saveItem($item);
    				$j++;
    			}
    			if(count($arr)==0 || $j<$count)$i=-1;
    			else $i = $i+count($arr);
    		}
    	}
    	if($this->setLucenee)$this->lucene->index->optimize();
    	//
    
    	$this->trace("FIN ".__METHOD__);
    }
    function saveItem($i){

    	
    	//récupère l'action
    	$idAct = $this->dbA->ajouter(array("code"=>__METHOD__));
    	
		//transforme l'objet en tableau dans le cas ou on n'utilise pas le json decode de Zen
	    $item['url'] = $i->url;
	    $item['title'] = $i->title;
	    $item['created_at'] = $i->created_at;
	    $item['annotations'] = $i->annotations;
	    $item['tags'] = $i->tags;

	    	
	    $idD = $this->dbD->ajouter(array("url"=>$item['url'],"titre"=>$item['title'], "parent"=>$this->idDocRoot,"tronc"=>0,"pubDate"=>$item['created_at']));
	    //$this->trace("document correspondant au lien ajouté = ".$idD);
	     
		//index le document
		if($this->setLucene) $this->lucene->addDoc($item['url']);
		
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idD,"src_obj"=>"doc"
				,"dst_id"=>$idAct,"dst_obj"=>"acti"
				,"pre_id"=>$this->user,"pre_obj"=>"uti"
		));
		//$this->trace("enregistre le rapport entre le document et l'action = ".$idRap);
		
		
		$i = 0;
		//$this->trace("traitement des annotations");
		if(count($item['annotations'])>0){
			foreach ($item['annotations'] as $note){
				$j['content'] = $note->content;
				$j['created_at'] = $note->created_at;
				$this->saveContent($j, $idD, $idRap);
			}
		}		
		//traitement des mots clefs		
		if($item['tags']!=""){
			$arrTags = explode(",", $item['tags']);
			foreach ($arrTags as $tag){
				$this->mc->save($tag, $idRap, 1);	    			
				$i++;
			}						
		}				
		
    } 

	function saveContent($item, $idD, $idRap){
	   	
		//récupère l'action
		$idAct = $this->dbA->ajouter(array("code"=>__METHOD__));		
		$id = $this->dbD->ajouter(array("parent"=>$idD,"tronc"=>$idRap,"data"=>$item['content'],"pubDate"=>$item['created_at']));
		//$this->trace(__METHOD__." enregistre le document = ".$id);

		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idRap,"src_obj"=>"rapport"
				,"dst_id"=>$id,"dst_obj"=>"doc"
				,"pre_id"=>$idAct,"pre_obj"=>"acti"
		));
		
		//récupère les mot clefs
	   	$arrKW = $this->mc->saveForChaine($idD, $item['content'],$idRap);

	   	/*enregistre les mots clefs
	   	$i=0; 
	   	foreach ($arrKW as $kw=>$nb){
			$this->saveTag($kw, $id, $nb, $item['created_at']);
			$i++;	    			
	   	}
		$this->dbUD->ajouter(array("uti_id"=>$this->user, "doc_id"=>$id, "poids"=>$i));										    
		*/
	   	//$results = $dom->query('/html/body/table[2]/tr[2]/td[2]/p[1]/strong');	
	}
    
    
    function getGroupeRss($groupe){

    	$url = self::RSS_GROUP_URL."/".$groupe."/rss?count=100&start=0";
		Zend_Feed_Reader::setCache($this->cache);
		Zend_Feed_Reader::useHttpConditionalGet();
    	
   		$lastModifiedDateReceived = 'Wed, 08 Jul 2008 13:37:22 GMT';
		$feed = Zend_Feed_Reader::import($url, null, $lastModifiedDateReceived);    	
	    foreach ($feed as $entry) {
		    $edata = array(
		        'title'        => $entry->getTitle(),
		        'description'  => $entry->getDescription(),
		        'dateModified' => $entry->getDateModified(),
		        'authors'       => $entry->getAuthors(),
		        'link'         => $entry->getLink(),
		        'content'      => $entry->getContent()
		    );
		    $data['entries'][] = $edata;
		}    	    	
    }

    function saveArchiveRss($url){

		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
    	
		//TODO ajouter les db pour graine utigraine et grainedoc 
		
		$this->getUser(array("login"=>$this->login,"flux"=>"diigo"));
		$this->getGraine(array("titre"=>"Diigo","class"=>"Flux_Diigo","url"=>"http://www.diigo.com/"));
		//TODO ajouter la utigraine 
		
		$feed = Zend_Feed_Reader::import($url);    	

		foreach ($feed as $entry) {
		    $edata = array(
		        'title'        => $entry->getTitle(),
		        'description'  => $entry->getDescription(),
		        'dateModified' => $entry->getDateModified(),
		        'authors'       => $entry->getAuthors(),
		        'link'         => $entry->getLink(),
		        'content'      => $entry->getContent()
		    );
		    			
			//ajouter un document correspondant au lien
			$idD = $this->dbD->ajouter(array("url"=>$edata['link'],"titre"=>$edata['title'],"pubDate"=>$edata['dateModified']->get("c")));
			//TODO ajouter la grainedoc 
			$i = 0;
			
				//traitement du contenu
			$dom = new Zend_Dom_Query($edata['content']);	    
	    	
		   	//récupère le titre du document
			$results = $dom->query('/html/body/table[2]/tr[2]/td[2]/p[1]/strong');
			
			
			/*
				foreach ($vSS['feuilles'] as $nWS=>$vWS) {
					//ajouter un document correspondant à la feuille
					$idDF = $this->dbD->ajouter(array("url"=>$vWS['url'],"titre"=>$vWS['titre'],"tronc"=>$idD,"pubDate"=>$date->get("c")));
					//TODO ajouter la grainedoc 
					$j = 0;
					if($idDF>44){
						foreach ($vWS['values'] as $v) {
							if(isset($v['cest'])){
								$tag = $v['cest'];
								$poids = $v['_cokwr'];
								//on ajoute le tag
								$idT = $this->dbT->ajouter(array("code"=>$tag));
								//on ajoute le lien entre le tag et le doc avec le poids
								$this->dbTD->ajouter(array("tag_id"=>$idT, "doc_id"=>$idDF,"poids"=>$poids));
								//on ajoute le lein entre le tag l'utilisateur et le doc
								$this->dbUTD->ajouter(array("uti_id"=>$this->user, "tag_id"=>$idT, "doc_id"=>$idDF,"maj"=>$date->get("c")));						
								$j ++;
							}
						}
						$i += $j;					
						//ajoute un lien entre l'utilisateur et le document avec un poids correspondant au nombre de tag
						$this->dbUD->ajouter(array("uti_id"=>$this->user, "doc_id"=>$idDF,"poids"=>$j));													
					}
				}
			*/
			
			//ajoute un lien entre l'utilisateur et le document avec un poids correspondant au nombre de tag
			$this->dbUD->ajouter(array("uti_id"=>$this->user, "doc_id"=>$idD,"poids"=>$i));								
			
		}		
		    
		    
    }

    /** décompose la hiérarchie d'une item d'un groupe 
     * 
     * @param array $data = les information sur le document
     * @param int 	$idMon = l'identifiant de la monade
     * 
     */
    function getCompoGroupItem($data, $idMon){

		$dbD = new Model_DbTable_Flux_Doc($this->db);
		$dbM = new Model_DbTable_Flux_Monade($this->db);
		
		
		//enregistre ou récupère Diigo comme existence
		$idEDii = $dbM->ajoutExi($idMon, array("nom"=>"Diigo", "niveau"=>1));
		
    		//enregistre ou récupère le groupe dans l'url comme existence
		$arrUrl = explode('/', $data['url']);
		$idEG = $dbM->ajoutExi($idMon, array("nom"=>$arrUrl[4], "parent"=>$idEDii));
				
		if(!$data["data"]){
	    	//récupère le contenu de l'url
			$html = $this->getUrlBodyContent($data['url']);    	
			$dom = new Zend_Dom_Query($html);			
			if($data["doc_id"]){
				//met à jour les infos de l'item
				$dbD->edit($data["doc_id"],array("data"=>$html, "poids"=>count($html)));
			}else{
				//ajoute un document
				$data["doc_id"] = $dbM->ajoutDoc($idMon, array("url"=>$data["url"], "data"=>$html, "poids"=>strlen($html)));
			}
		}else{
			$dom = new Zend_Dom_Query($data["data"]);						
		}			    				
		//récupère l'existence ayant posté l'item
		$results = $dom->query('//*[@id="item_0"]/div[2]/div/div[1]/span[1]/a');
		$exi = "";
		foreach ($results as $result) {
		    $exiUrl = $result->getAttribute("href");
	    	$exi = $result->nodeValue;
		}
		$results = $dom->query('//*[@id="item_0"]/div[1]/a/img');
		foreach ($results as $result) {
		    $exiTof = $result->getAttribute("src");
		}
		$idE = $dbM->ajoutExi($idMon, array("nom"=>$exi, "parent"=>$idEG, "data"=>'{"icon":"'.$exiTof.'", "url":"'.$exiUrl.'"}'));
		
		//récupère les infos sur le document posté
		$results = $dom->query('//*[@id="item_0"]/div[2]/div/div[1]/div[1]/span/a');
		foreach ($results as $result) {
		    $urlDoc = $result->getAttribute("href");
		}
		$results = $dom->query('//*[@id="title_link_0"]');
		foreach ($results as $result) {
		    $titreDoc = $result->nodeValue;
		}
		//vérifie si le document est téléchargé
		$arrDoc = $dbD->findByUrl($urlDoc);
		if(count($arrDoc && $arrDoc[0]["data"])){
			$idDoc = $arrDoc[0]["doc_id"];
			$datePost =  DateTime::createFromFormat('Y-m-d H:i:s', $arrDoc[0]["pubDate"]);
		}else{				
			$html = $this->getUrlBodyContent($urlDoc);
			//récupère la date du post
			$results = $dom->query('//*[@id="item_0"]/div[2]/div/div[1]/span[1]');
			foreach ($results as $result) {
			    $infoPost = $result->nodeValue;
			    $datePost = substr($infoPost, -9);
			    $datePost = DateTime::createFromFormat('j M y', $datePost);
			}
			$idDoc = $dbD->ajouter(array("url"=>$urlDoc,"parent"=>$data["doc_id"],"titre"=>$titreDoc, "data"=>$html, "poids"=>strlen($html), "pubDate"=>$datePost->format('Y-m-d H:i:s')));
		}
		$idDoc = $dbM->ajoutDoc($idMon, array("doc_id"=>$idDoc),$idE);
		
		//récupère les tags pour ce document
		$results = $dom->query('//*[@id="tags_0"]/a');
		$nbTag = 0;
		foreach ($results as $result) {
		    $tag = $result->nodeValue;
		    //enregistre le tag
			$idTag = $dbM->ajoutTag($idMon, array("code"=>$tag),$idDoc,$idE);
		    $nbTag ++;
		}
		if($nbTag==0){
			$idTag = $dbM->ajoutTag($idMon, array("code"=>"vide"),$idDoc,$idE);
		}		
		    
		
		//récupère les annotations pour ce document
		$results = $dom->query('//*[@class="annContent"]');
		$numFrag = 1;
		$numCmt = 1;
		foreach ($results as $result) {
			$s = simplexml_import_dom($result);
		    //récupère le fragment du document
		    	$anno = $s->div[0]->div;
		    	//on enregistre le fragment
			$idDocFrag = $dbM->ajoutDoc($idMon, array("parent"=>$idDoc,"titre"=>"fragment ".$numFrag, "note"=>$anno, "poids"=>strlen($anno), "pubDate"=>$datePost->format('Y-m-d H:i:s')),$idE);
			$numFrag ++;
		    	//récupère les commentaires
		    	foreach ($s->div[1]->ul->li as $c){
		    		$exiC = (string) $c->a->img['alt'];
		    		$exiTof = (string) $c->a->img['src'];
		    		$exiUrl =  (string) $c->a->img['href'];
		    		$dateC = $c->div->div[0]->span[0]."";
				$dateC = substr($dateC, -9);
				$dateC = DateTime::createFromFormat('j M y', $dateC);
		    		$cmt = $c->div->div[1]->div[1]."";
				//ajoute l'existence
				$idECmt = $dbM->ajoutExi($idMon, array("nom"=>$exiC, "parent"=>$idEG, "data"=>'{"icon":"'.$exiTof.'", "url":"'.$exiUrl.'"}'));
		    		//ajoute le commentaire
				$idDocCmt = $dbM->ajoutDoc($idMon, array("parent"=>$idDocFrag,"titre"=>"commentaire ".$numCmt, "note"=>$cmt, "poids"=>strlen($cmt), "pubDate"=>$dateC->format('Y-m-d H:i:s')),$idECmt);
				$numCmt ++;
		    	}
		}		
		
    	
    }

    /** enregistre les mots clefs d'un compte 
     * 
     * @login 	string	$login = login du compte
     * @return 	array	liste des tag du compte
     * 
     */
    function saveUserTag($login){
		$this->login = $login;    	
    		$url = "https://www.diigo.com/cloud/".$this->login."?sort=4";
		//enregistre ou récupère le comme existence
    		$this->getUser(array("login"=>$this->login,"flux"=>"diigo"));
		//initialise les gestionnaires de base de données
	    	if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUT)$this->dbUT = new Model_DbTable_Flux_UtiTag($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
				
	    	//récupère le contenu de l'url
		$html = $this->getUrlBodyContent($url);    	
		$idDoc = $this->dbD->ajouter(array("url"=>$url, "data"=>$html, "poids"=>strlen($html)));			
		
		//extraction de la liste des tags
		$dom = new Zend_Dom_Query($html);
		$results = $dom->query('//*[@id="innerCloud"]/ul/li/a');
		$nbTag = 0;
		foreach ($results as $result) {
		    $val = $result->nodeValue;
		    $tag = substr($val, 0, strrpos($val, " "));
		    $poids = substr($val, strrpos($val, "(")+1, -1);
		    //enregistre le tag
			$this->saveTag($tag, $idDoc, $poids);
		    
		    $nbTag ++;
		}
		
		return $this->dbUT->findTagByUtiId($this->user);
    }
    
    
    /** récupère l'historique des tags
     *
     * @param 	string	$dateUnit
     * @param 	int	$idUti
     * @param 	int	$idMonade
     * @param 	int	$idActi
     * @param 	int	$idParent
     * @param 	array	$arrTags
     * @param 	string	$req
     * @param 	array	$dates
     * @param 	string	$for
     * 
     * @return 	array	
     *
     */
    function getTagHisto($dateUnit, $idUti, $idMonade, $idActi, $idParent, $arrTags, $req, $dates, $for){

    	$sqlFormatDate = $dateUnit;
    	$this->bTrace = false;
    	$dbTag = new Model_DbTable_Flux_Tag($this->db);
    	$data = $dbTag->getTagHistoRapport($idMonade
    			, $idUti, $idActi, $idParent
    			, $arrTags, $sqlFormatDate, $req, $dates);
    	if($for=="stream"){
    		//récupère les extrémité des dates
    		$minDate = date("r");
    		$maxDate = 0;
    		foreach ($data as $d) {
    			if($minDate>$d['MinDate'])$minDate=$d['MinDate'];
    			if($maxDate<$d['MaxDate'])$maxDate=$d['MaxDate'];
    		}
    		$this->trace(date("r", $minDate)." -> ".date("r", $maxDate));
    		//calcul le tableau des dates
    		switch ($sqlFormatDate) {
    			case '%Y-%m':
    				$interval = new DateInterval('P1M');
    				$phpFormatDate = 'Y-m';
    				break;
				case '%Y':
    				$interval = new DateInterval('P1Y');
    				$phpFormatDate = 'Y';
    				break;
    				
    		}
    		//
    		$curDate = new DateTime();
    		$curDate->setTimestamp($minDate);
    		$mDate = new DateTime();
    		$mDate->setTimestamp($maxDate);
    		$this->trace($curDate->format('Y-m-d'));
    		$arrDate = array();
    		while ($curDate<$mDate) {
    			$arrDate[]=$curDate->format($phpFormatDate);
    			$curDate->add($interval);
    		}
    		$this->trace("le tableau des dates",$arrDate);
    		//ajoute les valeurs vides pour chaque éléments
    		$oTag = $data[0]['key'];
    		$j=0; $i=0; $nbDate = count($arrDate); $nbData = count($data);
    		$nData;
    		//foreach ($data as $d) {
    		for ($z = 0; $z < $nbData; $z++) {
    			$d = $data[$z];
    			$this->trace('temps '.$z.' : '.$i.' / '.$j,$d);
    			//on vérifie si on passe à un nouveau type
    			if($oTag!=$d['key']){
    				//on fini les temps restant
    				 for ($i = $j; $i < $nbDate; $i++) {
	    				 $nD = array('key'=>$oD['key'],'type'=>$oD['type'],'desc'=>$oD['desc']
	    				 ,'temps'=>$arrDate[$i],'score'=>0,'value'=>0
	    				 ,'MinDate'=>0,'MaxDate'=>0
	    				 );
	    				 $nData[]=$nD;
	    				 $this->trace('fin nouveau temps '.$i .' / '. $j,$nD);
    				 }
    				$j=0;
    				$oTag=$d['key'];
    			}
    			//on calcul les temps manquant
    			for ($i = $j; $i < $nbDate; $i++) {
    				//$this->trace($arrDate[$i]."==".$d['temps']);
    				if($arrDate[$i]==$d['temps']){
    					$nData[]=$d;
    					$j=$i+1;
    					$i=$nbDate;
    				}else{
    					$nD = array('key'=>$d['key'],'type'=>$d['type'],'desc'=>$d['desc']
    							,'temps'=>$arrDate[$i],'score'=>0,'value'=>0
    							,'MinDate'=>0,'MaxDate'=>0
    					);
    					$nData[]=$nD;
    					$this->trace('nouveau temps '.$i .' / '. $j,$nD);
    				}
    			}
    			$oD = $d;
    		}
    		//ordonne le tableau
    		$data = $nData;    		
	    }
	    return $data;
	  	}
    
}
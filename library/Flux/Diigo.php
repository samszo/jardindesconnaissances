<?php
/**
 * Flux_Diigo
 * Classe qui gère les flux Diigo
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\API
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Diigo extends Flux_Site{

	const API_URI = "https://secure.diigo.com";
    const API_URL = '/api/v2/bookmarks';
    const RSS_GROUP_URL = 'http://groups.diigo.com/group/';
    var $LUCENE_INDEX;
    var $setLucene = false;
    var $mc;
    //pour plus de détail cf. https://fr.wikipedia.org/wiki/Liste_des_codes_HTTP
    var $httpReponse = array(
    		"100"=>array("lib"=>"Continue","type"=>"Information","cycle"=>"vivant"),
    		"101"=>array("lib"=>"Switching Protocols","type"=>"Information","cycle"=>"vivant"),
    		"200"=>array("lib"=>"OK","type"=>"Succès","cycle"=>"vivant"),
    		"201"=>array("lib"=>"Created","type"=>"Succès","cycle"=>"vivant"),
    		"202"=>array("lib"=>"Accepted","type"=>"Succès","cycle"=>"vivant"),
    		"203"=>array("lib"=>"Non-Authoritative Succès","type"=>"Information","cycle"=>"malde"),
    		"204"=>array("lib"=>"No Content","type"=>"Succès","cycle"=>"malade"),
    		"205"=>array("lib"=>"Reset Content","type"=>"Succès","cycle"=>"vivant"),
    		"206"=>array("lib"=>"Partial Content","type"=>"Succès","cycle"=>"vivant"),
    		"300"=>array("lib"=>"Multiple Choices","type"=>"Redirection","cycle"=>"malade"),
    		"301"=>array("lib"=>"Moved Permanently","type"=>"Redirection","cycle"=>"vivant"),
    		"302"=>array("lib"=>"Found","type"=>"Redirection","cycle"=>"vivant"),
    		"303"=>array("lib"=>"See Other","type"=>"Redirection","cycle"=>"vivant"),
    		"304"=>array("lib"=>"Not Modified","type"=>"Redirection","cycle"=>"vivant"),
    		"305"=>array("lib"=>"Use Proxy","type"=>"Redirection","cycle"=>"vivant"),
    		"307"=>array("lib"=>"Temporary Redirect","type"=>"Redirection","cycle"=>"vivant"),
    		"400"=>array("lib"=>"Bad Request","type"=>"Erreur du client","cycle"=>"mort"),
    		"401"=>array("lib"=>"Unauthorized","type"=>"Erreur du client","cycle"=>"mort"),
    		"402"=>array("lib"=>"Payment Required","type"=>"Erreur du client","cycle"=>"malade"),
    		"403"=>array("lib"=>"Forbidden","type"=>"Erreur du client","cycle"=>"mort"),
    		"404"=>array("lib"=>"Not Found","type"=>"Erreur du client","cycle"=>"mort"),
    		"405"=>array("lib"=>"Method Not Allowed","type"=>"Erreur du client","cycle"=>"malade"),
    		"406"=>array("lib"=>"Not Acceptable","type"=>"Erreur du client","cycle"=>"mort"),
    		"407"=>array("lib"=>"Proxy Authentication Required","type"=>"Erreur du client","cycle"=>"mort"),
    		"408"=>array("lib"=>"Request Timeout","type"=>"Erreur du client","cycle"=>"mort"),
    		"409"=>array("lib"=>"Conflict","type"=>"Erreur du client","cycle"=>"mort"),
    		"410"=>array("lib"=>"Gone","type"=>"Erreur du client","cycle"=>"mort"),
    		"411"=>array("lib"=>"Length Required","type"=>"Erreur du client","cycle"=>"malade"),
    		"412"=>array("lib"=>"Precondition Failed","type"=>"Erreur du client","cycle"=>"mort"),
    		"413"=>array("lib"=>"Payload Too Large","type"=>"Erreur du client","cycle"=>"mort"),
    		"414"=>array("lib"=>"URI Too Long","type"=>"Erreur du client","cycle"=>"mort"),
    		"415"=>array("lib"=>"Unsupported Media Type","type"=>"Erreur du client","cycle"=>"mort"),
    		"416"=>array("lib"=>"Range Not Satisfiable","type"=>"Erreur du client","cycle"=>"mort"),
    		"417"=>array("lib"=>"Expectation Failed","type"=>"Erreur du client","cycle"=>"mort"),
    		"426"=>array("lib"=>"Upgrade Required","type"=>"Erreur du client","cycle"=>"mort"),
    		"500"=>array("lib"=>"Internal Server Error","type"=>"Erreur du serveur","cycle"=>"mort"),
    		"501"=>array("lib"=>"Not Implemented","type"=>"Erreur du serveur","cycle"=>"mort"),
    		"502"=>array("lib"=>"Bad Gateway","type"=>"Erreur du serveur","cycle"=>"mort"),
    		"503"=>array("lib"=>"Service Unavailable","type"=>"Erreur du serveur","cycle"=>"mort"),
    		"504"=>array("lib"=>"Gateway Timeout","type"=>"Erreur du serveur","cycle"=>"mort"),
    		"505"=>array("lib"=>"HTTP Version Not Supported","type"=>"Erreur du serveur","cycle"=>"mort"),
    		"520"=>array("lib"=>"Origin Error","type"=>"Erreur du serveur","cycle"=>"mort"),
	    "522"=>array("lib"=>"Origin Connection Time-out","type"=>"Erreur du serveur","cycle"=>"mort"),
    		"596"=>array("lib"=>"596","type"=>"Erreur du serveur","cycle"=>"mort"),    		
    		"999"=>array("lib"=>"Request denied","type"=>"Erreur du serveur","cycle"=>"mort")
    );
    
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
		//initialise les gestionnaires de base de données
		$this->initDbTables();
		$this->trace("Tables initialisées");
		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);    	 
		$this->idTagRoot = $this->dbT->ajouter(array("code"=>__CLASS__),true,false);    	 
    	
		if(!$login)$login = LOGIN_DIIGO_API;
		if(!$pwd)$pwd = MDP_DIIGO_API;

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
	 * récupére les informations des images
	 *
     * @param   string $separateur
	 *
	 * @return array
	 */
    public function getImageInfos($separateur='#'){

        /**
         * ATTENTION le group_concat limit la taille du texte
         * pour corriger : SET group_concat_max_len = <int>
         * merci à https://stackoverflow.com/questions/529105/trouble-with-group-concat-and-longtext-in-mysql/529123#529123
         */
        $sql = "SELECT 
				d.titre,
				d.url,
				d.doc_id,
				d.pubDate,
				di.doc_id idI,
				di.url urlI,
				di.tronc,
				SUBSTRING(di.data, 1) ref,
				GROUP_CONCAT(DISTINCT t.code SEPARATOR '".$separateur."' ) tags
			FROM
				flux_doc d
					INNER JOIN
				flux_doc di ON di.parent = d.doc_id
					INNER JOIN
				flux_rapport r ON r.src_id = d.doc_id
					AND r.src_obj = 'doc'
					INNER JOIN
				flux_rapport rt ON rt.src_id = r.rapport_id
					AND rt.src_obj = 'rapport'
					AND rt.dst_obj = 'tag'
					INNER JOIN
				flux_tag t ON t.tag_id = rt.dst_id
			WHERE
				d.parent = 1
			GROUP BY d.doc_id, di.doc_id ";
        return $this->dbD->exeQuery($sql);
    }	

	/**
	 * créer un csv pour importer des photos dans Omeka
	 *
	 *
	 * @return void
	 */
	function getImages(){

	    $this->trace(__METHOD__);

	    //récupère les infos 	    
	    $arr = $this->getImageInfos();

		return $arr;

	}

	/**
	 * créer un csv pour importer des photos dans Omeka
	 *
	 * @param  string  $fic
	 *
	 * @return void
	 */
	function getCsvToOmeka($fic){
	    
	    $this->trace(__METHOD__." ".$fic);
	    $arrItemParent = array();
	    $arrItem = array();

	    //récupère les infos 	    
	    $arr = $this->getImageInfos();
	    $nb = count($arr);
	    $idDocOld = 0;
	    //foreach ($arrH as $h) {
	    for ($i = 0; $i < $nb; $i++) {
			$h = $arr[$i];
			if($idDocOld!=$h['doc_id']){
				$arrItemParent[] = array("itemSet"=>1,"owner"=>"samuel.szoniecky@univ-paris8.fr"
						,"dcterms:title"=>$h["titre"]
						,"dcterms:isReferencedBy"=>$h["url"]
						,"dcterms:identifier"=>$this->idBase."-flux_doc-doc_id-".$h["doc_id"]
						,"dcterms:date"=>$h["pubDate"]
						,"dcterms:creator"=>$this->login
						,"dcterms:subject"=>$h["tags"]
						,"dcterms:provenance"=>"diigo"
				);
				$idDocOld=$h['doc_id'];
			}
            //récupère l'item set du parent
            //$is = $this->dbIS->getByIdentifier($this->idBase."-flux_doc-doc_id-".$h["parent"]);	            
            $path_parts = pathinfo($h["urlI"]);
            if(substr($h["url"],0,4)=="http"){ 
				$arrItem[] = array("itemSet"=>63,"owner"=>'samuel.szoniecky@univ-paris8.fr'
						,"dcterms:title"=>"annotation ".$h["tronc"]
    	                ,"dcterms:isReferencedBy"=>$h["ref"]
    	                ,"dcterms:identifier"=>$this->idBase."-flux_doc-doc_id-".$h["idI"]
                        ,"file"=>$path_parts["basename"]
						,"dcterms:creator"=>$this->login
						,"dcterms:subject"=>$h["tags"]
						,"dcterms:provenance"=>"diigo"
						,"dcterms:isPartOf"=>$this->idBase."-flux_doc-doc_id-".$h["doc_id"]					
                );
                //$this->trace("faceAnnotations=".$h["gv2note"]);	        	        
            }
        }
        
	    //enregistre le csv des parents dans un fichier
	    $fp = fopen(str_replace('.csv','_parent.csv',$fic), 'w');
	    $first = true;
	    foreach ($arrItemParent as $v) {	        
	        if($first)fputcsv($fp, array_keys($v));
	        $first=false;
	        fputcsv($fp, $v);
	    }
		fclose($fp);        	    
		

	    //enregistre le csv des images dans un fichier
	    $fp = fopen($fic, 'w');
	    $first = true;
	    foreach ($arrItem as $v) {	        
	        if($first)fputcsv($fp, array_keys($v));
	        $first=false;
	        fputcsv($fp, $v);
	    }
        fclose($fp);        	    
		
	    $this->trace("FIN ".__METHOD__);
        
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
			//$this->trace($json);
		    $arr = json_decode($json);
	        //le decode de zend est beaucoup trop long
	        //$arr = Zend_Json_Decoder::decode($json);
	        
			$this->cache->save($arr, $c);
        }        
        return $arr;
    	
    }
    

	/**
	 * enegistre les images récupère dans le outliner
	 *
	 * @param string 	$fic
	 *
	 * @return array
	 */
    function saveOutlinerImage($fic){
		$this->trace("DEBUT ".__METHOD__);				
		$html = $this->getUrlBodyContent($fic);
		$dom = new Zend_Dom_Query($html);	    

		//récupère le titre du doc
		$xPath = '//*[@id="diigoLibrary"]/div[2]/div';
		$results = $dom->queryXpath($xPath);
		foreach ($results as $result) {
			//récupère l'annotations correspondant à l'image
			$ref = $result->getAttribute('data-id');
			if($ref){
				$this->trace("ref = ".$ref);
				$sql = "SELECT doc_id FROM flux_doc WHERE data LIKE '%".$ref."%' ";
				//$this->trace($sql);
				$doc = $this->dbD->exeQuery($sql);
				$this->trace("nbDoc = ".count($doc));
				if(count($doc)){
					$idDoc = $doc[0]['doc_id'];
					//récupère l'adresse de l'image
					$imgs = $result->getElementsByTagName('img');
					foreach($imgs as $img){
						$src = $img->getAttribute('src');
						$src = str_replace('image_size=160','image_size=0',$src);
						//enregistre la photo
						$img = ROOT_PATH.'/data/diigo/photos/'.$idDoc."_".$ref.".jpg";
						if (!file_exists($img)){
							$client = new Zend_Http_Client($src,array('timeout' => 30));
							$client->setCookie('gcc_cookie_id','3477d5c247e572cdc3aa7073b295cebc');
							$client->setCookie('diigoandlogincookie','f1-.-luckysemiosis-.-20-.-0');
							$client->setCookie('CHKIO','7e58e62b93c12022e31eb3df8fb691ce');
							$client->setCookie('ditem_sort','updated');
							$client->setCookie('_smasher_session','1e67c34d3e85ab13b2fdd1513f0b41a8');
							$client->setCookie('outliner.sid','s%3A-dAe3XZThqsf1CSGjG8JeuwMP4EnZQGX.6gS1L6icv%2FiVxgs3RiwKM4PA2tKmm4DqGOy2IM5ZWto');
							$client->setCookie('_ga','GA1.2.273677479.1539250002');
							$client->setCookie('count','96');
							$client->setCookie('_gid','GA1.2.1428775098.1547539861');
							$client->setCookie('CACHE_TIP','true');
							$client->setCookie('__utma','45878075.273677479.1539250002.1547623534.1547623534.1');
							$client->setCookie('__utmc','45878075');
							$client->setCookie('__utmz','45878075.1547623534.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)');
							$response = $client->request();
							$raw = $response->getRawBody();		
							$res = file_put_contents($img, $raw);
							$this->trace($res." ko : Fichier créé ".$src." ".$img);
						}
						//met à jour les infos de l'annotation
						$this->dbD->edit($idDoc,array('url'=>$img,'note'=>$src));
					}					
				}
				//$titre = $result->nodeValue;
				//$this->trace($titre);
	
			}
		}

		$this->trace("FIN ".__METHOD__);				
	}	

	/**
	 * enegistre le bookmark d'un compte
	 *
	 * @param string 	$login
	 * @param string 	$tags
	 *
	 * @return array
	 */
    function saveAll($login=false, $tags=""){
    	
		$this->trace("DEBUT ".__METHOD__);				
		
		set_time_limit(0);
		
  		if(!$login)$login=$this->login;
    		$this->getUser(array("login"=>$login,"flux"=>"diigo"));
    		
		$this->trace("LOGIN ".$login." : ".$this->user);				
    				
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
		$paramQuery = array("user"=>$login,"count"=>$count);
		if($tags) $paramQuery["tags"]=urlencode($tags);
		while ($i>0) {
			$paramQuery["start"]=$i;
			$arr = $this->getRequest($paramQuery,false);
			if(!$arr){
				$i=-1;	
			}else{				
				$j = 0;
				$this->trace("getRequest   ".$i." : ".count($arr));
				foreach ($arr as $item){
					$this->trace($i."   ".$j." : ".$item->url);
					/*vérifie l'existence de l'url
					$idD = $this->dbD->existe(array("url"=>$item->url));
					if($idD){
						$this->trace("EXISTE = ".$idD);
					}else{
						$this->trace("ABSCENT",$item);						
						$this->saveItem($item);
					}
					*/
					$this->saveItem($item);
					$j++;
				}
				if(count($arr) <= 1)$i=-1;
				else $i = $i+$j-1;
			}
		}
		if($this->setLucene)$this->lucene->index->optimize();
		//
		
		$this->trace("FIN ".__METHOD__);				
    }    

    /**
     * évalue l'activité d'un compte
     *
     * @param string 	$user
     *
     * @return array
     */
    function evalActi($user=false){
    	$this->trace("DEBUT ".__METHOD__);
		$count = 10;
		$i = 1;
		$arr = $this->getRequest(array("user"=>$user,"count"=>$count, "start"=>$i),false);		
		//$result = array();

		return $arr;
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
    				//vérifie que l'item existe
    				$doc = $this->dbD->findByUrl($item->url);
    				//$this->trace("vérifie que l'item existe ",$doc);
    				if($doc) {
    					$rs = array();
    					//break;
    				}
    				else{
						$this->trace($i."   ".$j." : ".$item->url);
						$this->saveItem($item);
					} 
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
	    $item['created_at'] = strpos($i->created_at, '+') ? substr($i->created_at,0,-6) : $i->created_at;//supprime ' +0000'
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
		
		
		//$this->trace("traitement des annotations");
		if(count($item['annotations'])>0){
			$z = 0;
			foreach ($item['annotations'] as $note){
				$j = array();
				$j['i'] = $z;
				$j['content'] = $note->content;
				$j['created_at'] = $note->created_at;
				$this->saveContent($j, $idD, $idRap);
				$z++;
			}
		}		
		//traitement des mots clefs		
		if($item['tags']!=""){
			$arrTags = explode(",", $item['tags']);
			foreach ($arrTags as $tag){
				$this->mc->save($tag, $idRap, 1,'diigo');	    			
			}						
		}				
		
    } 

	function saveContent($item, $idD, $idRap){
	   	
		$pd = strpos($item['created_at'], '+') ? substr($item['created_at'],0,-6) : $item['created_at'];	
		$id = $this->dbD->ajouter(array("parent"=>$idD,"tronc"=>$item['i'],"data"=>$item['content'],"pubDate"=>$pd));
		//$this->trace(__METHOD__." enregistre le document = ".$id);

		/*récupère l'action
		$idAct = $this->dbA->ajouter(array("code"=>__METHOD__));	
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idRap,"src_obj"=>"rapport"
				,"dst_id"=>$id,"dst_obj"=>"doc"
				,"pre_id"=>$idAct,"pre_obj"=>"acti"
		));
		*/
		//récupère les mot clefs
	   	//$arrKW = $this->mc->saveForChaine($idD, $item['content'],$idRap);

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

    	$url = self::RSS_GROUP_URL.$groupe."/rss?count=100&start=0";
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

	    	$this->bTrace = false;
	    	$dbTag = new Model_DbTable_Flux_Tag($this->db);
	    	$data = $dbTag->getTagHistoRapport($idMonade
	    			, $idUti, $idActi, $idParent
	    			, $arrTags, $dateUnit, $req, $dates);
		$stat = new Flux_Stats();    	
	    	if($for=="stream"){
    		
    			$nData = $stat->getDataForStream($data, $dateUnit);
   		
    			//ordonne le tableau
    			$data = $nData;    		
	    }
	    return $data;
  	}

  	/** récupère le statut des url 
  	 *
     * @param 	string		$dateUnit
     * @param 	int			$idActi
     * @param 	string		$deb
     * @param 	string		$fin
     * @param 	string		$for
  	 *
  	 * @return 	array
  	 *
  	 */
  	function getStatutUrl($dateUnit="%Y", $idActi = 7, $deb="", $fin="", $for="multiligne", $statuts=false){
  	
  		$db = new Model_DbTable_Flux_Doc($this->db);
  		
  		$sql = "SELECT 
			    DATE_FORMAT(d.pubDate, '".$dateUnit."') temps,
  				COUNT(DISTINCT d.doc_id) nbDoc,
			    SUBSTRING(t.code, 10, 3) tags
			FROM
			    flux_rapport r
			        INNER JOIN
			    flux_tag t ON t.tag_id = r.dst_id
			        INNER JOIN
			    flux_doc d ON d.doc_id = r.src_id
			WHERE
			    r.src_obj = 'doc' AND r.dst_obj = 'tag'
			        AND r.pre_obj = 'acti'
			        AND r.pre_id = ".$idActi."
			";
  		if($deb && $fin)
  			$sql .= " AND doc.temps BETWEEN '".$deb."' AND '".$fin."' ";
  						
  		$sql .= " GROUP BY temps , tags ";
  		  		
  		if($statuts){
  			$sql .= " HAVING tags IN (";
  			foreach ($statuts as $s) {
  				$sql .= "'".$s."', ";
  			}
  			$sql .= "'rien') ";
  		}
  				
  		$sql .= " ORDER BY temps , tags ";
  		//echo $sql;
  	
  		//
		$rs = $db->getAdapter()->query($sql);
		$data = $rs->fetchAll();
		
		//regroupe les données suivant l'accessibilité
		$dataN = array();
		foreach ($data as $d) {
			if (!$this->httpReponse[$d['tags']]["cycle"]){
				$toto = 0;
			}
			if($for=="multiligne"){
				$dataN[] = array('temps'=>$d['temps'],'nbDoc'=>$d['nbDoc'],"tags"=>$this->httpReponse[$d['tags']]["cycle"]);
			}else{
				$dataN[] = array('temps'=>$d['temps'],'nbDoc'=>$d['nbDoc']
						,"tags"=>$d['tags']
						,"lib"=>$this->httpReponse[$d['tags']]["lib"]
						,"cycle"=>$this->httpReponse[$d['tags']]["cycle"]
						,"type"=>$this->httpReponse[$d['tags']]["type"]						
				);
			}
				
		}
		
		if($for=="multiligne"){
			$stat = new Flux_Stats();
			$data = $stat->getDataForMultiligne($dataN,"liste");
  		}	
  		if($for=="area"){
  			$data = $dataN;
  		}
  		return $data;
  		//
  		
  	}
  	 
  	/** récupère les performances d'importation
  	 * 
  	 * @param	$deb		string
  	 * @param	$fin		string
  	 * 
  	 * @return 	array
  	 *
  	 */
	function getPerformance($deb="", $fin=""){

    		$db = new Model_DbTable_Flux_Doc($this->db);
    		$sql = "SELECT 
			    tempsD as temps, nbDoc, nbTag
			FROM
			    (SELECT 
			        COUNT(d.doc_id) nbDoc,
			            SUM(LENGTH(d.data)) nbOct,
			            DATE_FORMAT(d.maj, '%Y-%m-%d %H:%i:%s') tempsD
			    FROM
			        flux_doc d
			    GROUP BY tempsD
			    ORDER BY nbDoc DESC) doc,
			    (SELECT 
			        COUNT(r.rapport_id) nbTag,
			            DATE_FORMAT(r.maj, '%Y-%m-%d %H:%i:%s') tempsT
			    FROM
			        flux_rapport r
			    WHERE
			        r.src_obj = 'rapport'
			            AND r.dst_obj = 'tag'
			            AND r.pre_obj = 'acti'
			    GROUP BY tempsT
			    ORDER BY nbTag DESC) tag
			WHERE
			    doc.tempsD = tag.TempsT ";
    		if($deb && $fin)
    			$sql .= " AND doc.tempsD BETWEEN '".$deb."' AND '".$fin."' ";
    		$sql .= " ORDER BY doc.tempsD";
    		// 
    		
    		$rs = $db->getAdapter()->query($sql);
    		return $rs->fetchAll();
    		//return $dbDoc->fetchAll($query)->toArray();
    }
    
    
    /** récupère les performances d'importation
     *
     * @param 	string	$dateUnit
     * @param 	int	$idUti
     * @param 	int	$idMonade
     * @param 	int	$idActi
     * @param 	int	$idParent
     * @param 	array	$dates
     * @param 	string	$for
     * @param 	array	$tags
     * @param 	int		$nbLimit
     * @param 	int		$nbMin
     *
     * @return 	array
     *
     */
    function getHistoTagLies($idTag, $dateUnit, $idUti, $idMonade, $idActi, $idParent, $dates=false, $for="stream", $tags=false,$nbLimit=1,$nbMin=0){
		    
		$db = new Model_DbTable_Flux_Doc($this->db);
		$sql = "SELECT 
		    t.tag_id,
		    t.code ,
		    COUNT(DISTINCT rd.src_id) value,
		    DATE_FORMAT(d.pubDate, '".$dateUnit."') temps,
		    MIN(UNIX_TIMESTAMP(d.pubDate)) MinDate,
		    MAX(UNIX_TIMESTAMP(d.pubDate)) MaxDate,
		    tl.tag_id 'key', tl.code 'type', tl.desc ";
		if ($for=="multiligne"){
			$sql = "SELECT
			t.tag_id,
			t.code tag,
			DATE_FORMAT(d.pubDate, '".$dateUnit."') temps,
			COUNT(DISTINCT rd.src_id) nbDoc,
			group_concat(tl.tag_id) idTags,
			group_concat(tl.code) tags ";
		}
		$sql .= " FROM
		    flux_tag t
		        INNER JOIN
		    flux_rapport r ON r.monade_id = ".$idMonade."
		        AND r.src_obj = 'rapport'
		        AND r.dst_obj = 'tag'
		        AND r.pre_obj = 'acti'
		        AND r.pre_id = ".$idActi."
		        AND t.tag_id = r.dst_id
		        INNER JOIN
		    flux_rapport rd ON rd.rapport_id = r.src_id
		        INNER JOIN
		    flux_doc d ON d.doc_id = rd.src_id AND d.parent = ".$idParent."
		        INNER JOIN
		    flux_rapport rl ON rl.src_id = r.src_id
		        AND rl.src_obj = 'rapport'
		        INNER JOIN
		    flux_tag tl ON tl.tag_id = rl.dst_id 
		WHERE
		    t.tag_id = ".$idTag;
		if($tags){
			$sql .= " AND tl.tag_id IN (".implode(',',$tags).") ";
		}
		
		if($dates){
			$minDate = new DateTime();
			$minDate->setTimestamp($dates[0]);
			$maxDate = new DateTime();
			$maxDate->setTimestamp($dates[1]);
			$sql .= " AND d.pubDate BETWEEN '".$minDate->format("Y-m-d H:i:s")."' AND '".$maxDate->format("Y-m-d H:i:s")."' ";
		}
		
		if($for=="stream"){		
			$sql .= " GROUP BY tl.tag_id, temps 
					HAVING value > ".$nbLimit."
					ORDER BY tl.code, temps
					";
		}
		if($for=="multiligne"){
			$sql .= " GROUP BY temps
				HAVING nbDoc > 1
				ORDER BY temps
						";
		}		
		
		//echo $sql;
		    		//
    		//execution de la requête
    		$rs = $db->getAdapter()->query($sql);
    		$data = $rs->fetchAll();
    		
    		//filtre les donnée
    		if($nbMin){
    			$newData = array();
    			foreach ($data as $d) {
    				if($d['value'] >= $nbMin) $newData[]=$d;
    			}
    			$data = $newData;
    		}
    		
    		$stat = new Flux_Stats();
    		if($for=="stream"){    		
    			$data = $stat->getDataForStream($data, $dateUnit);    			 
    		}
    		if($for=="multiligne"){
    			$data = $stat->getDataForMultiligne($data);
    		}
    		return $data;
    		
    }
    
    function getHistoUti(){
    	
    		$sql = "SELECT
			 COUNT(DISTINCT t.tag_id) nbTag
			 ,COUNT(DISTINCT rd.src_id) nbDoc
			 ,  DATE_FORMAT(d.pubDate, '%d-%c-%y') temps
             , u.login
			 FROM
			 flux_tag t
			 INNER JOIN
				 flux_rapport r ON r.monade_id = 2
			 	AND r.src_obj = 'rapport'
			 	AND r.dst_obj = 'tag'
			 	AND r.pre_obj = 'acti'
			 	AND r.pre_id = 2
			 	AND t.tag_id = r.dst_id
			 INNER JOIN
				 flux_rapport rd ON rd.rapport_id = r.src_id
			 INNER JOIN
				 flux_doc d ON d.doc_id = rd.src_id AND d.parent = 1
			 INNER JOIN
				 flux_uti u ON u.uti_id = rd.pre_id
			 GROUP BY u.uti_id, temps
    			 ORDER BY d.pubDate	";
    		//echo $sql;
    		
    		$db = new Model_DbTable_Flux_Doc($this->db);
    		$rs = $db->getAdapter()->query($sql);
    		$data = $rs->fetchAll();

    		//$stat = new Flux_Stats();
    		//$data = $stat->getDataForMultiligne($data,"liste","login");    		
    		
    		return $data;
    		
    }
    
    
    /** vérifie toutes les urls de la base
     *
     *
     */
    function verifAllUrl(){
    
	    	$this->trace("DEBUT ".__METHOD__);
	    	set_time_limit(0);
	    	
	    	//récupère l'action
	    	$idAct = $this->dbA->ajouter(array("code"=>__METHOD__));
	    	//récupère le tag général
	    $idTagCV = $this->dbT->ajouter(array("code"=>"Cycle de vie"));	    
	    $idTagIna = $this->dbT->ajouter(array("code"=>"HTTP/1.1 404 inaccessible","parent"=>$idTagCV));
	    //$this->dbT->ajouter(array("code"=>"inaccessible","parent"=>$idTagCV));
	     
	    //récupère les url
	    $arr = $this->dbD->findLikeUrl("http://");
	    $nbDoc = count($arr);
	    for ($i = 13431; $i < $nbDoc; $i++) {
	    		$d = $arr[$i];
	    		$r = $this->dbD->testUrl($d["url"]);
	    		
	    		if(!$r){
	    			//récupère le tag du statut
	    			$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    					,"src_id"=>$d["doc_id"],"src_obj"=>"doc"
	    					,"dst_id"=>$idTagIna,"dst_obj"=>"tag"
	    					,"pre_id"=>$idAct,"pre_obj"=>"acti"
	    					,"valeur"=>"maintenant"
	    					,"niveau"=>date('U')
	    			));	    			
	    		}else{
	    			//récupère le tag du statut
	    			if(isset($r[0]))$idTag = $this->dbT->ajouter(array("code"=>$r[0],"parent"=>$idTagCV));
	    			else $idTag = $idTagIna;
	    			
	    			//calcule les valeurs de date
	    			$arrDate = array();
	    			if(isset($r["Last-Modified"])) $arrDate[]="Last-Modified";
	    			if(isset($r["Expires"])) $arrDate[]="Expires";
	    			if(isset($r["pubDate"])) $arrDate[]="pubDate";
	    			if(isset($r["maj"])) $arrDate[]="maj";
	    			if(isset($r["Date"])) $arrDate[]="Date";
	    			
				foreach ($arrDate as $iDate) {
					if(is_array($r[$iDate]))$rD = $r[$iDate][0];
					else $rD = $r[$iDate];
							
					if($rD==0 || !is_numeric(strtotime($rD))){
						$dV = 0;						
					}else{
						$dV = new DateTime($rD);
						$dV = $dV->format('U');						
					}
					//enregistre le rapport
    					$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
    						,"src_id"=>$d["doc_id"],"src_obj"=>"doc"
    						,"dst_id"=>$idTag,"dst_obj"=>"tag"
    						,"pre_id"=>$idAct,"pre_obj"=>"acti"
    						,"valeur"=>$iDate
    						,"niveau"=> $dV
    							
    					));
    				}
	    		}

	    		$this->trace($i." = ".$d["url"]);
	    		 
	    	}

    }
    
}
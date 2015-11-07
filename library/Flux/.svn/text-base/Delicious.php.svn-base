<?php
/**
 * Classe qui gère les flux delicious
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Flux_Delicious  extends Zend_Service_Delicious{

	var $cache;
	var $forceCalcul = false;
	var $user;
	var $idUser;
	var $dbU;
	var $dbUU;
	var $dbUT;
	var $dbUD;
	var $dbUTD;
	var $dbT;
	var $dbTD;		
	var $dbD;

	const JSON2_URI     	= 'http://feeds.delicious.com';
    const JSON2_URL     	= '/v2/json/url/%s';
    const JSON2_URLINFO     = '/v2/json/urlinfo/%s';

 	public function __construct($user=null, $pwd=null, $cache=null)
    {
    	parent::__construct($user, $pwd);
    	
    	if($cache){
		    $this->cache = $cache;	
    	}else{
    		//paramètrage du cache
			$frontendOptions = array(
		    	'lifetime' => 31536000, //  temps de vie du cache de 1 an
		        'automatic_serialization' => true
			);
		   	$backendOptions = array(
				// Répertoire où stocker les fichiers de cache
		   		'cache_dir' => ROOT_PATH.'/tmp/flux/'
			);
			// créer un objet Zend_Cache_Core
			$this->cache = Zend_Cache::factory('Core','File',$frontendOptions,$backendOptions);
    	} 
    	
    }

    /**
     * Get details on a particular bookmarked URL
     *
     * Returned array contains four elements:
     *  - hash - md5 hash of URL
     *  - top_tags - array of tags and their respective usage counts
     *  - url - URL for which details were returned
     *  - total_posts - number of users that have bookmarked URL
     *
     * If URL hasen't been bookmarked null is returned.
     *
     * @param  string $url URL for which to get details
     * @return array
     */
    public function getUrlDetails2($url)
    {
        $path = sprintf(self::JSON2_URL, md5($url));
        return $this->makeRequestJ2($path, array("count"=>100), 'json2');               
    }

    
    /**
     * Get infos like tagometer on a particular bookmarked URL
     *
     * Returned array contains four elements:
     *  - hash - md5 hash of URL
     *  - title - title of the post
     *  - top_tags - array of tags and their respective usage counts
     *  - url - URL for which details were returned
     *  - total_posts - number of users that have bookmarked URL
     *
     * If URL hasen't been bookmarked null is returned.
     *
     * @param  string $url URL for which to get details
     * @return array
     */
    public function getUrlInfos($url)
    {
        $path = sprintf(self::JSON2_URLINFO, md5($url));
        return $this->makeRequestJ2($path, array("count"=>100), 'json2');
    }

    /**
     * Handles all GET requests to a web service
     *
     * @param   string $path  Path
     * @param   array  $parms Array of GET parameters
     * @param   string $type  Type of a request ("xml"|"json")
     * @return  mixed  decoded response from web service
     * @throws  Zend_Service_Delicious_Exception
     */
    public function makeRequestJ2($path, array $parms = array(), $type = 'xml')
    {
        // if previous request was made less then 1 sec ago
        // wait until we can make a new request
        $timeDiff = microtime(true) - self::$_lastRequestTime;
        if ($timeDiff < 1) {
            usleep((1 - $timeDiff) * 1000000);
        }      
        
        $this->_rest->getHttpClient()->setAuth($this->_authUname, $this->_authPass);

        $this->_rest->setUri(self::JSON2_URI);

        self::$_lastRequestTime = microtime(true);
        $response = $this->_rest->restGet($path, $parms);

        if (!$response->isSuccessful()) {
            /**
             * @see Zend_Service_Delicious_Exception
             */
            throw new Zend_Service_Delicious_Exception("Http client reported an error: '{$response->getMessage()}'");
        }

        $responseBody = $response->getBody();

        return Zend_Json_Decoder::decode($responseBody);

    }
    
    
    /**
     * Récupère l'identifiant d'utilisateur
     *
     * @param string $user
     * 
     * @return integer
     */
	function GetUser($user) {

		//récupère ou enregistre l'utilisateur
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti();
		return $this->dbU->ajouter(array("login"=>$user,"flux"=>"delicious"));		

	}
	
	/**
     * Enregistre le flux dans la base
     *
     * @param string $user
     * @param string $pwd
     * 
     */
	function SaveUserPost($user, $detail=false, $tag=null) {

        $c = "Flux_Delicious_SaveUserPost_".$user."_".md5($tag);
        if($this->forceCalcul)$this->cache->remove($c);
        if(!$posts = $this->cache->load($c)) {
			$posts = $this->getAllPosts($tag);
			$this->cache->save($posts,$c);
		}
		$this->SavePosts($posts, $user, $detail);
	}
	
	function SaveUserNetwork($user, $pwd) {

        $c = "Flux_Delicious_SaveUserNetwork_".$user;
        if($this->forceCalcul)$this->cache->remove($c);
        if(!$nw = $this->cache->load($c)) {
			$nw = $this->getUserNetwork($user);
			$this->cache->save($nw,$c);
		}

		$this->idUser = $this->GetUser($user);		
		
		//récupère ou enregistre le users du network
		if(!$this->dbUU)$this->dbUU = new Model_DbTable_Flux_UtiUti();
		foreach ($nw as $us) {
			$idUdst = $this->GetUser($us);		
			$this->dbUU->ajouter(array("uti_id_src"=>$this->idUser,"uti_id_dst"=>$idUdst));		
			$this->dbUU->edit($this->idUser, $idUdst, array("network"=>1));		
		};		
	}

	function SaveUserFan($user, $pwd) {

        $c = "Flux_Delicious_SaveUserFan_".$user;
        if($this->forceCalcul)$this->cache->remove($c);
        if(!$nw = $this->cache->load($c)) {
			$nw = $this->getUserFans($user);
			$this->cache->save($nw,$c);
		}

		$idUsrc = $this->GetUser($user);		
		
		//récupère ou enregistre le users du network
		if(!$this->dbUU)$this->dbUU = new Model_DbTable_Flux_UtiUti();
		$date = new Zend_Date();
		foreach ($nw as $us) {
			$idUdst = $this->GetUser($us);		
			$this->dbUU->ajouter(array("uti_id_src"=>$idUsrc,"uti_id_dst"=>$idUdst));		
			$this->dbUU->edit($idUsrc, $idUdst, array("fan"=>1));		
		};		
	}

	function SaveUserPostUser($user, $pwd) {

		//création des objets
		$this->user = $user;
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc();
		
		$this->idUser = $this->GetUser($user);		
		
		//récupère les posts d'un utilisateur
		$arrUd = $this->dbUD->findDocByUti($this->idUser);
		$date = new Zend_Date();
		foreach ($arrUd as $d) {
			//prise en compte d'une clef lors d'un plantage
			if($d["doc_id"]>=6621){
		        $this->SaveDetailUrl($d['url']);
			}	        
		}						
	}
		
	function SaveDetailUrl($url){

		//récupère le détail de l'url
		$urlDetails = $this->GetDetailUrl($url);
        //enregistre le detail de l'url
		foreach ($urlDetails as $post) {
			//ne prend pas en compte l'utilisateur source
			if($post["a"]!=$this->user){
				//enregistre la relation entre utilisateur au niveau post
				$this->SaveUserRela($post["a"], array("post"=>1));
				//enregistre le post
				$this->SavePosts(array($post), $post["a"]);
			}
		}
		
	}

	function SaveUserRela($userDst, $types){
		//enregistre la relation entre utilisateur au niveau post
		if(!$this->dbUU)$this->dbUU = new Model_DbTable_Flux_UtiUti();
		$idUdst = $this->GetUser($userDst);		
		$this->dbUU->ajouter(array("uti_id_src"=>$this->idUser,"uti_id_dst"=>$idUdst));		
		$this->dbUU->edit($this->idUser, $idUdst, $types);		
	}
	
	function GetDetailUrl($url){
		//vérifie si le detail est en cache
		$cD = str_replace("::", "_", __METHOD__)."_".md5($url);
	    if($this->forceCalcul)$this->cache->remove($cD);
		if(!$urlDetails = $this->cache->load($cD)) {	
			//vérifie si les infos de l'url sont en cache
			$c = $cD."details";
			if($this->forceCalcul)$this->cache->remove($c);
			if(!$urlInfo = $this->cache->load($c)) {	
				$urlInfo = $this->getUrlInfos($url);
				$this->cache->save($urlInfo,$c);
				//sauvegarde le détail
				if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc();
				$this->dbD->edit(-1,array("total_posts"=>$urlInfo[0]["total_posts"],"top_tags"=>json_encode($urlInfo[0]["top_tags"])),$url);
	        }
        	if($urlInfo[0]["total_posts"] > 100){
				//récupère les infos en parsant les pages html
				$urlDetails = $this->ParseHtmlDetailUrl($url);
			}else{									
				//récupère les infos à partir du flux
				$urlDetails = $this->getUrlDetails2($url);
			}
			//vérifie si l'url vient d'un agrégateur de flux
			$urlP = $this->VerifUrl($url);
			if($urlP){
				//on ajoute les détails de cette url à ceux de l'url d'origine
				$urlDetailsP = $this->GetDetailUrl($urlP);
				$urlDetails = array_merge($urlDetailsP,$urlDetails);
			}
	        $this->cache->save($urlDetails,$cD);
        }
		return $urlDetails;
	}
	
	function VerifUrl($url){
		$sale = false;
		//vérification de la propreté de l'url
		$arrUrl = parse_url($url);
		if(isset($arrUrl["query"])){
			$q = parse_str($arrUrl["query"], $ps);
			//vérifie un lien générer par feedburner
			if (array_key_exists('utm_source', $ps)) {
			    //on retourne l'url nettoyer des paramètres feedburner
				return $arrUrl["scheme"]."://".$arrUrl["host"].$arrUrl["path"];
			}
			
		}
		return $sale;
	}
	
	function ParseHtmlDetailUrl($url, $p=1){
		//vérifie si le detail est en cache
		$c = str_replace("::", "_", __METHOD__)."_".md5($url)."_".$p;
        if($this->forceCalcul)$this->cache->remove($c);
		if(!$html = $this->cache->load($c)) {
			$delUri = "http://www.delicious.com/url/".md5($url)."?show=all&page=".$p."&count=100";
			$client = new Zend_Http_Client($delUri);
			$response = $client->request();
			$html = $response->getBody();
	        $this->cache->save($html,$c);
		}
		//parse le html zend
		$domDetails = new Zend_Dom_Query($html);
		$results = $domDetails->queryXpath('//*[@id="bookmarklist_everyone"]/li');

		//construction du tableau
		$posts = array();
		foreach ($results as $post) {
			$s = simplexml_import_dom($post);
			if($s->div->div[0]['title']){
				$dt = strtotime($s->div->div[0]['title']."");
				$odt = new Zend_Date($dt);
				$dt = $odt->get("c");
				$numDiv = 2;
			}else{
				$numDiv = 1;
			}
			$a = $s->div->div[$numDiv]->a['title']."";
			$n = $s->div->div[$numDiv-1]."";
			$u = $url;
			$arrT = array();
			if($s->div->div[$numDiv]->div->ul){
				foreach($s->div->div[$numDiv]->div->ul->li as $t){
					$arrT[] = $t->a."";
				}
			}else{
				$arrT[] = "";
			}
			$d = ""; 
			//ajoute l'item au tableau
			$posts[] = array("a"=>$a,"u"=>$u,"d"=>$d,"n"=>$n,"dt"=>$dt, "t"=>$arrT);
		}
		//vérifie s'il y a plusieurs page
		//il y a une limite de 40 pages de 50 posts soit 2000 posts maximum	
		if($posts && $p < 40){
			$pp = $this->ParseHtmlDetailUrl($url,$p+1);
			foreach ($pp as $post) {
				$posts[] = $post;
			}		
		}
		
		return $posts;		
	}
	
	function UpdateUserBase($user, $pwd){
		//récupération de la dernière date de mise à jour
		$this->idUser = $this->GetUser($user);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc();
		$r = $this->dbD->findLastDoc($this->idUser);
		$date = new Zend_Date();
		if($r){
			$dt = new Zend_Date($r[0]["maj"]);
			//récupère les posts pour chaque jour entre le dernier et aujourd'hui
			while ($dt->compare($date)<0) {
				// TODO :: employer la même méthode pour le reste de la class
				$c = str_replace("::", "_", __METHOD__)."_".$dt->getTimestamp();
		        if($this->forceCalcul)$this->cache->remove($c);
				if(!$posts = $this->cache->load($c)) {	
					$posts = $this->getPosts(null,$dt);
			        $this->cache->save($posts, $c);
				}
				$this->SavePosts($posts, $user, true);
				$dt->addDay(1);
			}
		}		
	}
	
	function SavePosts($posts, $user, $detail=false){
								
		//initialise les variables
		$idUser = $this->GetUser($user);
     	// TODO :: définir des propriété de class pour les objet de base de données //		
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag();
		if(!$this->dbUT)$this->dbUT = new Model_DbTable_Flux_UtiTag();
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc();
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc();
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc();
		if(!$this->dbUU)$this->dbUU = new Model_DbTable_Flux_UtiUti();		
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc();		
		$date = new Zend_Date();

		//récupère ou enregistre le post
		foreach ($posts as $post) {
			//gestion du type d'objet post
			if(is_array($post)){
				//venant directement du json
				$pudDate = $post["dt"];
				$url = $post["u"];					
				$idD = $this->dbD->ajouter(array("url"=>$url,"titre"=>$post["d"],"note"=>$post["n"],"pubDate"=>$pudDate,"maj"=>$date->get("c")));
				$tags = $post["t"];
			}else{
				//venant d'un objet zend delicious
				$pudDate = $post->getDate()->toString("c");
				$url = $post->getUrl();					
				$idD = $this->dbD->ajouter(array("url"=>$url,"titre"=>$post->getTitle(),"note"=>$post->getNotes(),"pubDate"=>$pudDate,"maj"=>$date->get("c")));
				$tags = $post->getTags();				
			}
			// ajouter lors du calcul statistique $this->dbUD->ajouter(array("uti_id"=>$idUser, "doc_id"=>$idD, "maj"=>$pudDate));
			foreach ($tags as $tag) {
				$idT = $this->dbT->ajouter(array("code"=>$tag));
				// ajouter lors du calcul statistique $this->dbUT->ajouter(array("uti_id"=>$idUser, "tag_id"=>$idT));
				$this->dbUTD->ajouter(array("uti_id"=>$idUser, "tag_id"=>$idT, "doc_id"=>$idD,"maj"=>$pudDate));
			}
			//enregistre le detail de l'url
			if($detail){
				$this->SaveDetailUrl($url);
			}
		};
		
	}
}
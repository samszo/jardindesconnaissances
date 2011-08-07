<?php
/**
 * Classe qui gère les flux delicious
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Flux_Delicious{

	var $cache;
	var $forceCalcul = false;
	var $user;
	var $idUser;
	var $del;
	var $dbUU;
	
    /**
     * Récupère l'identifiant d'utilisateur
     *
     * @param string $user
     * 
     * @return integer
     */
	function GetUser($user) {

		//récupère ou enregistre l'utilisateur
		$u = new Model_DbTable_Flux_Uti();
		return $u->ajouter(array("login"=>$user,"flux"=>"delicious"));		

	}
	
	/**
     * Enregistre le flux dans la base
     *
     * @param string $user
     * @param string $pwd
     * 
     */
	function SaveUserPost($user, $pwd, $detail=false, $dt=false) {

        $c = "Flux_Delicious_SaveUserPost_".$user;
        if($this->forceCalcul)$this->cache->remove($c);
        if(!$posts = $this->cache->load($c)) {
			$delicious = new Zend_Service_Delicious($user, $pwd);
			$posts = $delicious->getAllPosts();
			$this->cache->save($posts,$c);
		}
		$this->SavePosts($posts, $user, $detail, $dt);
	}
	
	function SaveUserNetwork($user, $pwd) {

        $c = "Flux_Delicious_SaveUserNetwork_".$user;
        if($this->forceCalcul)$this->cache->remove($c);
        if(!$nw = $this->cache->load($c)) {
			$delicious = new Zend_Service_Delicious($user, $pwd);
			$nw = $delicious->getUserNetwork($user);
			$this->cache->save($nw,$c);
		}

		$this->idUser = $this->GetUser($user);		
		
		//récupère ou enregistre le users du network
		$uu = new Model_DbTable_Flux_UtiUti();		
		$date = new Zend_Date();
		foreach ($nw as $us) {
			$idUdst = $this->GetUser($us);		
			$uu->ajouter(array("uti_id_src"=>$this->idUser,"uti_id_dst"=>$idUdst));		
			$uu->edit($this->idUser, $idUdst, array("network"=>1));		
		};		
	}

	function SaveUserFan($user, $pwd) {

        $c = "Flux_Delicious_SaveUserFan_".$user;
        if($this->forceCalcul)$this->cache->remove($c);
        if(!$nw = $this->cache->load($c)) {
			$delicious = new Zend_Service_Delicious($user, $pwd);
			$nw = $delicious->getUserFans($user);
			$this->cache->save($nw,$c);
		}

		$idUsrc = $this->GetUser($user);		
		
		//récupère ou enregistre le users du network
		$uu = new Model_DbTable_Flux_UtiUti();		
		$date = new Zend_Date();
		foreach ($nw as $us) {
			$idUdst = $this->GetUser($us);		
			$uu->ajouter(array("uti_id_src"=>$idUsrc,"uti_id_dst"=>$idUdst));		
			$uu->edit($idUsrc, $idUdst, array("fan"=>1));		
		};		
	}

	function SaveUserPostUser($user, $pwd) {

		//création des objets
		$this->user = $user;
		$this->del = new Zend_Service_Delicious($user, $pwd);
		$ud = new Model_DbTable_Flux_UtiDoc();
		$this->dbUU = new Model_DbTable_Flux_UtiUti();		
		
		$this->idUser = $this->GetUser($user);		
		
		//récupère les posts d'un utilisateur
		$arrUd = $ud->findDocByUti($this->idUser);
		$date = new Zend_Date();
		foreach ($arrUd as $d) {
	        $this->SaveHtmlDetailUrl($d['url']);	        
		}						
	}
		
	function SaveHtmlDetailUrl($url){

		//récupère le détail de l'url
		$urlDetails = $this->GetHtmlDetailUrl($url);
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
		$idUdst = $this->GetUser($userDst);		
		$this->dbUU->ajouter(array("uti_id_src"=>$this->idUser,"uti_id_dst"=>$idUdst));		
		$this->dbUU->edit($this->idUser, $idUdst, $types);		
	}
	
	function GetHtmlDetailUrl($url){
		//vérifie si le detail est en cache
		$cD = "Flux_Delicious_GetHtmlDetailUrl_".md5($url)."details";
	    if($this->forceCalcul)$this->cache->remove($cD);
		if(!$urlDetails = $this->cache->load($cD)) {	
			//vérifie si les infos de l'url sont en cache
			$c = "Flux_Delicious_GetHtmlDetailUrl_".md5($url);
			if($this->forceCalcul)$this->cache->remove($c);
			if(!$urlInfo = $this->cache->load($c)) {	
				$urlInfo = $this->del->getUrlInfos($url);
				$this->cache->save($urlInfo,$c);
	        }
        	if($urlInfo[0]["total_posts"] > 100){
				//récupère les infos en parsant les pages html
				$urlDetails = $this->ParseHtmlDetailUrl($url);
			}else{					
				//récupère les infos à partir du flux
				$urlDetails = $this->del->getUrlDetails2($url);
			}
	        $this->cache->save($urlDetails,$cD);
        }
		return $urlDetails;
	}
	
	function ParseHtmlDetailUrl($url, $p=1){
		//vérifie si le detail est en cache
		$c = "Flux_Delicious_ParseHtmlDetailUrl_".md5($url)."_".$p;
        if($this->forceCalcul)$this->cache->remove($c);
		if(!$html = $this->cache->load($c)) {	
			$delUri = "http://www.delicious.com/url/".md5($url)."?show=all&page=".$p;
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
				$dt = $s->div->div[0]['title']."";
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
		if($posts){
			$pp = $this->ParseHtmlDetailUrl($url,$p+1);
			foreach ($pp as $post) {
				$posts[] = $post;
			}		
		}
		
		return $posts;		
	}
	
	function UpdateUserBase($user, $pwd){
		//récupération de la dernière date de mise à jour
		$d = new Model_DbTable_Flux_Doc();
		$this->idUser = $this->GetUser($user);
		$r = $d->findLastDoc($this->idUser);
		$this->forceCalcul = true;
		if($r){
			$dt = new Zend_Date($r[0]["maj"]);
			$this->SaveUserPost($user, $pwd, true, $dt);
		}		
	}
	
	function SavePosts($posts, $user, $detail=false, $dt=false){
								
		//initialise les variables
		$this->idUser = $this->GetUser($user);
     	// TODO :: définir des propriété de class pour les objet de base de données //		
		$t = new Model_DbTable_Flux_Tag();
		$ut = new Model_DbTable_Flux_UtiTag();
		$d = new Model_DbTable_Flux_Doc();
		$ud = new Model_DbTable_Flux_UtiDoc();
		$td = new Model_DbTable_Flux_TagDoc();
		$this->dbUU = new Model_DbTable_Flux_UtiUti();		
		$date = new Zend_Date();

		//récupère ou enregistre le post
		foreach ($posts as $post) {
			//gestion du type d'objet post
			if(is_array($post)){
				//venant directement du json
				$pudDate = $post["dt"];
				//gestion de la date de mise à jour
				if($pudDate<$dt)continue;
				$url = $post["u"];					
				$idD = $d->ajouter(array("url"=>$url,"titre"=>$post["d"],"note"=>$post["n"],"pubDate"=>$pudDate,"maj"=>$date->get("c")));
				$tags = $post["t"];
			}else{
				//venant d'un objet zend delicious
				$pudDate = $post->getDate()->toString("c");
				//gestion de la date de mise à jour
				if($pudDate<$dt)continue;
				$url = $post->getUrl();					
				$idD = $d->ajouter(array("url"=>$url,"titre"=>$post->getTitle(),"note"=>$post->getNotes(),"pubDate"=>$pudDate,"maj"=>$date->get("c")));
				$tags = $post->getTags();				
			}
			$ud->ajouter(array("uti_id"=>$this->idUser, "doc_id"=>$idD, "maj"=>$pudDate));
			foreach ($tags as $tag) {
				$idT = $t->ajouter(array("code"=>$tag));
				$ut->ajouter(array("uti_id"=>$this->idUser, "tag_id"=>$idT));
				$td->ajouter(array("tag_id"=>$idT, "doc_id"=>$idD));
			}
			//enregistre le detail de l'url
			if($detail){
				$this->SaveHtmlDetailUrl($url);
			}
		};
		
	}
}
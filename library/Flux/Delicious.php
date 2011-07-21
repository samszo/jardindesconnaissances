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
	var $del;
	
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
	function SaveUserPost($user, $pwd) {

        $c = "Flux_Delicious_SaveUserPost_".$user;
        if($this->forceCalcul)$this->cache->remove($c);
        if(!$posts = $this->cache->load($c)) {
			$delicious = new Zend_Service_Delicious($user, $pwd);
			$posts = $delicious->getAllPosts();
			$this->cache->save($posts,$c);
		}
		$this->SavePosts($posts, $user);
	}
	
	function SaveUserNetwork($user, $pwd) {

        $c = "Flux_Delicious_SaveUserNetwork_".$user;
        if($this->forceCalcul)$this->cache->remove($c);
        if(!$nw = $this->cache->load($c)) {
			$delicious = new Zend_Service_Delicious($user, $pwd);
			$nw = $delicious->getUserNetwork($user);
			$this->cache->save($nw,$c);
		}

		$idUsrc = $this->GetUser($user);		
		
		//récupère ou enregistre le users du network
		$uu = new Model_DbTable_Flux_UtiUti();		
		$date = new Zend_Date();
		foreach ($nw as $us) {
			$idUdst = $this->GetUser($us);		
			$uu->ajouter(array("uti_id_src"=>$idUsrc,"uti_id_dst"=>$idUdst));		
			$uu->edit($idUsrc, $idUdst, array("network"=>1));		
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
		$uu = new Model_DbTable_Flux_UtiUti();		
		
		$idUsrc = $this->GetUser($user);		
		
		//récupère les posts d'un utilisateur
		$arrUd = $ud->findDocByUti($idUsrc);
		$date = new Zend_Date();
		foreach ($arrUd as $d) {
			//récupère le détail de l'url
			$urlDetails = $this->GetHtmlDetailUrl($d);
	        //enregistre le detail de l'url
			foreach ($urlDetails as $post) {
				//ne prend pas en compte l'utilisateur source
				if($post["a"]!=$user){
					//enregistre la relation entre utilisateur au niveau post
					$idUdst = $this->GetUser($post["a"]);		
					$uu->ajouter(array("uti_id_src"=>$idUsrc,"uti_id_dst"=>$idUdst));		
					$uu->edit($idUsrc, $idUdst, array("post"=>1));		
					//enregistre le post
					$this->SavePosts(array($post), $post["a"]);
				}
			}
	        
		}						
	}
	
	function GetHtmlDetailUrl($d){
		//vérifie si le detail est en cache
		$cD = "Flux_Delicious_SaveUserPostUser_".$this->user."_".$d["doc_id"]."details";
	    if($this->forceCalcul)$this->cache->remove($cD);
		if(!$urlDetails = $this->cache->load($cD)) {	
			//vérifie si les infos de l'url sont en cache
			$c = "Flux_Delicious_SaveUserPostUser_".$this->user."_".$d["doc_id"];
			if($this->forceCalcul)$this->cache->remove($c);
			if(!$urlInfo = $this->cache->load($c)) {	
				$urlInfo = $this->del->getUrlInfos($d["url"]);
				$this->cache->save($urlInfo,$c);
	        }
        	if($urlInfo[0]["total_posts"] > 100){
				//récupère les infos en parsant les pages html
				$urlDetails = $this->ParseHtmlDetailUrl($d["url"]);
			}else{					
				//récupère les infos à partir du flux
				$urlDetails = $this->del->getUrlDetails2($d["url"]);
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
			if($s->div->div[0]['title'])
				$dt = $s->div->div[0]['title']."";
			$a = $s->div->div[2]->a['title']."";
			$n = $s->div->div[1]."";
			$u = $url;
			$arrT = array();
			if($s->div->div[2]->div->ul){
				foreach($s->div->div[2]->div->ul->li as $t){
					$arrT[] = $t->a."";
				}
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
		$r = $d->findLastDoc($user);

		if($r){
			$delicious = new Zend_Service_Delicious($user, $pwd);
			$date = new Zend_Date($r[0]["maj"]);
			$posts = $delicious->getPosts(null, $date);
			$this->SavePosts($posts, $user);		
		}else{
			$this->SaveUserBase($user, $pwd);
		}		
	}
	
	function SavePosts($posts, $user){
		$idU = $this->GetUser($user);		
				
		//récupère ou enregistre le post
		$t = new Model_DbTable_Flux_Tag();
		$ut = new Model_DbTable_Flux_UtiTag();
		$d = new Model_DbTable_Flux_Doc();
		$ud = new Model_DbTable_Flux_UtiDoc();
		$td = new Model_DbTable_Flux_TagDoc();
		$date = new Zend_Date();
		foreach ($posts as $post) {
			//gestion du type d'objet post
			if(is_array($post)){
				//venant directement du json
				$pudDate = $post["dt"];					
				$idD = $d->ajouter(array("url"=>$post["u"],"titre"=>$post["d"],"note"=>$post["n"],"pubDate"=>$pudDate,"maj"=>$date->get("c")));
				$tags = $post["t"];
			}else{
				//venant d'un objet zend delicious
				$pudDate = $post->getDate()->toString("c");
				$idD = $d->ajouter(array("url"=>$post->getUrl(),"titre"=>$post->getTitle(),"note"=>$post->getNotes(),"pubDate"=>$pudDate,"maj"=>$date->get("c")));
				$tags = $post->getTags();				
			}
			$ud->ajouter(array("uti_id"=>$idU, "doc_id"=>$idD, "maj"=>$pudDate));
			foreach ($tags as $tag) {
				$idT = $t->ajouter(array("code"=>$tag));
				$ut->ajouter(array("uti_id"=>$idU, "tag_id"=>$idT));
				$td->ajouter(array("tag_id"=>$idU, "doc_id"=>$idT));
			}
		};
		
	}
}
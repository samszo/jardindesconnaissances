<?php
/**
 * Classe qui gère les flux Gdata
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Gdata extends Flux_Site{

	var $client;
	var $docs;		
	var $feed;
 	
	public function __construct($login=null, $pwd=null, $idBase=false)
    {
    	parent::__construct($idBase);
    	
    	$this->login = $login;
    	$this->pwd = $pwd;
    }
	
	function getSpreadsheets(){
		$c = str_replace("::", "_", __METHOD__)."_".md5($this->login); 
	   	$this->feed = $this->cache->load($c);
        if(!$this->feed){
			$service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
		    $this->client = Zend_Gdata_ClientLogin::getHttpClient($this->login, $this->pwd, $service);
		    $spreadsheetService = new Zend_Gdata_Spreadsheets($this->client);
			$this->feed = $spreadsheetService->getSpreadsheetFeed();
			$this->cache->save($this->feed, $c);
        }	
	}

	function getSpreadsheetsContents(){
		$c = str_replace("::", "_", __METHOD__)."_".md5($this->login); 
	   	$arr = $this->cache->load($c);
        if(!$arr){
			$this->getSpreadsheets();
			$i = 0;
			foreach ($this->feed->entry as $ss){
				if($i<10000){					
					$wss = $ss->getWorksheets();
					$ssName = $ss->getTitleValue();
					//TODO récupérer l'url du classeur
					$ssUrl = "";
					$arrWs = array();
					foreach ($wss as $ws){
						$wsName = $ws->getTitleValue();
						//TODO récupérer l'url de la feuille
						$wsUrl = "";
						$arrWs[] = array('titre'=>$wsName,'url'=>$wsUrl,'values'=>$ws->getContentsAsRows());		
					}
					$arr[] = array('titre'=>$ssName,'url'=>$ssUrl,'feuilles'=>$arrWs); 
				}
				$i++;
			}
			$this->cache->save($arr, $c);
        }	
		return $arr;
	}
	
	function saveSpreadsheetsTags($docTronc=-1){
		
		$arr = $this->getSpreadsheetsContents();
		if($arr){
			if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
			if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
			if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
			if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
			if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
			//TODO ajouter les db pour graine utigraine et grainedoc 
			
			$this->getUser(array("login"=>$this->login,"flux"=>"gdata"));
			$this->getGraine(array("titre"=>"Google data","class"=>"Flux_Gdata","url"=>"????"));
			//TODO ajouter la utigraine 
			
			$date = new Zend_Date();
			
			foreach ($arr as $nSS=>$vSS){
				//ajouter un document correspondant au classeur
				$idD = $this->dbD->ajouter(array("url"=>$vSS['url'],"titre"=>$vSS['titre'],"tronc"=>$docTronc,"pubDate"=>$date->get("c")));
				//TODO ajouter la grainedoc 
				
				$i = 0;
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
				//ajoute un lien entre l'utilisateur et le document avec un poids correspodant au nombre de tag
				$this->dbUD->ajouter(array("uti_id"=>$this->user, "doc_id"=>$idD,"poids"=>$i));							
			}
	
			
		}		
		
		
	}
	
	public function getPicassaAlbum(){
		//https://developers.google.com/picasa-web/docs/1.0/developers_guide_php#ListAlbumPhotos
		$service = Zend_Gdata_Photos::AUTH_SERVICE_NAME;
		$client = Zend_Gdata_ClientLogin::getHttpClient("thyp1415@gmail.com", "hypermedia", $service);
		$service = new Zend_Gdata_Photos($client);
		
		// update the second argument to be CompanyName-ProductName-Version
		$gp = new Zend_Gdata_Photos($client, "Google-DevelopersGuide-1.0");
		
		$query = $gp->newAlbumQuery();	
		$query->setUser("thyp1415");
		$query->setAlbumName("Trombinoscope");
				
		$albumFeed = $gp->getAlbumFeed($query);
		foreach ($albumFeed as $albumEntry) {
		    echo $albumEntry->title->text . "<br />\n";
		    $this->getPhotoInfo($albumEntry);
		}
		
	}

	public function getPhotoInfo($photoEntry){

		$camera = "";
		$contentUrl = "";
		$firstThumbnailUrl = "";
		
		$albumId = $photoEntry->getGphotoAlbumId()->getText();
		$photoId = $photoEntry->getGphotoId()->getText();
		
		if ($photoEntry->getExifTags() != null && 
		    $photoEntry->getExifTags()->getMake() != null &&
		    $photoEntry->getExifTags()->getModel() != null) {
		
		    $camera = $photoEntry->getExifTags()->getMake()->getText() . " " . 
		              $photoEntry->getExifTags()->getModel()->getText();
		}
		
		if ($photoEntry->getMediaGroup()->getContent() != null) {
		  $mediaContentArray = $photoEntry->getMediaGroup()->getContent();
		  $contentUrl = $mediaContentArray[0]->getUrl();
		}
		
		if ($photoEntry->getMediaGroup()->getThumbnail() != null) {
		  $mediaThumbnailArray = $photoEntry->getMediaGroup()->getThumbnail();
		  $firstThumbnailUrl = $mediaThumbnailArray[0]->getUrl();
		}
		
		echo "AlbumID: " . $albumId . "<br />\n";
		echo "PhotoID: " . $photoId . "<br />\n";
		echo "Camera: " . $camera . "<br />\n";
		echo "Content URL: " . $contentUrl . "<br />\n";
		echo "First Thumbnail: " . $firstThumbnailUrl . "<br />\n";
		
		echo "<br />\n"; 		
	}
	
	public function getGPlusAlbum($userId, $albumId, $authKey="", $save=true){
		
		if(!$this->dbU) $this->dbU = new Model_DbTable_Flux_Uti($this->db);
		if(!$this->dbD) $this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbT) $this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbUTD) $this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
		//url pour récupérer les photos d'un album au format json
		//merci à http://justinlee.sg/2012/03/27/getting-the-media-rss-feed-from-a-google-photo-gallery/
		$url = "http://photos.googleapis.com/data/feed/api/user/".$userId."/albumid/".$albumId."?alt=json";
		
		if($authKey)$url .= "&authkey=".$authKey;

		//récupère le flux
		$json = $this->getUrlBodyContent($url, false, false);
		$o = json_decode($json);

		if($save){
			//enregistre l'album
			$auteur = $o->feed->author[0];
			//attention problème avec le nom de variable avec un "$"
			foreach ($auteur->name as $v) {
				$login = $v."";
			}
			foreach ($auteur->uri as $v) {
				$uriUti = $v."";
			}
			foreach ($o->feed->title as $v) {
				$titre = $v."";
				break;
			}
			$utiId = $this->getUser(array("login"=>$login,"flux"=>$uriUti));
			$docId = $this->dbD->ajouter(array("url"=>$url, "titre"=>$titre,"data"=>$json));			
			$this->saveTag("Album Photo Google+", $docId, 1,0, $utiId);
			
			//enregistre les photos
			foreach ($o->feed->entry as $tof) {
				foreach ($tof->summary as $v) {
					$titre = $v."";
					break;
				}
				$url = $tof->content->src;
				$data = json_encode($tof);
				$idTof = $this->dbD->ajouter(array("url"=>$url, "titre"=>$titre, "data"=>$data, "tronc"=>$docId));			
				$this->sauveImage($idTof, $url, $titre, ROOT_PATH.'/data/google/img');
			}
		}
		return $json;
	}
		
}
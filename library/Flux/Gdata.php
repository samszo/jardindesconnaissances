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
		
		$service = Zend_Gdata_Photos::AUTH_SERVICE_NAME;
		$client = Zend_Gdata_ClientLogin::getHttpClient("thyp1314@gmail.com", "Hypermedia1314", $service);
		$service = new Zend_Gdata_Photos($client);
		 
		//$userFeed = $service->getUserFeed("thyp1314");
		
		$query = new Zend_Gdata_Photos_PhotoQuery();
		$query->setUser("thyp1314");
		$query->setAlbumId("1");
		$albumFeed = $service->getAlbumFeed($query);
		
		$query->setPhotoId("100");
		$photoFeed = $service->getPhotoFeed($query);

		$toto = "";
   		
	}
	
}
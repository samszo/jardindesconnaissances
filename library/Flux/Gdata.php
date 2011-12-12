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
 	
	public function __construct($login=null, $pwd=null)
    {
    	parent::__construct();
    	
    	$this->login = $login;
    	$this->pwd = $pwd;
    }
	
	function getSpreadsheets(){
	    $service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
	    $this->client = Zend_Gdata_ClientLogin::getHttpClient($this->login, $this->pwd, $service);
	    $spreadsheetService = new Zend_Gdata_Spreadsheets($this->client);
		$this->feed = $spreadsheetService->getSpreadsheetFeed();	
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
					$arrWs = array();
					foreach ($wss as $ws){
						$wsName = $ws->getTitleValue();
						$arrWs[] = array('titre'=>$wsName,'values'=>$ws->getContentsAsRows());		
					}
					$arr[] = array('titre'=>$ssName,'feuilles'=>$arrWs); 
				}
				$i++;
			}
			$this->cache->save($arr, $c);
        }	
		return $arr;
	}
	
	function saveSpreadsheetsTags(){
		
		$arr = $this->getSpreadsheetsContents();
		if($arr){
			$this->getUser(array("login"=>$this->login,"flux"=>"gdata"));
	
			if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag();
			//if(!$this->dbUT)$this->dbUT = new Model_DbTable_Flux_UtiTag();
			if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc();
			if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc();
			if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc();
			if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc();
			$date = new Zend_Date();
			
			foreach ($arr as $nSS=>$vSS){
				$arrSS = split(" ", $vSS['titre']);
				$idD = $this->dbD->ajouter(array("url"=>$arrSS[1],"titre"=>$arrSS[0],"pubDate"=>$date->get("c")));
				$i = 0;
				foreach ($vSS['feuilles'] as $nWS=>$vWS) {
					//ajouter un document correspondant à la feuille
					$idDF = $this->dbD->ajouter(array("url"=>$arrSS[1],"titre"=>$vWS['titre'],"tronc"=>$idD,"pubDate"=>$date->get("c")));
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
	
}
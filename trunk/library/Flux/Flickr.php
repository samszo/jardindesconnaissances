<?php
/**
 * Classe qui gère les flux Flickr
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Flickr extends Flux_Site{

	var $key;
	var $pwd;
	var $flkr;
	var $idGroupe;
	var $chemin;
	
	public function __construct($key, $pwd, $idBase=false)
    {
    	parent::__construct($idBase);
    	
		$this->key = $key;
    	$this->pwd = $pwd;
		$this->flkr = new Zend_Service_Flickr($this->key);    	
    }
    
    /**
     * Enregistre les photos avec geolocalisation pour un groupe ID
     *
     * @param  string $id the NSID
     * 
     */
    function saveGeoImageGroupe($id){
    	
		$this->idGroupe = $id;
    	
    	//récupèration de l'utilisateur
    	$this->getUser(array("login"=>"Flux_Flickr"));

    	//création du répertoire de stockage
    	$this->chemin = ROOT_PATH.'/data/frontieres/Flickr/'.$this->idGroupe.'/img';
		if(!is_dir($this->chemin)) @mkdir($this->chemin,0777,true);
    	
		for ($i = 1; $i <= 10; $i++) {
		    $results = $this->flkr->tagSearch('',array("group_id"=>$this->idGroupe,"page"=>$i,"per_page"=>"100", "bbox"=>"-180,-90,180,90","accuracy"=>"1","extras"=>"owner_name,geo,media,tags,machine_tags"));
		    	    
			foreach ($results as $result) {
			    //echo $result->title . '<br />';
			    $this->saveImage($result);
			}    				
		}
    } 

    /**
     * Enregistre les photos avec geolocalisation
     *
     * @param  array $data les informations de l'image
     * 
     */
    function saveImage($data){

    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	if(!$this->dbDT)$this->dbDT = new Model_DbTable_Flux_DocTypes($this->db);
    	if(!$this->dbUD)$this->dbUD = new Model_DbTable_flux_utidoc($this->db);
    	if(!$this->dbG)$this->dbG = new Model_DbTable_Flux_Geos($this->db);
    	if(!$this->dbGUD)$this->dbGUD = new Model_DbTable_Flux_UtiGeoDoc($this->db);
    	
    	//création des données du document
    	$url = $data->Medium->uri;
		$extension = pathinfo($url, PATHINFO_EXTENSION);
    	$type = $this->dbDT->getIdByExtension($extension);
    	$arrDoc['type']=$type;
		$path = $this->chemin."/". $data->id.".".$extension;
		$urlLocal = str_replace(ROOT_PATH, WEB_ROOT, $path);     	
    	$arrDoc['url']=$urlLocal;
    	$arrDoc['titre']=$data->title;
    	if($data->dateupload)$arrDoc['pudDate']=$data->dateupload;    	
    	$note['id'] = $data->id;
    	$note['owner'] = $data->owner;
    	$note['Original'] = $data->Original;
    	$note['Small'] = $data->Small;
    	$jsnote = json_encode($note);
    	$arrDoc['note']=$jsnote;
    	
    	//ajoute le document
    	$idDoc = $this->dbD->ajouter($arrDoc);

    	//création des liens avec le flux
    	$this->dbUD->ajouter(array("doc_id"=>$idDoc,"uti_id"=>$this->user));
    	
    	//récupèration de l'auteur
    	$idU = $this->getUser(array("login"=>$data->owner),true);    	
    	//création des liens avec le flux
    	$this->dbUD->ajouter(array("doc_id"=>$idDoc,"uti_id"=>$idU));
    	
    	//création des tags
	   	$d = new Zend_Date();
    	$arrTag = explode(" ", $data->tags);
    	foreach ($arrTag as $tag) {
    		$this->saveTag($tag, $idDoc, 1, $d->get("c"));
    	}
    	
    	//enregistre la géolocalisation
    	if($data->latitude){
    		$idGeo = $this->dbG->ajouter(array("lat"=>$data->latitude,"lng"=>$data->longitude,"zoom_max"=>$data->accuracy));
    		$this->dbGUD->ajouter(array("doc_id"=>$idDoc,"uti_id"=>$idU,"geo_id"=>$idGeo,"maj"=>$d->get("c")));	
    	}
    	
		if(!is_file($path)){
    		//enregistre l'image sur le disque local
			if(!$img = file_get_contents($url)) { 
			  echo 'pas de fichier : '.$url."<br/>";
			}else{
				if(!$f = fopen($path, 'w')) { 
				  echo 'Ouverture du fichier impossible '.$path."<br/>";
				}elseif (fwrite($f, $img) === FALSE) { 
				  echo 'Ecriture impossible '.$path."<br/>";
				}else{
					echo 'Image '.$data->title.' enregistrée : <a href="'.$urlLocal.'">local</a> -> <a href="'.$url.'">FlickR</a><br/>';
				} 				
			}				
		}    	
	} 
    
    /**
     * Returns Flickr photo geolocalisation by for the given photo ID
     *
     * @param  string $id the NSID
     * @return array of Zend_Service_Flickr_Image, details for the specified image
     * @throws Zend_Service_Exception
     */
    public function getImageGeo($id)
    {
        static $method = 'flickr.photos.geo.getLocation';

        if (empty($id)) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('You must supply a photo ID');
        }

        $options = array('api_key' => $this->flkr->apiKey, 'method' => $method, 'photo_id' => $id);

        $restClient = $this->flkr->getRestClient();
        $restClient->getHttpClient()->resetParameters();
        $response = $restClient->restGet('/services/rest/', $options);

        $xml = simplexml_load_string($response->getBody());
        $retval = array();
        if($xml->location[0]){
        	$retval['lat'] = $xml->location[0]->latitude;
        	$retval['lng'] = $xml->location[0]->longitude;
        	$retval['zoom'] = $xml->location[0]->accuracy;
        }
        if($xml->err){
        	if($xml->err['code']=="2"){
    	    	//si la photo n'a pas de géo on cherche celui de l'auteur
    	    	$retval = $this->getAuthorGeo($id);		
        	}else
	        	echo $id." : ".$xml->err[0]->msg.'<br/>';
        }

        return $retval;
    }

    /**
     * Returns Flickr author geolocalisation by for the given autor ID
     *
     * @param  string $id the NSID
     * @return array of Zend_Service_Flickr_Image, details for the specified image
     * @throws Zend_Service_Exception
     */
    public function getAuthorGeo($id)
    {
        static $method = 'flickr.photos.geo.getLocation';

        if (empty($id)) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('You must supply a photo ID');
        }

        $options = array('api_key' => $this->flkr->apiKey, 'method' => $method, 'photo_id' => $id);

        $restClient = $this->flkr->getRestClient();
        $restClient->getHttpClient()->resetParameters();
        $response = $restClient->restGet('/services/rest/', $options);

        $xml = simplexml_load_string($response->getBody());
        $retval = array();
        if($xml->location[0]){
        	$retval['lat'] = $xml->location[0]->latitude;
        	$retval['lng'] = $xml->location[0]->longitude;
        	$retval['zoom'] = $xml->location[0]->accuracy;
        }
        if($xml->err[0]){
        	echo $id." : ".$xml->err[0]->msg.'<br/>';
        }

        return $retval;
    }    
}
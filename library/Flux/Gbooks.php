<?php
/**
 * Classe qui gère les flux de google books
 *
 * @copyright  2016 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 * REFERENCES
 * 
 * THANKS
 */
class Flux_Gbooks extends Flux_Site{

	var $client;
	var $service;
	
    /**
     * Constructeur de la classe
     *
     * @param  string 	$idBase
     * @param  boolean 	$bTrace
     * 
     */
	public function __construct($idBase=false, $bTrace=false)
    {
    		parent::__construct($idBase, $bTrace);    	

	    	$this->client = new Google_Client();
		$this->client->setClientId(KEY_GOOGLE_CLIENT_ID);
		$this->client->setClientSecret(KEY_GOOGLE_CLIENT_SECRET);

		$this->service = new Google_Service_Books($this->client);
    }

    /**
     * Fonction pour rechercher les livres
     * 
     * @param  	string 		$searchTerm
     * @param	array		$options
     * 
     * @return	array
     * 
     */
    function findBooks($searchTerm, $options = array("maxResults"=>20)){

      		$results = $this->service->volumes->listVolumes($searchTerm,$options);
			$arr = array();
			foreach ($results as $item) {
			  $thumbnail = $item['volumeInfo']['imageLinks']['smallThumbnail'];
			  $access = $item->getAccessInfo();
			  $r = array('titre'=>$item['volumeInfo']['title'],'soustitre'=>$item['volumeInfo']['subtitle']
			  	,"idGoogle"=>$item->getId()
			  	,"date"=>$item['volumeInfo']['publishedDate']
			  	,"page"=>$item['volumeInfo']['pageCount']
			  	,"langue"=>$item['volumeInfo']['language']
			  	,"img"=>$thumbnail
			  	,'auteurs'=>implode(";",$item['volumeInfo']['authors'])
			  	,'categories'=>implode(";",$item['volumeInfo']['categories'])
			  	,'visible'=>$access->viewability
			  	,'access'=>$access->accessViewStatus
			  	,'liens'=>array(
			  		"infos"=>$item['volumeInfo']['infoLink']
			  		,"vue"=>$item['volumeInfo']['previewLink']
			  		,"json"=>$item->selfLink
			  		,"imgPetite"=>$item['volumeInfo']['imageLinks']['smallThumbnail']
			  		,"imgMedium"=>$item['volumeInfo']['imageLinks']['medium']
			  		,"imgGrande"=>$item['volumeInfo']['imageLinks']['large']
			  		)
			  	);
				$isbns = $item['volumeInfo']['industryIdentifiers'];
				if($isbns){
					foreach ($isbns as $is){
						$r[$is->type]=$is->identifier; 
					}				  			
				}
			  	$arr[] = $r;			  	
			}		
		return $arr;
      	    	
	}
    
    /**
     * Fonction pour récupérer les informations d'un bouquin
     * 
     * @param  	int 		$id
     * 
     * @return	array
     * 
     */
    function getBooks($id){
      	    	
	}	
}
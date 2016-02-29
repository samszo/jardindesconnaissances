<?php
/**
 * Classe qui gère les flux Rmn-GP
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 * THANKS
 * https://docs.art.rmngp.fr/
 * https://docs.art.rmngp.fr/console/
 */
class Flux_Rmngp extends Flux_Site{

	var $formatResponse = "json";
	var $apiUrl = 'https://api.art.rmngp.fr/v1/';
	var $rs;
	var $key;
	
    /**
     * Constructeur de la classe
     *
     * @param  string $idBase
     * 
     */
	public function __construct($idBase=false, $bTrace=false)
    {
    		parent::__construct($idBase, $bTrace);
    		$this->key = KEY_RMNGP;    

    }

    /**
     * Execute une requète sur l'API
     *
     * @param  string $query
     *
     * @return string
     */
    public function query($query)
    {
	    $url = $this->apiUrl.$query.'&api_key='.$this->key;
	    //echo $url;
		return $this->getUrlBodyContent($url,false);
    }
    
    /**
     * Recherche une autocomplétion
     *
     * @param  	string 	$q
     * @param  	string 	$lang
     * @param  	string 	$types
     * @param	int		$per 
     *
     * @return string
     */
    public function getAutocomplete($q, $lang="fr", $types="work,author,location,period,technique",$per=5)
    {    	
		return $this->query("autocomplete?q=".$q."&types=".urlencode($types)."&lang=".$lang."&per=".$per);
    }
        
    /**
     * Recherche une suggestion d'oeuvre
     *
     * @param  	string 	$id
     * @param	boolean	$slug 
     * @param  	int 		$page
     * @param  	int	 	$per_page
     *
     * @return string
     */
    public function getSuggestion($id, $slug=false, $page=1, $per_page=10)
    {    	
		$query = "works/suggested?iq=".$id."&page=".$page."&per_page=".$per_page;
		if($slug)$query .= "slug=".$slug;
		return $this->query($query);
    }
    
    
}
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
	    $url = $this->apiUrl.$query
	      	.'&api_key='.$this->key;
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
        
}
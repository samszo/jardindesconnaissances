<?php
/**
 * Flux_Okapi
 * Classe qui gère les flux du serveur Okapi
 *
 * REFERENCES
 * 
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\API
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Flux_Okapi extends Flux_Site{

	var $urlBase = "http://gapai.univ-paris8.fr:3010/";
	
	/**
	 * Constructeur de la classe
	 *
	 * @param  string $idBase
	 *
	 */
	public function __construct($idBase=false, $bTrace=false)
	{
		parent::__construct($idBase, $bTrace);
	
		//on récupère la racine des documents
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbM)$this->dbM = new Model_DbTable_Flux_Monade($this->db);
		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
	
	}
	
	/**
	 * Recherche un media sur le serveur
	*
	* @param  $q: query comprenant optionnellement les opérateurs AND, OR, NEAR,NOT ainsi que des patterns (expressions régulières) avec wilcard * ou ? 
	* @param  $lang: langage de recherche (ex: fr pour français)
	* @param  $limit : nombre max de résultats renvoyés
	* @param  $offset : offset dans la liste des résultats. Permet de gérer avec « limit » la fourniture de résultats par pages.
	* 	
	* @return array
	*/
	public function chercherMedia($q, $lang="fr", $limit=10, $offset=0)
	{
		$url = $this->urlBase."api/saphir/search_media?q=".$q;
		$json = $this->getUrlBodyContent($url,false,$this->bCache);
		
	    return $json;
	}

	public function ajouterMedia($Download_url,$Streaming_url,$Creator,$Title,$Description)
	{
	    $url = "http://gapai.univ-paris8.fr:3010/";
	    $url .= "api/saphir/add_media?";
	    $url .= "download_url=[".$Download_url."]&";
	    $url .= "streaming_url=[".$Streaming_url."]&";
	    $url .= "creator=[".$Creator."]&";
	    $url .= "title=[".$Title."]&";
	    $url .= "description=[".$Description."]&"; 
	
	
	    $ch = curl_init(); 
	    $timeout = 5; // set to zero for no timeout 
	    curl_setopt ($ch, CURLOPT_URL, $url); 
	    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
	    $file_contents = curl_exec($ch); 
	    curl_close($ch); 
	    echo  $file_contents;
	}

}

?>
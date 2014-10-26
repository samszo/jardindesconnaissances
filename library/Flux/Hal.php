<?php
/**
 * Classe qui gère les flux HAL
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Hal extends Flux_Site{

	var $urlRepo = "http://hal-univ-paris8.archives-ouvertes.fr/rss.php?dc=1";
	
	public function __construct($idBase=false)
    {
    	parent::__construct($idBase);
    	
    }
    
    /**
     * renvoie les publications d'un auteur
     *
     * @param  string $id l'identifiant de l'auteur
     * @return SimpleXMLElement la liste des publications
     * 
     * @throws Zend_Service_Exception
     */
    public function getAuthorPubli($id)
    {

        if (empty($id)) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Vous devez préciser l'identifiant de l'auteur");
        }

		$flux = $this->urlRepo.'&author='.$id;
        $c = str_replace("::", "_", __METHOD__)."_".md5($flux); 
	   	$canal = $this->cache->load($c);
        if(!$canal){
			$canal = simplexml_load_file($flux);
			$this->cache->save($canal, $c);
        }	
        return $canal;
    }    
    
    
    /**
     * enregistre les publications d'un auteur
     *
     * @param  string $id l'identifiant de l'auteur
     * @return array la liste des publications
     * 
     */
    public function saveHTMLAuthorPubli($id)
    {

		$canal = $this->getAuthorPubli($id);

		foreach ($canal->channel->item as $i) {			
	    	//récupère les infos dublin core
			$dc = $i->children('http://purl.org/dc/elements/1.1/');
			$html .= '<span property="dc:title">'.$i->title.'</span>';
			foreach ($dc->creator as $c) {
			    $html .= '<span property="dc:creator">'.$c.'</span>';
			}
			foreach ($dc->publisher as $p) {
			    $html .= '<span property="dc:publisher">'.$p.'</span>';
			}    	
			$mc = $dc->coverage;
			$mc = explode(";", $mc);
			foreach ($mc as $m) {
			    $html .= '<span property="dc:coverage">'.$m.'</span>';
			}
			$html .= '<span property="dc:subject">'.$dc->subject.'</span>';
			$html .= '<span property="dc:identifier">'.$dc->identifier.'</span>';
			$html .= '<span property="dc:date">'.$dc->date.'</span>';
			$html .= '<span property="dc:description">'.$dc->description.'</span>';
			$html .= '<span property="dc:type">'.$dc->type.'</span>';
			
			//récupère les infos media
			//$media = $i->children('http://search.yahoo.com/mrss/');
			    	
		}
        
        return $html;
    }    
}
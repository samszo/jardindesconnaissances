<?php
/**
 * Flux_Hal
 * 
 * Classe qui gère les flux HAL
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\API
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Hal extends Flux_Site{

	var $urlRepo = "http://hal-univ-paris8.archives-ouvertes.fr/rss.php?dc=1";
	var $urlStat = "https://hal-univ-paris8.archives-ouvertes.fr/stat/ajaxdata";
	
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
    
    function getStatAuteur(){
	    /* pour trouver les docs de paragraphe
	     * https://api.archives-ouvertes.fr/search/?wt=xml&q=structId_i:39850
	     */	
	    /* pour trouver les docs de CITU
	     * https://api.archives-ouvertes.fr/search/?wt=xml&q=rteamStructName_s:CITU
	     * avec les champs pertinents
	     * https://api.archives-ouvertes.fr/search/?wt=xml&q=rteamStructName_s:CITU&fl=authId_i,authFirstName_s,authLastName_s,*IdExt_id,docid,team_t,keyword_s,labStructName_s,structName_s,structType_s,thematique_s,title_s,*domainAllCodeLabel_fs,*_discipline_s,
	     * uniquement les document et la liste des auteurs
	     * https://api.archives-ouvertes.fr/search/?wt=xml&q=rteamStructName_s:CITU&fl=docid&facet=true&facet.field=authFullName_s&facet.mincount=1
	     */	    	
	}
	
    /**
     * récupère les information d'une thèse
     *
     * @param  string $id l'identifiant de la thède
     * @return array la liste des information
     * 
     */
	function getInfosThese($id){
		//https://api.archives-ouvertes.fr/search/?q=tel-01964613&wt=xml&fl=abstract_s,label_s,acm_s,audience_s,authFirstName_s,authFullName_s,authIdHal_i,authQuality_s,authorityInstitution_s,city_s,classification_s,committee_s,country_s,defenseDate_s,director_s,domain_s,files_s,fulltext_t,keyword_s,page_s,popularLevel_s,structAcronym_s,thesisSchool_s		
	}
  
	
	//récupère les photos
	//pdfimages -j /Users/samszo/Documents/veille/va_Sarr_Lamine.pdf /Users/samszo/Documents/veille/tofs/
	


}
<?php
/**
 * Classe qui gère les flux SKOS
 * 
 * @copyright  2016 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Skos extends Flux_Site{

	var $formatResponse = "json";	
	var $searchUrl = 'http://skos.um.es/sparql/index.php?';
	
    /**
     * Constructeur de la classe
     *
     * @param  string $idBase
     * 
     */
	public function __construct($idBase=false, $bTrace=false)
    {
    		parent::__construct($idBase, $bTrace);    	
    }

    /**
     * Execute une resuète sur databnf
     *
     * @param  string $query
     *
     * @return string
     */
    public function query($query)
    {
	    $url = $this->searchUrl.'query='.urlencode($query)
	      	.'&format='.$this->formatResponse;
		return $this->getUrlBodyContent($url,false);
    }
    
     /**
     * enregistre les concept skos dans SPIP
     * cf. http://skos.um.es/unescothes
     *
     * @param  string 	$url
     * @param  integer 	$gm
     * @param  integer 	$idParent
     * @param  integer 	$niv
     * @param  boolean 	$arbre
     *
     * @return string
     */
    public function sauveToSpip($url, $gm, $idParent=0, $niv=0, $arbre=false){
    		$this->trace("DEBUT ".__METHOD__." $url, $gm, $idParent, $niv");
    	
    		if(!$this->dbM)$this->dbM = new Model_DbTable_Spip_mots($this->db);
    		if(!$this->dbGM)$this->dbGM = new Model_DbTable_Spip_groupesxmots($this->db);
    		
    		if(!is_array($gm)){
    			$gm = $this->dbGM->findById_groupe($gm);
    		}
    		
		//récupérer les données de la page
		$json = $this->getUrlBodyContent($url);
		$objSkos = json_decode($json);
    		//$this->trace("objSkos ",$objSkos);
		foreach ($objSkos as $c => $skos) {
			foreach ($skos as $k => $v) {
				//traite le mot clef
				if($k=="http://www.w3.org/2004/02/skos/core#prefLabel"){
					$tm = "<multi>";
					foreach ($v as $l) {
						$tm .= "[".$l->lang."]".$l->value;
						$this->trace($l->value.",  ".$l->type.",  ".$l->lang);						
					}
					$tm .= "</multi>";
					$this->trace("Mot clef multilingue :".$tm);						
					//ajouter un mot clef dans spip
					$idM = $this->dbM->ajouter(array("titre"=>$tm,"descriptif"=>$c,"id_groupe"=>$gm["id_groupe"],"type"=>$gm["titre"]
					,"profondeur"=>$niv, "id_parent"=>$idParent, "texte"=>$json));
					$this->dbM->edit($idM,array("id_mot_racine"=>$idM));					
				}
				/*traite les enfants du mot clef
				if($k=="http://www.w3.org/2004/02/skos/core#hasTopConcept"){
					foreach ($v as $l) {
						$this->sauveToSpip($l->value, $gm, $idM, $niv+1);	
					}										
				}
				*/
				//traite les enfants du mot clef
				if($arbre && $k=="http://www.w3.org/2004/02/skos/core#narrower"){
					foreach ($v as $l) {
						$this->sauveToSpip($l->value."/json", $gm, $idM, $niv+1);	
					}										
				}								
				//traite les domaines du mot clef
				if($arbre && $niv==0 && $k=="http://purl.org/umu/uneskos#hasMicroThesaurus"){
					foreach ($v as $l) {
						$this->sauveToSpip($l->value."/json", $gm, $idM, $niv+1);	
					}										
				}				
				//traite les relations du mot clef
				if($arbre && $k=="http://www.w3.org/2004/02/skos/core#related"){
					foreach ($v as $l) {
						//$this->sauveToSpip($l->value."/json", $gm, $idM, $niv+1);	
					}										
				}
				//traite les sous sous domaine du mot clef
				if($arbre && $k=="http://purl.org/iso25964/skos-thes#subGroup"){
					foreach ($v as $l) {
						$this->sauveToSpip($l->value."/json", $gm, $idM, $niv+1);	
					}										
				}
				//traite les membre d'un groupe
				if($arbre && $k=="http://www.w3.org/2004/02/skos/core#member"){
					foreach ($v as $l) {
						$this->sauveToSpip($l->value."/json", $gm, $idM, $niv+1);	
					}										
				}
				
												
			}
		}
		//$pl = $objSkos["http://www.w3.org/2004/02/skos/core#prefLabel"];
	    	$this->trace("FIN ".__METHOD__);    
		
    }

     /**
     * recupère le concept skos à partir d'un label
     *
     * @param  string 	$label
     *
     * @return uri
     */
    function getUriByLabel($label){
    		$this->trace("DEBUT ".__METHOD__." $label");
    	
    		$query = 'SELECT * WHERE {?id skos:prefLabel "'.$label.'"@fr.}';
    		$reponse = $this->query($query);
    		$json = json_decode($reponse);
    		if($json){
    			if(count($json->results->bindings)) return $json->results->bindings[0]->id->value;
    			else return false;
		}else{
			throw new Exception($reponse);			
		}
    }
}
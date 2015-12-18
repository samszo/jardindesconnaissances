<?php
/**
 * Classe qui gère les flux du site Cairn
 * https://www.cairn.info/
 * @copyright  2015 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Cairn extends Flux_Site{

	var $formatResponse = "json";
	
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
     * enregistre les citations de la série 100 citations...
     * cf. https://www.cairn.info/les-100-citations-de-la-philosophie--9782130632993.htm
     *
     * @param  string 	$url
     * @param  string 	$idRubSpip
     *
     * @return string
     */
    public function sauve100Citations($url, $idRubSpip=false){
    		$spipArt = new Model_DbTable_Spip_Articles($this->db);
    		$spipAut = new Model_DbTable_Spip_auteurs($this->db);
    		$spipAutLien = new Model_DbTable_Spip_auteursliens($this->db);
    		$rs = $this->get100Citations($url);
    		foreach ($rs as $s) {
    			if($idRubSpip){
    				//ajoute l'article
    				$idArt = $spipArt->ajouter(array("titre"=>$s["cita"],"descriptif"=>$s["ref"],"statut"=>"publie","id_rubrique"=>$idRubSpip));
    				//ajoute l'auteur
    				$idAut = $spipAut->ajouter(array("nom"=>$s["auteur"],"bio"=>$s["date"],"statut"=>"1comite"));
    				//ajoute le lien entre auteur et article
    				$spipAutLien->ajouter(array("id_auteur"=>$idAut,"objet"=>"article","id_objet"=>$idArt));    				    				
    			}
    		}

    }

     /**
     * récupère les citations de la série 100 citations...
     * cf. https://www.cairn.info/les-100-citations-de-la-philosophie--9782130632993.htm
     *
     * @param  string $url
     *
     * @return array
     */
    public function get100Citations($url){
    		$rs = array();
    		$html = $this->getUrlBodyContent($url,false);
    		//supprime les références du coté
    		$code = '#\<!--BeginNoIndex\-->(.*)\<!--EndNoIndex\-->#sU';
        $html = preg_replace($code, "", $html);
    		//constuction du xml à requeter
		$dom = new Zend_Dom_Query($html);	    
		//récupère la liste des citations
		$result = $dom->queryXpath("//div/section/h1");
		foreach ($result as $cn) {
		    $cita = $cn->nodeValue;
		    $rs[] = array("cita"=>$cita,"auteur"=>array(),"date"=>array());				
			$this->trace($cita);
		}
		//récupère la liste des auteurs
		$result = $dom->queryXpath("//div/blockquote/p");
		$i=0;
		foreach ($result as $cn) {
			//$this->trace($cn->nodeValue);
	    		$arr = explode("[",$cn->nodeValue);
	    		$auteur = $arr[0];
		    $arr = explode("(",$cn->nodeValue);
		    	$date = substr($arr[1],0,-1);
	    		$rs[$i]["auteur"] = $auteur;				
	    		$rs[$i]["date"] = $date;				
	    		$this->trace($auteur. " -> ".$date);
			$i++;
		}
    		//récupère la liste des références bibliographique
		$result = $dom->queryXpath("//section/section/div/p");
		$i=0;
		foreach ($result as $cn) {
	    		$rs[$i]["ref"] = $cn->nodeValue;
	    		$rs[$i]["ref"]=str_replace("Ibid.,",$rs[$i-1]["ref"],$rs[$i]["ref"]);
	    		$this->trace($rs[$i]["ref"]);
			$i++;
		}		
    		return $rs;
    }
          
}
<?php
/**
* Class pour gérer les flux de données SPIP
*
*
* @author     Samuel Szoniecky <samuel.szoniecky@univ-paris8.fr>
* @license    CC0 1.0 Universal (CC0 1.0) Public Domain Dedication http://creativecommons.org/publicdomain/zero/1.0/ 
*/
class Flux_Spip extends Flux_Site{
        
	var $statueDocument = "publie";
	var $statueArticle = "publie";
	var $statueAuteur = "1comite";	
	var $extImg = array("jpg", "png", "gif", "jpeg");	
    function __construct($idBase=false,$idTrace=false){    	
    	    	
    		parent::__construct($idBase,$idTrace);
    	    
    		$this->arrRub = array("en"=>array("Liste"=>15,"Comite"=>16,"Catalog"=>18),"fr"=>array("Liste"=>27,"Comite"=>28,"Catalog"=>23));
		$this->arrMC = array("serie"=>1,"comite"=>2, "role"=>3);
    		$this->arrLangue = array("en","fr","ar");
    				
		$this->dbArt = new Model_DbTable_Spip_articles($this->db);
		$this->dbAutS = new Model_DbTable_Spip_auteurs($this->db);
		$this->dbR = new Model_DbTable_Spip_rubriques($this->db);
		$this->dbM = new Model_DbTable_Spip_mots($this->db);
		$this->dbAutL = new Model_DbTable_Spip_auteursliens($this->db);
		$this->dbML = new Model_DbTable_Spip_motsliens($this->db);
		$this->dbD = new Model_DbTable_Spip_documents($this->db);
		$this->dbDL = new Model_DbTable_Spip_documentsliens($this->db);
				
    }

	/**
	 * création automatique des rubrique et article d'auteur
	 * 
	 * @param	array	$arrAuteur
	 * 
	 * @return array
	 */
	public function creaAuteurFromFlux($arrAuteur) {
				
		$this->trace("DEBUT ".__METHOD__);		
		$this->trace("auteur ".$arrAuteur["id_auteur"]." ".$arrAuteur["prenom"]." ".$arrAuteur["nom"]);		
						
		//construction du numéro d'ordre
		$num = "";//$i."00. ";

		//vérifie si la rubrique de l'auteur existe
		$titreArtAuteur = $num.$arrAuteur["prenom"]." ".$arrAuteur["nom"];

		//création de l'auteur
		$idAutS = $this->dbAutS->ajouter(array("nom"=>$arrAuteur["prenom"]." ".$arrAuteur["nom"],"statut"=>$this->statueAuteur,"source"=>"flux"));			
		$this->dbSpip->ajouter(array("id_spip"=>$idAutS,"id_flux"=>$arrAuteur["id_auteur"],"obj_spip"=>"auteurs","obj_flux"=>"auteur"));
		
		//création des articles de l'auteur
		$arrIds = $this->creaArticleMultilingue(array("texte"=>"A compléter...", "titre"=>$titreArtAuteur
			,"statut"=>"prepa"), $arrAuteur["id_auteur"], $idAutS, "Liste");

		$this->trace("FIN ".__METHOD__);		
		
	}

	/**
	 * création d'un article multilingue
	 * 
	 * @param	array		$data
	 * @param	int			$idBdRef
	 * @param	int			$idAutS
	 * @param	string		$typeRub
	 * 
	 * @return array
	 */
	public function creaArticleMultilingue($data, $idBdRef, $idAutS, $typeRub=false) {
		
		$i=0;
		$ids = array();
		foreach ($this->arrLangue as $l) {
			if($typeRub) $data["id_rubrique"]=$this->arrRub[$l]["Liste"]; 
			
			if($i==0){
				$data["lang"]=$l;
				$idArtRef = $this->dbArt->ajouter($data);				
				$this->dbArt->edit($idArtRef, array("id_trad"=>$idArtRef));
				$idArt=$idArtRef;
			}else{
				$data["lang"]=$l;
				$data["id_trad"]=$idArtRef;
				$idArt = $this->dbArt->ajouter($data);								
			}
			$ids[]=$idArt;
			$this->dbSpip->ajouter(array("id_spip"=>$idArt,"id_flux"=>$idBdRef,"obj_spip"=>"articles","obj_flux"=>"auteur","lang"=>$l));
			//ajoute l'auteur
			$this->dbAutL->ajouter(array("id_auteur"=>$idAutS,"id_objet"=>$idArt,"objet"=>"article"));
			$i ++;
		}		
		return $ids;	
	}
	
	/**
	 * création d'un article 
	 * 
	 * @param	string		$lang
	 * @param	array		$infoLivre
	 * 
	 * @return int
	 */
	public function creaArticleFromFlux($lang, $infoLivre) {
				
		$this->trace("DEBUT ".__METHOD__);		
		$this->trace("livre ".$infoLivre["id_livre"]." - ".$infoLivre["titre_fr"]." - ".$infoLivre["titre_en"]);		
		
		$first = true;
		$chapo = "";

		$chapo = "";
		
		//récupère la date de parution
		$arrDate = explode(",", $infoLivre["date_parution"]);
		$dateParution=false;
		foreach ($arrDate as $d) {
			if($d!="0000-00-00" || $d==null)$dateParution=$d;
		}
		$dataArt = array("id_rubrique"=>$this->arrRub[$lang]["Catalog"]
			, "chapo"=>$chapo, "titre"=>$infoLivre["titre_".$lang], "soustitre"=>$infoLivre["soustitre_".$lang]
			, "descriptif"=>$infoLivre["contexte_".$lang], "ps"=>$infoLivre["bio_".$lang], "texte"=>$infoLivre["tdm_".$lang]
			, "lang"=>$lang, "langue_choisie"=>"oui"
			);
		if($dateParution){
			$dataArt["statut"] = "publie"; 	
			$dataArt["date"] = $dateParution; 	
		}else $dataArt["statut"] = "prepa";			
		if($infoLivre["contexte_".$lang]=="")$dataArt["statut"] = "prepa";	
		
		//ajout de l'article
		$idArt = $this->dbArt->ajouter($dataArt);				
			
		$this->dbSpip->ajouter(array("id_spip"=>$idArt,"id_flux"=>$infoLivre["id_livre"],"obj_spip"=>"articles","obj_flux"=>"livre","lang"=>$lang));
		
		//ajout des mots clefs
		if($infoLivre["series"]){
			$series = explode(":", $infoLivre["series"]);
			foreach ($series as $s) {
				$this->creaMCFromIste(explode(",", $s), "serie", "article", $idArt);
			}
		}
		if($infoLivre["comites"]){
			$comites = explode(":", $infoLivre["comites"]);
			foreach ($comites as $c) {
				$this->creaMCFromIste(explode(",", $c), "comite", "article", $idArt);
			}
		}
		
		$this->trace("FIN ".__METHOD__);		
		
		return $idArt;
	}

	/**
	 * création des mots clefs
	 * 
	 * @param	int		$id
	 * @param	string	$type
	 * 
	 * @return array
	 */
	public function creaMCFromFlux($mc, $type, $obj, $id) {

		if(!$mc[2] && !$mc[1]) return;
		if(!$mc[1])$mc[1]=$mc[2];
		if(!$mc[2])$mc[2]=$mc[1];
		
		$idMC = $this->dbM->ajouter(array("titre"=>"<multi>[fr]".$mc[2]."[en]".$mc[1]."</multi>","id_groupe"=>$this->arrMC[$type]));
		$this->dbSpip->ajouter(array("id_spip"=>$idMC,"id_flux"=>$mc[0],"obj_spip"=>"mots","obj_flux"=>$type));
		$this->dbML->ajouter(array("objet"=>$obj,"id_objet"=>$id,"id_mot"=>$idMC));
				
	}
	
	/**
	 * mis à jour des articles
	 * 
	 * @param	int		$idLivre
	 * 
	 * @return array
	 */
	public function modifArticleFromFlux($idLivre) {

		$this->trace("DEBUT ".__METHOD__);		
		$this->trace("id_livre = ".$idLivre);										
		//récupère les infos du livre
		$infoLivre = $this->dbLivre->getProductionLivre($idLivre);
		$this->trace("Infos livre ".$infoLivre["titre_fr"]." ".$infoLivre["titre_en"]);
		//récupère la date de parution
		$arrDate = explode(",", $infoLivre["date_parution"]);
		$dateParution=false;
		foreach ($arrDate as $d) {
			if($d!="0000-00-00" || $d!=null)$dateParution=$d;
		}
		//récupère l'identifiant de l'article
		$arrArt = $this->dbSpip->findArtSpip($idLivre,"livre","articles",$this->idBase);
		$this->trace("Infos Article ",$arrArt);
		foreach ($arrArt as $art) {
			$lang = $art["lang"];
			$dataArt = array("chapo"=>"", "titre"=>$infoLivre["titre_".$lang], "soustitre"=>$infoLivre["soustitre_".$lang]
			, "descriptif"=>$infoLivre["contexte_".$lang], "ps"=>$infoLivre["bio_".$lang], "texte"=>$infoLivre["tdm_".$lang]
			, "lang"=>$lang, "langue_choisie"=>"oui");
			if($dateParution){
				$dataArt["statut"] = "publie"; 	
				$dataArt["date"] = $dateParution; 	
			}else $dataArt["statut"] = "prepa";			
			$this->dbArt->edit($art["id_article"], $dataArt);
			$this->trace("article mis à jour ".$idArt. " avec ".$idLivre);							
		}
		$this->trace("FIN ".__METHOD__);		
	}
	
}
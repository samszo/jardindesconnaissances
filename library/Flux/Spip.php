<?php
/**
* Flux_Spip
* 
* Class pour gérer les flux de données SPIP
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
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
				$this->dbP = new Model_DbTable_Spip_meta($this->db);
				
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
	 * export to jdc
	 * 
	 * @param	int			$idDocParent
	 * @param	string	$where
	 * @param	string	$idBddSrc
	 * @param	string	$idBddDst
	 * 
	 */
	public function importToJDC($idDocParent, $where, $idBddSrc, $idBddDst) {
			$sSrc = new Flux_Site($idBddSrc);
			$sDst = new Flux_Site($idBddDst);
			$dbDocSrc = new Model_DbTable_Flux_Doc($sSrc->db);
			$dbDocDst = new Model_DbTable_Flux_Doc($sDst->db);
			$sql = "SELECT 
					a.id_article,
					a.titre aTitre,
					r.id_rubrique,
					r.titre rTitre,
					d.id_document,
					d.titre dTitre,
					d.fichier,
					d.id_type
			FROM
					spip_articles a
							INNER JOIN
					spip_rubriques r ON r.id_rubrique = a.id_rubrique
							INNER JOIN
					spip_documents_articles da ON da.id_article = a.id_article
							INNER JOIN
					spip_documents d ON d.id_document = da.id_document
			WHERE ".$where."
			GROUP BY a.id_article
			ORDER BY r.id_rubrique , a.id_article , d.id_document DESC";
			$rs = $dbDocSrc->exeQuery($sql);
			//récupèration des paramètres du site
			$urlSite = $this->getParam('adresse_site');
			$version = $this->getParam('version_installee');
			$nomSite = $this->getParam('nom_site');
			if($version<2){
				$urlRub = $urlSite.'/rubrique.php3?id_rubrique=';	
				$urlArt = $urlSite.'/article.php3?id_article=';	
			}
			//création des documents
			$idRub = 0;
			$idSite = $dbDocDst->ajouter(array('titre'=>$nomSite,'parent'=>$idDocParent,'url'=>$urlSite));
			$this->trace('Site créé : '.$nomSite);
			foreach ($rs as $d) {
				if($idRub!=$d['id_rubrique']){
					//création du doc parent
					$idRub = $dbDocDst->ajouter(array('titre'=>$d['rTitre'],'parent'=>$idSite,'url'=>$urlRub.$d['id_rubrique']));
					$this->trace('Rub créée : '.$d['rTitre']);
				}
				//création du doc article
				$idArt = $dbDocDst->ajouter(array('titre'=>$d['aTitre'],'parent'=>$idRub,'url'=>$urlArt.$d['id_article']));
				$this->trace('Art créé : '.$d['aTitre']);
				//création du document
				$dTitre = $d['dTitre'];
				if($dTitre=='')$dTitre=='doc '.$d['id_document'];
				$idDoc = $dbDocDst->ajouter(array('titre'=>$dTitre,'parent'=>$idArt,'url'=>$urlSite.'/'.$d['fichier'],'type'=>$d['id_type']));
				$this->trace('Doc créé : '.$urlSite.'/'.$d['fichier']);
			}

	}
		/**
	 * récupère un paramètre de spip
	 * 
	 * @param	array		$param
	 * 
	 * @return string
	 */
	public function getParam($param) {
		$rs = $this->dbP->findByNom($param);
		if($rs)return $rs['valeur'];
		else false;	
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
	 * @param	string	$obj
	 * @param	int		$id
	 * 
	 * @return array
	 */
	public function creaMCFromFlux($mc, $type, $obj=false, $id=false) {

		if(!$mc[2] && !$mc[1]) return;
		if(!$mc[1])$mc[1]=$mc[2];
		if(!$mc[2])$mc[2]=$mc[1];
		
		$idMC = $this->dbM->ajouter(array("titre"=>"<multi>[fr]".$mc[2]."[en]".$mc[1]."</multi>","id_groupe"=>$this->arrMC[$type]));
		if($mc[0])$this->dbSpip->ajouter(array("id_spip"=>$idMC,"id_flux"=>$mc[0],"obj_spip"=>"mots","obj_flux"=>$type));
		if($obj)$this->dbML->ajouter(array("objet"=>$obj,"id_objet"=>$id,"id_mot"=>$idMC));
				
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
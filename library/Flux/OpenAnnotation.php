<?php
/**
 * Classe qui gère les flux Open Annotation
 *
 * @copyright  2016 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 * REFERENCES
 * http://www.openannotation.org/spec/core/index.html
 * 
 * THANKS
 */
class Flux_OpenAnnotation extends Flux_Site{

	var $idDocRoot;
	var $idMonade;
	var $livres;
	
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
     * importe les référence dans la base
     *
     * @param  string $url
     *
     */
    public function importRef($url)
    {
		//création des objets
    		$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		
    		//on ajoute le document csv
    		$urlCSV = WEB_ROOT."/data/w3c/annotation/importTagAnnotation.csv";
		$idDoc = $this->dbD->ajouter(array("titre"=>"Open Annotation Data Model"
			,"url"=>$urlCSV, "parent"=>$this->idDocRoot, "tronc"=>"crible"));    		
		/*
		$idTag = $g->dbT->ajouter(array("code"=>$axe["axis"]));
		$idAct = $g->dbA->ajouter(array("code"=>$acti));
		$g->dbS->ajouter(array("id_flux"=>$idTag,"id_spip"=>$axe["idSpip"],"obj_flux"=>"tag","obj_spip"=>"mots"));	
		//enregistre le rapport entre la question, l'utilisateur et l'action
		$idRap = $g->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
			,"src_id"=>$this->idRapQ,"src_obj"=>"rapport"
			,"pre_id"=>$this->idUti,"pre_obj"=>"uti"
			,"dst_id"=>$idAct,"dst_obj"=>"acti"
			));
		*/	
    		//on ajoute le tag global
    		$idTagG = $s->dbT->ajouter(array("code"=>"Cribles biolographes"));
    		
    		//on ajoute les tags génériques
    		$idTagOA = $s->dbT->ajouter(array("code"=>"Open Annotation Data Model","parent"=>$idTagG)); 	    				    		
		
    		//chargement des csv
    		$csv = $s->csvToArray($urlCSV,0,",");
    		$arrNs = array();
    		$arrTags = array();
    		$nbLigne = count($csv);
    		for ($i = 1; $i < $nbLigne; $i++) {
    			$l = $csv[$i];
    			//calcule le parent
    			$target = 0;
    			switch ($l[5]) {
    				case 0:
	    				$idTagP = $idTagOA;
	    				break;
    				case "ns":
	    				$idTagP = $arrNs[$l[1]];
	    				break;
    				default:
    					$arrP = explode("-",$l[5]);
    					if($arrP[1])$target = $arrTags[$arrP[1]];
    					$idTagP = $arrTags[$arrP[0]];
    				break;
    			}
    			//ajoute le tag
			$idT = $s->dbT->ajouter(array("ns"=>$l[1],"code"=>$l[2],"type"=>$l[3],"desc"=>$l[4],"uri"=>$l[6],"parent"=>$idTagP)); 
			$arrTags[$i]=$idT;
			if($target){
				//enregistre la relation
				$s->dbTT->ajouter(array("tag_id_src"=>$idTagP,"tag_id_dst"=>$target,"tag_id_rel"=>$idT));
    				$s->trace($i." enregistre la relation : ".$idTagP." = ".$idT." =>".$target);	    						
			}
    			//enregistre le lien avec l'existence racine
    			$s->dbETD->ajouter(array("exi_id"=>$idERacine,"tag_id"=>$idT,"doc_id"=>$idDoc));
    			$s->trace($i." référence enregistrée : ".$l[2]." = ".$idT);	    						
    			
    		}    
    }
     /**
     * Récupère les informations d'un livre à partir du code barre
     *
     * @param  string $cb
     *
     * @return array
     */
    public function getLivreByCodeBarre($cb)
    {
		if(!$this->dbD) $this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	
    }
        
    /**
     * Enregistre les informations contenu dans une page Web
     *
     * @param  string $idLivre
     *
     * @return array
     */
    public function setInfoPageLivre($idLivre)
    {
   
    		//execute la recherche
		$searchUrl = "http://catalogue.bu.univ-paris8.fr/cgi-bin/koha/opac-detail.pl?biblionumber=".$idLivre;
    		$html = $this->getUrlBodyContent($searchUrl,false,$this->bCache);
    		//echo $html;
		$dom = new Zend_Dom_Query($html);	    
		//récupère la couverture
		$xPath = '//*[@id="bookcover"]/a/img';
		$results = $dom->queryXpath($xPath);
		foreach ($results as $result) {
			//récupère l'identifiant du livre
			$this->livres[$idLivre]["couverture"]=$result->getAttribute('src');
		}	    
		//récupère les propriétés via zotero
		$xPath = '//*[@id="catalogue_detail_biblio"]/span[@class="Z3988"]';
		$results = $dom->queryXpath($xPath);
		foreach ($results as $result) {
			//récupère le nom de la propriété
			$prop = $result->getAttribute('title');
			//parse les propriétés
			//ctx_ver=Z39.88-2004&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Abook&amp;rft.genre=book&amp;rft.title=Mille+plateaux&amp;rft.btitle=Mille+plateaux&amp;rft.isbn=2-7073-0307-0&amp;rft.issn=&amp;rft.aulast=Deleuze&amp;rft.aufirst=Gilles&amp;rft.au=Gilles+Deleuze&amp;rft.pub=les+Ed.+de+Minuit&amp;rft.date=impr.+1980&amp;rft.pages=
			$arrProp = explode("&",$prop);
			foreach ($arrProp as $p) {
				$arrVal = explode("=",$p);
				//choix des propriétés à récupérer
				$this->livres[$idLivre][substr($arrVal[0],4)]=$arrVal[1];				
			}			
		}	    
		//récupère les propriétés
		$xPath = '//*[@id="catalogue_detail_biblio"]/span[@class="results_summary"]';
		$results = $dom->queryXpath($xPath);
		foreach ($results as $result) {
			//récupère le nom de la propriété
			$prop = $result->firstChild->nodeValue;
			//parse les propriétés
			if(substr($prop,0,5)=="Sujet"){
				if(!isset($this->livres[$idLivre]["sujets"]))$this->livres[$idLivre]["sujets"]=array();
				$s = explode(" - ",substr($prop,0,-2));
				$t = $result->textContent;
				$vals = explode(" | ",substr($t,strpos($t,":")+2));
				foreach ($vals as $v){
					if(!isset($this->livres[$idLivre]["sujets"][$s[1]]))$this->livres[$idLivre]["sujets"][$s[1]]=array();
					$this->livres[$idLivre]["sujets"][$s[1]][]=$v;					
				}
			}
		}			
		//récupère les disponibilités
		$xPath = '//*[@id="bibliodescriptions"]/div/table/tbody/tr';
		$results = $dom->queryXpath($xPath);
		$this->livres[$idLivre]["dispos"]=array();
		$dispo = array("Type de document","Emplacement actuel","Collection","Cote","Situation","Date de retour");	
		foreach ($results as $result) {
			//récupère les valeur du tableau
			$i=0;
			$arrD = array();
			foreach($result->childNodes as $cn){
				if ($cn->nodeType == XML_ELEMENT_NODE) {
					$nv = trim(str_replace("\n","",$cn->nodeValue));
					$arrD[$dispo[$i]]=trim(str_replace("(Parcourir l'étagère)","",$nv));
					$i++;
				}
			}
			$this->livres[$idLivre]["dispos"][]=$arrD;			
		}			
		
    		return $this->livres[$idLivre];
    }
  
    /**
     * Enregistre dans la base de données les informations d'un livre
     *
     * @param  array $rsLivre
     *
     * @return array
     */
	public function saveInfoLivre($rsLivre)
	{
		if(!$this->dbT) $this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbE) $this->dbE = new Model_DbTable_Flux_Exi($this->db);
		if(!$this->dbD) $this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbR) $this->dbR = new Model_DbTable_Flux_Rapport($this->db);
		
		//récupère les références
		$idTagProp = $this->dbT->ajouter(array("code"=>"Propriétés des livres"));		
		$idExi = $this->dbE->ajouter(array("nom"=>"Algo Bup8"));		
		
		//enregistre uniquement les informations nécessaires
		foreach ($this->propLivres as $p) {
			//enregistre le tag
			$idTag = $this->dbT->ajouter(array("code"=>$p,"parent"=>$idTagProp));
			switch ($p) {
				case "sujets":
					foreach ($rsLivre[$p] as $k => $s) {
						$idTagK = $this->dbT->ajouter(array("code"=>$k,"parent"=>$idTag));
						foreach ($s as $val) {
							//enregistre le rapport
							$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade
								,"src_id"=>$idTagK,"src_obj"=>"tag"
								,"pre_id"=>$idExi,"pre_obj"=>"exi"
								,"dst_id"=>$rsLivre["idDoc"],"dst_obj"=>"doc"
								,"valeur"=>$val					
								));
						}
						
					}
					break;
				case "dispos":
					foreach ($rsLivre[$p] as $d) {
						foreach ($d as $k=>$val) {
							$idTagK = $this->dbT->ajouter(array("code"=>$k,"parent"=>$idTag));							
							//enregistre le rapport
							$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade
								,"src_id"=>$idTagK,"src_obj"=>"tag"
								,"pre_id"=>$idExi,"pre_obj"=>"exi"
								,"dst_id"=>$rsLivre["idDoc"],"dst_obj"=>"doc"
								,"valeur"=>$val					
								));
						}
						
					}
					break;
				default:
					//enregistre le rapport
					$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade
						,"src_id"=>$idTag,"src_obj"=>"tag"
						,"pre_id"=>$idExi,"pre_obj"=>"exi"
						,"dst_id"=>$rsLivre["idDoc"],"dst_obj"=>"doc"
						,"valeur"=>$rsLivre[$p]						
						));					
					break;
			}
		}
				
	}
    
    
    /**
     * Enregistre les livres contenus dans une liste de la BU
     *
     * @param  int 		$idListe
     *
     * @return array
     */
    public function setListe($idListe)
    {
    		//récupère la liste avec le code barre
		$searchUrl = "http://catalogue.bu.univ-paris8.fr/cgi-bin/koha/opac-downloadshelf.pl";
    		$csv = $this->getUrlBodyContent($searchUrl,array("format"=>"6","shelfid"=>$idListe,"save"=>"Valider"),$this->bCache,Zend_Http_Client::POST);
		$this->livres=array();
    		$arrBook= $this->csvStringToArray($csv,",",true);

    		//récupère la liste avec l'identifiant du livre
		$searchUrl = "http://catalogue.bu.univ-paris8.fr/cgi-bin/koha/opac-downloadshelf.pl";
    		$bt = $this->getUrlBodyContent($searchUrl,array("format"=>"bibtex","shelfid"=>$idListe,"save"=>"Valider"),$this->bCache,Zend_Http_Client::POST);
    		$bt = new BibTeX_Parser(null,$bt);
    		//met à jour le numéro de bouquin
    		for ($i = 0; $i < count($bt->items["raw"]); $i++) {
    			//@book{361039,
    			$r = explode(",",substr($bt->items["raw"][$i], 6));
    			$idBU = $r[0]; 
    			$arrBook[$i]["idBU"]=$idBU;
    			$this->livres[$idBU]=$arrBook[$i];
    		}
    		
    		/*récupère la liste avec les mots clefs
		pas vraiment signifiant
		$searchUrl = "http://catalogue.bu.univ-paris8.fr/cgi-bin/koha/opac-downloadshelf.pl";
    		$bt = $this->getUrlBodyContent($searchUrl,array("format"=>"ris","shelfid"=>$idListe,"save"=>"Valider"),true,Zend_Http_Client::POST);
    		$bt = new BibTeX_Parser(null,$bt);
    		*/
    		
    		//enregistre la liste
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);	    	
	    	$idDocListe = $this->dbD->ajouter(array("titre"=>"Liste ".$idListe,"parent"=>$this->idDocRoot,"data"=>json_encode($this->livres)));
		foreach ($this->livres as $id=>$b) {
			//récupère les informations du livre
			$book = $this->setInfoPageLivre($b["idBU"]);
		    //complète les informations avec les linked data
			//enregistre le livre
		    $book["idDoc"] =  $this->dbD->ajouter(array("titre"=>$book["Titre"],"data"=>json_encode($book),"parent"=>$idDocListe));
		    	$this->saveInfoLivre($book);	    
		}
		//mise à des data
	    	$this->dbD->edit($idDocListe, array("data"=>json_encode($this->livres)));
		
    		return $this->livres;
    }    
}
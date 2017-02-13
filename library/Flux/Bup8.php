<?php
/**
 * Classe qui gère les flux de la BU de Paris 8
 *
 * @copyright  2016 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 * REFERENCES
 * http://catalogue.bu.univ-paris8.fr/cgi-bin/koha/opac-main.pl
 * 
 * THANKS
 */
class Flux_Bup8 extends Flux_Site{

	var $formatResponse = "json";
	var $searchUrl = '';
	var $rs;
	var $doublons;
	var $idDocRoot;
	var $idMonade;
	var $livres;
	var $propLivres = array("isbn","code barres","idBU","couverture","Localisation","cote","sujets","dispos");
	
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
					if(isset($rsLivre[$p])){
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
    		$this->initDbTables();
    	 	$idAct = $this->dbA->ajouter(array("code"=>__METHOD__));
    	 
    		//récupère la liste avec le code barre
		$searchUrl = "http://catalogue.bu.univ-paris8.fr/cgi-bin/koha/opac-downloadshelf.pl";
    		$csv = $this->getUrlBodyContent($searchUrl,array("format"=>"6","shelfnumber"=>$idListe,"save"=>"Valider"),$this->bCache,Zend_Http_Client::POST);
		$this->livres=array();
    		$arrBook= $this->csvStringToArray($csv,",",true);
		$this->trace($csv,$arrBook);
		
    		//récupère la liste avec l'identifiant du livre
		$searchUrl = "http://catalogue.bu.univ-paris8.fr/cgi-bin/koha/opac-downloadshelf.pl";
    		$bt = $this->getUrlBodyContent($searchUrl,array("format"=>"bibtex","shelfnumber"=>$idListe,"save"=>"Valider"),$this->bCache,Zend_Http_Client::POST);
    		$bt = new BibTeX_Parser(null,$bt);
    		//met à jour le numéro de bouquin
    		for ($i = 0; $i < count($bt->items["raw"]); $i++) {
    			//@book{361039,
    			$r = explode(",",substr($bt->items["raw"][$i], 6));
    			$idBU = $r[0]; 
    			$arrBook[$i]["idBU"]=$idBU;
    			$this->livres[$idBU]=$arrBook[$i];
    		}
    		
    		//récupère le titre de la liste
    		$searchUrl = "http://catalogue.bu.univ-paris8.fr/cgi-bin/koha/opac-shelves.pl?rss=1&op=view&shelfnumber=".$idListe;
    		$xmlDoc = new DOMDocument();
    		$xmlDoc->load($searchUrl);    		
    		//get elements from "<channel>"
    		$channel=$xmlDoc->getElementsByTagName('channel')->item(0);
    		$listeTitre = $channel->getElementsByTagName('title')->item(0)->childNodes->item(0)->nodeValue;
    		
    		/*récupère la liste avec les mots clefs
		pas vraiment signifiant
		$searchUrl = "http://catalogue.bu.univ-paris8.fr/cgi-bin/koha/opac-downloadshelf.pl";
    		$bt = $this->getUrlBodyContent($searchUrl,array("format"=>"ris","shelfnumber"=>$idListe,"save"=>"Valider"),true,Zend_Http_Client::POST);
    		$bt = new BibTeX_Parser(null,$bt);
    		*/
    		
    		//enregistre la liste
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);	
		if(!$this->dbR) $this->dbR = new Model_DbTable_Flux_Rapport($this->db);		
		if(!$this->dbT) $this->dbT = new Model_DbTable_Flux_Tag($this->db);
		$idTag = $this->dbT->ajouter(array("code"=>"liste"));
		$url = "http://catalogue.bu.univ-paris8.fr/cgi-bin/koha/opac-shelves.pl?op=view&shelfnumber=".$idListe;
		$idDocListe = $this->dbD->ajouter(array("titre"=>$listeTitre,"url"=>$url,"tronc"=>$idListe,"parent"=>$this->idDocRoot,"data"=>json_encode($this->livres)));
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade
				,"src_id"=>$idDocListe,"src_obj"=>"doc"
				,"pre_id"=>$idAct,"pre_obj"=>"acti"
				,"dst_id"=>$idTag,"dst_obj"=>"tag"
		));
		foreach ($this->livres as $id=>$b) {
			//récupère les informations du livre
			$book = $this->setInfoPageLivre($b["idBU"]);
		    //complète les informations avec les linked data
			//enregistre le livre
			$url = "http://catalogue.bu.univ-paris8.fr/cgi-bin/koha/opac-detail.pl?biblionumber=".$b["idBU"];
		    $book["idDoc"] =  $this->dbD->ajouter(array("tronc"=>$b["idBU"],"url"=>$url,"titre"=>$book["Titre"],"parent"=>$this->idDocRoot,"data"=>json_encode($book)));
		    //enregistre le lien avec la liste
		    $this->dbR->ajouter(array("monade_id"=>$this->idMonade
		    		,"src_id"=>$idDocListe,"src_obj"=>"doc"
		    		,"pre_id"=>$idRap,"pre_obj"=>"rapport"
		    		,"dst_id"=>$book["idDoc"],"dst_obj"=>"doc"
		    ));		    
		    	$this->saveInfoLivre($book);	    
		}
		//mise à des data
	    //	$this->dbD->edit($idDocListe, array("data"=>json_encode($this->livres)));
	    //	echo json_last_error_msg();
    		return $this->livres;
    }   
    
    /**
     * Récupère les livres contenus dans une liste de la BU
     *
     * @param  int 		$idListe
     *
     * @return array
     */
    public function getListeLivre($idListe)
    {
	    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
	    	/*
	    	 * SELECT 
			    d.doc_id, d.titre, d.tronc, d.url, dB.doc_id bIdDoc, dB.url bUrl, dB.titre bTitre, dB.tronc bTron, dB.data bData
			FROM
			    flux_doc d
			        INNER JOIN
			    flux_rapport r ON r.src_id = d.doc_id
			        AND r.src_obj = 'doc'
			        AND r.dst_obj = 'tag'
			        INNER JOIN
			    flux_tag t ON t.code = 'liste' AND t.tag_id = r.dst_id
			        INNER JOIN
			    flux_rapport rB ON rB.pre_id = r.rapport_id
			        AND rB.pre_obj = 'rapport'
			        INNER JOIN
			    flux_doc dB ON dB.doc_id = rB.dst_id;
	    	 */
	    	$query = $this->dbD->select()
		    	->from( array("d" => "flux_doc"), array("recid"=>"doc_id", "doc_id","titre","tronc", "url"))
		    	->where( "d.tronc = ?", $idListe)
		    	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
		    	->joinInner(array('r' => 'flux_rapport'),'r.src_id = d.doc_id AND r.src_obj="doc" AND r.dst_obj = "tag"',array())
		    	->joinInner(array('t' => 'flux_tag'),'t.code = "liste" AND t.tag_id = r.dst_id',array())
		    	->joinInner(array('rB' => 'flux_rapport'),'r.rapport_id = rB.pre_id AND rB.pre_obj = "rapport"',array())
		    	->joinInner(array('dB' => 'flux_doc'),'dB.doc_id = rB.dst_id',array("bIdDoc"=>"doc_id" , "bUrl"=>"url" , "bTitre"=>"titre" , "bTronc"=>"tronc" , "bData"=>"data" ));
		$liste = $this->dbD->fetchAll($query)->toArray();
	    	
	    	$json = '{"titre":"'.$liste[0]['titre'].'","idDoc":'.$liste[0]['doc_id'].',"idListe":'.$idListe.',"url":"'.$liste[0]['url'].'", "livres":[';
	    	foreach ($liste as $b) {
	    		$json .= '{"titre":"'.$b['bTitre'].'","idDoc":'.$b['bIdDoc'].',"url":"'.$b['bUrl'].'","idBU":'.$b['bTronc'].',"data":'.$b['bData'].'},';
	    	}
	    	$json = substr($json, 0, -1).']}';
	    	return $json;    	 
    }
    
    /**
     * Récupère les listes de la BU enregistrées dans la BDD
     *
     * 
     *
     * @return array
     */
    public function getListe($idListe)
    {
	    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
	    	/*
	    	 *     SELECT
	    d.doc_id, d.titre, d.tronc, d.url, count(distinct rB.rapport_id) nbLivre
	    FROM
	    flux_doc d
	    INNER JOIN
	    flux_rapport r ON r.src_id = d.doc_id
	    AND r.src_obj = 'doc'
	    		AND r.dst_obj = 'tag'
	    				INNER JOIN
	    				flux_tag t ON t.code = 'liste' AND t.tag_id = r.dst_id
	    				INNER JOIN
	    				flux_rapport rB ON rB.pre_id = r.rapport_id
	    				AND rB.pre_obj = 'rapport'
	    				group by d.doc_id;
	
	    	 */
	    	$query = $this->dbD->select()
	    	->from( array("d" => "flux_doc"), array("recid"=>"doc_id", "doc_id","titre","tronc", "url"))
	    	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
	    	->joinInner(array('r' => 'flux_rapport'),'r.src_id = d.doc_id AND r.src_obj="doc" AND r.dst_obj = "tag"',array())
	    	->joinInner(array('t' => 'flux_tag'),'t.code = "liste" AND t.tag_id = r.dst_id',array())
	    	->joinInner(array('rB' => 'flux_rapport'),'r.rapport_id = rB.pre_id AND rB.pre_obj = "rapport"',array("NbLivre"=>"COUNT(DISTINCT rB.rapport_id)"))
	    	->group("doc_id");
	    	$liste = $this->dbD->fetchAll($query)->toArray();
	    
	    	return $liste;
    }
    
    
}
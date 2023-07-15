<?php
/**
 * Flux_Eu
 * Classe qui gère les flux des sites de l'Union Européenne
 * http://europa.eu/
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Scraping
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Flux_Eu extends Flux_Site{

	var $rs;
	var $idTagLangue;
	var $idDocEu;
	var $langues = array(
			'English' => 'en',
			'French' => 'fr',
			'German' => 'de',
			'Dutch' => 'nl',
			'Italian' => 'it',
			'Spanish' => 'es',
			'Catalan' => 'ca',
			'Portuguese' => 'pt',
			'Romanian' => 'ro',
			'Danish' => 'da',
			'Norwegian' => 'no',
			'Swedish' => 'sv',
			'Greek' => 'el',
			'Finnish' => 'fi',
			'Hungarian' => 'hu',
			'Turkish' => 'tr',
			'Estonian' => 'et',
			'Lithuanian' => 'lt',
			'Slovenian' => 'sl',
			'Polish' => 'pl',
			'Russian' => 'ru',
			'Ukrainian' => 'uk',
			'Serbian' => 'sr',
			'Icelandic' => 'is',
			'Euskara' => 'eu',
			'Farsi' => 'fa',
			'Persian-Farsi' => 'per',
			'Arabic' => 'ar',
			'Afrikaans' => 'af',
			'Chinese' => 'zh',
			'Korean' => 'ko'
		);
    /**
     * Constructeur de la classe
     *
     * @param  string $idBase
     * 
     */
	public function __construct($idBase=false, $bTrace=false, $bCache=true)
    {
    		parent::__construct($idBase, $bTrace);    	
			
			$this->bCache = $bCache;

    		//on récupère la racine des documents
    		$this->initDbTables();
    		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
    		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
    		$this->idTagRoot = $this->dbT->ajouter(array("code"=>__CLASS__));
			
			
	    	$this->trace("//récupère les mots clefs");
	    	$this->idTagCom = $this->dbT->ajouter(array("code"=>"commission", "parent"=>$this->idTagRoot));
	    	$this->idTagRoleCom = $this->dbT->ajouter(array("code"=>"rôle de la commission", "parent"=>$this->idTagRoot));
	    	$this->idTagRoleComFond = $this->dbT->ajouter(array("code"=>"au fond", "parent"=>$this->idTagRoleCom));
	    	$this->idTagRoleComAvis = $this->dbT->ajouter(array("code"=>"pour avis", "parent"=>$this->idTagRoleCom));
	    	$this->idTagRoleRap = $this->dbT->ajouter(array("code"=>"rôle du rapporteur", "parent"=>$this->idTagRoot));
	    	$this->idTagRap = $this->dbT->ajouter(array("code"=>"rapporteur","parent"=>$this->idTagRoleRap));
	    	$this->idTagRapFic = $this->dbT->ajouter(array("code"=>"fictif","parent"=>$this->idTagRoleRap));
	    	$this->idTagRapAvis = $this->dbT->ajouter(array("code"=>"avis","parent"=>$this->idTagRoleRap));
	    	$this->idTagActi = $this->dbT->ajouter(array("code"=>"activité", "parent"=>$this->idTagRoot));
	    	$this->idTagActiNomi = $this->dbT->ajouter(array("code"=>"nomination","parent"=>$this->idTagActi));
	    	$this->idTagLang = $this->dbT->ajouter(array("code"=>"langue", "parent"=>$this->idTagRoot));
	    	$this->idTagVote = $this->dbT->ajouter(array("code"=>"vote", "parent"=>$this->idTagRoot));
	    	$this->idTagVoteAbs = $this->dbT->ajouter(array("code"=>"abstention","parent"=>$this->idTagVote));
	    	$this->idTagVoteCtr = $this->dbT->ajouter(array("code"=>"contre","parent"=>$this->idTagVote));
	    	$this->idTagVotePour = $this->dbT->ajouter(array("code"=>"pour","parent"=>$this->idTagVote));
			$this->idTagReponse = $this->dbT->ajouter(array("code"=>"réponse","parent"=>$this->idTagRoot));
			$this->idTagActeur = $this->dbT->ajouter(array("code"=>'acteur', "parent"=>$this->idTagRoot));
			$this->idTagActOpi = $this->dbT->ajouter(array("code"=>'opinions', "parent"=>$this->idTagActeur));
			$this->idTagActRes = $this->dbT->ajouter(array("code"=>'responsable', "parent"=>$this->idTagActeur));
			$this->idTagActRap = $this->dbT->ajouter(array("code"=>'rapporteur', "parent"=>$this->idTagActeur));
			$this->idTagDate = $this->dbT->ajouter(array("code"=>'date', "parent"=>$this->idTagRoot));
			$this->idTagDatePrevDeb = $this->dbT->ajouter(array("code"=>'début prévisionnel', "parent"=>$this->idTagDate));
			$this->idTagDatePrevFin = $this->dbT->ajouter(array("code"=>'début prévisionnelle', "parent"=>$this->idTagDate));
			$this->idTagDateDeb = $this->dbT->ajouter(array("code"=>'début', "parent"=>$this->idTagDate));
			$this->idTagDateFin = $this->dbT->ajouter(array("code"=>'fin', "parent"=>$this->idTagDate));
			$this->idTagList = $this->dbT->ajouter(array("code"=>'liste', "parent"=>$this->idTagRoot));
			$this->idTagRequete = $this->dbT->ajouter(array("code"=>'requête', "parent"=>$this->idTagRoot));

    		$this->uriBase = "http://parltrack.euwiki.org/";
    		$this->idDocRootParlTrack = $this->dbD->ajouter(array("url"=>$this->uriBase
    				,"titre"=>"parltrack"
    				,"parent"=>$this->idDocRoot));

			$this->mc = new Flux_MC($idBase, $bTrace);			
			
			

	}

   	/**
     * Récupère les sujets d'une recherche sur ParlTrack
     * pour les dossiers de 1er et deuxième niveau
	 * 
     * @param  string 	$q = requête de la recherche
     *
     * @return array
     */
	public function getResultSujets($q){

		$data = $this->getParlTrackResult($q);				
		$sujets = [];

	}

	
   	/**
     * Récupère le résultat d'une recherche sur ParlTrack
     * version JSON http://parltrack.euwiki.org/
     * 
     * @param  string 	$q = requête de la recherche
     *
     * @return array
     */
	public function getParlTrackResult($q){


        $this->trace(__METHOD__." ".$q);
    	 
		set_time_limit(0);
    		
	    //initialise les variables
	    $idAct = $this->dbA->ajouter(array("code"=>__METHOD__)); 

		//création du tag 
		$idTagQ = $this->dbT->ajouter(array("code"=>$q, "parent"=>$this->idTagRequete));
		
	    //récupère le document
	    $url = $this->uriBase."search?s_meps=checked&s_dossiers=checked&format=json&q=".$q;
	    $json = $this->getUrlBodyContent($url,false,$this->bCache);
		$idDoc = $this->dbD->ajouter(array(
			'titre'=>__METHOD__." ".$q,'parent'=>$this->idDocRootParlTrack
			,'url'=>$url
			, 'data'=>$json
			),true,false,false 
		);

		//création des rapports entre
		// src = la requête
		// pre = l'action
		// dst = le doc
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idTagQ,"src_obj"=>"tag"
				,"dst_id"=>$idDoc,"dst_obj"=>"doc"
				,"pre_id"=>$idAct,"pre_obj"=>"acti"
		));						

		//traitement de la réponse
		$obj = json_decode($json);
		$sujets = [];
		$procedures = [];
		$k=0;$j=0;
		foreach ($obj->items as $i) {
			$p = $i->procedure;
			if($procedures[$p->reference])$procedures[$p->reference]['nb']++;
			else {
				$procedures[$p->reference]=array('num'=>$k,'titre'=>$p->title,'niveau'=>1,'nb'=>1,'reference'=>$p->reference,'sujets'=>$p->subject,'etape'=>$p->stage_reached);
				$k++;
			}
			foreach ($p->subject as $s) {
				$arrS = explode(' ',$s);
				$num = $arrS[0];
				$titre = substr($s,strlen($num)+1);
				if($sujets[$num])$sujets[$num]['nb']++;
				else{
					$sujets[$num]=array('num'=>$j, 'titre'=>$titre, 'reference'=>$num,'niveau'=>1,'nb'=>1);
					$j++;
				} 
			}
		}
		//recherche les procédure liées au sujets trouvés
		$liens = [];$i=0;
		foreach ($sujets as $num => $s) {
			$arrS = $this->getParlTrackDossierBySujet($num);
			$sujets[$num]['procedures']=$arrS; 
			foreach ($arrS as $p) {
				if($procedures[$p])$procedures[$p]['nb']++;
				else{
					if($liens[$p])$liens[$p]['nb']++;
					else{
						$liens[$p] = array('num'=>$i,'reference'=>$p,'niveau'=>2,'nb'=>1);
						$i++;
					} 
				}
			}
		}
		$rs = array('idRapport'=>$idRap,'procedures'=>$procedures,'sujets'=>$sujets,'liens'=>$liens);
		//met à jour du document dans la base
		$this->dbD->edit($idDoc,array('note'=>json_encode($rs)));
		return $rs;	
	}
	
	
   	/**
     * Récupère les dossiers dans un sujet sur ParlTrack
     * exemple http://parltrack.euwiki.org/dossiers?sub=1.20.09
     * 
     * @param  string 	$sujet = identifiant du sujet
     *
     * @return array
     */
	public function getParlTrackDossierBySujet($sujet){

        $this->trace(__METHOD__." ".$q);
    	 
		set_time_limit(0);
    		    					    	
	    //récupère le document
	    $url = $this->uriBase."dossiers?sub=".$sujet;
		$html = $this->getUrlBodyContent($url,false,$this->bCache);
		$idDoc = $this->dbD->ajouter(array(
				'titre'=>__METHOD__." ".$q,'parent'=>$this->idDocRootParlTrack
				,'url'=>$url, 'note'=>$html
				) 
			);
		$dom = new Zend_Dom_Query($html);	    
		//récupère la couverture
		$xPath = '//*[@id="sortedlist"]/tbody/tr';
		$results = $dom->queryXpath($xPath);
		$procedures = [];
		foreach ($results as $result) {
			$p = false;
			foreach($result->childNodes as $cn){
				if (!$p && $cn->nodeType == XML_ELEMENT_NODE) {
					$p = trim(str_replace("\n","",$cn->nodeValue));
					//récupère l'identifiant de la procédure
					$procedures[]=$p;
				}
			}
		}	    
		//met à jour du document dans la base
		$this->dbD->edit($idDoc,array('data'=>json_encode($procedures)));
		return $procedures;	
	}


    /**
     * Enregistre un dossier procédure de l'observatoire législatif
     * version Web http://www.europarl.europa.eu/oeil/info/info2.do
     * version JSON http://parltrack.euwiki.org/
     * 
     * @param  string 	$idDossier
     * @param  array 	$calculs
     *
     * @return array
     */
	public function setDossierObsLegi($idDossier
		,$calculs=array('sujets'=>true,'activities'=>true,'amendements'=>true,'comeets'=>true,'votes'=>true))
    {    	
        $this->trace(__METHOD__." ".$idDossier);
    	 
    	set_time_limit(0);    		
    	 
	    //initialise les variables
	    $idAct = $this->dbA->ajouter(array("code"=>__METHOD__)); 
	    	
	    	//récupère le document
	    $url = $this->uriBase."dossier/".$idDossier."?format=json";
	    $json = $this->getUrlBodyContent($url,false,$this->bCache);
	    //	$urlL = "http://localhost/jdc/data/eu/2103(INL).json";
	    //	$json = $this->getUrlBodyContent($urlL);
	    	$obj = json_decode($json);
	    	
	    	
	    	$this->trace("//enregistre le document = ".$obj->procedure->title);
	    	$this->idDocDossier = $this->dbD->ajouter(array("url"=>$url
	    			,"titre"=>$obj->procedure->title, "tronc"=>$obj->procedure->type
	    			,"parent"=>$this->idDocRootParlTrack,"data"=>$json
	    		));
	    	$this->idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    			,"src_id"=>	$this->idDocDossier,"src_obj"=>"doc"
	    			,"dst_id"=>$idAct,"dst_obj"=>"acti"	    			
	    	));
	    	
			//enregistre les sujets
			if($calculs['sujets']){
				$idTagSujet = $this->dbT->ajouter(array("code"=>"sujet", "parent"=>$this->idTagRoot));	    	
				foreach ($obj->procedure->subject as $s) {
					$idTag = $this->dbT->ajouter(array("code"=>$s, "parent"=>$idTagSujet));	    		 
					$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
							,"src_id"=>$idTag,"src_obj"=>"tag"
							,"dst_id"=>	$this->idDocDossier,"dst_obj"=>"doc"
							,"pre_id"=>$this->idRap,"pre_obj"=>"rapport"
					));
					$this->trace("//enregistre le sujet = ".$s);
					 
				}	
			}	    				
	    	
			//enregistre les activités
			if($calculs['activities']){
				foreach ($obj->activities as $a) {
					$this->getActivite($a);
				}
			}
	    	//	    	
	    	//enregistre les amendements
			if($calculs['amendements']){
				foreach ($obj->amendments as $a) {
					$this->getAmendement($a);
				}
			}
			//

			//enregistre les comeets
			if($calculs['comeets']){
				foreach ($obj->comeets as $c) {
					$this->getComeet($c);
				}
			}

	    	//enregistre les votes
			if($calculs['votes']){
				foreach ($obj->votes as $v) {
					$this->getVote($v);
				}
			}
	    	//
	}    
    
	/**
	 * création d'un vote
	 *
	 * @param  objet		$v
	 *
	 * @return void
	 */
	function getVote($v){
		$this->trace(__METHOD__." ".$v->title);
		if(!$v->title)$v->title='inconnu';
		if(!$v->url)$v->url='inconnu';
		if(!$v->issue_type)$v->issue_type='inconnu';
		if(!$v->ts)$v->ts='inconnu';
		if(!$v->voteid)$v->voteid='inconnu';
		$idDoc = $this->dbD->ajouter(array(
				"titre"=>$v->title, "url"=>$v->url, "tronc"=>$v->issue_type, "data"=>json_encode($v)
				,"pubDate"=>$v->ts, "parent"=>$this->idDocDossier, "poids"=>$v->voteid
		));	
		//enregistre les rapporteurs
		foreach ($v->rapporteur as $r) {
			$idRapporteur = $this->dbE->ajouter(array("nom"=>$r->name));
			//création des rapports entre
			// pre = cette importation
			// src = le rapporteur du vote
			// dst = le doc vote
			// niveau = le role de l'existence
			// valeur = la date
			$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
					,"src_id"=>$idRapporteur,"src_obj"=>"exi"
					,"dst_id"=>$idDoc,"dst_obj"=>"doc"
					,"pre_id"=>$this->idRap,"pre_obj"=>"rapport"
					,"niveau"=>$this->idTagRap
					,"valeur"=>$v->ts
			));				
		}
		//enregistre les abstentions
		foreach ($v->Abstain->groups as $g) {
			$idRapVote = $this->getVoteGroupe($g, $idDoc, $this->idTagVoteAbs, $v->ts, $idRapporteur);
		}
		//enregistre les contre
		foreach ($v->Against->groups as $g) {
			$idRapVote = $this->getVoteGroupe($g, $idDoc, $this->idTagVoteCtr, $v->ts, $idRapporteur);
		}
		//enregistre les pour
		foreach ($v->For->groups as $g) {
			$idRapVote = $this->getVoteGroupe($g, $idDoc, $this->idTagVotePour, $v->ts, $idRapporteur);
		}		
	
	}

	/**
	 * création des votes pour un groupe
	 *
	 * @param  	objet	$g
	 * @param	int		$idDoc
	 * @param  	int		$idTag
	 * @param	date		$date
	 * @param	int		$idRapporteur
	 *
	 * @return int
	 */
	function getVoteGroupe($g, $idDoc, $idTag, $date, $idRapporteur){

		$this->trace(__METHOD__." ".$g->group." : ".$idTag);
		
		//récupère le groupe
		$idExiGr = $this->dbE->ajouter(array("url"=>$this->uriBase."meps/".$g->group));
		//création des rapports entre
		// pre = le document à voter
		// src = le groupe
		// dst = le vote
		// valeur = la date
		$idRapVote = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idExiGr,"src_obj"=>"exi"
				,"dst_id"=>$idTag,"dst_obj"=>"tag"
				,"pre_id"=>$idDoc,"pre_obj"=>"doc"
				,"valeur"=>$date
		));
		//ajoute les votes
		foreach ($g->votes as $vo) {
			$idExi = $this->dbE->ajouter(array("nom"=>$vo->name,"url"=>"http://www.europarl.europa.eu/meps/fr/".$vo->ep_id."/_history.html"));
			//création des rapports entre
			// pre = cette importation
			// src = le votant
			// dst = le vote pour le groupe
			// valeur = la date
			$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
					,"src_id"=>$idExi,"src_obj"=>"exi"
					,"dst_id"=>$idRapVote,"dst_obj"=>"rapport"
					,"pre_id"=>$this->idRap,"pre_obj"=>"rapport"
					,"valeur"=>$date
			));
		
		}
		return $idRapVote;
	}
	
    /**
     * création d'un amendement
     *
     * @param  objet		$a
     *
     * @return void
     */
    function getAmendement($a){
	    	$this->trace(__METHOD__." ".$a->reference." ".$a->seq);
	    	if(isset($a->authors)){
	    		$auteurs = explode(',',$a->authors);
	    		foreach ($auteurs as $aut) {
	    			$arrAuteur[] = $this->dbE->ajouter(array("nom"=>$aut));
	    		}	    		
	    	}else{
	    		$arrAuteur = -1;
	    	}
	    	$idTagC = $this->dbT->ajouter(array("uri"=>$this->uriBase."committee/".$a->committee[0]));
	    	if(isset($a->location))
		    	$loc = implode(",",$a->location[0]);
	    	else
	    		$loc = "";
	    	if(isset($a->orig_lang))$l=$a->orig_lang; else $l="no"; 
	    	$idTagLang = $this->dbT->ajouter(array("code"=>$l,"parent"=>$this->idTagLang));
	    	$idDoc = $this->dbD->ajouter(array(
	    			"titre"=>"Amendement : ".$loc." - ".$a->seq, "url"=>$a->src, "tronc"=>$loc, "data"=>json_encode($a)
	    			,"pubDate"=>$a->date, "parent"=>$this->idDocDossier, "poids"=>$a->seq
	    			,"type"=>$idTagLang
	    	));
	    	//création des rapports entre
	    	// pre = cette importation
	    	// src = l'auteur de l'amendement 
	    	// dst = le doc amendement
	    	// niveau = la commission
	    	// valeur = la date
	    	foreach ($arrAuteur as $idAut) {
	    		$idRapCR = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    				,"src_id"=>$idAut,"src_obj"=>"exi"
	    				,"dst_id"=>$idDoc,"dst_obj"=>"doc"
	    				,"pre_id"=>$this->idRap,"pre_obj"=>"rapport"
	    				,"niveau"=>$idTagC
	    				,"valeur"=>$a->date
	    		));
	    	}
	    	//création des sous document
	    	if(isset($a->new)){
	    		$i = 0;
	    		foreach ($a->new as $n) {
	    			$this->dbD->ajouter(array(
	    					"titre"=>"New - ".$a->seq." - ".$i, "tronc"=>$loc, "note"=>$n
	    					,"pubDate"=>$a->date, "parent"=>$idDoc
	    					,"type"=>$idTagLang
	    			));
	    			$i++;
	    		}
	    	}
	    	if(isset($a->old)){
	    		$i = 0;
	    		foreach ($a->old as $n) {
	    			$this->dbD->ajouter(array(
	    					"titre"=>"Old - ".$a->seq." - ".$i, "tronc"=>$loc, "note"=>$n
	    					,"pubDate"=>$a->date, "parent"=>$idDoc
	    					,"type"=>$idTagLang
	    			));
	    			$i++;
	    		}
	    	}
	    	if(isset($a->content)){
	    		$i = 0;
	    		foreach ($a->content as $n) {
	    			$this->dbD->ajouter(array(
	    					"titre"=>"Content - ".$a->seq." - ".$i, "tronc"=>$loc, "note"=>$n
	    					,"pubDate"=>$a->date, "parent"=>$idDoc
	    					,"type"=>$idTagLang
	    			));
	    			$i++;
	    		}
	    	}
	    	
    }
	
    /**
     * création d'un comeets
     *
     * @param  objet		$c
     *
     * @return void
     */
    function getComeet($c){
		$this->trace(__METHOD__." ".$c->type." ".$c->title);

		//récupération de la géolocalistion
		if(!$c->room)$c->room='inconnu';
		if(!$c->city)$c->city='inconnu';
		$idGeo = $this->dbG->ajouter(array('adresse'=>$c->room,'ville'=>$c->city));

		//enregistre le document
		if(!$c->seq_no)$c->seq_no='0';
		if(!$c->title)$c->title='inconnu';
		if(!$c->type)$c->type='inconnu';
		if(!$c->docid)$c->docid='inconnu';
		$idDoc = $this->dbD->ajouter(array(
			"url"=>$c->src, "titre"=>$c->seq_no.'. '.$c->title, "tronc"=>$c->type, "data"=>json_encode($c)
			,"note"=>$c->docid,"pubDate"=>$c->date, "parent"=>$this->idDocDossier
		));

		if($c->time && $c->time->date && $c->time->end){
			//création des rapports entre
			// src = le doc
			// dst = le tag date prévisionnnelle debut
			// valeur = la date prévisionnelle de début
			$idRapCR = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$idGeo
					,"src_id"=>$idDoc,"src_obj"=>"doc"
					,"dst_id"=>$this->idTagDatePrevDeb,"dst_obj"=>"tag"
					,"valeur"=>$c->time->date.""
			));
			//création des rapports entre
			// src = le doc
			// dst = le tag date prévisionnnelle fin
			// valeur = la date prévisionnelle de fin
			$idRapCR = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$idGeo
					,"src_id"=>$idDoc,"src_obj"=>"doc"
					,"dst_id"=>$this->idTagDatePrevFin,"dst_obj"=>"tag"
					,"valeur"=>$c->time->end.""
			));
		}
		//création des rapports entre
		// src = le doc
		// dst = le tag date de debut
		// valeur = la date de debut
		$idRapCR = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$idGeo
				,"src_id"=>$idDoc,"src_obj"=>"doc"
				,"dst_id"=>$this->idTagDateDeb,"dst_obj"=>"tag"
				,"valeur"=>$c->date.""
		));
		//création des rapports entre
		// src = le doc
		// dst = le tag date de fin
		// valeur = la date de fin
		$idRapCR = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$idGeo
				,"src_id"=>$idDoc,"src_obj"=>"doc"
				,"dst_id"=>$this->idTagDateFin,"dst_obj"=>"tag"
				,"valeur"=>$c->end.""
		));

		//enregistre les acteurs
		foreach ($c->actors->Opinions as $a) {
			$this->setActeur($a, $this->idTagActOpi, $idDoc, $idGeo);
		}
		foreach ($c->actors->Responsible as $a) {
			$this->setActeur($a, $this->idTagActRes, $idDoc, $idGeo);
		}
		foreach ($c->actors->Rapporteur as $a) {
			$this->setActeur($a, $this->idTagActRap, $idDoc, $idGeo);
		}
		
		//enregistre les list
		foreach ($c->list as $l) {
			$idTag = $this->dbT->ajouter(array("code"=>$l,'parent'=>$this->idTagList));
			$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$idGeo
				,"src_id"=>$idDoc,"src_obj"=>"doc"
				,"dst_id"=>$idTag,"dst_obj"=>"tag"
			));
		}
		
	}
	
	/**
	 * création d'un acteur
	 *
	 * @param  objet		$a
	 * @param  int			$idTagActeur
	 * @param  int			$idDoc
	 * @param  int			$idGeo
	 *
	 */
	function setActeur($a, $idTagActeur, $idDoc, $idGeo){
		$this->trace(__METHOD__." ".$d->title);
		if($a->comid){
			$idTagC = $this->dbT->ajouter(array("uri"=>$this->uriBase."committee/".$a->comid[0]));
			//création des rapports entre
			// src = le doc
			// dst = le comite
			// pre = opinions
			$idRapCR = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$idGeo
					,"src_id"=>$idDoc,"src_obj"=>"doc"
					,"dst_id"=>$idTagC,"dst_obj"=>"tag"
					,"pre_id"=>$this->idTagActeur,"pre_obj"=>"tag"
			));
		}
		if($a->group){
			$idExiGr = $this->dbE->ajouter(array("url"=>$this->uriBase."meps/".$g->group[0]));
			//création des rapports entre
			// src = le doc
			// dst = le group
			// pre = opinions
			$idRapCR = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$idGeo
					,"src_id"=>$idDoc,"src_obj"=>"doc"
					,"dst_id"=>$idExiGr,"dst_obj"=>"exi"
					,"pre_id"=>$this->idTagActeur,"pre_obj"=>"tag"
			));
		}
		if($a->name){
			$idExiRap = $this->dbE->ajouter(array("nom"=>$r->name[0],"url"=>$this->uriBase."mep/".$r->name[0]));
			//création des rapports entre
			// src = le doc
			// dst = la personne
			// pre = opinions
			$idRapCR = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$idGeo
					,"src_id"=>$idDoc,"src_obj"=>"doc"
					,"dst_id"=>$idExiRap,"dst_obj"=>"exi"
					,"pre_id"=>$this->idTagActeur,"pre_obj"=>"tag"
			));
		}
		if($a->response){
			$idTagR = $this->dbT->ajouter(array("code"=>$a->response[0],"parent"=>$this->idTagReponse));
			//création des rapports entre
			// src = le doc
			// dst = la réponse
			// pre = opinions
			$idRapCR = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$idGeo
					,"src_id"=>$idDoc,"src_obj"=>"doc"
					,"dst_id"=>$idTagR,"dst_obj"=>"tag"
					,"pre_id"=>$this->idTagActeur,"pre_obj"=>"tag"
			));
		}			
		if(isset($a->docs)){
			foreach ($a->docs as $d) {
				$idD = $this->getDoc($d, $idDoc);
				//création des rapports entre
				// src = le doc
				// dst = le doc
				// pre = opinions
				$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$idGeo
						,"src_id"=>$idDoc,"src_obj"=>"doc"
						,"dst_id"=>$idD,"dst_obj"=>"doc"
						,"pre_id"=>$this->idTagActeur,"pre_obj"=>"tag"
					));
			}    		
		}
	}


    /**
     * création d'une activité
     *
     * @param  objet		$c
     *
     * @return void
     */
    function getActivite($a){
    	 
    		$this->trace(__METHOD__." ".$a->type);
    		$idTagTA = $this->dbT->ajouter(array("code"=>$a->type, "parent"=>$this->idTagActi));

    		//parcourt les commissions
    		if(isset($a->committees)){    		
	    		foreach ($a->committees as $c) {
	    			$idRapC = $this->getCommission($c);
	    			//création des rapports entre
	    			// pre = cette importation
	    			// src = l'activité
	    			// dst = la commission
	    			// valeur = la date
	    			$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    					,"src_id"=>$idTagTA,"src_obj"=>"tag"
	    					,"dst_id"=>$idRapC,"dst_obj"=>"rapport"
	    					,"pre_id"=>$this->idRap,"pre_obj"=>"rapport"
	    					,"valeur"=>$a->date
	    			));
	    		}
    		}
    		//parcourt les documents
    		if(isset($a->docs)){
	    		foreach ($a->docs as $d) {
	    			$idD = $this->getDoc($d, 	$this->idDocDossier);
	    			//création des rapports entre
	    			// pre = cette importation
	    			// src = l'activité
	    			// dst = le document
	    			// valeur = la date
	    			$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    					,"src_id"=>$idTagTA,"src_obj"=>"tag"
	    			        ,"dst_id"=>$idD,"dst_obj"=>"doc"
	    					,"pre_id"=>$this->idRap,"pre_obj"=>"rapport"
	    					,"valeur"=>$a->date
	    			));
	    		}    		
    		}
    	}
    
    	/**
    	 * création d'un document
    	 *
    	 * @param  objet		$d
    	 * @param  int		$parent
    	 *
    	 * @return integer
    	 */
    	function getDoc($d, $parent=0){
    		$this->trace(__METHOD__." ".$d->title);
    		if(!isset($d->type))$d->type='inconnu';
    		if(!isset($d->url))$d->url='inconnu';
    		$data = array("titre"=>$d->title, "url"=>$d->url, "tronc"=>$d->type);
    		if($parent)$data["parent"]=$parent;
    		if(isset($d->text))$data["note"]=implode(".", $d->text);

    		$idDoc = $this->dbD->ajouter($data);
    		
    		return $idDoc;
    	}
    	
    	/**
     * création d'une commission
     *
     * @param  objet		$c
     *
     * @return integer
     */
    function getCommission($c){
    	
    		$this->trace(__METHOD__." ".$c->committee_full);
    	     	
	    	//enregistre la commission
	    	$idTagC = $this->dbT->ajouter(array("code"=>$c->committee_full, "uri"=>$this->uriBase."committee/".$c->committee, "parent"=>$this->idTagCom));
	    	 
	    	//enregistre le rapporteur
	    	if($c->rapporteur)$r = $this->getRapporteur($c->rapporteur[0]);
			else $r = false;
			 
	    	//vérifie le type de commission pour
	    	//création des rapports entre
	    	// pre = cette importation
	    	// src = le rapporteur associé à un groupe
	    	// dst = son role dans la commission
	    	// niveau = la nomination de son role dans la commission = $idRapCR
	    	// valeur = la date
	    	if($c->responsible){
	    		$idRapCR = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    				,"src_id"=>$idTagC,"src_obj"=>"tag"
	    				,"dst_id"=>$this->idTagRoleComFond,"dst_obj"=>"tag"
	    				,"pre_id"=>$this->idRap,"pre_obj"=>"rapport"
	    				,"niveau"=>$this->idTagActiNomi
				));
				if($r){
					$idRapRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
					,"src_id"=>$r[2],"src_obj"=>"rapport"
					,"dst_id"=>$this->idTagRap,"dst_obj"=>"tag"
					,"pre_id"=>$this->idRap,"pre_obj"=>"rapport"
					,"niveau"=>$idRapCR,"valeur"=>$c->date.""
					));
				}
	    		//enregistrement des fictifs
	    		foreach ($c->shadows as $f) {
	    			$rf = $this->getRapporteur($f);
	    			$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    					,"src_id"=>$rf[2],"src_obj"=>"rapport"
	    					,"dst_id"=>$this->idTagRapFic,"dst_obj"=>"tag"
	    					,"pre_id"=>$this->idRap,"pre_obj"=>"rapport"
	    					,"niveau"=>$idRapCR,"valeur"=>$c->date.""
	    			));
	    		}
	    	}else{
	    		$idRapCR = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    				,"src_id"=>$idTagC,"src_obj"=>"tag"
	    				,"dst_id"=>$this->idTagRoleComAvis,"dst_obj"=>"tag"
	    				,"pre_id"=>$this->idRap,"pre_obj"=>"rapport"
	    				,"niveau"=>$this->idTagActiNomi
				));
				if($r){
					$idRapRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
							,"src_id"=>$r[2],"src_obj"=>"rapport"
							,"dst_id"=>$this->idTagRapAvis,"dst_obj"=>"tag"
							,"pre_id"=>$this->idRap,"pre_obj"=>"rapport"
							,"niveau"=>$idRapCR //,"valeur"=>$c->date
					));
				}
	    	}
    		return $idRapCR;
    }
    
    /**
    * création d'un rapporteur
    *
   	* @param  objet 	$r
    	*
    	* @return array
    	*/    
    function getRapporteur($r){
    	
    		$this->trace(__METHOD__." ".$r->name);
	    	//enregistre le groupe
	    	$idExiGr = $this->dbE->ajouter(array("nom"=>$r->group,"url"=>$this->uriBase."meps/".$r->group));
	    	//enregistre le rapporteur
	    	$idExiRap = $this->dbE->ajouter(array("nom"=>$r->name,"url"=>$this->uriBase."mep/".$r->name));
	    	//création du rapport entre groupe et rapporteur
	    $idRapport = 	$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
	    			,"src_id"=>$idExiRap,"src_obj"=>"exi"
	    			,"dst_id"=>$idExiGr,"dst_obj"=>"exi"
	    			,"pre_id"=>$this->idRap,"pre_obj"=>"rapport"
	    			,"valeur"=>"membre"
	    	));
    	 	return array($idExiRap,$idExiRap,$idRapport);
    }

}
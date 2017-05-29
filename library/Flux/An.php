<?php
/**
 * Flux_An
 * Classe qui gère les flux des sites des archives nationales
 * 
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Scraping
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Flux_An extends Flux_Site{

	var $urlBaseTof = "http://www.siv.archives-nationales.culture.gouv.fr/mm/media/download/";

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
    		$this->initDbTables();
    		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
    		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
    		$this->idTagRoot = $this->dbT->ajouter(array("code"=>__CLASS__));
    		
    		$this->mc = new Flux_MC($idBase, $bTrace);
    		
    }
    
    /**
     * Enregistre un fichier XML pour gérer les rapports 
     * et la gestion IIIF des phtgraphies
     * 
     * @param  string 	$fic
     *
     * @return array
     */
    public function sauveXmlEad($fic)
    {    	
    		$this->trace(__METHOD__." ".$fic);
    	 
    		set_time_limit(0);
    		$this->initDbTables();
    		
    		$this->trace("//récupère les mots clefs");
    		$this->idTagAN = $this->dbT->ajouter(array("code"=>"mots clefs AN", "parent"=>$this->idTagRoot));
    		$this->idTagAuteur = $this->dbT->ajouter(array("code"=>"Établi par", "parent"=>$this->idTagAN));
    		$this->idTagDebut = $this->dbT->ajouter(array("code"=>"début", "parent"=>$this->idTagAN));
    		$this->idTagFin = $this->dbT->ajouter(array("code"=>"fin", "parent"=>$this->idTagAN));
    		
    		
    		$xml = $this->getUrlBodyContent($fic);
    		//enlève la dtd pour que le Xpath fonctionne
    		$xml = str_replace('<!DOCTYPE ead SYSTEM "ead_sia.dtd">',"",$xml);
    		
    		//echo $html;
    		$this->dom = new Zend_Dom_Query($xml);
    		
    		//récupère la référence du document
    		$xPath = '/ead/eadheader/eadid';
    		$results = $this->dom->queryXpath($xPath);
    		foreach ($results as $result) {
    			$refAn =$result->nodeValue;
    		}

    		//récupère le titre du document
    		$xPath = '/ead/eadheader/filedesc/titlestmt/titleproper';
    		$results = $this->dom->queryXpath($xPath);
    		foreach ($results as $result) {
    			$titre =$result->nodeValue;
    		}

    		//enregitre le document
    		$idDoc = $this->dbD->ajouter(array("url"=>$fic
    				,"titre"=>$titre
    				,"tronc"=>$refAn
    				,"data"=>$xml
    				,"parent"=>$this->idDocRoot));    		
    		
    		//récupère les auteurs du document
    		$xPath = '/ead/eadheader/filedesc/titlestmt/author';
    		$results = $this->dom->queryXpath($xPath);
    		$nbDoc = 0;
    		foreach ($results as $result) {
    			$auteurs = $result->nodeValue;
    			$auteurs = str_replace('Établi par ',"",$auteurs);
    			$arrAut = explode(',',$auteurs);
    			foreach ($arrAut as $a) {
    				//enregistre l'auteur
    				$idExi = $this->dbE->ajouter(array("nom"=>trim($a)));
    				//création des rapports entre
    				// src = le document
    				// dst = l'auteur
    				// pre = le type de rapport
    				$idRapDoc = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
    						,"src_id"=>$idDoc,"src_obj"=>"doc"
    						,"dst_id"=>$idExi,"dst_obj"=>"exi"
    						,"pre_id"=>$this->idTagAuteur,"pre_obj"=>"tag"
    				));
    			}
    		}
    		
    		//enregistre les séries    		
    		$xPath = '//dsc/c';
    		$results = $this->dom->queryXpath($xPath);
    		$i=0;
    		foreach ($results as $result) {
    			$this->trace("Série : ".$i);
    			$this->sauveSerie($result, $idDoc); 
    			$i++; 
    		}
    		    		
	}    
    
	/**
	 * enregistre une série
	 *
	 * @param  objet		$c
	 * @param  int		$idDoc
	 *
	 * @return void
	 */
	function sauveSerie($c, $idDoc){
			
		//enregistre la série
		$id = $c->getAttribute('id');
		/*
		if($id=='c-w7cte2jf0-1vfhgn505hxk9'){
			$to = 'toto';
		}
		*/
		
		$this->trace(__METHOD__." ".$id);
		
		foreach($c->childNodes as $cn){
			if ($cn->nodeType == XML_ELEMENT_NODE) {
					
				if ($cn->tagName == "did") {
					$rs = $cn->getElementsByTagName('unittitle');
					foreach ($rs as $r) {
						$titre = $r->nodeValue;
					}
					$rs = $cn->getElementsByTagName('unitid');				
					foreach ($rs as $r) {
						$refAn = $r->nodeValue;
					}
					if(isset($refAn)){
						//enregistre la série
						$idDocSerie = $this->dbD->ajouter(array("url"=>'//*[@id="'.$id.']'
								,"titre"=>$titre
								,"tronc"=>$refAn
								,"parent"=>$idDoc));
						/* enregistre la date sous forme de période
						 * dans un rapport
						 */
						$rs = $cn->getElementsByTagName('unitdate');
						foreach ($rs as $r) {
							$d = $r->getAttribute('normal');
						}
						if(isset($d)){
							$ds = explode('/',$d);
							//création des rapports entre
							// src = le document
							// dst = le tag = début ou fin
							// pre = le document root
							// valeur = la date
							$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
									,"src_id"=>$idDocSerie,"src_obj"=>"doc"
									,"dst_id"=>$this->idTagDebut,"dst_obj"=>"tag"
									,"pre_id"=>$idDoc,"pre_obj"=>"doc"
									,"valeur"=>$ds[0]
							));
							if(count($ds)>1){				
								$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
										,"src_id"=>$idDocSerie,"src_obj"=>"doc"
										,"dst_id"=>$this->idTagFin,"dst_obj"=>"tag"
										,"pre_id"=>$idDoc,"pre_obj"=>"doc"
										,"valeur"=>$ds[0]
								));						
							}				
						}
					}
				}
				//récupère les contenus
				if ($cn->tagName == "scopecontent") {
					$this->sauveContent($cn, $idDocSerie, $idDoc);
				}

				if ($cn->tagName == "daogrp") {
					$tofs = $cn->getElementsByTagName('daoloc');
					foreach($tofs as $tof){
						$ficnum = $tof->getAttribute('href');
					}
					$arrFicNum = explode("_",$ficnum);
					for ($i = 0; $i < count($this->arrItem); $i++) {
						$this->arrItem[$i]['fic']=$arrFicNum[0]."_".$arrFicNum[1]."_".$this->arrItem[$i]['num']."_L-medium.jpg";
					}
					//enregistre les documents
					foreach ($this->arrItem as $item) {
						$idDocTof = $this->dbD->ajouter(array("url"=>$this->urlBaseTof.$item['fic']
								,"titre"=>$item['text']
								,"tronc"=>$this->ss
								,"parent"=>$idDocSerie));
						//enregistre l'image						
						$url = $this->urlBaseTof.$item['fic'];
						$img = ROOT_PATH.'/data/AN/'.$item['fic'];
						if (!file_exists($img)) 
							file_put_contents($img, file_get_contents($url));
						
						//création des rapports entre
						// src = le document
						// dst = la photo
						// pre = le document root
						$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
								,"src_id"=>$idDocSerie,"src_obj"=>"doc"
								,"dst_id"=>$idDocTof,"dst_obj"=>"doc"
								,"pre_id"=>$idDoc,"pre_obj"=>"doc"
						));
					}
					$this->arrItem = array();
					$this->ss = "";						
				}
				
				if ($cn->tagName == "controlaccess") {
					foreach($cn->childNodes as $ca){
						if ($ca->nodeType == XML_ELEMENT_NODE) {
						
							if ($ca->tagName == "geogname") {
								//enregistre la géo
								$idGeo = $this->dbG->ajouter(array("adresse"=>$ca->nodeValue));
								//création des rapports entre
								// src = le document
								// dst = la géo
								// pre = le document root
								$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
										,"src_id"=>$idDocSerie,"src_obj"=>"doc"
										,"dst_id"=>$idGeo,"dst_obj"=>"geo"
										,"pre_id"=>$idDoc,"pre_obj"=>"doc"
								));
							}
							if ($ca->tagName == "persname") {
								$p = $ca->nodeValue;
								$arrP = explode(", ",$p);
								if(count($arrP)<2){
									$arrP = explode(" (",$p);
									$nom = $arrP[0];
									$prenom = "";
									$nait = substr($arrP[1], 0, 4);
									$mort = substr($arrP[1], 5, 4);
								}else{
									$nom = $arrP[0];
									$arrP = explode(" (",$arrP[1]);
									$prenom = $arrP[0];
									$nait = substr($arrP[1], 0, 4);
									$mort = substr($arrP[1], 5, 4);
								}
								//enregistre l'existence
								$idExi = $this->dbE->ajouter(array("nom"=>$nom,"prenom"=>$prenom,"nait"=>$nait,"mort"=>$mort));
								//création des rapports entre
								// src = le document
								// dst = l'existence
								// pre = le document root
								$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
										,"src_id"=>$idDocSerie,"src_obj"=>"doc"
										,"dst_id"=>$idExi,"dst_obj"=>"exi"
										,"pre_id"=>$idDoc,"pre_obj"=>"doc"
								));
							}
						}
				
					}
				}
				if ($cn->tagName == "c") {
					$this->sauveSerie($cn, $idDocSerie);
				}
				
			}
		}
		
	}
	
	/**
	 * enregistre un contenu
	 *
	 * @param  objet		$c
	 * @param  int		$idDoc
	 *
	 * @return void
	 */
	function sauveContent($r, $idDocSerie, $idDoc){

		$this->trace(__METHOD__." ".$idDocSerie);
		$this->arrItem = array();
		$this->ss = "";
		
		foreach($r->childNodes as $sc){
			if ($sc->nodeType == XML_ELEMENT_NODE) {
					
				if ($sc->tagName == "p") {
					//récupère la sous série
					$this->ss = $sc->nodeValue;
				}
				if ($sc->tagName == "list") {
					//récupère les items
					foreach($sc->childNodes as $s){
						if ($s->nodeType == XML_ELEMENT_NODE) {
							if ($s->tagName == "item") {
								$strItem = $s->nodeValue;
								$arrStrItem = explode(":", $strItem);
								$arrNum = explode("-", $arrStrItem[0]);
								$deb = str_replace('n°','',$arrNum[0])+0;
								$fin = isset($arrNum[1]) ? str_replace('n° ','',$arrNum[1]) : $deb+1;
								$nb = $fin-$deb;
								for ($i = 0; $i < $nb; $i++) {
									$num = $deb+$i;
									if($num==477){
										$toto = 1;
									}
									$this->arrItem[] = array("ss"=>$this->ss,"text"=>$arrStrItem[1],"num"=>sprintf("%'.04d", $num));
								}
							}
						}
					}
				}				
			}
		}	
	}
		
	
}
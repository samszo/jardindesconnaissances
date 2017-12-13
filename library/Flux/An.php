<?php
use function GuzzleHttp\json_encode;

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
    var $dbOmk;
    var $idBaseOmk;
    var $isPartOf = 33;
    var $hasPart = 34;
    var $isReferencedBy = 35;
    var $description = 4;
    var $title = 1;
    var $reference = 10;
    var $birthDate = 1269;
    var $deathDate = 2090;
    var $isVisage = 42333;
    var $owner = "samuel.szoniecky@univ-paris8.fr";
    var $idOwner = 1;
    var $idClassImage = 26;
    
    /**
     * Constructeur de la classe
     *
     * @param  string $idBase
     * 
     */
	public function __construct($idBase=false, $idBaseOmk=false, $bTrace=false)
    {
        $this->dbOmk = $this->getDb($idBaseOmk);
        $this->idBaseOmk = $idBaseOmk;
        $this->initVarOmk();
        
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
     * et la gestion IIIF des photographies
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
    				// ,"data"=>$xml
    				,"parent"=>$this->idDocRoot),true, false, false);    		
    		
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
    		//$xPath = '//c[@id="c-1y68bw3v2-1wqsh11mhqsq"]';
    		$results = $this->dom->queryXpath($xPath);
    		$i=0;
    		foreach ($results as $result) {
    			$this->trace("Série : ".$i);
    			//if($i>=618)
        			$this->sauveSerie($result, $idDoc); 
    			$i++; 
    		}
    		    		
	}    
    
	/**
	 * enregistre une série
	 *
	 * @param  object		$c
	 * @param  int		$idDoc
	 *
	 * @return void
	 */
	function sauveSerie($c, $idDoc){
			
		//enregistre la série
		$id = $c->getAttribute('id');
		//Flux_An::sauveSerie c-a57vz4drc-1pgslyhluxn5p |5951,431|0
		
		if($id=='c-a57vz4drc-1pgslyhluxn5p'){
			$to = 'toto';
		}
		//
		
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
						    $ds = explode('/',trim($d));
							//vérifie les dates 00XX-01-01
							if(substr($ds[0], 0, 2)=="00"){
							    $arrD = explode("-", $ds[1]);
							    $ds[0] = $arrD[0]."-".$arrD[1]."-".substr($ds[0], 2, 2);
							}
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
										,"valeur"=>$ds[1]
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
					if(count($this->arrItem)){
					    //gestion des photo  à patir de la liste d'item
					    for ($i = 0; $i < count($this->arrItem); $i++) {
					        $numFic = str_pad($this->arrItem[$i]['num'], 4, "0", STR_PAD_LEFT);
					        $this->arrItem[$i]['fic']=$arrFicNum[0]."_".$arrFicNum[1]."_".$numFic."_L-medium.jpg";
        						$this->arrItem[$i]['tronc']=$this->ss;
					    }
					}else{
					    //gestion des photos directement par 
					    //<daoloc href="FRAN_0138_2365_L.msp#FRAN_0138_2394_L.msp"/>
					    $j=0;
					    //ATTENTION le nombre de 0 varie suivant les collections
					    $numZero = strlen($arrFicNum[2]);
					    $deb = $arrFicNum[2]+0;
					    $fin = $arrFicNum[5]+0;
					    for ($i = $deb; $i <= $fin; $i++) {
					        //ATTENTION le nombre de 0 varie suivant les collections
					        $numFic = str_pad($i, $numZero, "0", STR_PAD_LEFT);					        
					        $this->arrItem[$j]['fic']=$arrFicNum[0]."_".$arrFicNum[1]."_".$numFic."_L-medium.jpg";		
					        $this->arrItem[$j]['text']="photo ".$numFic;
					        $this->arrItem[$j]['tronc'] = $arrFicNum[0]."_".$arrFicNum[1];
					        $j++;
					    }					        
					}
					//enregistre les documents
					foreach ($this->arrItem as $item) {
						$idDocTof = $this->dbD->ajouter(array("url"=>$this->urlBaseTof.$item['fic']
								,"titre"=>$item['text']
						        ,"type"=>1
						        ,"tronc"=>$item['tronc']
								,"parent"=>$idDocSerie));
						//enregistre l'image						
						$url = $this->urlBaseTof.$item['fic'];
						$img = ROOT_PATH.'/data/AN/photos/'.$item['fic'];
						if (!file_exists($img)){ 
							$res = file_put_contents($img, file_get_contents($url));
							$this->trace($res." ko : Fichier créé ".$url." ".$img);						
						}
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
									$nait = substr($arrP[1], 0, 4)."-01-01";;
									$mort = substr($arrP[1], 5, 4)."-01-01";;
								}else{
									$nom = $arrP[0];
									$arrP = explode(" (",$arrP[1]);
									$prenom = $arrP[0];
									$nait = substr($arrP[1], 0, 4)."-01-01";
									$mort = substr($arrP[1], 5, 4)."-01-01";
								}
								if($mort==" )-01-01")$mort=null;
								if($mort==")-01-01")$mort=null;
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
								$this->trace("existence créé $nom $prenom $nait $mort");
								
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
	 * @param  class		$c
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
		
	
	/**
	 * créer un csv pour importer des photos dans Omeka
	 *
	 * @param  string  $fic
	 *
	 * @return void
	 */
	function getCsvToOmeka($fic){
	    
	    $this->trace(__METHOD__." ".$fic);
	    $arrItem = array();

	    //récupère l'arboressence des documents
	    $arrH = $this->getPhotos();	    
	    
	    //construction du tableau pour le csv
	    $i=0;
	    //foreach ($arrH as $h) {
	    for ($i = 0; $i < 10; $i++) {
	        $h = $arrH[$i];
            //récupère l'item set du parent
            //$is = $this->dbIS->getByIdentifier($this->idBase."-flux_doc-doc_id-".$h["parent"]);	            
            $path_parts = pathinfo($h["url"]);
            if(substr($h["url"],0,4)=="http"){ 
                $arrItem[] = array("dcterms:isPartOf"=>5468,"owner"=>$this->owner ,"dcterms:title"=>$h["titre"]
    	                ,"dcterms:isReferencedBy"=>$h["url"]
    	                ,"dcterms:identifier"=>$this->idBase."-flux_doc-doc_id-".$h["doc_id"]
    	                ,"file"=>$path_parts["basename"],"dcterms:type"=>"image");	        	        
            }
        	    $pTitre = $h["titre"];
        	    //$i++;
	    }
	    //enregistre le csv dans un fichier
	    $fp = fopen($fic, 'w');
	    $first = true;
	    foreach ($arrItem as $v) {	        
	        if($first)fputcsv($fp, array_keys($v));
	        $first=false;
	        fputcsv($fp, $v);
	    }
	    fclose($fp);        	    
	}
	
	
	/**
	 * Initialise les variables pour l'importation Omk
	 *
	 *
	 */
	function initVarOmk(){
	    //initialistion des variables
	    $this->dbOmkR = new Model_DbTable_Omk_Resource($this->dbOmk);
	    $this->dbIS = new Model_DbTable_Omk_ItemSet($this->dbOmk);
	    $this->dbI = new Model_DbTable_Omk_Item($this->dbOmk);
	    $this->dbV = new Model_DbTable_Omk_Value($this->dbOmk);
	    $this->dbVoc = new Model_DbTable_Omk_Vocabulary($this->dbOmk);
	    $this->dbP = new Model_DbTable_Omk_Property($this->dbOmk);
	    $this->dbRC = new Model_DbTable_Omk_ResourceClass($this->dbOmk);
	    $this->dbIIS = new Model_DbTable_Omk_ItemItemSet($this->dbOmk);
	    $this->owner = "samuel.szoniecky@univ-paris8.fr";
	    $this->idOwner = 1;
	    $this->arrClass = array();
	}

	
	/**
	 * Création des item set OMK à partir de l'arboressence d'un document
	 * @param  int $idDoc
	 *
	 */
	function setItemSetFromDocRoot($idDoc){
	    
	    
	    //récupère l'arboressence des documents
	    $arrH = $this->dbD->getFullChild($idDoc);
	    
	    //pour éviter les doublons géographiques
	    $this->arrGeo = array();
	    //création des itemSet géo
	    $this->idRGEO = $this->setItemSet(array("idClass"=>23,"titre"=>"Références géographiques"));
	    $this->getItemSetGeo("France", $this->idRGEO);
	    
        //pour récupérer la référence du parent
	    $arrDocItem = array();	    
	    
	    foreach ($arrH as $h) {	        
	        
	        //choisi la ressource suivant le niveau
	        if($h["niveau"] < 4) $h["idClass"] = 23;//collection
	        elseif($h["niveau"]==4) $h["idClass"] = 25;//event
	        elseif ($h["niveau"]>4  && substr($h["url"],0,4)!="http") $h["idClass"] = 25;//event
	        elseif ($h["type"]==1)$h["idClass"] = 26;//image
	        else $h["idClass"] = 23;//collection
	        
	        //on ne crée pas les items
	        //elles le seront lors de l'import des données
	        if($h["idClass"] == 26){
	            //créer l'item
	            $idR = 0;//$this->setItem($h);
	        }else{
	            //créer l'itemSet
	            $idR = $this->setItemSet($h);
        	        //enregistre le lien entre doc et itemSet
        	        $arrDocItem[$h['doc_id']]= $idR;
        
        	        //enregistre les dates de début et de fin = dcterms:temporal
        	        $rs = $this->getItemDate($h["doc_id"]);
        	        foreach ($rs as $d) {
        	            if($d["debut"]){
                	            $dt = $d["debut"];
                	            if ($d["fin"])$dt .= "/".$d["fin"];
                	            $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>41,"type"=>"literal","lang"=>"fr","value"=>$dt));
        	            }
        	        }
        	        //enregistre les lieux = dcterms:spatial : adresse + relation à l'item set
        	        $rs = $this->getItemLieux($h["doc_id"]);
        	        foreach ($rs as $d) {
        	            if($d["geo_id"]){
        	                $this->setItemSetGeo($idR, $d);
        	            }
        	        }
        	        
        	        //creation de la relation hiérarchique
        	        if(isset($arrDocItem[$h['parent']])){	            
            			//ajouter le lien entre la class et son parent
        	            $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>33,"type"=>"resource","value_resource_id"=>$arrDocItem[$h['parent']]));	    				   
        	        }
	        
	        	}	        
            
	    }
	}


	/**
	 * Création des items set OMK suivant les tags
     * @desc ATTENTON OMK-S ne sait pas gérer quand il y a trop d'ITEM SET
	 * @exception ATTENTION au chargement des pages OMK !

	 */
	function setItemSetFromTag(){
	    
	    $this->trace(__METHOD__);
	    
	    //récupère les racines des tags
	    $arrR = $this->dbT->findByNiveau(1);
	    
	    $idRMC = $this->setItemSet(array("idClass"=>23,"titre"=>"Mot-Clefs"));
	    	    	    	    
	    foreach ($arrR as $r) {
	        //récupère l'arboressence des tags
	        $arrH = $this->dbT->getFullChild($r['tag_id']);
	        //pour récupérer la référence du parent
	        $arrTagItem = array();
	        foreach ($arrH as $h) {	            
	            $this->trace($h["tag_id"]." ".$h["code"]);	            
	            if($h["niveau"]==1){
            	        //créer l'itemSet
    	               $h["idClass"] = 23;//collection	            
            	       $idR = $this->setItemSet($h);
            	       $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->isPartOf,"type"=>"resource","value_resource_id"=>$idRMC));
            	       //$this->dbV->ajouter(array("resource_id"=>$idRMC,"property_id"=>$this->hasPart,"type"=>"resource","value_resource_id"=>$idR));            	       
	            }else{
	                $h["idClass"] = 31;//text
	                $idR = $this->setItem($h);	                
	            }
	            //enregistre le lien entre doc et la ressource
	            $arrTagItem[$h['tag_id']]= $idR;
	            //creation de la relation hiérarchique
        	        if(isset($arrTagItem[$h['parent']])){
        	            //vérifie la présence d'un type
        	            if($h['type']){
        	                if(!isset($arrTagItem[$h['type']])){
        	                    $arrTagItem[$h['type']] = $this->setItemSet(array("idClass"=>23,"titre"=>$h['type']));
        	                    $this->dbV->ajouter(array("resource_id"=>$arrTagItem[$h['type']],"property_id"=>$this->isPartOf,"type"=>"resource","value_resource_id"=>$arrTagItem[$h['parent']]));
        	                    //$this->dbV->ajouter(array("resource_id"=>$arrTagItem[$h['parent']],"property_id"=>$this->hasPart,"type"=>"resource","value_resource_id"=>$arrTagItem[$h['type']]));        	                    
        	                }
        	                $idRP = $arrTagItem[$h['type']];
        	            }else{
        	                $idRP = $arrTagItem[$h['parent']];        	                
        	            }
        	            //ajouter le lien entre la class et son parent
        	            $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->isPartOf,"type"=>"resource","value_resource_id"=>$idRP));
        	            //ajouter le lien entre le parent et la class
        	            //$this->dbV->ajouter(array("resource_id"=>$idRP,"property_id"=>$this->hasPart,"type"=>"resource","value_resource_id"=>$idR));
        	        }    	        
	        } 
	        //return 1;
    	        
	    }
	    	    
	}
	
	/**
	 * Création des items set OMK suivant les Existences
	 * @desc ATTENTON OMK-S ne sait pas gérer quand il y a trop d'ITEM SET
	 *
	 */
	function setItemSetFromExi(){
	    
	    $this->trace(__METHOD__);
	    
	    //créer l'itemSet des existences	    
	    $idRA = $this->setItemSet(array("idClass"=>23,"titre"=>"Acteurs"));
	    
	    //récupère les racines des tags
	    $arrR = $this->dbE->getAll();
	    
	    foreach ($arrR as $h) {
	        $this->trace($h["exi_id"]." ".$h["prenom"]." ".$h["nom"]);
	        $h["idClass"] = 94;//person
	        $idR = $this->setItem($h);
            //ajouter le lien entre la class et son parent
	        $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>33,"type"=>"resource","value_resource_id"=>$idRA));

            //return 1;	        
	    }
	    
	}

	/**
	 * Création des items set OMK suivant les géographies
	 * @desc ATTENTON OMK-S ne sait pas gérer quand il y a trop d'ITEM SET
	 *
	 */
	function setItemSetFromGeo(){
	    
	    $this->trace(__METHOD__);
	    
	    //pour éviter les doublons géographiques
	    $this->arrGeo = array();
	    //création des itemSet géo
	    $this->idRGEO = $this->setItemSet(array("idClass"=>23,"titre"=>"Références géographiques"));
	    $this->getItemSetGeo("France", $this->idRGEO);
	    
	    //récupère les racines des tags
	    $arrR = $this->dbG->getAll();
	    
	    foreach ($arrR as $h) {
	        $this->trace($h["geo_id"]." ".$h["adresse"]);
	        $this->setItemSetGeo(false, $h);
	        
	        //return 1;
	    }
	    
	}
	
	
	/**
	 * récupération d'une item geo d'un item set Geo
	 * @param  string  $titre
	 * @param  int     $idP
	 * 
	 * @return int
	 *
	 */
	function getItemSetGeo($titre, $idP){
	    if(!isset($this->arrGeo[$titre])){
        	    $g = $this->dbIS->getByTitre($titre);
        	    if($g){
        	        $this->arrGeo[$titre] = $g[0]["resource_id"];
        	    }else{
        	        //$this->arrGeo[$titre] = $this->setItemSet(array("idClass"=>9,"titre"=>$titre));
        	        $this->arrGeo[$titre] = $this->setItem(array("idClass"=>9,"titre"=>$titre));
        	        //ajoute le lien vers la collection des geos
        	        $this->dbV->ajouter(array("resource_id"=>$this->arrGeo[$titre],"property_id"=>33,"type"=>"resource","value_resource_id"=>$idP));
        	    }
	    }
	    return $this->arrGeo[$titre];
	}
	
	/**
	 * creation d'un item set Geo
	 * @param  array   $i
	 *
	 */
	function setItemSetGeo($idR, $d){
        //décompose l'adresse
	    $pos1 = strpos($d["adresse"], " (");
        if($pos1){
            $pos2 = strpos($d["adresse"], ")");
            $geo = substr($d["adresse"], 0, $pos1);
            $geoP = substr($d["adresse"], $pos1+2, $pos2-$pos1-2);
            $geoPid = $this->arrGeo["France"];
        }else{
            $geo = $d["adresse"];
            $geoPid = $this->idRGEO;
        }
        if(!isset($this->arrGeo[$geo])){
            if($geoPid != $this->idRGEO){
                $this->getItemSetGeo($geoP, $geoPid);
                $this->getItemSetGeo($geo, $this->arrGeo[$geoP]);
            }else 
                $this->getItemSetGeo($geo, $geoPid);
                
	    }
	    //création du lien
	    if($idR)$this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>40,"type"=>"resource","lang"=>"fr","value_resource_id"=>$this->arrGeo[$geo]));	    
	}
	
	/**
	 * creation d'un item set
	 * @param  array   $i
	 *
	 * @return int 
	 */
	function setItemSet($i){
	    
	    //ne pas faire gérer les doublons de titre qui peuvent exister par exemple : "Déplacmeent à Bordeaux"
	    $is = false;//$this->dbIS->getByTitre($i["titre"]);
	    if($is){
	        $idR = $is[0]["resource_id"];
	    }else{
        	    //créer la ressource
        	    $idR = $this->dbOmkR->ajouter(array("resource_type"=>"Omeka\Entity\ItemSet","owner_id"=>$this->idOwner,"is_public"=>1,"resource_class_id"=>$i["idClass"]),false);
        	    //ajouter l'itemSet
        	    $this->dbIS->ajouter(array("id"=>$idR,"is_open"=>1));
        	    //ajoute les valeurs
        	    $this->setValues($i, $idR);
	    }	    
	    return $idR;
	    
	}
	    
	/**
	 * creation d'un item 
	 * @param  array   $i
	 *
	 * @return int
	 */
	function setItem($i){
	    //créer la ressource
	    $idR = $this->dbOmkR->ajouter(array("resource_type"=>"Omeka\Entity\Item","owner_id"=>$this->idOwner,"is_public"=>1,"resource_class_id"=>$i["idClass"]),false);
	    //ajouter l'item
	    $this->dbI->ajouter(array("id"=>$idR));
	    
	    //ajoute les valeurs
	    $this->setValues($i, $idR);
	    
	    return $idR;
	    
	}

	
	/**
	 * creation des valeurs
	 * @param  array   $i
	 * @param  int     $idR
	 *
	 * @return int
	 */
	function setValues($i, $idR){
	    //liens
	    if(isset($i["url"])){
	        $type="literal";
	        if(substr($i["url"],0,4)!="http")$type="uri";
	        $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->isReferencedBy,"type"=>"uri","value"=>$i["url"]));
	    }	    
	    if(isset($i["uri"] )){
	        $type="literal";
	        if(substr($i["uri"],0,4)=="http")$type="uri";
	        $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->isReferencedBy,"type"=>$type,"value"=>$i["uri"]));
	    }
	    if(isset($i["data"] )){
	        $type="literal";
	        if(substr($i["data"],0,4)=="http")$type="uri";
	        $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->description,"type"=>$type,"value"=>$i["data"]));
	    }
	    if(isset($i["desc"] )){
	        $type="literal";
	        if(substr($i["desc"],0,4)=="http")$type="uri";
	        $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->description,"type"=>$type,"value"=>$i["desc"]));
	    }
	    
	    //reference	    
	    if (isset($i["doc_id"]))$this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->reference,"type"=>"literal","value"=>$this->idBase."-flux_doc-doc_id-".$i["doc_id"]));
	    if (isset($i["tag_id"]))$this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->reference,"type"=>"literal","value"=>$this->idBase."-flux_tag-tag_id-".$i["tag_id"]));
	    if (isset($i["exi_id"]))$this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->reference,"type"=>"literal","value"=>$this->idBase."-flux_exi-exi_id-".$i["exi_id"]));
	    
	    //titre
	    if (isset($i["titre"])) $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->title,"type"=>"literal","lang"=>"fr","value"=>$i["titre"]));
	    if (isset($i["code"])) $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->title,"type"=>"literal","lang"=>"fr","value"=>$i["code"]));

	    if (isset($i["nom"])) {
	        $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>145,"type"=>"literal","lang"=>"fr","value"=>$i["nom"]));
	        $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->title,"type"=>"literal","lang"=>"fr","value"=>$i["prenom"]." ".$i["nom"]));
	    }
	    if (isset($i["prenom"])) $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>139,"type"=>"literal","lang"=>"fr","value"=>$i["prenom"]));
	    if (isset($i["nait"])) $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->birthDate,"type"=>"literal","lang"=>"fr","value"=>$i["nait"]));
	    if (isset($i["mort"])) $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->deathDate,"type"=>"literal","lang"=>"fr","value"=>$i["mort"]));
	    	    
	}
	    
	
	/**
	 * renvoit les dates d'une item
	 * @param  int $idDoc
	 * 
	 * @return array
	 *
	 */
	function getItemDate($idDoc){
	    
	    $sql = "SELECT 
                d.doc_id,
                rTd.valeur 'debut',
                rTf.valeur 'fin'
            FROM
                flux_doc d
                    LEFT JOIN
                flux_rapport rTd ON rTd.src_id = d.doc_id
                    AND rTd.src_obj = 'doc'
                    AND rTd.dst_obj = 'tag'
                    AND rTd.dst_id = 4
                    LEFT JOIN
                flux_rapport rTf ON rTf.src_id = d.doc_id
                    AND rTf.src_obj = 'doc'
                    AND rTf.dst_obj = 'tag'
                    AND rTf.dst_id = 5
             WHERE  d.doc_id=".$idDoc;
	    
	    return $this->dbD->exeQuery($sql);
	    
	}
	
	/**
	 * renvoit les lieux d'une item
	 * @param  int $idDoc
	 *
	 */
	function getItemLieux($idDoc){
	    
	    $sql = "SELECT
                d.doc_id,
                g.adresse,
                g.geo_id
            FROM
                flux_doc d
                    LEFT JOIN
                flux_rapport rG ON rG.src_id = d.doc_id
                    AND rG.src_obj = 'doc'
                    AND rG.dst_obj = 'geo'
                    LEFT JOIN
                flux_geo g ON g.geo_id = rG.dst_id
             WHERE  d.doc_id=".$idDoc;
	    
	    return $this->dbD->exeQuery($sql);
	    
	}
	

	/**
	 * renvoit les évaluation d'une monade
	 * @param  int $idMonade
	 *
	 */
	function getEvalsMonade($idMonade){
	    
	    $sql = "select r.rapport_id, r.maj, r.niveau, r.valeur
        	, u.uti_id, u.login
        	, t.tag_id, t.code
        	, d.doc_id, d.url, d.note
        	, dp.doc_id, dp.url
        	, dgp.doc_id, dgp.titre
        	from flux_rapport r
        	inner join flux_doc d on d.doc_id = r.src_id
        	inner join flux_doc dp on dp.doc_id = d.parent
        	inner join flux_doc dgp on dgp.doc_id = dp.parent
        	inner join flux_uti u on u.uti_id = r.pre_id
        	inner join flux_tag t on t.tag_id = r.dst_id
        	where r.monade_id = ".$idMonade;
	    
	    return $this->dbD->exeQuery($sql);
	    
	}
	

	/**
	 * renvoit les évaluations temporelle d'une monade par Tag
	 * @param  int      $idMonade
     * @param  string   $dateUnit
     * @param  string   $dateType
	 *
	 */
	function getEvalsMonadeHistoByTag($idMonade, $dateUnit, $dateType="dateChoix"){
	    
	    if($dateType=="dateChoix")$colTemps = "r.maj";
	    if($dateType=="dateDoc")$colTemps = "r.maj";
	    
	    
	    $sql = "select
        	SUM(r.niveau) value
        	, GROUP_CONCAT(u.uti_id) utis
        	, t.tag_id 'key', t.code 'type', t.desc , t.type color
        	, GROUP_CONCAT(d.doc_id) docs
        	,	DATE_FORMAT(".$colTemps.", '".$dateUnit."') temps
        	,	MIN(UNIX_TIMESTAMP(".$colTemps.")) MinDate
        	,	MAX(UNIX_TIMESTAMP(".$colTemps.")) MaxDate
        	
        	from flux_rapport r
        	inner join flux_doc d on d.doc_id = r.src_id
        	inner join flux_uti u on u.uti_id = r.pre_id
        	inner join flux_tag t on t.tag_id = r.dst_id
        	where r.monade_id = ".$idMonade."
        	GROUP BY t.tag_id, temps
        	ORDER BY temps ";
	    $this->trace($sql);
	    return $this->dbD->exeQuery($sql);
	    
	}
	
	
	/**
	 * renvoit les évaluations temporelle d'une monade par Utilisateur
	 * @param  int      $idMonade
	 * @param  string   $dateUnit
	 * @param  string   $dateType
	 *
	 */
	function getEvalsMonadeHistoByUti($idMonade, $dateUnit, $dateType="dateChoix"){
	    
	    if($dateType=="dateChoix")$colTemps = "r.maj";
	    if($dateType=="dateDoc")$colTemps = "r.maj";
	    
	    
	    $sql = "SELECT
            	SUM(r.niveau) value,
            	GROUP_CONCAT(t.tag_id) tags,
            	u.uti_id 'key',
            	u.login 'type',
            	GROUP_CONCAT(d.doc_id) docs
        	,	DATE_FORMAT(".$colTemps.", '".$dateUnit."') temps
        	,	MIN(UNIX_TIMESTAMP(".$colTemps.")) MinDate
        	,	MAX(UNIX_TIMESTAMP(".$colTemps.")) MaxDate
            	FROM
            	flux_rapport r
            	INNER JOIN
            	flux_doc d ON d.doc_id = r.src_id
            	INNER JOIN
            	flux_uti u ON u.uti_id = r.pre_id
            	INNER JOIN
            	flux_tag t ON t.tag_id = r.dst_id
            	WHERE
            	r.monade_id = ".$idMonade."
            	GROUP BY u.uti_id , temps
            	ORDER BY temps";
	    
        	    $this->trace($sql);
        	    return $this->dbD->exeQuery($sql);
        	    
        	}

        	/**
        	 * renvoit les évaluations temporelle d'une monade par Document
        	 * @param  int      $idMonade
        	 * @param  string   $dateUnit
        	 * @param  string   $dateType
        	 *
        	 */
        	function getEvalsMonadeHistoByDoc($idMonade, $dateUnit, $dateType="dateChoix"){
        	    
        	    if($dateType=="dateChoix")$colTemps = "r.maj";
        	    if($dateType=="dateDoc")$colTemps = "r.maj";
        	    
        	    
        	    $sql = "SELECT 
                SUM(r.niveau) value,
                GROUP_CONCAT(t.tag_id) tags,
                CONCAT(dgp.doc_id,'_',dp.doc_id) 'key',
                CONCAT(dgp.titre, ' : ',dp.titre) 'type',
                dp.note,
                GROUP_CONCAT(d.note) evals,
                GROUP_CONCAT(u.uti_id) utis
                	,	DATE_FORMAT(".$colTemps.", '".$dateUnit."') temps
                	,	MIN(UNIX_TIMESTAMP(".$colTemps.")) MinDate
                	,	MAX(UNIX_TIMESTAMP(".$colTemps.")) MaxDate
            FROM
                flux_rapport r
                    INNER JOIN
                flux_doc d ON d.doc_id = r.src_id
                    INNER JOIN
                flux_doc dp ON dp.doc_id = d.parent
                    INNER JOIN
                flux_doc dgp ON dgp.doc_id = dp.parent
                    INNER JOIN
                flux_uti u ON u.uti_id = r.pre_id
                    INNER JOIN
                flux_tag t ON t.tag_id = r.dst_id
            WHERE
                r.monade_id = ".$idMonade."
            GROUP BY dp.doc_id, temps
            ORDER BY temps";
        	    
        	    $this->trace($sql);
        	    return $this->dbD->exeQuery($sql);
        	    
        	}
        	
        	
        	/**
        	 * renvoit les photos
        	 *
        	 * @param  $where
        	 *
        	 * @return array
        	 *
        	 */
        	function getPhotos($where=""){
        	    $sql = "SELECT * FROM `flux_doc` WHERE `url` LIKE '%medium.jpg%' ".$where." ORDER BY `doc_id`";        	    
        	    $this->trace($sql);
        	    return $this->dbD->exeQuery($sql);
        	}
        	    
        	
        	/**
        	 * renvoit le nombre de photo pour chaque item
        	 * @param  string   $dateUnit
        	 * 
        	 * @return array
        	 *
        	 */
        	function getNbPhoto($dateUnit="%Y-%m-%d"){
        	            	    
        	    
        	    $sql = "SELECT 
                dp.doc_id, dp.titre,
                COUNT(de.doc_id) nbTof,
                DATE_FORMAT(dgprDeb.valeur, '".$dateUnit."') temps,
                MIN(DATEDIFF(DATE_FORMAT(dgprDeb.valeur, '%Y-%m-%d'),
                        FROM_UNIXTIME(0)) * 24 * 3600) MinDate,
                MAX(DATEDIFF(DATE_FORMAT(dgprDeb.valeur, '%Y-%m-%d'),
                        FROM_UNIXTIME(0)) * 24 * 3600) MaxDate,
                MAX(DATEDIFF(DATE_FORMAT(dgprFin.valeur, '%Y-%m-%d'),
                        FROM_UNIXTIME(0)) * 24 * 3600) MaxFinDate
            FROM
                flux_doc dp
                    INNER JOIN
                flux_doc de ON de.lft BETWEEN dp.lft AND dp.rgt
                    AND SUBSTRING(de.url, - 4) = '.jpg'
                    INNER JOIN
                flux_rapport dgprDeb ON dgprDeb.src_id = dp.doc_id
                    AND dgprDeb.src_obj = 'doc'
                    AND dgprDeb.dst_id = 4
                    AND dgprDeb.dst_obj = 'tag'
                    LEFT JOIN
                flux_rapport dgprFin ON dgprFin.src_id = dp.doc_id
                    AND dgprFin.src_obj = 'doc'
                    AND dgprFin.dst_id = 5
                    AND dgprFin.dst_obj = 'tag'
            GROUP BY dp.doc_id , temps";
        	    
        	    $this->trace($sql);
        	    return $this->dbD->exeQuery($sql);
        	    
        	}
        	
        	/**
        	 * exporte une liste de visage pour pour une importation dans Omeka
        	 *
        	 * @param  string  $pathIIIF
        	 * @param  string  &fic
        	 * 
        	 * @return array
        	 *
        	 */
        	function getCsvGoogleVisageToOmk($pathIIIF, $fic){
        	    $this->trace(__METHOD__);
        	    
        	    $sql = "SELECT 
                d.doc_id,
                d.titre,
                d.note,
                d.parent,
                dp.titre titreParent,
                ov.resource_id,
                om.id imageId
            FROM
                flux_doc d
                    INNER JOIN
                flux_doc dp ON dp.doc_id = d.parent
                    INNER JOIN
                ".$this->idBaseOmk.".value ov ON ov.value LIKE '".$this->idBase."-flux_doc-doc_id-%'
                    AND SUBSTRING(ov.value, 31) = dp.doc_id
                    INNER JOIN
                ".$this->idBaseOmk.".media om ON om.item_id = ov.resource_id
            WHERE
                d.tronc = 'visage'
            ORDER BY d.parent";
        	    
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    //foreach ($arr as $h) {
        	    $nb = count($arr);
        	    $arrItem = array();
        	    for ($i = 0; $i < $nb; $i++) {
        	        $this->trace($nb." ".$h["doc_id"]." ".$h["titre"]);
        	        $h = $arr[$i];
        	        $h["idClass"] = $this->idClassImage;//image
        	        $data = json_decode($h["note"]);
        	        $h["titre"] = str_replace('faceAnnotations 0', 'visage', $h["titre"]);
    	            //calcule la position de l'image
    	            $v = $data->boundingPoly->vertices;
    	            $h["url"] = $pathIIIF.$h['imageId'].'/'.$v[0]->x.','.$v[0]->y.','.($v[1]->x - $v[0]->x).','.($v[2]->y - $v[0]->y).'/full/0/default.jpg';
    	            $h["item_set"]=$this->isVisage;
    	            $h["reference"]=$this->idBase."-flux_doc-doc_id-".$h["doc_id"];
    	            unset($h['note']);
    	            unset($h['doc_id']);
    	            unset($h['parent']);
    	            unset($h['titreParent']);
    	            unset($h['imageId']);    	            
    	            $arrItem[] = $h;
    	            /*ajoute l'item
    	            $idR = $this->setItem($h);
    	            //ajoute le lien vers la photo originale
    	            $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->isPartOf,"type"=>"resource","value_resource_id"=>$h['resource_id']));
    	            //ajoute un lien vers l'itemset visage
    	            $this->dbIIS->ajouter(array("item_set"=>$idR,"item_set_id"=>$this->isVisage));
    	            */
        	    }
        	    
        	    //enregistre le csv dans un fichier
        	    $fp = fopen($fic, 'w');
        	    $first = true;
        	    foreach ($arrItem as $v) {
        	        if($first)fputcsv($fp, array_keys($v));
        	        $first=false;
        	        fputcsv($fp, $v);
        	    }
        	    fclose($fp);        	            	    
        	        
        	}
        	
        	
        	/**
        	 * décompose l'analyse des visages de google
        	 *
        	 *
        	 * @return array
        	 *
        	 */
        	function exploseGoogleVisage(){
        	    $this->trace(__METHOD__);
        	    set_time_limit(0);
        	    
        	    $dbVisage = new Model_DbTable_Flux_Visage($this->db);
        	    $dbRepere = new Model_DbTable_Flux_Repere($this->db);
        	    
        	    $sql = "SELECT
                d.doc_id,
                d.titre,
                d.note,
                d.parent
            FROM
                flux_doc d
                    INNER JOIN
                flux_doc dp ON dp.doc_id = d.parent
            WHERE
                d.tronc = 'visage'
            ORDER BY d.doc_id";
        	    
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    //foreach ($arr as $h) {
        	    $nb = count($arr);
        	    $arrItem = array();
        	    for ($i = 9605; $i < $nb; $i++) {
        	        $this->trace($i." ".$h["doc_id"]." ".$h["titre"]);
        	        $h = $arr[$i];
        	        $data = json_decode($h["note"]);
        	        $v = $data->boundingPoly->vertices;
        	        for ($j = 0; $j < 4; $j++) {
        	            if(!isset($v[$j]->x)) $v[$j]->x=0;
        	            if(!isset($v[$j]->y)) $v[$j]->y=0;
        	        }
        	        $r = array("doc_id"=>$h["doc_id"],
           	        "rollAngle"=>$data->rollAngle,
        	            "panAngle"=>$data->panAngle,
        	            "tiltAngle"=>$data->tiltAngle,
        	            "detectionConfidence"=>$data->detectionConfidence,
        	            "landmarkingConfidence"=>$data->landmarkingConfidence,
        	            "joy"=>$data->joyLikelihood,
        	            "sorrow"=>$data->sorrowLikelihood,
            	        "anger"=>$data->angerLikelihood,
            	        "surprise"=>$data->surpriseLikelihood,
            	        "underExposed"=>$data->underExposedLikelihood,
            	        "blurred"=>$data->blurredLikelihood,
            	        "headwear"=>$data->headwearLikelihood,
        	            "v0x"=>$v[0]->x,
        	            "v0y"=>$v[0]->y,
        	            "v1x"=>$v[1]->x,
        	            "v1y"=>$v[1]->y,
        	            "v2x"=>$v[2]->x,
        	            "v2y"=>$v[2]->y,
        	            "v3x"=>$v[3]->x,
        	            "v3y"=>$v[3]->y);        	            
        	        $dbVisage->ajouter($r);
        	        foreach ($data->landmarks as $l) {
        	            $p = $l->position;
        	            if(!isset($p->x)) $p->x=0;
        	            if(!isset($p->y)) $p->y=0;
        	            if(!isset($p->z)) $p->z=0;        	            
        	            $dbRepere->ajouter(array("doc_id"=>$h["doc_id"],"type"=>$l->type,"x"=>$p->x, "y"=>$p->y, "z"=>$p->z));
        	        }
        	        
        	    }
        	            	    
        	}
        	
        	/**
        	 * enregistre les analyses de photo faite par google
        	 *
        	 * @return array
        	 *
        	 */
        	function getAnalyseGooglePhoto(){
        	    $this->trace(__METHOD__);
        	    set_time_limit(0);
        	    
        	    //récupère les items
        	    $arr = $this->getPhotos();
        	    //création de l'analyseur
        	    $g = new Flux_Gvision($this->idBase);
        	    $idTagLA = $this->dbT->ajouter(array('code'=>'labelAnnotations','parent'=>$g->idTagRoot));
        	    $idTagWE = $this->dbT->ajouter(array('code'=>'webEntities','parent'=>$g->idTagRoot));
        	    $numItem = 0;
        	    foreach ($arr as $item) {
        	        if($item["doc_id"]>=5981){
        	            $this->trace($item["doc_id"]);
        	            $c = json_decode($g->analyseImage($item['url']), true);
            	        foreach ($c['responses'][0] as $k => $r) {
            	            $this->trace($numItem.' '.$k);            	            
            	            switch ($k) {        	                
            	                case 'logoAnnotations':
            	                    $i=0;
            	                    foreach ($r as $fa) {
            	                        //création d'un document par visage
            	                        $this->dbD->ajouter(array("parent"=>$item["doc_id"],"titre"=>$k." ".$numItem." ".$i, 'tronc'=>'logo',"note"=>json_encode($fa)));
            	                        $i++;
            	                    }
            	                    break;
            	                case 'landmarkAnnotations':
            	                    $i=0;
            	                    foreach ($r as $fa) {
            	                        //création d'un document par visage
            	                        $this->dbD->ajouter(array("parent"=>$item["doc_id"],"titre"=>$k." ".$numItem." ".$i, 'tronc'=>'paysage',"note"=>json_encode($fa)));
            	                        $i++;
            	                    }
            	                    break;
            	                case 'faceAnnotations':
            	                    $i=0;
            	                    foreach ($r as $fa) {
            	                        //création d'un document par visage
            	                        $this->dbD->ajouter(array("parent"=>$item["doc_id"],"titre"=>$k." ".$numItem." ".$i, 'tronc'=>'visage',"note"=>json_encode($fa)));
            	                        $i++;
            	                    }        	                    
            	                    break;
            	                case 'labelAnnotations':
            	                    foreach ($r as $la) {
                	                    $idTag = $this->dbT->ajouter(array('code'=>$la['description'],'uri'=>$la['mid'],'parent'=>$idTagLA));
                	                    $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                	                        ,"src_id"=>$item["doc_id"],"src_obj"=>"doc"
                	                        ,"dst_id"=>$idTag,"dst_obj"=>"tag"
                	                        ,"pre_id"=>$g->idMonade,"pre_obj"=>"monade"
                	                        ,"valeur"=>$la['score']
                	                    ));        	  
            	                    }
            	                break;
            	                case 'imagePropertiesAnnotation':
            	                    //enregistre l'analyse
            	                    $this->dbD->ajouter(array("parent"=>$item["doc_id"],"titre"=>$k." ".$numItem,"note"=>json_encode($r)));
            	                    break;
            	                case 'cropHintsAnnotations':
            	                    $this->dbD->ajouter(array("parent"=>$item["doc_id"],"titre"=>$k." ".$numItem,"note"=>json_encode($r)));
            	                    break;
            	                case 'webDetection':
            	                    foreach ($r['webEntities'] as $we) {
            	                        if(isset($we['description'])){
                	                        $idTag = $this->dbT->ajouter(array('code'=>$we['description'],'uri'=>$we['entityId'],'parent'=>$idTagWE));
                	                        $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                	                            ,"src_id"=>$item["doc_id"],"src_obj"=>"doc"
                	                            ,"dst_id"=>$idTag,"dst_obj"=>"tag"
                	                            ,"pre_id"=>$g->idMonade,"pre_obj"=>"monade"
                	                            ,"valeur"=>$we['score']
                	                        ));
            	                        }            	                        
            	                    }
            	                    break;
            	            }
            	        }
        	        }
        	    }
        	    $numItem++;
        	}
        	
        	/**
        	 * enregistre les analyses de language faite par google
        	 * 
        	 * @param  string  $champ
        	 * @param  string  $query
        	 *
        	 * @return array
        	 *
        	 */
        	function getAnalyseGoogle($champ="titre", $query="parent photo"){
        	    $this->trace(__METHOD__);
        	    set_time_limit(0);
        	    //récupère les références
        	    //récupère les items
        	    if($query=="parent photo")$arr = $this->getNbPhoto();
        	    if($query=="titre photo")$arr = $this->getPhotos(" AND titre not like 'photo %' ");
        	    //création de l'analyseur
        	    $gl = new Flux_Glanguage($this->idBase);
        	    $idTagSent = $this->dbT->ajouter(array('code'=>'sentiment','parent'=>$gl->idTagRoot));
        	    $idTagSyntaxe = $this->dbT->ajouter(array('code'=>'syntaxe','parent'=>$gl->idTagRoot));
        	    $numItem = 0;
        	    foreach ($arr as $item) {
        	        //pour gérer la reprise 
        	        if($numItem > -1){
        	            $this->trace($numItem." ".$item["doc_id"]." ".$item[$champ]);
        	            //execute et sauve l'analyse uniquement sur les types possible en français
            	        $analyses = $gl->sauveAnalyseTexte($item[$champ], $item["doc_id"], array('analyzeEntities','analyzeSentiment','analyzeSyntax'));
            	        //décompose l'analyse
            	        $notes = json_decode($analyses["note"], true);
        	            //enregistre les data
        	            $i = 0;
        	            foreach ($notes as $k => $prop) {
        	                $this->trace($k);
        	                switch ($k) {
        	                    case 'analyzeSyntax':
        	                        //enregistre l'analyse
        	                        $this->dbD->ajouter(array("parent"=>$analyses["doc_id"],"titre"=>$k." ".$numItem,"note"=>json_encode($prop)));
        	                    break;
        	                    case 'analyzeEntities':
        	                        foreach ($prop as $p => $v) {
        	                            $desc = "";
        	                            if (array_key_exists('wikipedia_url', $v['metadata'])) {
        	                                $desc = $v['metadata']['wikipedia_url'];
        	                            }
        	                            $uri = "";
        	                            if (array_key_exists('mid', $v['metadata'])) {
        	                                $uri = $v['metadata']['mid'];
        	                            }
        	                            if($v['type']=="PERSON"){
        	                                //enregistre une existence
        	                                $id = $this->dbE->ajouter(array("nom"=>$v['name'],'url'=>$uri,'data'=>$desc));
        	                                $obj = "exi";
        	                            }elseif($v['type']=="LOCATION" && $desc != ""){
        	                                //enregistre une géographie
        	                                $id = $this->dbG->ajouter(array("adresse"=>$v['name'],'uri'=>$uri,'data'=>$desc));
        	                                $obj = "geo";    	                                
        	                            }else{
            	                            //enregistre le tag
            	                            $id = $this->dbT->ajouter(array('code'=>$v['name'],'type'=>$v['type'],'parent'=>$gl->idTagRoot,'desc'=>$desc,'uri'=>$uri));
            	                            $obj = "tag";
        	                            }
            	                        //enregistre le rapport
        	                            $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
        	                                ,"src_id"=>$analyses["doc_id"],"src_obj"=>"doc"
        	                                ,"dst_id"=>$id,"dst_obj"=>$obj
        	                                ,"pre_id"=>$gl->idMonade,"pre_obj"=>"monade"
        	                                ,"niveau"=>$v['salience']
        	                                ,"valeur"=>json_encode($v)
        	                            ));    	                                
        	                        }
        	                    break;
        	                    case 'analyzeSentiment':
        	                        //sentiment global du document
        	                        $idSent = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
        	                           ,"src_id"=>$analyses["doc_id"],"src_obj"=>"doc"
        	                           ,"dst_id"=>$idTagSent,"dst_obj"=>"tag"
        	                           ,"pre_id"=>$gl->idMonade,"pre_obj"=>"monade"
        	                           ,"niveau"=>$prop['magnitude']
        	                           ,"valeur"=>$prop['score']
        	                                ));    	                       
        	                        $numSent = 0;
        	                        if(isset($prop->sentences)){
            	                        foreach ($prop->sentences as $p => $v) {    	                            
            	                            //création du document si le texte est différent.
            	                            $cnt = $v["text"]["content"];
            	                            if($cnt != $item[$champ]){
            	                                $idDocSent = $this->dbD->ajouter(array("parent"=>$analyses["doc_id"], "tronc"=>$v["text"]["beginOffset"],"titre"=>"Phrase :".$idSent."_".$numSent,"note"=>json_encode($v)));    	                                
            	                            }else{
            	                                $idDocSent = $item["doc_id"];
            	                            }
            	                            //sentiment de la phrase
            	                            $idSent = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
            	                                ,"src_id"=>$idDocSent,"src_obj"=>"doc"
            	                                ,"dst_id"=>$idTagSent,"dst_obj"=>"tag"
            	                                ,"pre_id"=>$gl->idMonade,"pre_obj"=>"monade"
            	                                ,"niveau"=>$v["sentiment"]['magnitude']
            	                                ,"valeur"=>$v["sentiment"]['score']
            	                            ));  
            	                        }
        	                        }
        	                    break;    	                        
        	                }
        	                $i++;
        	            }
        	            $this->trace('analyse sauvée');
            	        //attend pour éviter de stresser google
            	        sleep(1);
        	        }
        	        $numItem ++;
        	    }
        	}
        	    
        	/**
        	 * récupère les données pour chaque photo
        	 *
        	 * @param  boolean $bParent
        	 *
        	 * @return array
        	 *
        	 */
        	function getPhotosDatas($bParent=true){
        	    
        	    $this->trace(__METHOD__);
        	    
        	    //recupère les données pour les photos
        	    $sql = "SELECT 
                d.doc_id,
                d.titre,
                d.url,
                d.parent,
                MIN(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d')) temps,
                MIN(DATEDIFF(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d'),
                        FROM_UNIXTIME(0)) * 24 * 3600) MinDate,
                GROUP_CONCAT(DISTINCT rGoS.valeur) score,
                GROUP_CONCAT(DISTINCT rGoS.niveau) magnitude,
                COUNT(DISTINCT dv.doc_id) nbVisage,
                COUNT(DISTINCT t.tag_id) nbTag,
                GROUP_CONCAT(DISTINCT CONCAT(t.tag_id, '_', t.code)) tags,
                COUNT(DISTINCT e.exi_id) nbExi,
                GROUP_CONCAT(DISTINCT CONCAT(e.exi_id,
                            '_',
                            IFNULL(e.prenom, ''),
                            ' ',
                            e.nom)) exis,
                COUNT(DISTINCT g.geo_id) nbGeo,
                GROUP_CONCAT(DISTINCT CONCAT(g.geo_id, '_', g.adresse)) geos
            FROM
                flux_doc d
                    INNER JOIN
                flux_rapport rDeb ON rDeb.src_id = d.parent
                    AND rDeb.src_obj = 'doc'
                    AND rDeb.dst_obj = 'tag'
                    AND rDeb.dst_id = 4
                    LEFT JOIN
                flux_doc dGo ON dGo.parent = d.doc_id
                    AND dGo.titre = 'Flux_Glanguage_Flux_Glanguage::sauveAnalyseTexte'
                    LEFT JOIN
                flux_rapport rGoS ON rGoS.src_id = dGo.doc_id
                    AND rGoS.src_obj = 'doc'
                    AND rGoS.dst_obj = 'tag'
                    AND rGoS.dst_id = 7
                    LEFT JOIN
                flux_rapport rGoT ON rGoT.src_id = dGo.doc_id
                    AND rGoT.src_obj = 'doc'
                    AND rGoT.dst_obj = 'tag'
                    AND rGoT.dst_id <> 7
                    LEFT JOIN
                flux_tag t ON t.tag_id = rGoT.dst_id
                    LEFT JOIN
                flux_rapport rGoE ON rGoE.src_id = dGo.doc_id
                    AND rGoE.src_obj = 'doc'
                    AND rGoE.dst_obj = 'exi'
                    LEFT JOIN
                flux_exi e ON e.exi_id = rGoE.dst_id
                    LEFT JOIN
                flux_rapport rGoG ON rGoG.src_id = dGo.doc_id
                    AND rGoG.src_obj = 'doc'
                    AND rGoG.dst_obj = 'geo'
                    LEFT JOIN
                flux_geo g ON g.geo_id = rGoG.dst_id
                    LEFT JOIN
                flux_doc dv ON dv.parent = d.doc_id
            WHERE
                SUBSTRING(d.url, - 4) = '.jpg'
            GROUP BY d.doc_id
            ORDER BY nbExi DESC";
        	    
            //LIMIT 40";
        	    
        	    $this->trace($sql);
        	    $arr =  $this->dbD->exeQuery($sql);
        	    
        	    if($bParent){
        	        $arrP = $this->getThemeDatas();
        	    }
        	    
        	    //compilation des résultats
        	    $result = array();
        	    foreach ($arr as $p) {
        	        $this->trace($p['doc_id']." ".$p['titre']);
        	        $p['tags']=$this->concatToArray($p['tags']);
        	        $p['exis']=$this->concatToArray($p['exis']);
        	        $p['geos']=$this->concatToArray($p['geos']);
        	        if ($bParent){
        	            $parent = $arrP[$p['parent']];
        	            $p['theme'] = $parent['titre'];
        	            $p['tagsTheme']=$parent['tags'];
        	            $p['exisTheme']=$parent['exis'];
        	            $p['geosTheme']=$parent['geos'];
        	            $p['scoreTheme']=$parent['score'];
        	            $p['magnitudeTheme']=$parent['magnitude'];
        	        }
        	        $result[]=$p;
        	    }
        	    return $result;
        	}

        	/**
        	 * transforme un group_concat en array
        	 *
        	 * @param  string  $lbl
        	 *
        	 * @return array
        	 *
        	 */
        	function concatToArray($d){
        	    if($d){        	        
                $r = array();
        	        $e = explode(',', $d);
        	        foreach ($e as $v) {
        	            $l = explode('_', $v);
        	            //version complexe pour intéraction
        	            //$r[]=array('id'=>$l[0],'lbl'=>$l[1]);
        	            //version simple pour keshif
        	            $r[]=$l[1];
        	        }
        	        $d = $r;
        	    }
        	    return $d;
        	}
        	
        	
        	/**
        	 * récupère les données pour un document
        	 *
        	 * @param  int  $idDoc
        	 *
        	 * @return array
        	 *
        	 */
        	function getDocDatas($idDoc){
        	    
        	    //recupère les données pour les photos
        	    $sql = "SELECT 
                    d.doc_id,
                    d.titre,
                    d.url,
                    dp.doc_id idParent,
                    dp.titre titreParent,
                    MIN(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d')) temps,
                    MIN(DATEDIFF(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d'),
                            FROM_UNIXTIME(0)) * 24 * 3600) MinDate,
                    GROUP_CONCAT(DISTINCT rGoS.valeur) score,
                    GROUP_CONCAT(DISTINCT rGoS.niveau) magnitude,
                    COUNT(DISTINCT dv.doc_id) nbVisage,
                    COUNT(DISTINCT t.tag_id) nbTag,
                    GROUP_CONCAT(DISTINCT CONCAT(t.tag_id, '_', t.code)) tags,
                    COUNT(DISTINCT e.exi_id) nbExi,
                    GROUP_CONCAT(DISTINCT CONCAT(e.exi_id,
                                '_',
                                IFNULL(e.prenom, ''),
                                ' ',
                                e.nom)) exis,
                    COUNT(DISTINCT g.geo_id) nbGeo,
                    GROUP_CONCAT(DISTINCT CONCAT(g.geo_id, '_', g.adresse)) geos
                FROM
                    flux_doc d
                        INNER JOIN
                    flux_doc dp ON dp.doc_id = d.parent
                        INNER JOIN
                    flux_rapport rDeb ON rDeb.src_id = dp.doc_id
                        AND rDeb.src_obj = 'doc'
                        AND rDeb.dst_obj = 'tag'
                        AND rDeb.dst_id = 4
                        LEFT JOIN
                    flux_doc dGo ON dGo.parent = d.doc_id
                        AND dGo.titre = 'Flux_Glanguage_Flux_Glanguage::sauveAnalyseTexte'
                        LEFT JOIN
                    flux_rapport rGoS ON rGoS.src_id = dGo.doc_id
                        AND rGoS.src_obj = 'doc'
                        AND rGoS.dst_obj = 'tag'
                        AND rGoS.dst_id = 7
                        LEFT JOIN
                    flux_rapport rGoT ON rGoT.src_id = dGo.doc_id
                        AND rGoT.src_obj = 'doc'
                        AND rGoT.dst_obj = 'tag'
                        AND rGoT.dst_id <> 7
                        LEFT JOIN
                    flux_tag t ON t.tag_id = rGoT.dst_id
                        LEFT JOIN
                    flux_rapport rGoE ON rGoE.src_id = dGo.doc_id
                        AND rGoE.src_obj = 'doc'
                        AND rGoE.dst_obj = 'exi'
                        LEFT JOIN
                    flux_exi e ON e.exi_id = rGoE.dst_id
                        LEFT JOIN
                    flux_rapport rGoG ON rGoG.src_id = dGo.doc_id
                        AND rGoG.src_obj = 'doc'
                        AND rGoG.dst_obj = 'geo'
                        LEFT JOIN
                    flux_geo g ON g.geo_id = rGoG.dst_id
                        LEFT JOIN
                    flux_doc dv ON dv.parent = d.doc_id
                    
                            LEFT JOIN
                    flux_rapport rGeo ON rGeo.src_id = d.doc_id
                        AND rGeo.src_obj = 'doc'
                        AND rGeo.dst_obj = 'geo'
                        LEFT JOIN
                	flux_geo gAN ON g.geo_id = rGeo.dst_id
                        LEFT JOIN
                    flux_rapport rExi ON rExi.src_id = d.doc_id
                        AND rExi.src_obj = 'doc'
                        AND rExi.dst_obj = 'exi'
                        LEFT JOIN
                	flux_exi eAN ON e.exi_id = rExi.dst_id
                
                WHERE
                    d.doc_id = ".$idDoc;
        	    
        	    //$this->trace($sql);
        	    return $this->dbD->exeQuery($sql);
        	    
        	}
        	        	
        	/**
        	 * récupère les données pour un thème
        	 *
        	 *
        	 * @return array
        	 *
        	 */
        	function getThemeDatas(){
        	    
        	    //recupère les données pour les photos
        	    $sql = "SELECT 
                    d.doc_id,
                    d.titre,
                    GROUP_CONCAT(DISTINCT rGoS.valeur) score,
                    GROUP_CONCAT(DISTINCT rGoS.niveau) magnitude,
                    COUNT(DISTINCT dv.doc_id) nbVisage,
                    COUNT(DISTINCT t.tag_id) nbTag,
                    GROUP_CONCAT(DISTINCT CONCAT(t.tag_id, '_', t.code)) tags,
                    COUNT(DISTINCT e.exi_id) nbExi,
                    GROUP_CONCAT(DISTINCT CONCAT(e.exi_id,
                                '_',
                                IFNULL(e.prenom, ''),
                                ' ',
                                e.nom)) exis,
                    COUNT(DISTINCT g.geo_id) nbGeo,
                    GROUP_CONCAT(DISTINCT CONCAT(g.geo_id, '_', g.adresse)) geos
                    FROM
                    flux_doc d    
                        INNER JOIN
                    flux_doc dGo ON dGo.parent = d.doc_id
                        AND dGo.titre = 'Flux_Glanguage_Flux_Glanguage::sauveAnalyseTexte'
                        LEFT JOIN
                    flux_rapport rGoS ON rGoS.src_id = dGo.doc_id
                        AND rGoS.src_obj = 'doc'
                        AND rGoS.dst_obj = 'tag'
                        AND rGoS.dst_id = 7
                        LEFT JOIN
                    flux_rapport rGoT ON rGoT.src_id = dGo.doc_id
                        AND rGoT.src_obj = 'doc'
                        AND rGoT.dst_obj = 'tag'
                        AND rGoT.dst_id <> 7
                        LEFT JOIN
                    flux_tag t ON t.tag_id = rGoT.dst_id
                        LEFT JOIN
                    flux_rapport rGoE ON rGoE.src_id = dGo.doc_id
                        AND rGoE.src_obj = 'doc'
                        AND rGoE.dst_obj = 'exi'
                        LEFT JOIN
                    flux_exi e ON e.exi_id = rGoE.dst_id
                        LEFT JOIN
                    flux_rapport rGoG ON rGoG.src_id = dGo.doc_id
                        AND rGoG.src_obj = 'doc'
                        AND rGoG.dst_obj = 'geo'
                        LEFT JOIN
                    flux_geo g ON g.geo_id = rGoG.dst_id
                        LEFT JOIN
                    flux_doc dv ON dv.parent = d.doc_id    
                WHERE d.type is null
                GROUP BY d.doc_id
                ";
        	    
        	    //$this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    $arrP = array();
    	        foreach ($arr as $p) {
    	            $this->trace("PARENT ".$p['doc_id']." ".$p['titre']);
    	            $p['tags']=$this->concatToArray($p['tags']);
    	            $p['exis']=$this->concatToArray($p['exis']);
    	            $p['geos']=$this->concatToArray($p['geos']);
    	            $arrP[$p['doc_id']]=$p;
    	        }
        	    
    	        return $arrP;
        	    
        	}
        	
        	
}
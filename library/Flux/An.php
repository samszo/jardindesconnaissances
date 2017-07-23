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
    var $dbOmk;
    
    /**
     * Constructeur de la classe
     *
     * @param  string $idBase
     * 
     */
	public function __construct($idBase=false, $idBaseOmk=false, $bTrace=false)
    {
        $this->dbOmk = $this->getDb($idBaseOmk);
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
		//
		if($id=='c-cimw17fjw-198351nl5y6ey'){
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
							$ds = explode('/',$d);
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
					    $deb = $arrFicNum[2]+0;
					    $fin = $arrFicNum[5]+0;
					    for ($i = $deb; $i <= $fin; $i++) {
					        $numFic = str_pad($i, 4, "0", STR_PAD_LEFT);					        
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
	 * @param  int     $idDoc
	 * @param  string  $fic
	 *
	 * @return void
	 */
	function getCsvToOmeka($idDoc, $fic){
	    
	    $this->trace(__METHOD__." ".$fic);
	    $arrItem = array();

	    //récupère l'arboressence des documents
	    $arrH = $this->dbD->getFullChild($idDoc);	    
	    
	    //construction du tableau pour le csv
	    $i=0;
	    foreach ($arrH as $h) {
	        if($h["niveau"]>4 && substr($h["url"],0,4)=="http" && $i<10){
	            //récupère l'item set du parent
	            $is = $this->dbIS->getByIdentifier("flux_an-flux_doc-doc_id-".$h["parent"]);	            
	            $path_parts = pathinfo($h["url"]);
	            $arrItem[] = array("Item-set"=>$is[0]["resource_id"],"owner"=>$this->owner ,"dcterms:title"=>$h["titre"]
	                ,"referenceAN"=>$h["url"]
	                ,"referenceJDC"=>"flux_an-flux_doc-doc_id-".$h["doc_id"]
	                ,"file"=>$path_parts["basename"],"dcterms:type"=>"image");	        	        
	        }
        	    $pTitre = $h["titre"];
        	    $i++;
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
	    $this->dbR = new Model_DbTable_Omk_Resource($this->dbOmk);
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
	        elseif (substr($h["url"],0,4)=="http")$h["idClass"] = 26;//image
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
        	        $this->arrGeo[$titre] = $this->setItemSet(array("idClass"=>9,"titre"=>$titre));
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
	    //création du lien avec l'itemset
	    $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>40,"type"=>"resource","lang"=>"fr","value_resource_id"=>$this->arrGeo[$geo]));	    
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
        	    $idR = $this->dbR->ajouter(array("resource_type"=>"Omeka\Entity\ItemSet","owner_id"=>$this->idOwner,"is_public"=>1,"resource_class_id"=>$i["idClass"]),false);
        	    //ajouter l'itemSet
        	    $this->dbIS->ajouter(array("id"=>$idR,"is_open"=>1));
        	    //reference
        	    if(isset($i["url"])){
            	    $type="literal";
            	    if(substr($i["url"],0,4)=="http")$type="uri";
            	    $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>10,"type"=>$type,"value"=>$i["url"]));
        	    }
        	    if (isset($i["doc_id"]))$this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>10,"type"=>"literal","value"=>"flux_an-flux_doc-doc_id-".$i["doc_id"]));
        	        
        	    //titre
        	    $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>1,"type"=>"literal","lang"=>"fr","value"=>$i["titre"]));
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
	    $idR = $this->dbR->ajouter(array("resource_type"=>"Omeka\Entity\Item","owner_id"=>$this->idOwner,"is_public"=>1,"resource_class_id"=>$i["idClass"]),false);
	    //ajouter l'item
	    $this->dbI->ajouter(array("id"=>$idR));
	    //reference
	    if($i["url"]){
	        $type="literal";
	        if(substr($i["url"],0,4)!="http")$type="uri";
	        $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>10,"type"=>"uri","value"=>$i["url"]));
	    }
	    //titre
	    $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>1,"type"=>"literal","lang"=>"fr","value"=>$i["titre"]));
	    
	    return $idR;
	    
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
	
}
<?php
// use function GuzzleHttp\json_encode;
use function Composer\Autoload\includeFile;

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
    var $iiif;
    
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
     * Enregistre les photos qui ne sont pas dans l'ordre d'extraction de l'inventaire 
     * par exemple : 
     * http://www.siv.archives-nationales.culture.gouv.fr/mm/media/download/FRAN_0023_00278_L-medium.jpg
     * qui n'est pas mentionné dans l'inventaire FRAN_IR_055457
   <c id="c-x2twmou2-1rvji4s7mvdoa">
   <did>
    <unitid type="identifiant">AG/5(2)/977/N1</unitid>
     <unittitle>Reportage n° 2165 / Chasse déléguée à Marly : portrait de M. Bourges (23 octobre 1969).</unittitle>
     <unitdate calendar="gregorian" era="ce" normal="1969-10-23">23 octobre 1969</unitdate>
     <physdesc>
     <extent>1 négatif 6 x 6</extent>
     </physdesc>
    </did>
    <daogrp>
     <daoloc href="FRAN_0023_00275_L.msp"/>
    </daogrp>
   </c>
   <c id="c-xn77kaat-1m45su1l08e5">
   <did>
    <unitid type="identifiant">AG/5(2)/977/N1</unitid>
     <unittitle>Reportage n° 2165 / Chasse déléguée à Marly : portrait de M. Lebel (23 octobre 1969).</unittitle>
     <unitdate calendar="gregorian" era="ce" normal="1969-10-23">23 octobre 1969</unitdate>
     <physdesc>
     <extent>1 négatif 6 x 6</extent>
     </physdesc>
    </did>
    <daogrp>
     <daoloc href="FRAN_0023_00281_L.msp"/>
    </daogrp>
   </c>
     * 
     *
     *
     * @return array
     */
    public function sauvePhotosAbscentes()
    {
        $this->trace(__METHOD__." ".$fic);
        
        set_time_limit(0);
        
        $sql = "SELECT * FROM (
                SELECT 
                    COUNT(*) nb,
                    GROUP_CONCAT(d.doc_id) ids,
                    SUBSTRING(d.url,
                        (LOCATE('_', d.url) + 1),
                        LOCATE('_', d.url, LOCATE('_', d.url) + 1) - LOCATE('_', d.url) - 1) as prefixe,
                    SUBSTRING(d.url,
                        (LOCATE('_', d.url, LOCATE('_', d.url) + 1) + 1),
                        LOCATE('_L', d.url)-(LOCATE('_', d.url, LOCATE('_', d.url) + 1) + 1)) as num,
                    SUBSTRING(d.url,
                        (LOCATE('_', d.url) + 1),
                        LOCATE('_L', d.url) - LOCATE('_', d.url) - 1) court,
                    d.url
                FROM
                    flux_doc d
                WHERE
                    d.type = 1
                GROUP BY d.url
                ) idx
                ORDER BY prefixe, num";
        $arr = $this->dbD->exeQuery($sql);
        $c = count($arr);
        for ($i = 0; $i < $c; $i++) {
            if(!isset($arr[$i+1])) return;
            $diff = $arr[$i+1]['num']-$arr[$i]['num'];
            if($arr[$i]['prefixe']==$arr[$i+1]['prefixe'] && $diff > 1){
                $ids = explode(',', $arr[$i]['ids']);
                for ($j = 1; $j < $diff; $j++) {
                    $numZero = strlen($arr[$i]['num']);                    
                    $numFic = str_pad($arr[$i]['num']+$j, $numZero, "0", STR_PAD_LEFT);
                    $fic = 'FRAN_'.$arr[$i]['prefixe'].'_'.$numFic.'_L-medium.jpg';
                    $url = $this->urlBaseTof.$fic;                                        
                    $this->trace('manque : '.$url);
                    //vérifie si le fichier existe
                    $doc = $this->dbD->findByUrl($url);
                    if($doc){
                        $this->trace("Le fichier existe",$doc);
                    }else{
                        //récupère le n° d'inventaire
                        $idsParent = $this->dbD->getFullParent($ids[0]);
                        //récupère le parent des abscents
                        $idDocAbs = $this->dbD->ajouter(array("titre"=>'Manques'
                            ,"parent"=>$idsParent[1]['doc_id']));
                        //ajoute la photo
                        $idDocTof = $this->dbD->ajouter(array("url"=>$url
                            ,"titre"=>'Manque : '.$i.'_'.$j
                            ,"type"=>1
                            ,"tronc"=>$arr[$i]['prefixe'].'_'.$arr[$i]['num'].'/'.$arr[$i+1]['prefixe'].'_'.$arr[$i+1]['num']
                            ,"parent"=>$idDocAbs));
                        //enregistre l'image
                        $img = ROOT_PATH.'/data/AN/photos/'.$fic;
                        if (!file_exists($img)){
                            $res = file_put_contents($img, file_get_contents($url));
                            $this->trace($res." ko : Fichier créé ".$url." ".$img);
                        }
                        //on n'enregistre pas de rapport.
                   }
                }                    
            }
        }
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
		
		if($id=='c-vsu391k0--y9g8wqbr3e83' || $id=='c-21r6uib53-1dypx4p4a5zd4'){
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
						$idDocSerie = $this->dbD->ajouter(array("url"=>'//*[@id="'.$id.'"]'
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
					//pour la gestion du nombre de photo quand ce n'est pas préciser autrement
					$rs = $cn->getElementsByTagName('physdesc');
					foreach ($rs as $r) {
					    foreach($r->childNodes as $rcn){
					        if ($rcn->nodeType == XML_ELEMENT_NODE) {					            
					            if ($rcn->tagName == "extent") {
					                $nbTof = $rcn->nodeValue;
					                $nbTof = explode(" ",$nbTof);
					                //pour gérer les description physique sans chiffre
					                if (is_numeric($nbTof[0]))
    					                   $nbTof = $nbTof[0]+0;
					                else
					                    $nbTof = 1;
					            }
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
					$nbFic = 0; 
					$ficnumArr = []; 
					foreach($tofs as $tof){
				        $ficnumArr[] = $tof->getAttribute('href');
				        $this->trace("nbFic=".$nbFic.":".$tof->getAttribute('href'));
					    $nbFic ++;
					}
					if($nbFic > 1){
					    $toto = 1;
					}
					//boucle sur les localisation de media
					foreach ($ficnumArr as $ficnum) {
        					$arrFicNum = explode("_",$ficnum);
        					if(count($this->arrItem)){
        					    //gestion des photo  à partir de la liste d'item
        					    for ($i = 0; $i < count($this->arrItem); $i++) {
        					        $numFic = str_pad($this->arrItem[$i]['num'], 4, "0", STR_PAD_LEFT);
        					        $this->arrItem[$i]['fic']=$arrFicNum[0]."_".$arrFicNum[1]."_".$numFic."_L-medium.jpg";
        					        $this->arrItem[$i]['tronc']=$arrFicNum[0]."_".$arrFicNum[1];
        					    }
        					}elseif ($arrFicNum[0]=="http://www.siv.archives-nationales.culture.gouv.fr/siv/media/FRAN"){
        					    //gestion des photos sans précision du nombre 
        					    //par exemple Charles De Gaulle : https://www.siv.archives-nationales.culture.gouv.fr/siv/IR/FRAN_IR_054722
        					    $arrPath = explode("/",$ficnum);
        					    $pathBase = $arrPath[count($arrFicNum)-1];
        					    $numZero = strlen($arrFicNum[4]);					    
        					    $deb = $arrFicNum[4]+0;
        					    //qaund la fin est précisée dans le deuxième fichier
        					    if($ficnum1){
        					        $arrFicNum1 = explode("_",$ficnum1);
        					        $fin = $arrFicNum1[4]+0;
        					    }else
                					    $fin = $deb+$nbTof;
        					    $j=0;					    
        					    for ($i = $deb; $i < $fin; $i++) {
        					        //ATTENTION le nombre de 0 varie suivant les collections
        					        $numFic = str_pad($i, $numZero, "0", STR_PAD_LEFT);
        					        $this->arrItem[$j]['fic']="FRAN_".$arrFicNum[3]."_".$numFic."_L-medium.jpg";
        					        $this->arrItem[$j]['text']="photo ".$numFic;
        					        $this->arrItem[$j]['tronc'] = $arrPath[5];
        					        $j++;
        					    }
        					}elseif ($nbTof==1){
        					    $this->arrItem[0]['fic']=$arrFicNum[0]."_".$arrFicNum[1]."_".$arrFicNum[2]."_L-medium.jpg";
        					    $this->arrItem[0]['text']="photo ".$arrFicNum[2];
        					    $this->arrItem[0]['tronc'] = $arrFicNum[0]."_".$arrFicNum[1];					    
        					}else{
        					    //gestion des photos directement par 
        					    //<daoloc href="FRAN_0138_2365_L.msp#FRAN_0138_2394_L.msp"/>
        					    //ATTENTION le nombre de 0 varie suivant les collections
        					    $numZero = strlen($arrFicNum[2]);
        					    $deb = $arrFicNum[2]+0;
        					    //dans le cas où il n'y a qu'un fichier
        					    if(isset($arrFicNum[5]))
            					    $fin = $arrFicNum[5]+0;
        					    else 
        					        $fin = $deb;
        					    for ($i = $deb; $i <= $fin; $i++) {
        					        //ATTENTION le nombre de 0 varie suivant les collections
        					        $numFic = str_pad($i, $numZero, "0", STR_PAD_LEFT);					        
        					        $this->arrItem[]=array('fic'=>$arrFicNum[0]."_".$arrFicNum[1]."_".$numFic."_L-medium.jpg",
            					           'text'=>"photo ".$numFic,
        					               'tronc'=> $arrFicNum[0]."_".$arrFicNum[1]);
        					        $this->trace($arrFicNum[0]."_".$arrFicNum[1]."_".$numFic."_L-medium.jpg");
        					    }					        
        					}
        					//enregistre les documents
        					foreach ($this->arrItem as $item) {
        					    $url = $this->urlBaseTof.$item['fic'];
        					    if($url=='http://www.siv.archives-nationales.culture.gouv.fr/mm/media/download/FRAN_0158_0387_L-medium.jpg'){
                                    $toto = 1;					        
        					    }
        					    $idDocTof = $this->dbD->ajouter(array("url"=>$url
        								,"titre"=>$item['text']
        						        ,"type"=>1
        						        ,"tronc"=>$item['tronc']
        								,"parent"=>$idDocSerie));
        						//enregistre l'image						
        						$img = ROOT_PATH.'/data/AN/photos/'.$item['fic'];
        						if (!file_exists($img)){ 
        							$res = file_put_contents($img, file_get_contents($url));
        							$this->trace($res." ko : Fichier créé ".$url." ".$img);						
        						}						
        						//création des rapports entre
        						// src = le document
        						// dst = la photo
        						// pre = le document root
        						//valeur = la date si elle est présente
        						$this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
        								,"src_id"=>$idDocSerie,"src_obj"=>"doc"
        								,"dst_id"=>$idDocTof,"dst_obj"=>"doc"
        								,"pre_id"=>$idDoc,"pre_obj"=>"doc"
            						        ,"valeur"=>isset($item['date']) ? $item['date'] : ""
        						));	
        						//création de la geo si elle existe
        						if(isset($item['geo'])){
        						    $idGeo = $this->dbG->ajouter(array("adresse"=>$item['geo']));
        						    //création des rapports entre
        						    // src = le document
        						    // dst = la géo
        						    // pre = le document série
        						    $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
        						        ,"src_id"=>$idDocTof,"src_obj"=>"doc"
        						        ,"dst_id"=>$idGeo,"dst_obj"=>"geo"
        						        ,"pre_id"=>$idDocSerie,"pre_obj"=>"doc"
        						    ));						    
        						}
        					}
        					$this->arrItem = array();
					}
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
			    $geo = "";
			    $date = "";
				if ($sc->tagName == "p") {
					//récupère les infos plus précise
				    $strP = trim($sc->nodeValue);
				    $arrP = explode('.',$strP);
				    $geo = $arrP[0];
				    $date = isset($arrP[1]) ? $arrP[1] : "";
				}
				if ($sc->tagName == "list") {
					//récupère les items
					foreach($sc->childNodes as $s){
						if ($s->nodeType == XML_ELEMENT_NODE) {
							if ($s->tagName == "item") {
								$strItem = $s->nodeValue;
								$strItem = str_replace('*','',$strItem);
								$arrStrItem = explode(":", $strItem);
								$arrNum = explode("-", $arrStrItem[0]);
								$deb = trim(str_replace(' ','',str_replace('n°','',$arrNum[0])))+0;
								$fin = isset($arrNum[1]) ? trim(str_replace(' ','',str_replace('n°','',$arrNum[1]))) : $deb;
								$nb = $fin-$deb;
								for ($i = 0; $i <= $nb; $i++) {
									$num = $deb+$i;
									if($num==477){
										$toto = 1;
									}
									$this->arrItem[] = array("date"=>$date,"geo"=>$geo,"text"=>$arrStrItem[1],"num"=>sprintf("%'.04d", $num));
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
	    //$arrH = $this->getPhotos();	    

	    $sql = "SELECT
                d.doc_id,
                d.titre,
                d.url,
                d.parent
            FROM
                flux_doc d
            WHERE d.type = 1 AND d.maj > '2018-01-22'";
	    
	    $arr = $this->dbD->exeQuery($sql);
	    $nb = count($arr);
	    
	    //foreach ($arrH as $h) {
	    for ($i = 0; $i < $nb; $i++) {
	        $h = $arr[$i];
            //récupère l'item set du parent
            //$is = $this->dbIS->getByIdentifier($this->idBase."-flux_doc-doc_id-".$h["parent"]);	            
            $path_parts = pathinfo($h["url"]);
            if(substr($h["url"],0,4)=="http"){ 
                $arrItem[] = array("dcterms:isPartOf"=>5468,"owner"=>$this->owner ,"dcterms:title"=>$h["titre"]
    	                ,"dcterms:isReferencedBy"=>$h["url"]
    	                ,"dcterms:identifier"=>$this->idBase."-flux_doc-doc_id-".$h["doc_id"]
    	                ,"file"=>$path_parts["basename"],"dcterms:type"=>"image");	        	        
            }
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
	    
	    $sql = "SELECT 
    r.rapport_id,
    r.maj,
    r.niveau,
    r.valeur,
    u.uti_id,
    u.login,
    t.tag_id,
    t.code,
    d.doc_id,
    d.url,
    d.note,
    d.tronc,
    dp.doc_id,
    dp.note pNote,
    dp.url,
    dgp.doc_id,
    dgp.titre
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
ORDER BY d.tronc
";
	    
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
        	, GROUP_CONCAT(DISTINCT d.parent) docsP
        	, GROUP_CONCAT(DISTINCT d.doc_id) docs
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
	 * @param  int      $idTag
	 *
	 */
	function getEvalsMonadeHistoByUti($idMonade, $dateUnit, $dateType="dateChoix", $idTag=false){
	    
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
            	r.monade_id = ".$idMonade;
	    if($idTag) $sql .= "	AND t.tag_id = ".$idTag;         
         $sql .= "
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
        	    $sql = "SELECT * FROM `flux_doc` WHERE d.type = 1 ".$where." ORDER BY `doc_id`";        	    
        	    $this->trace($sql);
        	    return $this->dbD->exeQuery($sql);
        	}
        	    
        	
        	/**
        	 * renvoit le nombre de photo pour chaque item
        	 * @param  string   $dateUnit
        	 * @param  string   $q
        	 * 
        	 * @return array
        	 *
        	 */
        	function getNbPhoto($dateUnit="%Y-%m-%d",$q='par doc',$w=""){
        	            	    
        	    
        	    if($q=='total'){
        	        $sql = "SELECT COUNT(doc_id) nbTof
                FROM flux_doc
                WHERE type = 1";        	        
        	    }elseif ($w){
        	            $sql = "SELECT dp.doc_id, dp.titre,
                    COUNT(de.doc_id) nbTof
                    FROM flux_doc dp
                        INNER JOIN
                    flux_doc de ON de.parent = dp.doc_id AND de.type = 1 ".$w."
                    GROUP BY dp.doc_id
                    ";
        	    }else{        	    
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
                        AND de.type = 1
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
        	    }        	    
        	    $this->trace($sql);
        	    return $this->dbD->exeQuery($sql);
        	    
        	}
        	
        	/**
        	 * renvoit la hiérarchie des séries et le nombre de photo poru chaque série
        	 * @param  string   $idDoc

        	 * @return array
        	 *
        	 */
        	function getTreemapPhoto($idDoc){
        	    
        	    $c = str_replace("::", "_", __METHOD__)."_".$idDoc;
        	    $data = $this->cache->load($c);
        	    if(!$data){
        	        
        	        $sql = "SELECT
            	    d.doc_id,
            	    d.titre,
            	    de.niveau + 1 - d.niveau niv,
            	    de.type,
            	    de.titre tE,
            	    de.doc_id tId,
        	        COUNT(DISTINCT dpe.doc_id) nbEnf,
        	        COUNT(DISTINCT dt.doc_id) nbTof
        	    FROM
        	    flux_doc d
            	    INNER JOIN
            	    flux_doc de ON de.lft BETWEEN d.lft AND d.rgt
                LEFT JOIN
                flux_doc dpe ON dpe.parent = de.doc_id
            	    LEFT JOIN
            	    flux_doc dt ON dt.parent = de.doc_id AND dt.type = 1
        	    WHERE
            	    d.doc_id = ".$idDoc." and de.type is null
        	    GROUP BY niv , de.type, de.titre, de.doc_id
        	    ORDER BY de.doc_id";
        	        
        	        $this->trace($sql);
        	        $arr =  $this->dbD->exeQuery($sql);
        	        
        	        $this->fin = false;
        	        $treemap = $this->getTreemapPhotoChildren($arr);
        	        
        	        $data = $treemap[0];
            	    
            	    $this->cache->save($data, $c);
            	}
            	return $data;
        	    
        	}
        	   
        
        	
        	/**
        	 * fonction récursive pour les noeuds du treemap
        	 * @param  array   $arr
        	 * @param  array   $t
        	 * @param  int     $i
        	 * 
        	 * @return array
        	 *
        	 */
        	function getTreemapPhotoChildren($arr, $i=0){
        	    while (isset($arr[$i+1]) && $arr[$i+1]["niv"] >= $arr[$i]["niv"] ){
        	        $te = array("name"=>$arr[$i]['tE'],"i"=>$i,"idDoc"=>$arr[$i]['tId'],"niv"=>$arr[$i]['niv']);
        	        if($arr[$i]["nbTof"]){
        	            $te["size"]=$arr[$i]['nbTof'];
        	        }elseif ($arr[$i]["nbEnf"]){ //on gère les séries qui n'on pas de photo
        	            $arrC = $this->getTreemapPhotoChildren($arr, $i+1);
        	            //ajoute les enfants
        	            $te["children"]=$arrC;
        	            
        	            //calcule le nouveau $i
        	            $nbC = count($te["children"]);
        	            $te["i"] = $te["children"][$nbC-1]["i"];        	            
        	            $i=$te["i"];
        	            
        	            //vérifie qu'il ne faut pas monter d'un niveau supplémentaire
        	            if(isset($arr[$i+1]) && $arr[$i+1]["niv"] < $te["niv"]){
        	                array_push($t,$te);
        	                return $t;
        	            }        	            
        	        }
        	        if(!isset($t))$t = array();
        	        array_push($t,$te);
        	        $i++;
        	    }
        	    //ajoute le dernier enfant 
        	    if(isset($arr[$i]) ){
        	        if($arr[$i]["nbTof"]){
            	        if(!isset($t))$t = array();
            	        array_push($t,array("name"=>$arr[$i]['tE'],"size"=>$arr[$i]['nbTof'],"i"=>$i,"idDoc"=>$arr[$i]['tId'],"niv"=>$arr[$i]['niv']));
            	        if($arr[$i]['tId']=="4181"){
            	            $toto = 1;
            	        }
        	        }
        	    }else{
        	        $this->fin = true;
        	    }
        	    return $t;
        	}
        	    
        	/**
        	 * renvoit le nombre de visage
        	 *
        	 * @return array
        	 *
        	 */
        	function getNbVisage(){
            $sql = "SELECT COUNT(doc_id) nbVisage
                FROM flux_doc
                WHERE tronc = 'visage'";

        	    $this->trace($sql);
        	    return $this->dbD->exeQuery($sql);        	    
        	}
        	
        	/**
        	 * exporte une liste de visage pour pour une importation dans Omeka
        	 *
        	 * @param  string  $pathIIIF
        	 * @param  string  $fic
        	 * @param  string  $ficFail
        	 * @param  boolean $ajoutAbscent
        	 * 
        	 * @return array
        	 *
        	 */
        	function getCsvGoogleVisageToOmk($pathIIIF, $fic, $ficFail="", $ajoutAbscent=false){
        	    $this->trace(__METHOD__);
        	    
        	    if($ficFail) require_once($ficFail);
        	    
        	    /*pour récupérer les visages qui ne sont pas dans OMK = $ajoutAbscent = true
        	     INSERT INTO  test (id) 
        	     SELECT SUBSTRING(ov.value, 31)    
             FROM omk_valarnum1.value ov 
             WHERE ov.value LIKE 'flux_valarnum-flux_doc-doc_id-%'
        	     */        	    
        	    
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
                ".$this->idBaseOmk.".media om ON om.item_id = ov.resource_id ";
        	    if($ajoutAbscent) $sql .= " LEFT JOIN test t on t.id = d.doc_id ";
            $sql .= " WHERE d.tronc = 'visage' ";
        	    if($ajoutAbscent) $sql .= " AND t.id is null "; 
            $sql .= " ORDER BY d.parent";
        	    
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    //foreach ($arr as $h) {
        	    $nb = count($arr);
        	    $arrItem = array();
        	    for ($i = 0; $i < $nb; $i++) {
        	        $h = $arr[$i];
        	        $h["idClass"] = $this->idClassImage;//image
        	        $data = json_decode($h["note"]);
        	        $h["titre"] = str_replace('faceAnnotations 0', 'visage', $h["titre"]);
        	        //calcule la position de l'image
        	        $v = $data->boundingPoly->vertices;
        	        if($ficFail && !in_array($h['imageId'], $arrFail) ){
        	            $this->trace("PAS FAIL");
        	        }elseif($ficFail && isset($v[0]->x) && isset($v[0]->y) && isset($v[1]->x) && isset($v[2]->y)){
        	                $this->trace("FAIL OK");
        	        }else{
        	            $this->trace($nb." ".$h["doc_id"]." ".$h["titre"]);
        	            //met des 0 pour les valeurs abscentes
        	            if(!isset($v[0]->x))$v[0]->x=0;
        	            if(!isset($v[0]->y))$v[0]->y=0;
        	            if(!isset($v[1]->x))$v[1]->x=0;
        	            if(!isset($v[1]->y))$v[1]->y=0;
        	            if(!isset($v[2]->x))$v[2]->x=0;
        	            if(!isset($v[2]->y))$v[2]->y=0;
        	            //construction de l'url
        	            $h["url"] = $pathIIIF.$h['imageId'].'/'.$v[0]->x.','.$v[0]->y.','.($v[1]->x - $v[0]->x).','.($v[2]->y - $v[0]->y).'/full/0/default.jpg';
        	            $h["item_set"]=$this->isVisage;
        	            $h["reference"]=$this->idBase."-flux_doc-doc_id-".$h["doc_id"];
        	            unset($h['note']);
        	            unset($h['doc_id']);
        	            unset($h['parent']);
        	            unset($h['titreParent']);
        	            unset($h['imageId']);    	            
        	            $arrItem[] = $h;
        	            $this->trace($h["url"]);        	            
        	            /*ajoute l'item
        	            $idR = $this->setItem($h);
        	            //ajoute le lien vers la photo originale
        	            $this->dbV->ajouter(array("resource_id"=>$idR,"property_id"=>$this->isPartOf,"type"=>"resource","value_resource_id"=>$h['resource_id']));
        	            //ajoute un lien vers l'itemset visage
        	            $this->dbIIS->ajouter(array("item_set"=>$idR,"item_set_id"=>$this->isVisage));
        	            */
        	        }
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
                    LEFT JOIN
                flux_visage v ON v.doc_id = d.doc_id
            WHERE
                d.tronc = 'visage' AND v.doc_id is null
            ORDER BY d.doc_id";
        	    /*pour ensuite mettre à jour la table des visages avec les url
        	     UPDATE flux_visage v
                        INNER JOIN
                    omk_valarnum1.value ov ON ov.value LIKE 'flux_valarnum-flux_doc-doc_id-%'
                        AND SUBSTRING(ov.value, 31) = v.doc_id
                        INNER JOIN
                    omk_valarnum1.media om ON om.item_id = ov.resource_id 
             SET 
                    v.url = CONCAT('http://gapai.univ-paris8.fr/ValArNum/omks/files/original/',
                            om.storage_id,
                            '.',
                            om.extension),
                    v.source = om.source
        	     */
        	    
        	    
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    //foreach ($arr as $h) {
        	    $nb = count($arr);
        	    $arrItem = array();
        	    for ($i = 0; $i < $nb; $i++) {
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
        	 * migre les analyses de photo faite par google
        	 *
        	 * @param  $idBaseSrc    string
        	 * @param  $idBaseDst    string
        	 * 
        	 * @return array
        	 *
        	 */
        	function migreAnalyseGooglePhoto($idBaseSrc, $idBaseDst){
        	    $this->trace(__METHOD__);
        	    set_time_limit(0);

        	    $dbDst = $this->getDb($idBaseDst);
        	    $dbDocDst = new Model_DbTable_Flux_Doc($dbDst);
        	    
        	    $dbSrc = $this->getDb($idBaseSrc);
        	    $dbDocSrc = new Model_DbTable_Flux_Doc($dbSrc);
        	    
        	    /* Problème de manque
        	    $sql = "SELECT 
                    d.doc_id,
                    d.url,
                    d.titre,
                    COUNT(DISTINCT dv.doc_id) nbDv,
                    GROUP_CONCAT(DISTINCT dv.doc_id) dvIds,
                    COUNT(DISTINCT dp.doc_id) nbDp,
                    GROUP_CONCAT(DISTINCT dp.doc_id) dpIds
                FROM
                    flux_doc d
                        INNER JOIN
                    flux_doc dv ON dv.parent = d.doc_id
                        AND (dv.titre LIKE 'imagePropertiesAnnotation%'
                        OR dv.titre LIKE 'faceAnnotations%'
                        OR dv.titre LIKE 'landmarkAnnotations%'
                        OR dv.titre LIKE 'logoAnnotations%')
                        INNER JOIN
                    ".$idBaseDst.".flux_doc dp ON dp.url = d.url
                GROUP BY d.doc_id
                ORDER BY d.doc_id";
        	    $arr = $dbDocSrc->exeQuery($sql);
        	    */
        	    
        	    $sql = "SELECT 
                    d.doc_id,
                    dpv.titre, dpv.tronc, dpv.note
                FROM
                    flux_doc d
                        INNER JOIN
                    ".$idBaseSrc.".flux_doc dp ON dp.url = d.url
                        INNER JOIN
                    ".$idBaseSrc.".flux_doc dpv ON dpv.parent = dp.doc_id
                        AND (dpv.titre LIKE 'imagePropertiesAnnotation%'
                        OR dpv.titre LIKE 'faceAnnotations%'
                        OR dpv.titre LIKE 'landmarkAnnotations%'
                        OR dpv.titre LIKE 'logoAnnotations%')
                        LEFT JOIN
                    flux_doc dv ON dv.parent = d.doc_id
                        AND (dv.titre LIKE 'imagePropertiesAnnotation%'
                        OR dv.titre LIKE 'faceAnnotations%'
                        OR dv.titre LIKE 'landmarkAnnotations%'
                        OR dv.titre LIKE 'logoAnnotations%')
                WHERE
                    d.type = 1 AND dv.doc_id IS NULL
                ORDER BY d.doc_id";
        	    $this->trace($sql);        	    
        	    $arr = $dbDocDst->exeQuery($sql);
        	    
        	    foreach ($arr as $v) {
        	        if($v['doc_id'] >= -1){
    	                $id = $dbDocDst->ajouter(
    	                    array("titre"=>$v['titre'],"parent"=>$v['doc_id']
    	                       ,'tronc'=>$v['tronc'] ? $v['tronc'] : 'Google Vision'
    	                       ,"note"=>$v['note'])
    	                    ,false);
    	                $this->trace('--- '.$id.' = '.$v['doc_id']." ".$v['titre']);
    	            }
        	    }
        	
        	}
        	
        	/**
        	 * migre les mots clefs de photo faite par google
        	 *
        	 * @param  $idBaseSrc    string
        	 * @param  $idBaseDst    string
        	 *
        	 * @return array
        	 *
        	 */
        	function migreAnalyseGooglePhotoMC($idBaseSrc, $idBaseDst){
        	    $this->trace(__METHOD__);
        	    set_time_limit(0);
        	            	    
        	    $dbSrc = $this->getDb($idBaseSrc);
        	    $dbDocSrc = new Model_DbTable_Flux_Doc($dbSrc);
        	    
        	    $dbDst = $this->getDb($idBaseDst);
        	    $dbDocDst = new Model_DbTable_Flux_Doc($dbDst);
        	    
        	    $g = new Flux_Gvision($idBaseDst);
        	    
        	    $arrTagP['labelAnnotations'] = $this->dbT->ajouter(array('code'=>'labelAnnotations','parent'=>$g->idTagRoot));
        	    $arrTagP['webEntities'] = $this->dbT->ajouter(array('code'=>'webEntities','parent'=>$g->idTagRoot));
        	    
        	    $sql = "SELECT 
                    r.pre_id,
                    r.valeur,
                    t.code,
                    t.uri,
                    tp.code TagP,
                    dp.doc_id
                FROM
                    flux_doc d
                        INNER JOIN
                    ".$idBaseDst.".flux_doc dp ON dp.url = d.url
                        INNER JOIN
                    flux_rapport r ON r.src_id = d.doc_id
                        AND r.src_obj = 'doc'
                        AND r.dst_obj = 'tag'
                        AND r.pre_obj = 'monade'
                        INNER JOIN
                    flux_tag t ON t.tag_id = r.dst_id
                        INNER JOIN
                    flux_tag tp ON tp.tag_id = t.parent
                        AND tp.code IN ('webEntities' , 'labelAnnotations')
                WHERE dp.type = 1
                ORDER BY dp.doc_id";
        	    $this->trace($sql);
        	    $arr = $dbDocSrc->exeQuery($sql);
        	    
        	    foreach ($arr as $v) {
        	        if($v['doc_id'] >= -1){
        	            $idTag = $this->dbT->ajouter(array('code'=>$v['code'],'uri'=>$v['uri'],'parent'=>$arrTagP['TagP']));
        	            $id = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
        	                ,"src_id"=>$v['doc_id'],"src_obj"=>"doc"
        	                ,"dst_id"=>$idTag,"dst_obj"=>"tag"
        	                ,"pre_id"=>$g->idMonade,"pre_obj"=>"monade"
        	                ,"valeur"=>$v['valeur']
        	            ),false);
        	            $this->trace('--- '.$id.' = '.$v['doc_id']." ".$v['code']);
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
        	        if($item["doc_id"]>=19397){
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
        	 * enregistre les extraction de mots clefs
        	 *
        	 * @param  string  $champ
        	 *
        	 * @return array
        	 *
        	 */
        	function getMC($champ="titre"){
        	    $this->trace(__METHOD__);
    	        //pour optimiser le nombre d'appel
    	        $sql = "SELECT
                    COUNT(*) nb, ".$champ.", GROUP_CONCAT(doc_id) ids
                FROM
                    flux_doc
                WHERE
                    titre NOT LIKE 'Photo %'
                GROUP BY ".$champ."
                ORDER BY nb DESC";
        	    $arr = $this->dbD->exeQuery($sql);
        	    $numItem = 0;
        	    $mc = new Flux_MC($this->idBase);
        	    foreach ($arr as $item) {
        	        $ids = explode(',', $item['ids']);
        	        $mc->saveForChaine($ids, $item[$champ],"","alchemy");
        	    }
        	    
        	    
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
        	function getAnalyseGoogle($champ="titre", $query="sans doublons"){
        	    $this->trace(__METHOD__);
        	    set_time_limit(0);
        	    //récupère les références
        	    //récupère les items
        	    if($query=="parent photo")$arr = $this->getNbPhoto();
        	    if($query=="titre photo")$arr = $this->getPhotos(" AND titre not like 'photo %' ");
        	    if($query=="last import")$arr = $this->getNbPhoto("%Y-%m-%d",'par doc'," AND de.maj > '2018-01-22' ");
        	    if($query=="mini"){
            	    //pour optimiser le nombre d'appel à Google        	    
            	    $sql = "SELECT 
                        COUNT(*) nb, ".$champ.", GROUP_CONCAT(doc_id) ids
                    FROM
                        flux_doc
                    WHERE
                        titre NOT LIKE 'Photo %'
                    GROUP BY ".$champ."
                    ORDER BY nb DESC";
        	    }
        	    if($query=="sans doublons"){
        	        //pour optimiser le nombre d'appel à Google
        	        $sql = "SELECT 
                    COUNT(*) nb,
                    GROUP_CONCAT(d.doc_id) ids,
                    SUBSTRING(d.titre, (LOCATE('/',d.titre)+2)) titre
                FROM
                    flux_doc d
                        LEFT JOIN
                	flux_doc dg ON dg.parent = d.doc_id
                        AND dg.titre = 'Flux_Glanguage::sauveAnalyseTexte'
                WHERE
                	dg.doc_id is null and
                    d.titre NOT LIKE 'Photo %' AND d.titre NOT LIKE 'Flux_Glanguage::sauveAnalyseTexte' AND d.titre NOT LIKE 'analyzeSyntax %' AND d.titre != 'Flux_Glanguage' AND d.titre != 'Manques' AND d.titre NOT LIKE 'manque : %'
                GROUP BY SUBSTRING(d.titre, (LOCATE('/',d.titre)+2))
                ORDER BY titre";
        	    }
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    //création de l'analyseur
        	    $gl = new Flux_Glanguage($this->idBase);
        	    $numItem = 0;
        	    foreach ($arr as $item) {
        	        //pour gérer la reprise 
        	        if($numItem >= -1 ){        	            
        	            $this->trace($numItem." ".$item["ids"]." ".$item[$champ]);
        	            $arrIds = explode(',',$item["ids"]);        	            
        	            //execute et sauve l'analyse uniquement sur les types possible en français
        	            $analyses = $gl->sauveAnalyseTexte($item[$champ], false, array('analyzeEntities','analyzeSentiment','analyzeSyntax'),$arrIds);
        	            //décompose l'analyse
        	            $this->exploseAnalyseGoogle($arrIds, json_decode($analyses["note"],true), $gl);
            	        //attend pour éviter de stresser google
            	        sleep(1);
        	        }
        	        $numItem ++;
        	    }
        	}

        	
        	/**
        	 * actualise les analyses Google
        	 *
        	 *
        	 * @return void
        	 *
        	 */
        	function actualiseAnalyseGoogle(){
        	    
        	    $this->trace(__METHOD__);
        	    
        	    $sql = "SELECT parent, note, doc_id FROM flux_doc WHERE titre LIKE 'Flux_Glanguage::sauveAnalyseTexte'";
    	        $arr = $this->dbD->exeQuery($sql);
    	        $this->trace($sql);
    	        
    	        //création de l'analyseur
    	        $gl = new Flux_Glanguage($this->idBase);
    	        $numItem = 0;
    	        foreach ($arr as $a) {
    	            $this->exploseAnalyseGoogle(array($a['doc_id']), json_decode($a["note"],true), $gl);    	            
    	        }
        	            
    	        $this->trace(__METHOD__ ." FIN");
    	        
        	}
        	
        	
        	 /**
        	 * explose les analyses Google
        	 *
        	 * @param  array     $arrIds
        	 * @param  object    $notes
        	 *
        	 * @return array
        	 *
        	 */
        	function exploseAnalyseGoogle($arrIds, $notes, $gl){

        	    //décompose l'analyse
        	    $i = 0;
        	    foreach ($notes as $k => $prop) {
        	        switch ($k) {
        	            case 'analyzeSyntax':
        	                //enregistre l'analyse pour chaque document
        	                foreach ($arrIds as $idDoc) {
        	                    $this->dbD->ajouter(array("parent"=>$idDoc,"titre"=>$k." ".$numItem,"note"=>json_encode($prop)));
        	                }
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
        	                    foreach ($arrIds as $idDoc) {
        	                        $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
        	                            ,"src_id"=>$idDoc,"src_obj"=>"doc"
        	                            ,"dst_id"=>$id,"dst_obj"=>$obj
        	                            ,"pre_id"=>$gl->idMonade,"pre_obj"=>"monade"
        	                            ,"niveau"=>$v['salience']
        	                            ,"valeur"=>json_encode($v)
        	                        ));
        	                    }
        	                }
        	                break;
        	            case 'analyzeSentiment':
        	                //sentiment global du document
        	                foreach ($arrIds as $idDoc) {
        	                    $idSent = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
        	                        ,"src_id"=>$idDoc,"src_obj"=>"doc"
        	                        ,"dst_id"=>$gl->idTagSent,"dst_obj"=>"tag"
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
        	                                $idDocSent = $this->dbD->ajouter(array("parent"=>$idDoc, "tronc"=>$v["text"]["beginOffset"],"titre"=>"Phrase :".$idSent."_".$numSent,"note"=>json_encode($v)));
        	                            }else{
        	                                $idDocSent = $item["doc_id"];
        	                            }
        	                            //sentiment de la phrase
        	                            $idSent = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
        	                                ,"src_id"=>$idDocSent,"src_obj"=>"doc"
        	                                ,"dst_id"=>$gl->idTagSent,"dst_obj"=>"tag"
        	                                ,"pre_id"=>$gl->idMonade,"pre_obj"=>"monade"
        	                                ,"niveau"=>$v["sentiment"]['magnitude']
        	                                ,"valeur"=>$v["sentiment"]['score']
        	                            ));
        	                        }
        	                    }
        	                }
        	                break;
        	        }
        	        $this->trace('analyse sauvée : '.$i." ".$k);
        	        $i++;
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
                    COUNT(DISTINCT dGo.doc_id) nbGanalyse,
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
                    flux_doc dGo ON dGo.parent = d.parent
                        AND dGo.titre = 'Flux_Glanguage::sauveAnalyseTexte'
                        LEFT JOIN
                    flux_rapport rGoS ON rGoS.src_id = dGo.doc_id
                        AND rGoS.src_obj = 'doc'
                        AND rGoS.dst_obj = 'tag'
                        AND rGoS.dst_id = 4
                        LEFT JOIN
                    flux_rapport rGoT ON rGoT.src_id = dGo.doc_id
                        AND rGoT.src_obj = 'doc'
                        AND rGoT.dst_obj = 'tag'
                        AND rGoT.dst_id <> 4
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
                    d.type = 1
                GROUP BY d.doc_id
                ORDER BY d.doc_id DESC";
        	    
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
        	 * récupère les données pour chaque visage
        	 *
        	 * @param  int         $deb
        	 * @param  int         $nb
        	 * @param  boolean     $bCount
        	 *
        	 * @return array
        	 *
        	 */
        	function getVisagesDatas($deb="",$nb="",$bCount=false){
        	    
        	    $this->trace(__METHOD__);
        	    
        	    if($bCount){
        	        $sql = "SELECT 
                        COUNT(DISTINCT v.visage_id) nbVisage
                    FROM
                        flux_visage v
                            INNER JOIN
                        omk_valarnum1.value ov ON ov.value LIKE 'flux_valarnum-flux_doc-doc_id-%'
                            AND SUBSTRING(ov.value, 31) = v.doc_id
                            INNER JOIN
                        omk_valarnum1.media om ON om.item_id = ov.resource_id
                            INNER JOIN
                        flux_doc dv ON dv.doc_id = v.doc_id
                            INNER JOIN
                        flux_doc d ON d.doc_id = dv.parent
                            INNER JOIN
                        flux_rapport rDeb ON rDeb.src_id = d.parent
                            AND rDeb.src_obj = 'doc'
                            AND rDeb.dst_obj = 'tag'
                            AND rDeb.dst_id = 4
                    ";
        	    }else{
            	    //recupère les données pour les photos
            	    $sql = "SELECT 
            dv.doc_id,
            MIN(d.parent) gpId,
            MIN(d.doc_id) pId,
            MIN(d.titre) label,
            MIN(d.url) original,
            MIN(dp.titre) theme,
            MIN(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d')) temps,
            MIN(DATEDIFF(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d'),
                    FROM_UNIXTIME(0)) * 24 * 3600) MinDate,
            dv.titre,
            om.source imgFull,
            om.item_id idOmk,
            COUNT(v.visage_id) nbVisage,
            SUM(FIND_IN_SET(v.joy,
                    'VERY_UNLIKELY,UNLIKELY,POSSIBLE,LIKELY,VERY_LIKELY')) / COUNT(v.visage_id) joie,
            SUM(FIND_IN_SET(v.anger,
                    'VERY_UNLIKELY,UNLIKELY,POSSIBLE,LIKELY,VERY_LIKELY')) / COUNT(v.visage_id) colere,
            SUM(FIND_IN_SET(v.surprise,
                    'VERY_UNLIKELY,UNLIKELY,POSSIBLE,LIKELY,VERY_LIKELY')) / COUNT(v.visage_id) surprise,
            SUM(FIND_IN_SET(v.sorrow,
                    'VERY_UNLIKELY,UNLIKELY,POSSIBLE,LIKELY,VERY_LIKELY')) / COUNT(v.visage_id) ennui,
            SUM(FIND_IN_SET(v.blurred,
                    'VERY_UNLIKELY,UNLIKELY,POSSIBLE,LIKELY,VERY_LIKELY')) / COUNT(v.visage_id) flou,
            SUM(FIND_IN_SET(v.headwear,
                    'VERY_UNLIKELY,UNLIKELY,POSSIBLE,LIKELY,VERY_LIKELY')) / COUNT(v.visage_id) chapeau        	            	        
            FROM
                flux_doc d
                    INNER JOIN
                flux_rapport rDeb ON rDeb.src_id = d.parent
                    AND rDeb.src_obj = 'doc'
                    AND rDeb.dst_obj = 'tag'
                    AND rDeb.dst_id = 4
                    INNER JOIN
                flux_doc dp ON dp.doc_id = d.parent
                    INNER JOIN
                flux_doc dv ON dv.parent = d.doc_id
                    INNER JOIN
                flux_visage v ON v.doc_id = dv.doc_id
                    INNER JOIN
                omk_valarnum1.value ov ON ov.value LIKE 'flux_valarnum-flux_doc-doc_id-%'
                    AND SUBSTRING(ov.value, 31) = dv.doc_id
                    INNER JOIN
                omk_valarnum1.media om ON om.item_id = ov.resource_id
            WHERE
                d.type = 1 
            GROUP BY dv.doc_id, om.item_id, om.source  ";
        	    
        	        if($deb=='alea'){
            	        $sql .= " ORDER BY RAND() LIMIT ".$nb;
            	    }else{
            	        $sql .= " ORDER BY MinDate ";          	        
                    if($deb!="")$sql .=" LIMIT ".$deb;                
                    if($nb!="")$sql .=",".$nb;
            	    }
            	    //LIMIT 40";
        	    }        	    
        	    $this->trace($sql);
        	    $arr =  $this->dbD->exeQuery($sql);
        	            	    
        	    
        	    return $arr;
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
                        AND dGo.titre = 'Flux_Glanguage::sauveAnalyseTexte'
                        LEFT JOIN
                    flux_rapport rGoS ON rGoS.src_id = dGo.doc_id
                        AND rGoS.src_obj = 'doc'
                        AND rGoS.dst_obj = 'tag'
                        AND rGoS.dst_id = 4
                        LEFT JOIN
                    flux_rapport rGoT ON rGoT.src_id = dGo.doc_id
                        AND rGoT.src_obj = 'doc'
                        AND rGoT.dst_obj = 'tag'
                        AND rGoT.dst_id <> 4
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


        	/**
        	 * calcule la complexité de l'écosystème
        	 *
        	 * @param  int         $idDoc
        	 * @param  int         $idTag
        	 * @param  int         $idExi
        	 * @param  int         $idGeo
        	 * @param  int         $idMonade
        	 * @param  int         $idRapport
        	 * @param  boolean     $cache
        	 *
        	 * @return array
        	 *
        	 */
        	function getComplexEcosystem($idDoc=0, $idTag=0, $idExi=0, $idGeo=0, $idMonade=0, $idRapport=0, $cache=true){
        	    
        	    $result = false;
        	    $c = str_replace("::", "_", __METHOD__)."_"
        	        .$this->idBase.'_'.$idDoc.'_'.$idTag.'_'.$idExi.'_'.$idGeo.'_'.$idMonade.'_'.$idRapport;
        	    if($cache){
        	        $result = $this->cache->load($c);
        	    }
        	    if(!$result){        	    
            	    $result = array("idBase"=>$this->idBase,"sumNiv"=>0,"sumEle"=>0,"sumComplex"=>0,"details"=>array());
            	    //récupère la définition des niches si besoin
            	    $niches = false;
            	    if($idDoc){
            	        $niches = $this->getNicheDoc($idDoc);
            	        $d = $this->getComplexDoc($idDoc);
            	        $t = $this->getComplexTag(implode(',',$niches['tag']));
                	    $p = $this->getComplexActeurPersonne(implode(',',$niches['exi']));
                	    $g = $this->getComplexActeurGeo(implode(',',$niches['geo']));
                	    $m = $this->getComplexActeurAlgo(implode(',',$niches['monade']));
                	    $r = $this->getComplexRapport(implode(',',$niches['rapport']));
            	    } elseif ($idTag){
            	        $niches = $this->getNicheTag($idTag);
            	        $d = $this->getComplexDoc(implode(',',$niches['doc']));
            	        $t = $this->getComplexTag($idTag);
            	        $p = $this->getComplexActeurPersonne(implode(',',$niches['exi']));
            	        $g = $this->getComplexActeurGeo(implode(',',$niches['geo']));
            	        $m = $this->getComplexActeurAlgo(implode(',',$niches['monade']));
            	        $r = $this->getComplexRapport(implode(',',$niches['rapport']));        	        
            	    } elseif ($idExi){
            	        $niches = $this->getNicheExi($idExi);
            	        $d = $this->getComplexDoc(implode(',',$niches['doc']));
            	        $t = $this->getComplexTag(implode(',',$niches['tag']));
            	        $p = $this->getComplexActeurPersonne($idExi);
            	        $g = $this->getComplexActeurGeo(implode(',',$niches['geo']));
            	        $m = $this->getComplexActeurAlgo(implode(',',$niches['monade']));
            	        $r = $this->getComplexRapport(implode(',',$niches['rapport']));
            	    } elseif ($idGeo){
            	        $niches = $this->getNicheGeo($idGeo);
            	        $d = $this->getComplexDoc(implode(',',$niches['doc']));
            	        $t = $this->getComplexTag(implode(',',$niches['tag']));
            	        $p = $this->getComplexActeurPersonne(implode(',',$niches['exi']));
            	        $g = $this->getComplexActeurGeo($idGeo);
            	        $m = $this->getComplexActeurAlgo(implode(',',$niches['monade']));
            	        $r = $this->getComplexRapport(implode(',',$niches['rapport']));        	        
            	    } elseif ($idMonade){
            	        $niches = $this->getNicheMonade($idMonade);
            	        $d = $this->getComplexDoc(implode(',',$niches['doc']));
            	        $t = $this->getComplexTag(implode(',',$niches['tag']));
            	        $p = $this->getComplexActeurPersonne(implode(',',$niches['exi']));
            	        $g = $this->getComplexActeurGeo(implode(',',$niches['geo']));
            	        $m = $this->getComplexActeurAlgo($idMonade);
            	        $r = $this->getComplexRapport(implode(',',$niches['rapport']));
            	    } elseif ($idRapport){
            	        $niches = $this->getNicheRapport($idRapport);
            	        $d = $this->getComplexDoc(implode(',',$niches['doc']));
            	        $t = $this->getComplexTag(implode(',',$niches['tag']));
            	        $p = $this->getComplexActeurPersonne(implode(',',$niches['exi']));
            	        $g = $this->getComplexActeurGeo(implode(',',$niches['geo']));
            	        $m = $this->getComplexActeurAlgo(implode(',',$niches['monade']));
            	        $r = $this->getComplexRapport($idRapport);
            	    }else{
            	        $d = $this->getComplexDoc();
            	        $t = $this->getComplexTag();
            	        $p = $this->getComplexActeurPersonne();
            	        $g = $this->getComplexActeurGeo();
            	        $m = $this->getComplexActeurAlgo();
            	        $r = $this->getComplexRapport();        	        
            	    }
            	    
            	    $result["sumNiv"]=$d["numNiv"]+$t["numNiv"]+$p["numNiv"]+$g["numNiv"]+$m["numNiv"]+$r["numNiv"];
            	    $result["sumEle"]=$d["sumNb"]+$t["sumNb"]+$p["sumNb"]+$g["sumNb"]+$m["sumNb"]+$r["sumNb"];
            	    $result["sumComplex"]=$d["sumComplex"]+$t["sumComplex"]+$p["sumComplex"]+$g["sumComplex"]+$m["sumComplex"]+$r["sumComplex"];
            	    $result["details"]=array($d,$t,$p,$g,$m,$r);
            	    
            	    $this->cache->save($result, $c);
            	    
        	    }
        	    
        	    return $result;
        	    
        	}

        	/**
        	 * calcule la niche
        	 *
        	 * @param  int         $idDoc
        	 * @param  string      $type
        	 * @param  array       $arr
        	 * @param  array       $result
        	 *
        	 * @return array
        	 *
        	 */
        	function getNiche($id, $type, $arr){
        	    
        	    //calcul les regroupements
        	    $result = array("doc"=>array(),"tag"=>array(),"exi"=>array(),"geo"=>array(),"monade"=>array(),"rapport"=>array());
        	    //récupère les identifiants uniques pour chaque objet
        	    foreach ($arr as $r) {
        	        $result[$r["sdo"]][$r["sdid"]] = 1;
        	        $result[$r["spo"]][$r["spid"]] = 1;
        	        $result[$r["dso"]][$r["dsid"]] = 1;
        	        $result[$r["dpo"]][$r["dpid"]] = 1;
        	        $result[$r["pso"]][$r["psid"]] = 1;
        	        $result[$r["pdo"]][$r["pdid"]] = 1;
        	        $result["rapport"][$r["sid"]] = 1;
        	        $result["rapport"][$r["did"]] = 1;
        	        $result["rapport"][$r["pid"]] = 1;
        	        $result["monade"][$r["sm"]] = 1;
        	        $result["monade"][$r["dm"]] = 1;
        	        $result["monade"][$r["pm"]] = 1;
        	    }
        	    //construction du tableau des objets
        	    $rs = array("type"=>$type,"id"=>$id
        	        ,"doc"=>array(),"tag"=>array(),"exi"=>array(),"geo"=>array(),"monade"=>array(),"rapport"=>array()
        	        ,"details"=>$arr);
        	    foreach ($result as $k => $vs){
        	        if($k){
        	            if(!count($vs))$rs[$k][]=-1;
        	            foreach ($vs as $v=>$n) {
        	                if($v) $rs[$k][]=$v;
        	            }
        	        }
        	    }
        	    
        	    return $rs;
        	    
        	}
        	    
        	/**
        	 * calcule la niche pour 1 document
        	 *
        	 * @param  int         $idDoc
        	 *
        	 * @return array
        	 *
        	 */
        	function getNicheDoc($idDoc){
        	    
        	    $sql = "	SELECT
                	    de.doc_id idE,
                    de.titre titreE,
                    de.niveau-d.niveau+1 niveauE,
                    rS.rapport_id sid,
                    rS.monade_id sm,
                    rS.dst_id sdid,
                    rS.dst_obj sdo,
                    rS.pre_id spid,
                    rS.pre_obj spo,
                    rD.rapport_id did,
                    rD.monade_id dm,
                    rD.src_id dsid,
                    rD.src_obj dso,
                    rD.pre_id dpid,
                    rD.pre_obj dpo,
                    rP.rapport_id pid,
                    rP.monade_id pm,
                    rP.dst_id pdid,
                    rP.dst_obj pdo,
                    rP.src_id psid,
                    rP.src_obj pso
                	FROM flux_doc d
                	INNER JOIN flux_doc de ON de.lft BETWEEN d.lft AND d.rgt
                	LEFT JOIN
                    	flux_rapport rS ON rS.src_id = de.doc_id
                    	AND rS.src_obj = 'doc'
            	    LEFT JOIN
                	    flux_rapport rD ON rD.dst_id = de.doc_id
                	    AND rD.dst_obj = 'doc'
        	        LEFT JOIN
            	        flux_rapport rP ON rP.pre_id = de.doc_id
            	        AND rP.pre_obj = 'doc'
        	        WHERE
        	        d.doc_id = ".$idDoc."
        	        ORDER BY de.lft";
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    return $this->getNiche($idDoc, "document", $arr);
        	    
        	}
        	
        	/**
        	 * calcule la niche pour 1 tag
        	 *
        	 * @param  int         $idTag
        	 *
        	 * @return array
        	 *
        	 */
        	function getNicheTag($idTag){
        	    
        	    $sql = "	SELECT 
                    te.tag_id idE,
                    te.code titreE,
                    te.niveau-t.niveau+1 niveauE,
                    rS.rapport_id sid,
                    rS.monade_id sm,
                    rS.dst_id sdid,
                    rS.dst_obj sdo,
                    rS.pre_id spid,
                    rS.pre_obj spo,
                    rD.rapport_id did,
                    rD.monade_id dm,
                    rD.src_id dsid,
                    rD.src_obj dso,
                    rD.pre_id dpid,
                    rD.pre_obj dpo,
                    rP.rapport_id pid,
                    rP.monade_id pm,
                    rP.dst_id pdid,
                    rP.dst_obj pdo,
                    rP.src_id psid,
                    rP.src_obj pso    
                FROM
                    flux_tag t
                        INNER JOIN
                    flux_tag te ON te.lft BETWEEN t.lft AND t.rgt
                        LEFT JOIN
                    flux_rapport rS ON rS.src_id = te.tag_id
                        AND rS.src_obj = 'tag'
                        LEFT JOIN
                    flux_rapport rD ON rD.dst_id = te.tag_id
                        AND rD.dst_obj = 'tag'
                        LEFT JOIN
                    flux_rapport rP ON rP.pre_id = te.tag_id
                        AND rP.pre_obj = 'tag'
                WHERE
                    t.tag_id = ".$idTag."
                ORDER BY te.lft";
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    return $this->getNiche($idTag, "tag", $arr);

        	}
        	
        	/**
        	 * calcule la niche pour 1 exi
        	 *
        	 * @param  int         $idExi
        	 *
        	 * @return array
        	 *
        	 */
        	function getNicheExi($idExi){
        	    
        	    $sql = "SELECT 
                    ee.exi_id idE,
                    CONCAT(ee.prenom, ' ', ee.nom) titreE,
                    ee.niveau - e.niveau + 1 niveauE,
                    rS.rapport_id sid,
                    rS.monade_id sm,
                    rS.dst_id sdid,
                    rS.dst_obj sdo,
                    rS.pre_id spid,
                    rS.pre_obj spo,
                    rD.rapport_id did,
                    rD.monade_id dm,
                    rD.src_id dsid,
                    rD.src_obj dso,
                    rD.pre_id dpid,
                    rD.pre_obj dpo,
                    rP.rapport_id pid,
                    rP.monade_id pm,
                    rP.dst_id pdid,
                    rP.dst_obj pdo,
                    rP.src_id psid,
                    rP.src_obj pso
                FROM
                    flux_exi e
                        INNER JOIN
                    flux_exi ee ON ee.lft BETWEEN e.lft AND e.rgt
                        LEFT JOIN
                    flux_rapport rS ON rS.src_id = ee.exi_id
                        AND rS.src_obj = 'exi'
                        LEFT JOIN
                    flux_rapport rD ON rD.dst_id = ee.exi_id
                        AND rD.dst_obj = 'exi'
                        LEFT JOIN
                    flux_rapport rP ON rP.pre_id = ee.exi_id
                        AND rP.pre_obj = 'exi'
                WHERE
                    e.exi_id = ".$idExi."
                ORDER BY ee.lft";
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    return $this->getNiche($idExi, "acteur-personne", $arr);
        	    
        	}
        	        	
        	/**
        	 * calcule la niche pour 1 geo
        	 *
        	 * @param  int         $idGeo
        	 *
        	 * @return array
        	 *
        	 */
        	function getNicheGeo($idGeo){
        	    
        	    $sql = "SELECT 
                    g.geo_id idE,
                    g.adresse titreE,
                    1 niveauE,
                    rS.rapport_id sid,
                    rS.monade_id sm,
                    rS.dst_id sdid,
                    rS.dst_obj sdo,
                    rS.pre_id spid,
                    rS.pre_obj spo,
                    rD.rapport_id did,
                    rD.monade_id dm,
                    rD.src_id dsid,
                    rD.src_obj dso,
                    rD.pre_id dpid,
                    rD.pre_obj dpo,
                    rP.rapport_id pid,
                    rP.monade_id pm,
                    rP.dst_id pdid,
                    rP.dst_obj pdo,
                    rP.src_id psid,
                    rP.src_obj pso
                FROM
                    flux_geo g
                        LEFT JOIN
                    flux_rapport rS ON rS.src_id = g.geo_id
                        AND rS.src_obj = 'geo'
                        LEFT JOIN
                    flux_rapport rD ON rD.dst_id = g.geo_id
                        AND rD.dst_obj = 'geo'
                        LEFT JOIN
                    flux_rapport rP ON rP.pre_id = g.geo_id
                        AND rP.pre_obj = 'geo'
                WHERE
                    g.geo_id = ".$idGeo."
                ORDER BY g.geo_id";
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    return $this->getNiche($idGeo, "acteur-geo", $arr);
        	    
        	}
        	
        	/**
        	 * calcule la niche pour 1 monade
        	 *
        	 * @param  int         $idMonade
        	 *
        	 * @return array
        	 *
        	 */
        	function getNicheMonade($idMonade){
        	    
        	    $sql = "SELECT 
                    m.monade_id idE,
                    m.titre titreE,
                    1 niveauE,
                    rS.rapport_id sid,
                    rS.monade_id sm,
                    rS.dst_id sdid,
                    rS.dst_obj sdo,
                    rS.pre_id spid,
                    rS.pre_obj spo,
                    rD.rapport_id did,
                    rD.monade_id dm,
                    rD.src_id dsid,
                    rD.src_obj dso,
                    rD.pre_id dpid,
                    rD.pre_obj dpo,
                    rP.rapport_id pid,
                    rP.monade_id pm,
                    rP.dst_id pdid,
                    rP.dst_obj pdo,
                    rP.src_id psid,
                    rP.src_obj pso
                FROM
                    flux_monade m
                        LEFT JOIN
                    flux_rapport rS ON (rS.src_id = m.monade_id
                        AND rS.src_obj = 'monade') OR rS.monade_id = m.monade_id
                        LEFT JOIN
                    flux_rapport rD ON (rD.dst_id = m.monade_id
                        AND rD.dst_obj = 'monade') OR rS.monade_id = m.monade_id
                        LEFT JOIN
                    flux_rapport rP ON (rP.pre_id = m.monade_id
                        AND rP.pre_obj = 'monade') OR rS.monade_id = m.monade_id
                WHERE
                    m.monade_id = ".$idMonade."
                ORDER BY m.monade_id";
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    return $this->getNiche($idMonade, "acteur-algo", $arr);
        	    
        	}

        	/**
        	 * calcule la niche pour 1 rapport
        	 *
        	 * @param  int         $idGeo
        	 *
        	 * @return array
        	 *
        	 */
        	function getNicheRapport($idRapport){
        	    
        	    $sql = "SELECT 
                    r.rapport_id idE,
                    CONCAT(r.src_obj,
                            '-',
                            r.dst_obj,
                            '-',
                            r.pre_obj) titreE,
                    1 niveauE,
                    rS.rapport_id sid,
                    rS.monade_id sm,
                    rS.dst_id sdid,
                    rS.dst_obj sdo,
                    rS.pre_id spid,
                    rS.pre_obj spo,
                    rD.rapport_id did,
                    rD.monade_id dm,
                    rD.src_id dsid,
                    rD.src_obj dso,
                    rD.pre_id dpid,
                    rD.pre_obj dpo,
                    rP.rapport_id pid,
                    rP.monade_id pm,
                    rP.dst_id pdid,
                    rP.dst_obj pdo,
                    rP.src_id psid,
                    rP.src_obj pso
                FROM
                    flux_rapport r
                        LEFT JOIN
                    flux_rapport rS ON (rS.src_id = r.rapport_id AND rS.src_obj = 'rapport') OR rS.rapport_id = r.rapport_id
                        LEFT JOIN
                    flux_rapport rD ON (rD.dst_id = r.rapport_id AND rD.dst_obj = 'rapport') OR rD.rapport_id = r.rapport_id
                        LEFT JOIN
                    flux_rapport rP ON (rP.pre_id = r.rapport_id AND rP.pre_obj = 'rapport') OR rP.rapport_id = r.rapport_id
                WHERE
                    r.rapport_id  = ".$idRapport."
                ORDER BY r.rapport_id";
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    return $this->getNiche($idRapport, "rapport", $arr);
        	    
        	}
        	
        	/**
        	 * calcule la complexité des documents
        	 *
        	 * @param  string          $ids
        	 *
        	 * @return array
        	 *
        	 */
        	function getComplexDoc($ids=""){
        	    
        	    $result = array("idBase"=>$this->idBase,"type"=>"document","ids"=>$ids,"sumNb"=>0,"numNiv"=>0,"sumNiv"=>0,"sumComplex"=>0,"details"=>array());
        	    if($ids == "-1") return $result;
        	    
        	    if ($ids) $w = " WHERE d.doc_id IN (".$ids.") ";
        	    else $w = "";
        	    
        	    $sql = "SELECT
                    COUNT(DISTINCT de.doc_id) nb,
                        de.niveau + 1 - d.niveau niv,
                        COUNT(DISTINCT de.doc_id) * (de.niveau + 1 - d.niveau) complexite
                FROM
                    flux_doc d
                INNER JOIN flux_doc de ON de.lft BETWEEN d.lft AND d.rgt
                ".$w."
                GROUP BY de.niveau + 1 - d.niveau";
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    //calcul les sommes
        	    foreach ($arr as $r) {
        	        $result["sumNb"] += $r["nb"];
        	        $result["sumNiv"] += $r["niv"];
        	        $result["sumComplex"] += $r["complexite"];
        	        if($result["numNiv"] < $r["niv"]) $result["numNiv"]=$r["niv"];
        	        $result["details"][] = $r;
        	    }
        	    
        	    return $result;
        	}
        	
        	/**
        	 * calcule la complexité des tags
        	 *
        	 * @param  string          $ids
        	 *
        	 * @return array
        	 *
        	 */
        	function getComplexTag($ids=""){        	    
        	    
        	    $result = array("idBase"=>$this->idBase,"type"=>"concept","ids"=>$ids,"sumNb"=>0,"numNiv"=>0,"sumNiv"=>0,"sumComplex"=>0,"details"=>array());
        	    if($ids == "-1") return $result;
        	    
        	    if ($ids) $w = " WHERE t.tag_id IN (".$ids.") ";        	    
        	    else $w = "";
        	    
        	    $sql = "SELECT 
                    count(distinct te.tag_id) nb,
                	te.niveau + 1 - t.niveau niv,
                    count(distinct te.tag_id)*(te.niveau + 1 - t.niveau) complexite
                
                FROM
                    flux_tag t
                        INNER JOIN
                    flux_tag te ON te.lft between t.lft and t.rgt
                ".$w."
                group by te.niveau + 1 - t.niveau";
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    //calcul les sommes
        	    foreach ($arr as $r) {
        	        $result["sumNb"] += $r["nb"];
        	        $result["sumNiv"] += $r["niv"];
        	        $result["sumComplex"] += $r["complexite"];
        	        if($result["numNiv"] < $r["niv"]) $result["numNiv"]=$r["niv"];
        	        $result["details"][] = $r;
        	    }
        	    
        	    return $result;
        	}
        	
        	/**
        	 * calcule la complexité des acteurs - personne = exi
        	 *
        	 * @param  string          $ids
        	 *
        	 * @return array
        	 *
        	 */
        	function getComplexActeurPersonne($ids=""){        	    
        	    
        	    $result = array("idBase"=>$this->idBase,"type"=>"acteur-personne","ids"=>$ids,"sumNb"=>0,"numNiv"=>0,"sumNiv"=>0,"sumComplex"=>0,"details"=>array());
        	    if($ids == "-1") return $result;
        	    
        	    if ($ids) $w = " WHERE e.exi_id IN (".$ids.") ";
        	    else $w = "";
        	    //ATTENTION les exi anciens sont mal géré au niveau lft rgt
        	    $sql = "SELECT 
                    count(distinct ee.exi_id) nb,
                	ee.niveau + 1 - e.niveau niv,
                    count(distinct ee.exi_id)*(ee.niveau + 1 - e.niveau) complexite
                
                FROM
                    flux_exi e
                        INNER JOIN
                    -- flux_exi ee ON ee.lft BETWEEN e.lft AND e.rgt
                    flux_exi ee ON ee.exi_id = e.exi_id
                ".$w."
                    group by ee.niveau + 1 - e.niveau";
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    //calcul les sommes
        	    foreach ($arr as $r) {
        	        $result["sumNb"] += $r["nb"];
        	        $result["sumNiv"] += $r["niv"];
        	        $result["sumComplex"] += $r["complexite"];
        	        if($result["numNiv"] < $r["niv"]) $result["numNiv"]=$r["niv"];
        	        $result["details"][] = $r;
        	    }
        	    
        	    return $result;
        	}
        	
        	/**
        	 * calcule la complexité des acteurs - geo
        	 *
        	 * @param  string          $ids
        	 *
        	 * @return array
        	 *
        	 */
        	function getComplexActeurGeo($ids=""){        	    
        	    
        	    $result = array("idBase"=>$this->idBase,"type"=>"acteur-geo","ids"=>$ids,"sumNb"=>0,"numNiv"=>0,"sumNiv"=>0,"sumComplex"=>0,"details"=>array());
        	    if($ids == "-1") return $result;
        	    
        	    if ($ids) $w = " WHERE g.geo_id IN (".$ids.") ";
        	    else $w = "";
        	    
        	    $sql = "SELECT 
                    count(distinct g.geo_id) nb,
                    	1 niv,
                    count(distinct g.geo_id)*1 complexite
                
                FROM
                    flux_geo g
                ".$w." ";
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    //calcul les sommes
        	    foreach ($arr as $r) {
        	        $result["sumNb"] += $r["nb"];
        	        $result["sumNiv"] += $r["niv"];
        	        $result["sumComplex"] += $r["complexite"];
        	        if($result["numNiv"] < $r["niv"]) $result["numNiv"]=$r["niv"];
        	        $result["details"][] = $r;
        	    }
        	    
        	    return $result;
        	}
        	
        	/**
        	 * calcule la complexité des acteurs - algo
        	 *
        	 * @param  string          $ids
        	 *
        	 * @return array
        	 *
        	 */
        	function getComplexActeurAlgo($ids=""){        	    
        	    
        	    $result = array("idBase"=>$this->idBase,"type"=>"acteur-algo","ids"=>$ids,"sumNb"=>0,"numNiv"=>0,"sumNiv"=>0,"sumComplex"=>0,"details"=>array()
        	        ,"sumNbRapport"=>0,"sumSrc"=>0,"sumPre"=>0,"sumDst"=>0,"sumComplexRapport"=>0
        	    );
        	    if($ids == "-1") return $result;
        	    
        	    if ($ids) $w = " WHERE m.monade_id IN (".$ids.") ";
        	    else $w = "";
        	    
        	    $sql = "SELECT 
                    COUNT(DISTINCT m.monade_id) nb,
                    1 niv,
                    COUNT(DISTINCT m.monade_id) * 1 complexite,
                    CONCAT(r.src_obj,
                            '-',
                            r.dst_obj,
                            '-',
                            r.pre_obj) obj,
                    COUNT(DISTINCT r.rapport_id) nbRapport,
                    COUNT(DISTINCT r.src_id) nbSrc,
                    COUNT(DISTINCT r.dst_id) nbDst,
                    COUNT(DISTINCT r.pre_id) nbPre,
                    COUNT(DISTINCT r.rapport_id) + COUNT(DISTINCT r.src_id) + COUNT(DISTINCT r.dst_id) + COUNT(DISTINCT r.pre_id) complexiteRapport
                FROM
                    flux_monade m
                        INNER JOIN
                    flux_rapport r ON r.monade_id = m.monade_id
                ".$w." 
                    GROUP BY m.monade_id , CONCAT(r.src_obj,
                        '-',
                        r.dst_obj,
                        '-',
                        r.pre_obj)";
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    //calcul les sommes
        	    foreach ($arr as $r) {
        	        $result["sumNb"] += $r["nb"];
        	        $result["sumNiv"] += $r["niv"];
        	        $result["sumComplex"] += $r["complexite"];
        	        if($result["numNiv"] < $r["niv"]) $result["numNiv"]=$r["niv"];        	        
        	        $result["sumNbRapport"] += $r["nbRapport"];
        	        $result["sumSrc"] += $r["nbSrc"];
        	        $result["sumDst"] += $r["nbDst"];
        	        $result["sumPre"] += $r["nbPre"];
        	        $result["sumComplexRapport"] += $r["complexiteRapport"];
        	        $obj = explode("-", $r["obj"]);
        	        $r["typeSrc"]=$obj[0];
        	        $r["typeDst"]=$obj[1];
        	        $r["typePre"]=$obj[2];        	        
        	        $result["details"][] = $r;
        	    }
        	    
        	    return $result;
        	}
        	/**
        	 * calcule la complexité des rapport
        	 *
        	 * @param  string          $ids
        	 *
        	 * @return array
        	 *
        	 */
        	function getComplexRapport($ids=""){        	    
        	    
        	    $result = array("idBase"=>$this->idBase,"type"=>"rapport","ids"=>$ids,"sumNb"=>0,"sumSrc"=>0,"sumPre"=>0,"sumDst"=>0,"sumComplex"=>0,"details"=>array());
        	    if($ids == "-1") return $result;
        	    
        	    if ($ids) $w = " WHERE r.rapport_id IN (".$ids.") ";
        	    else $w = "";
        	    
        	    $sql = "SELECT 
                    CONCAT(r.src_obj,
                            '-',
                            r.dst_obj,
                            '-',
                            r.pre_obj) obj,
                    COUNT(DISTINCT r.rapport_id) nbRapport,
                    COUNT(DISTINCT r.src_id) nbSrc,
                    COUNT(DISTINCT r.dst_id) nbDst,
                    COUNT(DISTINCT r.pre_id) nbPre,
                    COUNT(DISTINCT r.rapport_id)+COUNT(DISTINCT r.src_id)+COUNT(DISTINCT r.dst_id)+COUNT(DISTINCT r.pre_id) complexite
                FROM
                    flux_rapport r
                ".$w."
                GROUP BY CONCAT(r.src_obj,
                        '-',
                        r.dst_obj,
                        '-',
                        r.pre_obj)";
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    //calcul les sommes
        	    foreach ($arr as $r) {
        	        $result["sumNb"] += $r["nbRapport"];
        	        $result["sumSrc"] += $r["nbSrc"];
        	        $result["sumDst"] += $r["nbDst"];
        	        $result["sumPre"] += $r["nbPre"];
        	        $result["sumComplex"] += $r["complexite"];
        	        $obj = explode("-", $r["obj"]);
        	        $r["typeSrc"]=$obj[0];
        	        $r["typeDst"]=$obj[1];
        	        $r["typePre"]=$obj[2];
        	        
        	        $result["details"][] = $r;        	        
        	    }
        	    $result["numNiv"]=count($arr);
        	    
        	    return $result;
        	}
        	
        	
        	/**
        	 * récupère les acteurs pour un contexte documentaire
        	 * 
        	 * @param  int         $idDoc
        	 * @param  string      $q -- spécifier le type de requête
        	 * @param  int         $idTheme -- pour récupérer les stats d'une photo
        	 * @param  int         $idVisage -- pour récupérer les stats d'un visage
        	 *
        	 * @return array
        	 *
        	 */
        	function getActeursContexte($idDoc, $q=false, $idTheme=false, $idVisage=false){
        	    
        	    //recupère les données pour les photos
        	    $sql = "SELECT 
                    e.exi_id,
                    e.nom,
                    e.prenom,
                    e.nait,
                    e.mort,
                    e.url,
                    e.data,
                    e.exi_id recid,
                    SUM(dt.niveau) + COUNT(DISTINCT dt.doc_id) pertinenceTof,
                    SUM(dp.niveau) + COUNT(DISTINCT dp.doc_id) pertinenceParent,
                    SUM(dt.niveau) + COUNT(DISTINCT dt.doc_id) + SUM(dp.niveau) + COUNT(DISTINCT dp.doc_id) pertinence,
                    COUNT(DISTINCT rU.rapport_id)+COUNT(DISTINCT rV.rapport_id) nbVote,
                    SUM(rU.niveau)/COUNT(rU.src_id) confiancePhoto,
                    SUM(rV.niveau)/COUNT(rV.src_id) confianceVisage
                FROM
                    flux_exi e
                        INNER JOIN
                    flux_rapport r ON r.dst_id = e.exi_id
                        AND r.dst_obj = 'exi'
                        AND r.src_obj = 'doc'
                        INNER JOIN
                    flux_doc dt ON dt.doc_id = r.src_id
                        LEFT JOIN
                    flux_doc dp ON dp.doc_id = ".$idDoc."
                        AND dt.lft BETWEEN dp.lft AND dp.rgt
                        LEFT JOIN
                    flux_rapport rU ON rU.dst_id = e.exi_id
                        AND rU.dst_obj = 'exi'
                        AND rU.src_obj = 'doc'
                        AND rU.src_id = ".$idTheme."
                        AND rU.pre_obj = 'uti'
                        LEFT JOIN
                    flux_rapport rV ON rV.dst_id = e.exi_id
                        AND rV.dst_obj = 'exi'
                        AND rV.src_obj = 'doc'
                        AND rV.src_id = ".$idVisage."
                        AND rV.pre_obj = 'uti' ";
        	    if($q == "strict"){
        	        $sql .= " WHERE dp.doc_id IS NOT NULL ";
        	    }
        	    if($q == "statTof"){
        	        $sql .= " WHERE dt.doc_id = ".$idTheme." ";
        	    }
        	    $sql .= " GROUP BY e.exi_id
                ORDER BY confianceVisage DESC, confiancePhoto DESC, pertinence DESC, e.nom
                ";
        	    
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	            	    
        	    return $arr;
        	    
        	}
        
        	        
        	/**
        	 * Fonction pour récupérer une liste de photo aléatoire
        	 *
        	 * @param  	int 		$nb
        	 *
        	 * @return	array
        	 *
        	 */
        	function getAleaTofs($nb=10){
        	    
        	    $this->trace(__METHOD__." ".$idCol);
        	            	    
        	    
        	    $data= array();
        	    //récupère une liste aléatoire des photos
        	    $sql = "SELECT 
    d.parent gpId,
    d.doc_id pId,
    d.note,
    d.titre label,
    d.url original,
    dp.titre theme,
    MIN(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d')) temps,
    om.source imgFull,
    om.item_id idOmkItem,
    MIN(om.id) idOmkMedia
FROM
    flux_doc d
        INNER JOIN
    flux_rapport rDeb ON rDeb.src_id = d.parent
        AND rDeb.src_obj = 'doc'
        AND rDeb.dst_obj = 'tag'
        AND rDeb.dst_id = 4
        INNER JOIN
    flux_doc dp ON dp.doc_id = d.parent
        INNER JOIN
    omk_valarnum1.value ov ON ov.value LIKE 'flux_valarnum-flux_doc-doc_id-%'
        AND SUBSTRING(ov.value, 31) = d.doc_id
        INNER JOIN
    omk_valarnum1.media om ON om.item_id = ov.resource_id
WHERE
    d.type = 1
GROUP BY d.doc_id , om.item_id , om.source
ORDER BY RAND()
LIMIT ".$nb;
        	    
        	    $this->trace($sql);
        	    $arr = $this->dbD->exeQuery($sql);
        	    
        	    //constuction des résultats
        	    $data[] = array("label"=>"racine","id"=>"root","value"=>"");
        	    $i=1;
        	    foreach ($arr as $t) {
        	        if(!$t['note']){
        	            //pas assez performant par IIF
        	            //$dt = $this->iiif->getTofInfos($this->iiif->urlRoot."/".$t['idOmkItem']."/manifest",$i);
        	            //récupère la taille de la photo        	                    	            
        	            $filename = WEB_ROOT."/data/AN/photos/".$t['imgFull'];
        	            $this->trace($filename);
        	            if ($this->url_exists($filename)) {
        	                $size = getimagesize($filename);
        	                //construction des données
        	                $img = $this->iiif->urlRoot."-img/".$t['idOmkMedia'];
        	                $dt = array("gpId"=>$t['gpId'],"idDoc"=>$t['pId'],"idCol"=>'alea',"value"=>"","label"=>$t['label']
        	                    ,"original"=>$t['original'],"idOmkItem"=>$t['idOmkItem'],"idOmkMedia"=>$t['idOmkMedia'],"imgOmk"=>$img,"imgFull"=>$img.'/full/full/0/default.jpg'
        	                    ,"w"=>$size[0],"h"=>$size[1]
        	                    ,"metadata"=>array("Identifier"=>$this->idBase."-flux_doc-doc_id-".$t['pId']));
        	                $json = json_encode($dt);
        	                $this->dbD->edit($t['pId'], array('note'=>$json));
        	                $dt["id"]="root.".$i;
        	            }else{
        	                $this->dbD->edit($t['pId'], array('data'=>"Le fichier $filename n'existe pas."));
        	            }        	            
        	            //pour réinitialisé 
        	            //update `flux_doc` set note='' WHERE `note` IS NOT NULL AND `type` = 1 ORDER BY `note` DESC
        	        }else{
        	            $this->trace($i." : ".$t['note']);        	            
    	                $dt = json_decode($t['note']);
    	                $this->trace(json_last_error());
        	            $dt->id="root.".$i;
        	        }
        	        $data[] = $dt;
        	        $i++;
        	    }
        	    
        	    $this->trace(__METHOD__." FIN");
        	    return $data;
        	    
        	}
        	
}
<?php

class Flux_Site{
    
    var $cache;
	var $idBase;
    var $idExi;
	var $login;
	var $pwd;
    var $user;
	var $graine;
	var $dbU;
	var $dbUU;
	var $dbUT;
	var $dbUD;
	var $dbUTD;
	var $dbT;
	var $dbTT;
	var $dbTD;		
	var $dbD;
	var $dbDT;
	var $dbIEML;
	var $dbUIEML;
	var $dbTrad;
	var $dbE;
	var $dbED;
	var $dbET;
	var $dbG;
	var $dbGUD;
	var $db;
	var $lucene;
	var $kwe = array("autokeyword","zemanta", "alchemy", "opencalais", "yahoo", "textalytics","aylien");
    //pour l'optimisation
    var $bTrace = false;
    var $bTraceFlush = false;//mettre false pour les traces de debuggage
    var $echoTrace = false;
	var $temps_debut;
    var $temps_inter;
    var $temps_nb=0;
    var $idDoc;
    
    function __construct($idBase=false, $bTrace=false){    	
    	
    		if($bTrace){
			$this->bTrace = true;		
			$this->temps_debut = microtime(true);
    		}
    		
    		$this->getDb($idBase);
    	
        $frontendOptions = array(
            'lifetime' => 30000000, // temps de vie du cache en seconde
            'automatic_serialization' => true,
        	'caching' => true //active ou desactive le cache
        );  
        $backendOptions = array(
            // Répertoire où stocker les fichiers de cache
            'cache_dir' => '../tmp/flux/'
        ); 
        // créer un objet Zend_Cache_Core
        $this->cache = Zend_Cache::factory('Core',
                                     'File',
                                     $frontendOptions,
                                     $backendOptions); 
    
    }

	/**
	* fonction pour tracer l'éxécution du code
	*
    * @param string 	$message
    * @param array 	$data
    * 
    */
	public function trace($message, $data=false){
		if($this->bTrace){
			if(!$this->temps_debut)$this->temps_debut = microtime(true);
			
			$temps_fin = microtime(true);
			$tG = str_replace(".",",",round($temps_fin - $this->temps_debut, 4));
			$tI = str_replace(".",",",round($temps_fin - $this->temps_inter, 4));
			$mess = $this->temps_nb." | ".$message." |".$tG."|".$tI."<br/>";
			if($this->echoTrace)
				$this->echoTrace .= $mess;
			else{
				echo $mess;
				if($data){print_r($data); echo "<br/>";}
				if($this->bTraceFlush){
					ob_flush();
			        flush();				
				}
		        //
			}
			$this->temps_inter = $temps_fin;
			$this->temps_nb ++;
		}		
	}
    
    
    /**
    * @param string $c
    */
    function removeCache($c){
        $res = $this->manager->remove($c);
    }
    
    /**
    * supprime un répertoire sur le serveur
    * @param string $dir
    * @return Zend_Db_Table
    */
    public function removeRep($dir) { 
   		$files = array_diff(scandir($dir), array('.','..')); 
	    foreach ($files as $file) { 
	      (is_dir("$dir/$file")) ? $this->removeRep("$dir/$file") : unlink("$dir/$file"); 
	    } 
	    return rmdir($dir); 
	} 
    
    /**
     * retourne une connexion à une base de donnée suivant son nom
    * @param string $idBase
    * @return Zend_Db_Table
    */
    public function getDb($idBase){
    	
 		$db = Zend_Db_Table::getDefaultAdapter();
    	if($idBase){
    		//change la connexion à la base
    		$arr = $db->getConfig();
			$arr['dbname']=$idBase;
			$db = Zend_Db::factory('PDO_MYSQL', $arr);	
    	}
      	
    	$this->db = $db;
    	$this->idBase = $idBase;
    	return $db;
    }
    
    /**
     * Récupère l'identifiant d'utilisateur ou le crée
     *
     * @param array $user
     * @param boolean $getId
     * 
     * return integer
     */
	function getUser($user, $getId=false) {

		//récupère ou enregistre l'utilisateur
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
		$idU = $this->dbU->ajouter($user);		
		if(!$getId)$this->user = $idU;
		
		return $idU;
	}

    /**
     * getArrHier
     * 
     * Création d'un tableau hiérarchique à partir d'un tableau de parent
     *
     * @param array $arr
     * @param array $result
     * @param int $niv
     * 
     * return $arr
     */
	function getArrHier($arr, $arrParent, $result= array(), $niv=0) {

		if($arr['niveau']==$niv){
			$result[]=$arr;
		}
		$i=0;
		//recherche le bon parent
		foreach ($result as $parent){
			if($parent["tag_id"]==$arrParent[$niv]['tag_id'])break;
			$i++;
		}
		if(!isset($result[$i]['children'])){
			$result[$i]['children']= array();			
		}
		if($niv<$arr['niveau']){
			$result[$i]['children'] = $this->getArrHier($arr, $arrParent, $result[$i]['children'], $niv+1);
		}

		return $result;
	}
	
    /**
     * Récupère l'identifiant de la graine ou la crée
     *
     * @param array $graine
     * 
     */
	function getGraine($graine) {

		//TODO récupère ou enregistre la graine
		//if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti();
		//$this->graine = $this->dbU->ajouter($user);		

	}

    /**
     * Récupère le contenu body d'une url
     *
     * @param string $url
     * @param array $param
     * @param boolean $cache
     *   
     * @return string
     */
	function getUrlBodyContent($url, $param=false, $cache=true, $method=null) {
		$html = false;
		if(substr($url, 0, 7)!="http://")$url = urldecode($url);
		if($cache){
			$c = str_replace("::", "_", __METHOD__)."_".md5($url); 
			if($param)$c .= "_".$this->getParamString($param);
		   	$html = $this->cache->load($c);
		}
        if(!$html){
		    	$client = new Zend_Http_Client($url,array('timeout' => 30));
		    	if($param && !$method)$client->setParameterGet($param);
		    	if($param && $method==Zend_Http_Client::POST)$client->setParameterPost($param);
		    	try {
					$response = $client->request($method);
					$html = $response->getBody();
				}catch (Zend_Exception $e) {
					echo "Récupère exception: " . get_class($e) . "\n";
				    echo "Message: " . $e->getMessage() . "\n";
				}				
	        	if($cache)$this->cache->save($html, $c);
        }
		return $html;
	}

	/**
     * création d'un tableau à partir d'un csv
     *
     * @param string $file = adresse du fichier
     * 
     */
    function csvToArray($file, $tailleCol="0", $sep=";"){
		ini_set("memory_limit",'1024M');
    	$this->trace("DEBUT ".__METHOD__);     	
	    if (($handle = fopen($file, "rb")) !== FALSE) {
    		$this->trace("Traitement des lignes : ".ini_get("memory_limit"));     	
	    	$i=0;
    		while (($data = fgetcsv($handle, $tailleCol, $sep)) !== FALSE) {
    			$num = count($data);
 				$numTot = count($csvarray);
 				//$this->trace("$numTot -> $num fields in line $i:");
        		$csvarray[] = $data;
    			$i++;
	    	}
	    	$this->trace("FIN Traitement des lignes");     	
	        fclose($handle);
	    }
    	
    	$this->trace("FIN ".__METHOD__);     	
		return $csvarray;		
	}	
	
    /**
     * récupère le contenu HTML d'un DOMElement
     *
     * @param DOMElement $node
     *   
     * @return string
     */
	function getInnerHtml( $node ) { 
	    $innerHTML= ''; 
	    $children = $node->childNodes; 
	    foreach ($children as $child) { 
	        $innerHTML .= $child->ownerDocument->saveXML( $child ); 
	    } 
	
	    return $innerHTML; 
	} 
	
    /**
     * Ajoute des informations supplémentaire d'indexation
     *
     * @param string $url
     * @param Zend_Search_Lucene_Document_Html $doc
     *   
     * @return Zend_Search_Lucene_Document_Html
     */
	function addInfoDocLucene($url, $doc) {
	   	
    	//récupère le body de l'url
    	//$html = $this->getUrlBodyContent($url);
		//$dom = new Zend_Dom_Query($html);	    
    					
		//ajoute l'url du document
		$doc->addField(Zend_Search_Lucene_Field::Keyword('url',urlencode($url)));
				
		return $doc;		 
	}	
		
	function getParamString($params, $md5=false){
		$s="";
		foreach ($params as $k=>$v){
			if($md5) $s .= "_".md5($v);
			else $s .= "_".$v;
		}
		return $s;	
	}
	

	
    /**
     * Sauvegarde d'un tag
     *
     * @param string $tag
     * @param integer $idD
     * @param integer $poids
     * @param date $date
     * @param int $idUser
     * @param boolean $existe : mettre à false pour forcer la création 
     * @param boolean $utd : renvoie l'identifiant Uti Tag Doc 
     *   
     * @return integer
     */
	function saveTag($tag, $idD, $poids, $date=0, $idUser=0, $existe = true, $utd=false){

		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUT)$this->dbUT = new Model_DbTable_Flux_UtiTag($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
		if(!$date){
			$d = new Zend_Date();
	    	$date= $d->get("c");			
		}
		
		if(!$idUser)$idUser=$this->user;
		
		//on ajoute le tag
		if(is_array($tag))
			$idT = $this->dbT->ajouter($tag, true);
		else
			$idT = $this->dbT->ajouter(array("code"=>$tag), true);
		//on ajoute le lien entre le tag et le doc avec le poids
		$this->dbTD->ajouter(array("tag_id"=>$idT, "doc_id"=>$idD, "poids"=>$poids, "maj"=>$date));
		//on ajoute le lien entre le tag et l'uti avec le poids
		$this->dbUT->ajouter(array("tag_id"=>$idT, "uti_id"=>$idUser, "poids"=>$poids, "maj"=>$date));
		//on ajoute le lien entre le tag l'utilisateur et le doc
		$idUTD = $this->dbUTD->ajouter(array("uti_id"=>$idUser, "tag_id"=>$idT, "doc_id"=>$idD, "maj"=>$date, "poids"=>$poids), $existe);

		if($utd)
			return $idUTD;
		else
			return $idT;
	}

	
    /**
     * Sauvegarde une relation enytre tag
     *
     * @param string $tagSrc
     * @param string $tagDst
     * @param integer $poids
     * @param date $date
     *   
     * @return integer
     */
	function saveTagTag($tagSrc, $tagDst, $poids, $date, $idD, $idSrc=-1, $idDst=-1, $idUser=-1){

		if(!$this->dbTT)$this->dbTT = new Model_DbTable_Flux_TagTag($this->db);
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);

		if($idSrc==-1){
			$idSrc = $this->saveTag($tagSrc, $idD, $poids, $date, $idUser);
		}
		if($idDst==-1){
			$idDst = $this->saveTag($tagDst, $idD, $poids, $date, $idUser);
		}
		
		//on ajoute la relation entre les tag
		return $this->dbTT->ajouter(array("tag_id_src"=>$idSrc, "tag_id_dst"=>$idDst, "poids"=>$poids, "maj"=>$date));

	}
	
    /**
     * Sauvegarde d'un tag sémantique IEML
     *
     * @param string $ieml
     * @param integer $idUti
     * @param integer $idTag
     *   
     * @return integer
     */
	function saveIEML($ieml, $idUti, $idTag){
		//on ajoute le tag ieml
		$idIeml = $this->dbIEML->ajouter(array("code"=>$ieml));
		//on ajoute le tag ieml à l'utilisateur
		$this->dbUIEML->ajouter(array("uti_id"=>$idUti, "ieml_id"=>$idIeml));
		//on ajoute la traduction ieml
		$this->dbTrad->ajouter(array("tag_id"=>$idTag, "ieml_id"=>$idIeml));			
		
		return $idIeml;
	}


	
    /**
     * enregistre les mots clefs d'une chaine
     *
     * @param int $idDoc
     * @param string $texte
     * @param string $html
     * @param string $class
     *   
     * @return array
     */
	function saveKW($idDoc, $texte, $html="", $class="all"){
		
		//initialise les gestionnaires de base de données
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		$this->idDoc = $idDoc;
		
		if($class=="all"){
			foreach ($this->kwe as $c) {
				$result[$c] = $this->saveKW($idDoc, $texte, $html, $c);
			}
			return $result;
		}else{
			//récupère les mots clefs
			$arrKW = $this->getKW($texte, $html, $class);			
		}
		

		//récupère la date courante
		$d = new Zend_Date();
		
		//récupère l'utilisateur correspondant à la classe
		$idUdst = $this->getUser(array("login"=>"KWE_".$class),true);
		
		//enregistre l'extraction de mots clefs
		$idDe = $this->dbD->ajouter(array("titre"=>"json_".$class,"tronc"=>$idDoc,"maj"=>$d->get("c"), "type"=>78, "note"=>json_encode($arrKW)));
		
		//enregistre les mots clefs
	   	if($arrKW){
		   	$i=0;
			switch ($class) {
				case "autokeyword":
					foreach ($arrKW as $kw=>$nb){
						$this->saveTag($kw, $idDoc, $nb, $d->get("c"),$idUdst);
						$i++;	    			
				   	}
					break;
				case "alchemy":
					if($arrKW->status=="OK"){
						foreach ($arrKW->keywords as $kw){
							$idT = $this->saveTag($kw->text, $idDoc, $kw->relevance, $d->get("c"), $idUdst);
							//enregistre le sentiment
							if($kw->sentiment){
								$poids=1;
								if(isset($kw->sentiment->score))$poids=$kw->sentiment->score;
								$this->saveTagTag("", $kw->sentiment->type, $poids, $d->get("c"), $idDoc, $idT, -1, $idUdst);								
							}
							$i++;	    			
					   	}
					}
					break;
				case "yahoo":
					if(isset($arrKW->query->results->yctCategories)){
						foreach ($arrKW->query->results->yctCategories as $kw){
							$idT = $this->saveTag($kw->content, $idDoc, $kw->score, $d->get("c"), $idUdst);
							//enregistre les types
							if(isset($kw->types)){							
								foreach ($kw->types->type as $t){
									if(isset($t->content)){
										$poids=1;
										$this->saveTagTag("", $t->content, $poids, $d->get("c"), $idDoc, $idT, -1, $idUdst);
									}
								}
							}
							/**TODO compléter avec les autres champs de réponse
							 * http://developer.yahoo.com/search/content/V2/contentAnalysis.html
							 */
							$i++;	    			
					   	}
					}
					break;
				case "aylien":
					//problème avec l'extraction des sentiments
					$arrC = $arrKW['concepts']->concepts;
					foreach ($arrC as $url=>$kw){
						//récupère le nom de la ressource
						$pu = parse_url($url);
						//enregistre le document dppedia
						$idDocDBP = $this->dbD->ajouter(array("titre"=>$pu["path"],"tronc"=>"dbpedia","maj"=>$d->get("c"), "type"=>33, "url"=>$url));
						//récupères les formes du concept
						foreach ($kw->surfaceForms as $w){
							$idT = $this->saveTag($w->string, $idDoc, $w->score, $d->get("c"), $idUdst);
						}
						/*récupères les types du concept
						foreach ($kw->types as $w){
							$idT = $this->saveTag($w->string, $idDoc, $w->score, $d->get("c"), $idUdst);
						}
						*/
					}
					$arrC = $arrKW['entities']->entities;
				   	foreach ($arrC as $lbl=>$kw){
						foreach ($kw as $w){
							$this->saveTagTag($lbl, $w, 1, $d->get("c"), $idDoc, -1, -1, $idUdst);							
						}				   			
				   	}
				   	break;
				case "textalytics":
					foreach ($arrKW as $lbl=>$kw){
						foreach ($kw as $w){
							switch ($lbl) {
								case "entity_list":
									$sem = $w->sementity;
									$this->saveTagTag($lbl, $w->form, $w->relevance, $d->get("c"), $idDoc, -1, -1, $idUdst);
									foreach ($w->variant_list as $v) {
										$this->saveTagTag("variant_list", $v->form, 1, $d->get("c"), $idDoc, -1, -1, $idUdst);
										$idDocVar = $this->dbD->ajouter(array("titre"=>$v->form,"parent"=>$idDoc,"maj"=>$d->get("c"), "type"=>39, "url"=>"inip=".$v->inip."&endp=".$v->endp, "note"=>json_encode($v)));										
									}							
									break;								
								case "concept_list":
									$sem = $w->sementity;
									$this->saveTagTag($lbl, $w->form, $w->relevance, $d->get("c"), $idDoc, -1, -1, $idUdst);
									foreach ($w->variant_list as $v) {
										$this->saveTagTag("variant_list", $v->form, 1, $d->get("c"), $idDoc, -1, -1, $idUdst);
										$idDocVar = $this->dbD->ajouter(array("titre"=>$v->form,"parent"=>$idDoc,"maj"=>$d->get("c"), "type"=>39, "url"=>"inip=".$v->inip."&endp=".$v->endp, "note"=>json_encode($v)));										
									}							
									break;								
								case "money_expression_list":
									$this->saveTagTag($lbl, $w->form, 1, $d->get("c"), $idDoc, -1, -1, $idUdst);
									$idDocUri = $this->dbD->ajouter(array("titre"=>$w->type,"parent"=>$idDoc,"maj"=>$d->get("c"), "type"=>39, "url"=>"", "note"=>json_encode($w)));										
									break;								
								case "time_expression_list":
									$this->saveTagTag($lbl, $w->form, 1, $d->get("c"), $idDoc, -1, -1, $idUdst);
									$idDocUri = $this->dbD->ajouter(array("titre"=>$w->form,"parent"=>$idDoc,"maj"=>$d->get("c"), "type"=>39, "url"=>"inip=".$w->inip."&endp=".$w->endp, "note"=>json_encode($w)));										
									break;								
								case "uri_list":
									$idDocUri = $this->dbD->ajouter(array("titre"=>$lbl,"parent"=>$idDoc,"maj"=>$d->get("c"), "type"=>33, "url"=>$w->form, "note"=>json_encode($w)));										
									break;								
								case "relation_list":
									$sem = $w->verb;
									$this->saveTagTag($lbl, $w->form, $w->degree, $d->get("c"), $idDoc, -1, -1, $idUdst);
									$idDocRela = $this->dbD->ajouter(array("titre"=>$w->form,"parent"=>$idDoc,"maj"=>$d->get("c"), "type"=>33, "url"=>"inip=".$w->inip."&endp=".$w->endp, "note"=>json_encode($w)));										
									foreach ($w->complement_list as $v) {
										$this->saveTagTag($v->type, $v->form, 1, $d->get("c"), $idDoc, -1, -1, $idUdst);
									}							
									break;								
							}
						}
				   	}
					break;
				case "opencalais":
					foreach ($arrKW as $lbl=>$kw){
						foreach ($kw as $w){
							$this->saveTagTag($lbl, $w, 1, $d->get("c"), $idDoc, -1, -1, $idUdst);							
						}
				   	}
					break;
				case "zemanta":
					if($arrKW->status=="ok"){
						if(isset($arrKW->keywords)){
							foreach ($arrKW->keywords as $kw){
								$type = $kw->scheme;
								$poids = $kw->confidence;
								//enregistre le tag
								$idT = $this->saveTag($kw->name, $idDoc, $poids, $d->get("c"), $idUdst);
								if($type){
									$this->saveTagTag("", $type, $poids, $d->get("c"), $idDoc, $idT, -1, $idUdst);
								}
						   	}
						}
						if(isset($arrKW->markup->links)){
							foreach ($arrKW->markup->links as $kw){
								$type=false;
								$poids = $kw->relevance;
								foreach ($kw->target as $t){
									//enregistre le tag
									$idT = $this->saveTag($t->title, $idDoc, $poids, $d->get("c"), $idUdst);
									//récupère le document lié
									$idD = $this->dbD->ajouter(array("url"=>$t->url,"titre"=>$t->title,"tronc"=>0,"maj"=>$d->get("c"), "type"=>39));
									//ajoute un lien entre zemanta et le document avec un poids
									$this->dbUD->ajouter(array("uti_id"=>$idUdst, "doc_id"=>$idD, "poids"=>$kw->confidence));										    
									//enregistre le tag pour le document
									$idTLie = $this->saveTag($t->type, $idD, $kw->confidence, $d->get("c"), $idUdst);
									//enregistre les tags liés
									$this->saveTagTag("", "", $poids, $d->get("c"), $idDoc, $idT, $idTLie, $idUdst);
									//enregistre les types
									if(isset($kw->entity_type)){
										if(is_array($kw->entity_type)){
											foreach ($kw->entity_type as $tp) {
												//enregistre les tags liés
												$this->saveTagTag("", $tp, $kw->confidence, $d->get("c"), $idD, $idTLie, -1, $idUdst);
											}											
										}else{
											$this->saveTagTag("", $kw->entity_type, $kw->confidence, $d->get("c"), $idD, $idTLie, -1, $idUdst);
										}
								}
									
								}
								$i++;	    			
						   	}
						}
						/**TODO compléter avec les autres champs de réponse
						 * http://developer.zemanta.com/docs/suggest_markup/
						 */
				   	}
					break;
			}
	   	}
		return $arrKW;		
	}

    /**
     * enregistre les mots clefs d'un utilisateur à partir d'un fichier csv
     *
     * @param string $login
     * @param string $fic
     *   
     * @return array
     */
	function saveKWUti($login, $fic){
	    	//
	    	$this->bTrace = true; // pour afficher les traces   	
	    	$this->temps_debut = microtime(true);
		$this->trace("DEBUT ".__METHOD__);

		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		
		//récupère les données csv
		$arrKW = $this->csvToArray($fic,0,",");

		//récupère la date courante
		$d = new Zend_Date();
		
		//récupère l'utilisateur
		$idUti = $this->getUser(array("login"=>$login),true);
		
		//enregistre l'extraction de mots clefs
		$idDoc = $this->dbD->ajouter(array("titre"=>"Mots clefs de ".$login,"maj"=>$d->get("c"), "type"=>78, "note"=>json_encode($arrKW)));
		
		foreach ($arrKW as $kw) {
			$this->saveTag($kw[0], $idDoc, 1, $d->get("c"),$idUti);
		}
		
	}
	
    /**
     * enregistre les mots clefs des fichiers d'un répertoire
     *
     * @param string $rep
     *   
     * @return array
     */
	function saveKWFicRep($rep){
    	//
    	$this->bTrace = true; // pour afficher les traces   	
    	$this->temps_debut = microtime(true);
		$this->trace("DEBUT ".__METHOD__);
    	//
		
		//initialise les objets
		$pdfParse = new pdfParser();
		$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		
		//récupère les fichiers	
		$globOut = glob($rep);
		foreach ($globOut as $filename) {
			$path_parts = pathinfo($filename);
			switch ($path_parts['extension']) {
				case "pdf":
					//problème avec l'extraction des données du pdf
					$type = 35;
					$pdf = Zend_Pdf::load($filename);
					$contents = false;//$pdfParse->pdf2txt($pdf->render());
					break;
				case "doc":
					$type = 27;
					$docObj = new DocxConversion($filename);
					$contents = $docObj->convertToText();
					break;
			}
			if($contents){
	        		$this->trace($filename);
				//$this->trace($contents);
				//enregistre le document
		        	$idDoc = $this->dbD->ajouter(array("titre"=>$path_parts['filename'],"url"=>$filename, "type"=>$type, "note"=>$contents));
		        	//enregistre les mots clefs
				//var $kwe = array("autokeyword","zemanta", "alchemy", "opencalais", "yahoo", "textalytics","aylien");
		        	$this->saveKW($idDoc, $contents,"","all");	        
			}
	    }
		
	}
	
	/**
     * Récupère les mots clefs d'une chaine
     *
     * @param string $texte
     * @param string $html
     * @param string $class
     *   
     * @return array
     */
	function getKW($texte, $html="", $class="autokeyword"){
		
		switch ($class) {
			case "autokeyword":
				if($html!="")$chaine = strip_tags($html);
				else $chaine = $texte;
				$rs = $this->getKWAutokeyword($chaine);
				break;
			case "alchemy":
				$rs = $this->getKWAlchemy($texte, $html);
				break;
			case "yahoo":
				$rs = $this->getKWYahoo($texte, $html);
				break;
			case "zemanta":
				$rs = $this->getKWZemanta($texte, $html);
				break;
			case "opencalais":
				$rs = $this->getKWOpencalais($texte, $html);
				break;
			case "textalytics":
				$rs = $this->getKWTextalytics($texte, $html);
				break;
			case "aylien":
				$rs = $this->getKWAylien($texte, $html);
				break;
		}
		return $rs;		
	}	
	
    /**
     * Récupère les mots clefs d'une chaine
     *
     * @param string $chaine
     *   
     * @return array
     */
	function getKWAutokeyword($chaine){
		
		$params['content'] = $chaine; //page content
		//set the length of keywords you like
		$params['min_word_length'] = 4;  //minimum length of single words
		$params['min_word_occur'] = 1;  //minimum occur of single words
		
		$params['min_2words_length'] = 4;  //minimum length of words for 2 word phrases
		$params['min_2words_phrase_length'] = 10; //minimum length of 2 word phrases
		$params['min_2words_phrase_occur'] = 2; //minimum occur of 2 words phrase
		
		$params['min_3words_length'] = 4;  //minimum length of words for 3 word phrases
		$params['min_3words_phrase_length'] = 10; //minimum length of 3 word phrases
		$params['min_3words_phrase_occur'] = 2; //minimum occur of 3 words phrase
		
		$keyword = new autokeyword($params, "UTF-8");
		
		//return $keyword->get_keywords();
		return $keyword->parse_words();
		
	}	
	/**
     * Récupère les mots clefs avec AlchemyAPI
     * http://www.alchemyapi.com
     * @param string $texte
     * @param string $html
     * @param string $format
     * 
     * @return array/xml
     */
	function getKWAlchemy($texte, $html='', $format = 'json'){

		if($html!="")$chaine=strip_tags($html);
		else $chaine=$texte; 		
		
		// Create an AlchemyAPI object.
		$alchemyObj = new AlchemyAPI();
		$alchemyObj->setAPIKey(KEY_ALCHEMY);
		
		/**TODO: vérifier avec le format html
		
		if($html!=""){
			$body = $alchemyObj->HTMLGetRankedKeywords($html, "", $format);			
		}else{
			$body = $alchemyObj->TextGetRankedKeywords($texte, $format);			
		}
		*/
		
		$body = $alchemyObj->TextGetRankedKeywords($chaine, $format);			
		
		if($format=="json"){
			$result = json_decode($body);
		}else{
			$result = simplexml_load_string($body);
		}
		
		return $result;
		
	}		
	
	/**
     * Récupère les mots clefs avec Zemanta
     * http://developer.zemanta.com/
     * @param string $texte
     * @param string $html
     * @param string $format
     * 
     * @return string
     */
	function getKWZemanta($texte, $html="", $format = 'json'){
		
		if($html!="")$chaine=$html;
		else $chaine=$texte; 		
				
		/* This are the vars you may need to modify */
		/* Some may be placed in conf files */
		/* Some may be generated by your application */
		$url = 'http://api.zemanta.com/services/rest/0.0/'; //Should be in a conf file
		 // May depend of your application context
		$method="zemanta.suggest";
		//$method="zemanta.suggest_markup";
		
		/* It is easier to deal with arrays */
		$args = array(
		'method'=> $method,
		'api_key'=> KEY_ZEMANTA,
		'text'=> $chaine,
		'format'=> $format,
		'return_rdf_links'=>1
		);
		
		//problème à résoudre marche pas sur mac ???
		$response = $this->getUrlBodyContent($url, $args, false, Zend_Http_Client::POST);		
		
		
		
		if($format=="json"){
			$result = json_decode($response);
		}else{
			$result = simplexml_load_string($response);
		}
		
		
		/* $xml now contains the response body */
		return $result;		
	}	

	/**
     * Récupère le contenu d'une page html avec des argument POST
     * @param string $url
     * @param array $args
     * 
     * @return string
     */
	function getUrlPostContent($url, $args){

		/* Here we build the data we want to POST */
		$data = "";
		foreach($args as $key=>$value)
		{
			$data .= ($data != "")?"&":"";
			$data .= urlencode($key)."=".urlencode($value);
		}
		
		/* Here we build the POST request */
		$params = array('http' => array(
			'method' => 'POST',
			'Content-type'=> 'application/x-www-form-urlencoded',
			'Content-length' =>strlen($data),
			'content' => $data
		));
		
		/* Here we send the post request */
		$ctx = stream_context_create($params); // We build the POST context of the request
		$fp = @fopen($url, 'rb', false, $ctx); // We open a stream and send the	   request
		if ($fp){
			/* Finaly, herewe get the response of Zementa */
			$response = @stream_get_contents($fp);
			if ($response === false){
				$response = "Problem reading data from ".$url.", ".$php_errormsg;
			}
			fclose($fp); // We close the stream
		}else{
			$response = "Problem reading data from ".$url.", ".$php_errormsg;
		}
		return $response;		
	}
	
	/**
     * Récupère les mots clefs avec Yahoo
     * http://developer.yahoo.com/search/content/V2/contentAnalysis.html
     * @param string $text
     * @param string $html
     * @param string $format
     * 
     * @return xml/array
     */
	function getKWYahoo($texte, $html="", $format='json'){
		
		$url = 'http://query.yahooapis.com/v1/public/yql'; 
		
		/**TODO:vérifier si le traitement est plsu efficace avec une url
		 * 
		 */
		if($html!="")$chaine=strip_tags($html);
		else $chaine=$texte; 		
		
		$characters = array('=', '"', '\\');
		$replacements = array('%3D', '%22', ' ');
		$chaine = str_replace($characters, $replacements, $chaine);
		
		$query = 'SELECT * FROM contentanalysis.analyze WHERE text = "'.$chaine.'"';
		$query = 'SELECT * FROM contentanalysis.analyze WHERE url = "http://sfsic2014.sciencesconf.org/program/details"';
		

		/* It is easier to deal with arrays 
		text	 string (required if url parameter is not used)	 The content to perform analysis (UTF-8 encoded).
		url	 string (required if text parameter is not used)	 The url of t
		related_entities	 boolean: true (default), false	 Whether or not to include related entities/concepts in the response
		show_metadata	 boolean: true (default), false	 Whether or not to include entity/concept metadata in the response
		enable_categorizer	 boolean: true (default), false	 Whether or not to include document category information in the response
		unique	 boolean: true, false (default)	 Whether or not to detect only one occurrence of an entity or a concept that my appear multiple times		
		*/
		$args = array(
		'q'=> $query,
		'format'=> $format
		);
		
		/* Execute the request 
		$client = new Zend_Http_Client($url);
		$client->setParameterPost($args);
		$response = $client->request(Zend_Http_Client::POST);	
		$body = $response->getBody();		
		*/
		$body = $this->getUrlBodyContent($url, $args, false, Zend_Http_Client::POST);		
		
		if($format=="json"){
			$result = json_decode($body);
		}else{
			$result = simplexml_load_string($body);
		}
		
		return $result;
		
		/* message de dépassement du nombre de requête
			cbfunc({
			 "error": {
			  "lang": "en-US",
			  "description": "Query syntax error(s) [line 1:9 missing FROM at 'FROdfM']"
			 }
			});
			<Error xmlns="urn:yahoo:api"  
			 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  
			 xsi:noNamespaceSchemaLocation="http://api.yahoo.com/Api/V1/error.xsd">  
			     The following errors were detected:  
			   <Message>limit exceeded</Message>  
			 </Error> 	
		 */
		
	}
	
	

	/**
     * Récupère les mots clefs avec getKWTextalytics
     * https://textalytics.com/core/topics-info#doc
     * @param string $texte
     * @param string $html
     * 
     * @return array
     */
	function getKWTextalytics($texte, $html){
		
		if($html!="")$chaine= strip_tags($html);
		else $chaine=$texte; 		
		
		$url = "http://textalytics.com/core/topics-1.2";
				
		$args = array(
			'key'=> KEY_TEXTALYTICS,
			'lang'=> "fr",
			'txt'=>$chaine,
			'tt'=>"a"
		);
		
		/* Execute the request 
		*/
		$body = $this->getUrlBodyContent($url, $args, false, Zend_Http_Client::POST);		
		
		$result = json_decode($body);
		
		return $result;
						
	}

	/**
     * Récupère les mots clefs avec getKWAylien
     * https://textalytics.com/core/topics-info#doc
     * @param string $texte
     * @param string $html
     * @param string $url
     * 
     * @return array
     */
	function getKWAylien($texte, $html, $url=""){
		
		if($html!="")$chaine= strip_tags($html);
		else $chaine=$texte; 		
		
		$aylien = new Flux_Aylien();
		
		$result = $aylien->getAnalyses($texte, $url, $this->idBase."_".$this->idDoc);
		//$result = $aylien->getAnalyses($texte, $url);
		
		return $result;
						
	}
	
	/**
     * Récupère les mots clefs avec Open calais
     * http://www.opencalais.com/documentation/opencalais-documentation
     * @param string $texte
     * @param string $html
     * 
     * @return array
     */
	function getKWOpencalais($texte, $html){
		
		if($html!="")$chaine= strip_tags($html);
		else $chaine=$texte; 		
		
		$oc = new OpenCalais(KEY_OPENCALAIS);
		//$oc->outputFormat = "Application/JSON";
				
		$result = $oc->getEntities(substr($chaine, 0, 99000));
		
		return $result;
				
	}
	
	
	/**
     * Récupère les mots clefs avec CEPT
     * https://cept.3scale.net/docs
     * @param string $text
     * @param string $action
    * 
     * @return xml/array
     */
	function getKWCEPT($texte, $action){
		
		$url = 'http://api.cept.at/v1/'; 
		
		switch ($action) {
			case "similarterms":
				$url .= $action."?term=".$texte; 
				break;
			case "term2bitmap":
				$url .= $action."?term=".$texte; 
				break;
			default:
				;
			break;
		}
		//$url .= "&app_key=".KEY_CEPT."&app_id=".KEY_CEPT_APP_ID;
		
		$args = array(
		'app_key'=> KEY_CEPT,
		'app_id'=> KEY_CEPT_APP_ID
		);
		
		/* Execute the request 
		*/
		$body = $this->getUrlBodyContent($url, $args, false);		
		
		$result = json_decode($body);
		
		return $result;
		
		/* message d'erreur
		    "errorCode": 400,
		    "errorMessage": "at least 'term1' or 'term2' must be specified"
		 */
		
	}
	
    /**
     * sauveImage
     *
     * enregistre l'image du document
     * 
     * @param int $idDoc
     * @param string $url
     * @param string $titre
     * @param string $chemin
     * 
     * @return int
     */
	function sauveImage($idDoc, $url, $titre, $chemin){

    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	if(!$this->dbDT)$this->dbDT = new Model_DbTable_Flux_DocTypes($this->db);
    	if(!$this->dbUD)$this->dbUD = new Model_DbTable_flux_utidoc($this->db);
    	
    	//création du répertoire de stockage de l'image
		if(!is_dir($chemin)) @mkdir($chemin,0777,true);
    	
		//création des données du document
		$extension = pathinfo($url, PATHINFO_EXTENSION);
    	$type = $this->dbDT->getIdByExtension($extension);
    	$arrDoc['type']=$type;
		$path = $chemin."/".$this->idBase."_".$idDoc.".".$extension;
		$urlLocal = str_replace(ROOT_PATH, WEB_ROOT, $path);     	
    	$arrDoc['url']=$urlLocal;
    	$arrDoc['titre']=$titre;
    	$arrDoc['tronc']=$idDoc;
    	
    	//ajoute le document
    	$idDoc = $this->dbD->ajouter($arrDoc);

    	//création des liens avec le flux
    	$this->dbUD->ajouter(array("doc_id"=>$idDoc,"uti_id"=>$this->user));
    	    	    	
		if(!is_file($path)){
    		//enregistre l'image sur le disque local
			if(!$img = file_get_contents($url)) { 
			  echo 'pas de fichier : '.$url."<br/>";
			}else{
				if(!$f = fopen($path, 'w')) { 
				  echo 'Ouverture du fichier impossible '.$path."<br/>";
				}elseif (fwrite($f, $img) === FALSE) { 
				  echo 'Ecriture impossible '.$path."<br/>";
				}else{
					echo 'Image '.$titre.' enregistrée : <a href="'.$urlLocal.'">local</a> -> <a href="'.$url.'">En ligne</a><br/>';
				} 				
			}				
		} 
		return $idDoc;   	
	} 	

    /**
     * sauveUtiByImage
     *
     * enregistre un utilisateur à partir d'une liste d'image
     * les fichiers doivent avoir la forme nom_prenom.ext
     * 
     * @param string $rep
     * @param string $role
     * 
     */
	function sauveUtiByImage($rep, $role){

    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
    	if(!$this->dbUD)$this->dbUD = new Model_DbTable_flux_utidoc($this->db);
		
		if($dossier = opendir($rep)){
			while(false !== ($fichier = readdir($dossier))){
				if($fichier != '.' && $fichier != '..'){			
					$arrNom = explode("_", substr($fichier, 0, -4));
					$foaf = '<foaf:Person>
					   <foaf:name>'.$arrNom[1]." ".$arrNom[1].'</foaf:name>
					   <foaf:firstName>'.$arrNom[1].'</foaf:firstName>
					   <foaf:surname>'.$arrNom[0].'</foaf:surname>
					   <foaf:img>'.$rep."/".$fichier.'</foaf:img>
					</foaf:Person>';
					//ajoute l'utilisateur
					$idUti = $this->dbU->ajouter(array("login"=>$arrNom[1]." ".$arrNom[0],"note"=>$foaf,"role"=>$role));
					//ajoute le document
					$idDoc = $this->dbD->ajouter(array("url"=>$rep."/".$fichier,"type"=>"foaf:img"));
					//met en relation l'uti et le doc
					$this->dbUD->ajouter(array("uti_id"=>$idUti,"doc_id"=>$idDoc));
				}
			}
		}
	}	
	
	function objectToObject($instance, $className) {
	    //merci à http://stackoverflow.com/questions/3243900/convert-cast-an-stdclass-object-to-another-class
		return unserialize(sprintf(
	        'O:%d:"%s"%s',
	        strlen($className),
	        $className,
	        strstr(strstr(serialize($instance), '"'), ':')
	    ));
	}

	
	/**
	 * Removes invalid XML
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	function stripInvalidXml($value)
	{
	    $ret = "";
	    $current;
	    if (empty($value)) 
	    {
	        return $ret;
	    }
	
	    $length = strlen($value);
	    for ($i=0; $i < $length; $i++)
	    {
	        $current = ord($value{$i});
	        if (($current == 0x9) ||
	            ($current == 0xA) ||
	            ($current == 0xD) ||
	            (($current >= 0x20) && ($current <= 0xD7FF)) ||
	            (($current >= 0xE000) && ($current <= 0xFFFD)) ||
	            (($current >= 0x10000) && ($current <= 0x10FFFF)))
	        {
	            $ret .= chr($current);
	        }
	        else
	        {
	            $ret .= " ";
	        }
	    }
	    return $ret;
	}

		
	/**
	 * tri un tableau par une de ces clefs
	 *
	 * @param array $array
	 * @param string $on
	 * @param string $order
	 * 
	 * @return array
	 */
	function array_sort($array, $on, $order=SORT_ASC)
	{
	    $new_array = array();
	    $sortable_array = array();
	
	    if (count($array) > 0) {
	        foreach ($array as $k => $v) {
	            if (is_array($v)) {
	                foreach ($v as $k2 => $v2) {
	                    if ($k2 == $on) {
	                        $sortable_array[$k] = $v2;
	                    }
	                }
	            } else {
	                $sortable_array[$k] = $v;
	            }
	        }
	
	        switch ($order) {
	            case SORT_ASC:
	                asort($sortable_array);
	            break;
	            case SORT_DESC:
	                arsort($sortable_array);
	            break;
	        }
	
	        foreach ($sortable_array as $k => $v) {
	            $new_array[$k] = $array[$k];
	        }
	    }
	
	    return $new_array;
	}	
	
    /**
     * construction du format json correspondant à heatmap.js
     * @param array 	$DocsClic
     * @param boolean 	$json
     * 
     * return array
     */
    function getHeatmapClic($DocsClic, $json=false){
		$dc = "";
		$max = 0;
    	foreach ($DocsClic as $d) {
    		if($json){
    			$js = json_decode($d["data"]);
    			$dc .= "{x:".$js->x.",y:".$js->y.",count:".$d["nbEval"].",doc_id:".$d["doc_id"]."},";  			
    		    if($max<$d["nbEval"])$max=$d["nbEval"];    			
    		}else{
	    		$coor = substr($d["data"],0,-1);
	    		$dc .= $coor.",count:".$d["poids"].",doc_id:".$d["doc_id"]."},";    			
    			if($max<$d["poids"])$max=$d["poids"];
    		}
    	}    		
    	$dc = "{max: ".$max.", data: [".substr($dc,0,-1)."]}";
    	return $dc;
    }    
    
    /** 
     * Copy file or folder from source to destination, it can do 
     * recursive copy as well and is very smart 
     * It recursively creates the dest file or directory path if there weren't exists 
     * Situtaions : 
     * - Src:/home/test/file.txt ,Dst:/home/test/b ,Result:/home/test/b -> If source was file copy file.txt name with b as name to destination 
     * - Src:/home/test/file.txt ,Dst:/home/test/b/ ,Result:/home/test/b/file.txt -> If source was file Creates b directory if does not exsits and copy file.txt into it 
     * - Src:/home/test ,Dst:/home/ ,Result:/home/test/** -> If source was directory copy test directory and all of its content into dest      
     * - Src:/home/test/ ,Dst:/home/ ,Result:/home/**-> if source was direcotry copy its content to dest 
     * - Src:/home/test ,Dst:/home/test2 ,Result:/home/test2/** -> if source was directoy copy it and its content to dest with test2 as name 
     * - Src:/home/test/ ,Dst:/home/test2 ,Result:->/home/test2/** if source was directoy copy it and its content to dest with test2 as name 
     * @todo 
     *     - Should have rollback technique so it can undo the copy when it wasn't successful 
     *  - Auto destination technique should be possible to turn off 
     *  - Supporting callback function 
     *  - May prevent some issues on shared enviroments : http://us3.php.net/umask 
     * @param $source //file or folder 
     * @param $dest ///file or folder 
     * @param $options //folderPermission,filePermission 
     * @return boolean 
     */ 
    function smartCopy($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755)) 
    { 
        $result=false; 
        
        if (is_file($source)) { 
            if ($dest[strlen($dest)-1]=='/') { 
                if (!file_exists($dest)) { 
                    cmfcDirectory::makeAll($dest,$options['folderPermission'],true); 
                } 
                $__dest=$dest."/".basename($source); 
            } else { 
                $__dest=$dest; 
            } 
            $result=copy($source, $__dest); 
            chmod($__dest,$options['filePermission']); 
            
        } elseif(is_dir($source)) { 
            if ($dest[strlen($dest)-1]=='/') { 
                if ($source[strlen($source)-1]=='/') { 
                    //Copy only contents 
                } else { 
                    //Change parent itself and its contents 
                    $dest=$dest.basename($source); 
                    @mkdir($dest); 
                    chmod($dest,$options['filePermission']); 
                } 
            } else { 
                if ($source[strlen($source)-1]=='/') { 
                    //Copy parent directory with new name and all its content 
                    @mkdir($dest,$options['folderPermission']); 
                    chmod($dest,$options['filePermission']); 
                } else { 
                    //Copy parent directory with new name and all its content 
                    @mkdir($dest,$options['folderPermission']); 
                    chmod($dest,$options['filePermission']); 
                } 
            } 

            $dirHandle=opendir($source); 
            while($file=readdir($dirHandle)) 
            { 
                if($file!="." && $file!="..") 
                { 
                     if(!is_dir($source."/".$file)) { 
                        $__dest=$dest."/".$file; 
                    } else { 
                        $__dest=$dest."/".$file; 
                    } 
                    //echo "$source/$file ||| $__dest<br />"; 
                    $result=$this->smartCopy($source."/".$file, $__dest, $options); 
                } 
            } 
            closedir($dirHandle); 
            
        } else { 
            $result=false; 
        } 
        return $result; 
    } 	
}
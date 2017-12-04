<?php
/**
 * Flux_Site
 *
 * Classe générique qui permet de gérer les méthodes globales pour la manipulation d'un site
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Site{
    
    var $cache;
    var $bCache=true;
    var $idBase;
    var $idExi;
	var $login;
	var $pwd;
    var $user;
	var $graine;
	var $dbA;
	var $dbC;		
	var $dbCG;		
	var $dbD;
	var $dbDT;
	var $dbE;
	var $dbED;
	var $dbET;
	var $dbETD;
	var $dbG;
	var $dbGM;
	var $dbGUD;
	var $dbIEML;
	var $dbM;
	var $dbR;		
	var $dbT;
	var $dbTrad;
	var $dbTT;
	var $dbTD;		
	var $dbU;
	var $dbUIEML;
	var $dbUU;
	var $dbUT;
	var $dbUD;
	var $dbUTD;
	var $db;
	var $lucene;
    //pour l'optimisation
    var $bTrace = false;
    var $bTraceFlush = false;//mettre false pour les traces de debuggage
    var $echoTrace = false;
	var $temps_debut;
    var $temps_inter;
    var $temps_nb=0;
    var $idDoc;
    var $idGeo=0;
    var $type;
	var $idDocRoot;
	var $idMonade;
	var $idTagRoot;
	var $rs;
	var $rsVerifGroup = array();
	var $bconnect = false;
	
    
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
        
        //vérifie la connexion extérieur
        try {        
	        $g = fopen("http:\\www.google.com", "r") ? true : false;
	        $this->bConnect = true;
        }catch (Zend_Exception $e) {
            	$this->bConnect = false;
        }
        
    
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
     * Fonction pour initialiser les tables de la base de données
     *
     */
    function initDbTables(){
    		$this->trace("DEBUT ".__METHOD__);
    	 	/*construction des objets*/
	    	if(!$this->dbD){
	    	    $this->dbD = new Model_DbTable_Flux_Doc($this->db);
        	    	$this->trace("Model_DbTable_Flux_Doc");
        	    	if(!$this->dbE)$this->dbE = new Model_DbTable_Flux_Exi($this->db);
        	    	$this->trace("Model_DbTable_Flux_Exi");
        	    	if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
        	    	$this->trace("Model_DbTable_Flux_Tag");
        	    	if(!$this->dbR)$this->dbR = new Model_DbTable_Flux_Rapport($this->db);
        	    	$this->trace("Model_DbTable_Flux_Rapport");
        	    	if(!$this->dbM)$this->dbM = new Model_DbTable_Flux_Monade($this->db);
        	    	$this->trace("Model_DbTable_Flux_Monade");
        	    	if(!$this->dbA)$this->dbA = new Model_DbTable_Flux_Acti($this->db);
        	    	$this->trace("Model_DbTable_Flux_Acti");
        	    	if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
        	    	$this->trace("Model_DbTable_Flux_Uti");
        	    	if(!$this->dbG)$this->dbG = new Model_DbTable_Flux_Geo($this->db);
        	    	$this->trace("Model_DbTable_Flux_Geo");  
	    	}
        	    	
	    	$this->trace("FIN ".__METHOD__);
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
     * @param string 	$url
     * @param array 		$param
     * @param boolean 	$cache
     * @param array	 	$rawData
     *   
     * @return string
     */
	function getUrlBodyContent($url, $param=false, $cache=true, $method=null, $rawData=false) {
		$html = false;
		/*pas d'encodage explicite
		if(substr($url, 0, 7)!="http://")$url = urldecode($url);
		*/
		if($cache){
			$c = str_replace("::", "_", __METHOD__)."_".md5($url); 
			if($param)$c .= "_".$this->getParamString($param);
		   	$html = $this->cache->load($c);
		}
        if(!$html){
		    	$client = new Zend_Http_Client($url,array('timeout' => 30));
		    	if($param && !$method)$client->setParameterGet($param);
		    	if($param && $method==Zend_Http_Client::POST)$client->setParameterPost($param);
		    	if($rawData) $client->setRawData($rawData["value"], $rawData["type"]);
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
	 * récupère le code XML d'un domNode
	 * pour par exemple faire un Xpath dessus
	 *
	 * @param  domNode 	$n
	 *
	 * @return string
	 */
	function getStringDomNode($n) {
		$newdoc = new DOMDocument();
		$cloned = $n->cloneNode(TRUE);
		$newdoc->appendChild($newdoc->importNode($cloned,TRUE));
		$s = $newdoc->saveHTML();
		return $s;
	}
	
	
	/**
	 * Formats a line (passed as a fields  array) as CSV and returns the CSV as a string.
	 * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120
	 * 
     * 
	 */
	function arrayToCsv( array &$fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false ) {
		$delimiter_esc = preg_quote($delimiter, '/');
		$enclosure_esc = preg_quote($enclosure, '/');
		$output = array();
		foreach ( $fields as $field ) {
			if ($field === null && $nullToMysqlNull) {
				$output[] = 'NULL';
				continue;
			}
	
			// Enclose fields containing $delimiter, $enclosure or whitespace
			if ( $encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field ) ) {
				$output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
			}
			else {
				$output[] = $field;
			}
	}
	
		return implode( $delimiter, $output );
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
	    	$csvarray = array();
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
     * création d'un tableau à partir d'une chaine csv
     *
     * @param string 	$csv = chaine de caractère csv
     * @param string 	$sep = séparateur de valeur
     * @param booblean 	$first = la première ligen contient le nom des champs
     *
     * @return array
     */
    function csvStringToArray($csv, $sep=";", $first=true){
    		$arr = array();
    		$cols = false;
		$data = str_getcsv($csv, "\n"); //parse the rows 
		foreach($data as $row){
			if($first){
				$cols = str_getcsv($row, $sep);
				$first=false;
			}else{
				$r = str_getcsv($row, $sep); //parse the items in rows 
				if($cols){
					$arrR = array();
					for ($i = 0; $i < count($cols); $i++) {
						if(isset($r[$i]))$arrR[$cols[$i]]=$r[$i];
					}
					$r = $arrR;	
				}
				$arr[] = $r;
			}
		} 
		
		return $arr;
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
			else $s .= "_".str_replace(",","_",$v);
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
     * Récupère le contenu d'une page html avec des argument POST
     * @param string 	$url
     * @param array 		$args
     * @param string 	$contentType
     * 
     * @return string
     */
	function getUrlPostContent($url, $args, $contentType='application/x-www-form-urlencoded'){

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
			'Content-type'=> $contentType,
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
	 * Remplace les caratcères spéciaux
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 */
	function xml_entities($string) {
		return strtr(
				$string,
				array(
						"<" => "&lt;",
						">" => "&gt;",
						'"' => "&quot;",
						"'" => "&apos;",
						"&" => "&amp;",
				)
				);
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
	 * tri un tableau sur plusieurs clefs
	 *
	 * @example array_orderby($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
	 *
	 * @return array
	 */
	function array_orderby()
	{
		$args = func_get_args();
		$data = array_shift($args);
		foreach ($args as $n => $field) {
			if (is_string($field)) {
				$tmp = array();
				foreach ($data as $key => $row)
					$tmp[$key] = $row[$field];
					$args[$n] = $tmp;
			}
		}
		$args[] = &$data;
		call_user_func_array('array_multisort', $args);
		return array_pop($args);
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
     *  
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
                    //cmfcDirectory::makeAll($dest,$options['folderPermission'],true); 
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
    
	/**
	 * Retourne le nom de domaine d'une url
	 *
	 * @param string $url L'url dont il faut récupérer le NDD
	 * @return string $domain Le nom de domaine
	 * @phpversion : 5+
	 * @see http://fr.php.net/parse-url
	 * @author : Hugo HAMON <webmaster@apprendre-php.com>
	 */
	function getNomDeDomaine($url) {
	    
	    $hostname = parse_url($url, PHP_URL_HOST);
	    $hostParts = explode('.', $hostname);
	    $numberParts = sizeof($hostParts);
	    $domain='';
	    
	    // Domaine sans tld (ex: http://server/page.php)
	    if(1 === $numberParts) {
	        $domain = current($hostParts);
	    }
	    // Domaine avec tld (ex: http://fr.php.net/parse-url)
	    elseif($numberParts>=2) {
	        $hostParts = array_reverse($hostParts);
	        $domain = $hostParts[1] .'.'. $hostParts[0];
	    }
	    return $domain;
	}    
	
	/**
     * regroupe les données par clefs et titre
     *
     * @param string 	$k
     * @param integer 	$id
     * @param string 	$titre
     *
     */
	function groupResult($k,$id,$titre){
		if(!isset($this->rsVerifGroup[$k.$id])){
			$this->rs[$k][]=array("recid"=>$id,"lib"=>$titre);	
			$this->rsVerifGroup[$k.$id]=1;
		}		
	}	
	
	
	static function guid($lowercase = TRUE) {
		$charid = strtoupper(md5(uniqid(rand(), TRUE)));
		$hyphen = chr(45);// "-"
		$uuid = substr($charid, 0, 8) . $hyphen
		. substr($charid, 8, 4) . $hyphen
		. substr($charid, 12, 4) . $hyphen
		. substr($charid, 16, 4) . $hyphen
		. substr($charid, 20, 12);
		return $lowercase ? strtolower($uuid) : $uuid;
	}
	
	
}
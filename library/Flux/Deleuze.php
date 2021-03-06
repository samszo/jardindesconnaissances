<?php
/**
 * Flux_Deleuze
 * Classe qui gère les flux venant du site deleuze
 * http://www2.univ-paris8.fr/deleuze/
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Deleuze extends Flux_Site{
	
	var $root = "http://www2.univ-paris8.fr/deleuze/";

	public function __construct($idBase=false)
    {
    	parent::__construct($idBase);
    }
	
    /**
     * cherche
     *
     * Recherche une expression dans le texte d'un cours
     * 
     * @param string $txt
     * @return array
     *       
     */
    function cherche($txt) {

    	$luc = new Flux_Lucene(null, null, 	$this->idBase, false, '../data/deleuze-index');
    	$dbD = new Model_DbTable_Spip_Articles($this->db);
		$arr = $dbD->findFullText($txt);
    	
		$i=0;
		foreach ($arr as $doc){
			//on exclue les articles des rubriques menu
			if($doc["id_parent"]!=0){
				//on récupère les extraits de texte
				//$frags = explode($txt, utf8_encode($doc["texte"]));
				$frags = $luc->getSegments(utf8_encode($doc["texte"]), $txt);
				//récupère les phrases
				$phrases = $luc->getPhrases($frags, false, null);
				//on ajoute les phrases à la réponse
				$arr[$i]["phrases"] = $phrases;
				//ajoute le lien ver l'url complète
				$arr[$i]["fichier"] = "http://193.54.152.233/dev-deleuze.fr/".$arr[$i]["fichier"];
				//supprime le texte complet
				unset($arr[$i]["texte"]);				
			}else{
				unset($arr[$i]);				
			}
			$i++;
		} 
		return $arr;   	    	
    }
    
    
    /**
     * getLocalMp3
     *
     * Récupère les mp3 pour un usage local
     * 
     *       
     */
    function getLocalMp3() {

    	//le temps d'exécution est illimité
    	set_time_limit(0);
    	//pour tracer le temps que ça prend
    	$startTime = microtime(true);   
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		
    	//récupère le texte des documents
    	$upload_folder = "/data/deleuze/";
		$arr = $this->dbD->findByType(14);
		$i=0;
		foreach ($arr as $doc){
			//pour tester sur un doc
			if($i==8){
				try {
			    	echo "<a href='".$doc['url']."'>".$doc['url']."</a> -> ".ROOT_PATH.$upload_folder.$doc['doc_id'].".mp3<br/>";     
					//zend marche en local mais pas sur le serveur
					/*				
					$client = new Zend_Http_Client($doc['url']);
					$client->setStream(ROOT_PATH.$upload_folder.$doc['doc_id'].".mp3"); 
				    $response = $client->request();
				    copy($response->getStreamName(),ROOT_PATH.$upload_folder.$doc['doc_id'].".mp3");
				    */
					//CURL marche sur les deux
					//merci à http://www.webdigity.com/index.php?action=tutorial;code=45
					$fp = fopen (ROOT_PATH.$upload_folder.$doc['doc_id'].".mp3", 'w+');//This is the file where we save the information
					$ch = curl_init($doc['url']);//Here is the file we are downloading
					curl_setopt($ch, CURLOPT_TIMEOUT, 50);
					curl_setopt($ch, CURLOPT_FILE, $fp);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
					curl_exec($ch);
					curl_close($ch);
					fclose($fp);				    
				    
			    	echo "OK transfert ";     
				}catch (Zend_Exception $e) {
					echo "Récupère exception: " . get_class($e) . "\n";
				    echo "Message: " . $e->getMessage() . "\n";
				}
			}
			$i++;
			$endTime = microtime(true);  
			$elapsed = $endTime - $startTime;  
	    	echo ": $elapsed secondes ";     
			echo "<a href='../".$upload_folder.$doc['doc_id'].".mp3'>".$upload_folder.$doc['doc_id']."</a><br/>";     
		}    	    	
    }

    /**
     * getLocalMp3
     *
     * Converti les mp3 en ogg
     * 
     *       
     */
    function convertMp3ToOgg() {

    	//le temps d'exécution est illimité
    	set_time_limit(0);
    	//pour tracer le temps que ça prend
    	$startTime = microtime(true);   
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	if(!$this->audio)$this->audio = new Flux_Audio();
		    	
    	//récupère le texte des documents
		$arr = $this->dbD->findByType(14);
		$i=0; 
		foreach ($arr as $doc){
			$infos = $this->getInfosSon($infos, $doc);
			//on ne converti que les mp3 qui n'ont pas de ogg
			if($i>=0 && !$infos['oggInfos']){
				try {
					$this->audio->convertMp3ToOgg($infos["urlPathLocal"], $infos["urlPathLocalOgg"]);
			    	echo "<a href='".$infos["urlSonLocalOgg"]."'>".$infos["urlSonLocalOgg"]."</a> -> <a href='".$infos["urlSonLocal"]."'>".$infos["urlSonLocal"]."</a><br/>";
					$infos['oggInfos'] = $this->audio->getOggInfos($infos['urlPathLocalOgg']);	    
				}catch (Zend_Exception $e) {
					echo "Récupère exception: " . get_class($e) . "\n";
				    echo "Message: " . $e->getMessage() . "\n";
				}
			}
			$i++;
			echo $infos['oggInfos'][12]."<br/>";
			echo $infos['mp3Infos']['Length mm:ss']."<br/>";
			$endTime = microtime(true);  
			$elapsed = $endTime - $startTime;  
	    	echo "$elapsed secondes <br/><br/>";     
		}    	    	
    }    
    /**
     * addInfoDocLucene
     *
     * Ajoute des champs de recherche au document Lucene
     * 
     * @param array $docInfos
     * @param Zend_Search_Lucene_Document_Html $doc
     * 
     * @return Zend_Search_Lucene_Document_Html
     */
    function addInfoDocLucene($docInfos, $doc) {
	   	
    	//récupère le body de l'url
    	$html = $this->getUrlBodyContent($docInfos["url"]);
		$dom = new Zend_Dom_Query($html);	    
    	
		//ajoute les coordonnées de la base
		$doc->addField(Zend_Search_Lucene_Field::Keyword('idBase',$this->idBase));
		//ajoute l'identifiant du document dans la base
		$doc->addField(Zend_Search_Lucene_Field::Keyword('doc_id',$docInfos["doc_id"]));
		
		//récupère le titre du document
		$results = $dom->query('/html/body/table[2]/tr[2]/td[2]/p[1]/strong');
		$titre = "";
		foreach ($results as $result) {
		    $titre = $result->nodeValue;
		}	    
		$doc->addField(Zend_Search_Lucene_Field::Keyword('titre',$titre));
		
		//récupère le mp3 du document 
		$results = $dom->query('/html/body/table[2]/tr[2]/td[2]/table/tr/td[1]/a');
		$mp3 = "";
		foreach ($results as $result) {
		    $mp3 = $result->getAttribute("onclick");
		    $mp3 = explode("'", $mp3);
		    $mp3 = $this->root.$mp3[1];
		}	    
		$doc->addField(Zend_Search_Lucene_Field::Keyword('mp3',$mp3));

		//récupère la transcription du cours 
		$results = $dom->query('/html/body/table[2]/tr[2]/td[2]');
		$cours = "";
		foreach ($results as $result) {
		    $cours = $result->nodeValue;
		}	    
		$doc->addField(Zend_Search_Lucene_Field::text('cours',$cours));
		
		//ajoute l'url du document
		$doc->addField(Zend_Search_Lucene_Field::Keyword('url',urlencode($docInfos["url"])));
		
		//ajoute dans la base le sous-document mp3
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		$this->dbD->ajouter(array("url"=>$mp3, "tronc"=>$docInfos["doc_id"], "note"=>$cours));
		
		
		return $doc;		 
	}	

    /**
     * getTermPositions
     *
     * Récupère les positions d'un term dans les document texte et mp3
     * 
     * @param string 	$term
     * @param boolean 	$json
     * @param boolean 	$bdd
     * @param string 	$order
     * @param int		$idDoc
     * 
     * @return array
     */
    function getTermPositions($term, $json=false, $bdd=false, $order = "score DESC", $idDoc=false) {

    		$c = str_replace("::", "_", __METHOD__).md5($term); 
	   	$posis = false;//$this->cache->load($c);
	   	if(!$posis){
	        $lu = new Flux_Lucene(null, null, 	$this->idBase, false, ROOT_PATH.'/data/deleuze-index');
	   		$dbD = new Model_DbTable_Flux_Doc($this->db);

			if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
			if(!$this->audio)$this->audio = new Flux_Audio();
			
			//récupère les positions du term dans les documents
			if($bdd){
				$arr = $dbD->findFullText($term, 48, true, $order, $idDoc);
				$posis="";
				foreach ($arr as $ps) {
					$segs = $lu->getSegments($ps["texte"], $term);
					if(count($segs)>1){		    	
			    		$phrases = $lu->getPhrases($segs, $find, $ps);
					    $ps["taille"]=strlen($ps["texte"]);
					    $ps["phrases"]=$phrases;
					    $posis[] = $ps;
			    	}							
				}
			}else{
				//$lu->index->optimize();
	        		$lu->db = $this->db;
				$passToIndexer = $lu->normalize($term);
				$posis = $lu->getTermPositions(array('field'=>'cours', 'texte'=>$passToIndexer),array("titre", "url", "mp3", "doc_id"),true);					
			}				
			
			//ajoute les informations du mp3
			for ($i = 0; $i < count($posis); $i++) {
	    		//récupère le document et son contenu
				$arr = $dbD->findByTronc($posis[$i]['doc_id']);
		    		foreach ($arr as $doc){
					//vérifie si on traite le mp3
					if(substr($doc['url'],-3)=="mp3"){
						$posis[$i] = $this->getInfosSon($posis[$i], $doc);
						$posis[$i]['exi'] = "lucene";
					}
					//vérifie si on traite une position
					if(substr($doc['note'], 0, 33)=='{"controller":"deleuze","action":'){
						$tags = $this->dbUTD->GetUtiTagDoc($this->user, $doc['doc_id']);
						$posis[$i]['posis'][] = array("idDoc"=>$doc['doc_id'], "note"=>$doc['note'], "tags"=>$tags);
					}
				}
			}
			//supprime les cours qui n'ont pas de mp3
			$result = "";
			foreach ($posis as $p) {
				if(isset($p['mp3Infos']))$result[]=$p;
			}

			$this->cache->save($result, $c);			
        }
	    
		return $result;
    }

    /**
     * getInfosSon
     *
     * récupère les information du Mp3
     * 
     * @param array $posi
     * @param array $doc
     * 
     * @return array
     */
    function getInfosSon($posi, $doc){
    	
    	if(!$this->audio)$this->audio = new Flux_Audio();
    	
		$posi['urlSon'] = $doc["url"];
		$posi['text'] = htmlspecialchars(preg_replace("/(\r\n|\n|\r)/", " ", $doc["note"]));
		//met au format local l'url distante
		//$pos = strrpos($doc["url"], "/");
		//$urlSon = substr($doc["url"], 0, $pos+1).$doc["doc_id"]."-.mp3"; 
		$urlSon = $doc["url"]; 
		$posi['urlPathLocal'] = str_replace("http://www2.univ-paris8.fr/deleuze/IMG/mp3/", ROOT_PATH."/data/deleuze/mini/", $urlSon);
		$posi['urlPathLocalOgg'] = str_replace(".mp3", ".ogg", $posi['urlPathLocal']);
		$posi['urlSonLocal'] = str_replace("http://www2.univ-paris8.fr/deleuze/IMG/mp3/", WEB_ROOT."/data/deleuze/mini/", $urlSon);
		$posi['urlSonLocalOgg'] = str_replace(".mp3", ".ogg", $posi['urlSonLocal']);
		//récupère les infos du mp3
		$posi['mp3Infos'] = $this->audio->getMp3Infos($posi['urlPathLocal']);	    
		$posi['oggInfos'] = $this->audio->getOggInfos($posi['urlPathLocalOgg']);	    
		
		return $posi;
    }

    /**
     * saveAutoKeyword
     *
     * enregistre les mots-clef calculé automatiquement
     * 
     * @param string $class
     * 
     * @return array
     */
    function saveAutoKeyword($class) {
    	
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);

    	$this->user = $this->dbU->ajouter(array("login"=>$class));
		
    	//récupère le texte des documents
		$arr = $this->dbD->getAll();
		$d = new Zend_Date();
		foreach ($arr as $doc){
			//on ne traite que les mp3
			if($doc['note']!="" && substr($doc['note'], 0, 33) != '{"controller":"deleuze","action":'){
				$arrKw = $this->getKW($doc['note'], $class);
				foreach ($arrKw as $k=>$val){
					$this->saveTag($k, $doc['doc_id'], $val, $d->get("c"));
				}
			}
		}    	
    }
    
    /**
     * saveTermPosition
     *
     * enregistre les positions défini par l'utilisateur
     * 
     * @param int $idUser
     * @param array $data
     * 
     * @return array
     */
    function saveTermPosition($idUser, $data) {
    	
    	$this->user = $idUser;
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
    	//ajoute le document
    	$note = json_encode($data);
    	$idDoc = $this->dbD->ajouter(array("tronc"=>$data['idDoc'],"note"=>$note),false);
    	
		$d = new Zend_Date();
    	$idTag = $this->saveTag($data['term'], $idDoc, 1, $d->get("c"));
    	
		$tags = $this->dbUTD->GetUtiTagDoc($idUser, $idDoc);
    	    	
    	//echo "retourne les information du nouvel élément";
    	return array("idDoc"=>$idDoc, "note"=>$note, "tags"=>$tags);
    	
    }

    /**
     * suppTermPosition
     *
     * supprime une défini par l'utilisateur
     * 
     * @param int $idDoc
     * 
     */
    function suppTermPosition($idDoc) {
    	
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	$this->dbD->remove($idDoc);
    	    	
    }

    /**
     * modifTermPosition
     *
     * modifie une position défini par l'utilisateur
     * 
     * @param array $data
     * 
     */
    function modifTermPosition($data) {
    	
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		
    	//ajoute le document
    	$note = json_encode($data);
    	$this->dbD->edit($data['idDocPosi'], array("note"=>$note));
    	    	    	
    }

    /**
     * getFragment
     *
     * Récupère un fragment avec son identifiant
     * 
     * @param int $id
     * 
     * @return array
     */
    function getFragment($id) {

	    $c = str_replace("::", "_", __METHOD__).md5($id); 
	   	$posis = false;//$this->cache->load($c);
        if(!$posis){
        	
	    	$dbD = new Model_DbTable_flux_doc($this->db);
	    	$audio = new Flux_Audio();
	    	
	    	//récupère le document et son contenu
			$doc = $dbD->findBydoc_id($id);
			$doc = $doc[0];

	    	//récupère le document PARENT et son contenu
			$docP = $dbD->findBydoc_id($doc["tronc"]);
			$docP = $docP[0];
			
			//récupère le mp3 associé
			$docMp3 = $dbD->findByUrlByParent(".mp3", $doc["tronc"]);
			$docMp3 = $docMp3[0];
			
			$posis[0] = $this->getInfosSon($posis[0], $docMp3);
			$posis[0]['titre'] = "fragment de ".$docP["titre"];
			$posis[0]['phrases'] = "";
			$posis[0]['doc_id'] = $doc["tronc"];
			$posis[0]['exi'] = "lien";

			$posis[0]['posis'][] = array("idDoc"=>$doc['doc_id'], "note"=>$doc['note'], "tags"=>-1);
        	
        	$this->cache->save($posis, $c);			
        }
	    
    	return $posis;
    }    
    
}
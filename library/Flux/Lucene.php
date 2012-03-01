<?php
/**
 * Classe qui gère les flux d'indexation Lucene
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Lucene extends Flux_Site{
	
	var $index;
	var $classUrl;
	var $nbCaract = 100;
	
	public function __construct($login=null, $pwd=null, $idBase=false, $classUrl=false, $path='../data/flux-index')
    {
    	parent::__construct($idBase);
    	
    	if(!$classUrl)$this->classUrl = $this;
    	else $this->classUrl = new $classUrl();

    	$this->getDb($idBase);
    	
		try {
    		//ouverture de l'index
			$this->index = Zend_Search_Lucene::open($path);
		}catch (Zend_Exception $e) {
			// Création de l'index
			$this->index = Zend_Search_Lucene::create($path);
		}
    		
    }

    function addDoc($DocInfos, $replace=false) {
    	$url = $DocInfos["url"];
    	//vérifie si le document existe
    	$hits = false;//$this->index->find('url:'.urlencode($url));
    	if($hits){
    		if(!$replace)return "";
    		else $this->deleteDoc($hits);
    	} 
    	
		$c = str_replace("::", "_", __METHOD__)."_".md5($url); 
	   	$doc = false;//$this->cache->load($c);
        if(!$doc){
    		$doc = Zend_Search_Lucene_Document_Html::loadHTMLFile($url);
			$this->cache->save($doc, $c);
        }
		//récupère les informations du type de document
		$doc = $this->classUrl->addInfoDocLucene($DocInfos, $doc);
        $this->index->addDocument($doc);		 
	}
	
	function deleteDoc($hits){
		foreach ($hits as $hit) {
		    $this->index->delete($hit->id);
		}		
	}
	
    function addDocsInfos($replace=false) {   	
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    	//$arr = $this->dbD->getDistinct("url");
    	$arr = $this->dbD->getAll();
    	$i = 0;
    	foreach ($arr as $u){
    		if($u["tronc"]==""){// && $u["doc_id"]>=4318
    			$this->addDoc($u,$replace);
    		}  		
    		$i++;
    	}
	}
	
	function find($query) {
		$c = str_replace("::", "_", __METHOD__)."_".md5($query); 
	   	$hits = $this->cache->load($c);
        if(!$hits){
			$hits = $this->index->find($query);
			$this->cache->save($hits, $c);
        }
        return $hits;
	}
	
	function getTermPositions($term, $fields=array("title", "url")) {
		//création du term
		$c = str_replace("::", "_", __METHOD__)."_".$term['text']."_".$term['field']; 
	   	$result = $this->cache->load($c);
        if(!$result){
			$objTerm = new Zend_Search_Lucene_Index_Term(strtolower($term['text']), $term['field']);
			$posis = $this->index->termPositions($objTerm);
			$result = array();
			foreach ($posis as $kD => $ps) {
				//on récupère le document lucene
		    	$doc = $this->index->getDocument($kD);
			    $url = urldecode($doc->getFieldValue('url'));
		    	//récupère le contenu du document
		    	if($term['field']=="cours"){
			    	$html = $doc->getFieldValue('cours');		    		
		    	}else{
			    	$html = $this->getUrlBodyContent($url);		    		
		    	}
		    	//on recherche les occurrences du mots dans le doc
				$pattern = '/\b'.$term['text'].'\b/i';
				$segs = preg_split($pattern, $html);
			    //on calcule les phrases
			    $pHTML=0;$phrases=array();
			    for ($i = 0; $i < count($segs); $i++) {
			    	//vérifie si on traite le premier élément
					if($i==0){
						//on récupère le début de la phrase
						//$deb = substr($segs[$i], -$this->nbCaract, strlen($segs[$i]));
						$deb = $this->cutStr($segs[$i],$this->nbCaract,"...","dg");
						$p = $ps[$i];	
						$pHTML += strlen($segs[$i]);
						//on récupère la fin de la phrase
						//$arrFin = $this->getFinPhrase($segs, $i+1);
						$fin = $this->cutStr($segs[$i+1],$this->nbCaract);
					}else{
						//on récupère le début de la phrase
						//$deb = substr($segs[$i-1], -$this->nbCaract, strlen($segs[$i-1]));
						$deb = $this->cutStr($segs[$i-1],$this->nbCaract,"...","dg");
						$p = $ps[$i-1];
						$pHTML += strlen($term['text'])+strlen($segs[$i-1]);
						//on récupère la fin de la phrase
						//$arrFin = $this->getFinPhrase($segs, $i);
						$fin = $this->cutStr($segs[$i],$this->nbCaract);
					}
					
		    		$phrases[] = array("p"=>$p, "pHTML"=> $pHTML, "deb"=> $deb,"fin"=> $fin);
		    		$i++;
					
			    }
			    //on stocke les informations
			    $result[] = array("titre"=>$doc->getFieldValue('titre'),"url"=>$url,"mp3"=>$doc->getFieldValue('mp3'),"taille"=>strlen($html),"phrases"=>$phrases);
			}
			$this->cache->save($result, $c);
        }

		$objTerm = new Zend_Search_Lucene_Index_Term(strtolower($term['text']), $term['field']);
		//récupère les positions du term
		$c = str_replace("::", "_", __METHOD__)."_getPosis_".$term['field']."_".$term['text']; 
	   	$posis = $this->cache->load($c);
        if(!$posis){
			$posis = $this->index->termPositions($objTerm);
        	$this->cache->save($posis, $c);			
        }
		
		$result = array();
		foreach ($posis as $kD => $ps) {
			//on récupère le document lucene
	    	$doc = $this->index->getDocument($kD);
	    	//récupère le body de l'url
	    	$html = $this->getUrlBodyContent($doc->getFieldValue('url'));
	    	//on recherche les occurrences du mots dans le doc
			$pattern = '/\b'.$term['text'].'\b/i';
			$segs = preg_split($pattern, $html);
		    //on calcule les phrases
		    $pHTML=0;$phrases=array();
		    for ($i = 0; $i < count($segs); $i++) {
		    	//vérifie si on traite le premier élément
				if($i==0){
					//on récupère le début de la phrase
					//$deb = substr($segs[$i], -$this->nbCaract, strlen($segs[$i]));
					$deb = $this->cutStr($segs[$i],$this->nbCaract,"...","dg");
					$p = $ps[$i];	
					$pHTML += strlen($segs[$i]);
					//on récupère la fin de la phrase
					//$arrFin = $this->getFinPhrase($segs, $i+1);
					$fin = $this->cutStr($segs[$i+1],$this->nbCaract);
				}else{
					//on récupère le début de la phrase
					//$deb = substr($segs[$i-1], -$this->nbCaract, strlen($segs[$i-1]));
					$deb = $this->cutStr($segs[$i-1],$this->nbCaract,"...","dg");
					$p = $ps[$i-1];
					$pHTML += strlen($term['text'])+strlen($segs[$i-1]);
					//on récupère la fin de la phrase
					//$arrFin = $this->getFinPhrase($segs, $i);
					$fin = $this->cutStr($segs[$i],$this->nbCaract);
				}
				
	    		$phrases[] = array("p"=>$p, "pHTML"=> $pHTML, "deb"=> $deb,"fin"=> $fin);
	    		$i++;
				
		    }
		    //on stocke les informations
		    //$result[] = array("titre"=>$doc->getFieldValue('titre'),"url"=>$doc->getFieldValue('url'),"mp3"=>$doc->getFieldValue('mp3'),"taille"=>strlen($html),"phrases"=>$phrases);
		    $r = "";
		    foreach ($fields as $f) {
		    	$r[$f]=$doc->getFieldValue($f);
		    }
		    $r["taille"]=strlen($html);
		    $r["phrases"]=$phrases;
		    $result[] = $r;
		}
		
		return $result;
	}
	
	function getFinPhrase($segments, $i){
		//vérifie s'il reste encore des segments
		if($i>count($segments)){
			$fin = "";										
			return array($i,$fin);	
		}		
		//on récupère la fin de la phrase
		$fin = strpos($segments[$i], ".");
		//vérifie s'il faut chercher la fin de la phrase dans l'élément suivant
		if($fin===false){
			$this->getFinPhrase($segments, $i+1);
		}else{
			$fin = substr($segments[$i], 0, $fin+1);										
			return array($i,$fin);				
		}					
	}

	/**
	 * Coupe une chaine de caractères (sans interrompre un mot)
	 *
	 * @param string $str chaine à couper
	 * @param integer $max nombre maximum de caractères
	 * @param string $finish chaine de caractère à appliquer en fin.
	 * @return string
	 */
	public function cutStr($str, $max = 100, $finish = '...', $dir="gd"){

		if (strlen($str) <= $max){
	        return $str;
	    }
	        
	    $max = intval($max) - strlen($finish);
	        
	    /* 
	     * On coupe la chaine au nombre de caractères... +1, et on récupére toute
	     * la chaine... jusqu'au dernier caractère blanc.
	     * sois de gauche à droite $dir = "gd";
	     * sois de droite à gauche $dir = "dg";
	     */
	    if($dir == "gd"){
		    $str = substr($str, 0, $max + 1);    	
	    	$str = strrev(strpbrk(strrev($str), " \t\n\r\0\x0B"));
	    }
	    if($dir == "dg"){
		    $str = substr($str, -$max + 1, strlen($str));    	
	    	$str = strpbrk($str, " \t\n\r\0\x0B");
	    }
	   
	    
	    if($dir == "gd"){
		    $str = rtrim($str) . $finish;  	
	    }
	    if($dir == "dg"){
		    $str = $finish.rtrim($str);   	
	    }
	    
	    return str_replace(CHR(13).CHR(10),"",strip_tags($str));
	}
	
	function removeAllIndex(){
		$nb = $this->index->count();
		for ($i = 0; $i < $nb; $i++) {
		    $this->index->delete($i);
		}
	}

	function getDocsTerms(){
		$nb = $this->index->numDocs();
		for ($i = 0; $i < $nb; $i++) {
		    $doc = $this->index->getDocument($i);
		    $fields = $doc->getFieldNames();
		    $body = $doc->getFieldValue("body");
		}
	}
	
    /**
     * Enregistre dans la base de donnée les termes associés aux document
     *
     * @param array $terms
     * 
     */
	function saveDocsTerms($terms=""){

		//création des tables
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
		if(!$terms){
			$c = "LuceneGetAllTerms"; 
		   	$terms = $this->cache->load($c);
	        if(!$terms){
				$terms = $this->index->terms();
				$this->cache->save($terms, $c);
	        }
		}
		
        foreach ($terms as $kT => $term) {
        	//on ne gère que les terms du body
        	if($term->field == "body"){
				//retrouve la fréquence du term pour chaque document
				$docs = $this->index->termFreqs($term);
			    
			    //pour chaque document
			    foreach ($docs as $kD=>$freq) {
			    	//on récupère le document lucene
			    	$doc = $this->index->getDocument($kD);			    	
				    //retrouve le document avec l'url
				    $idDoc = $this->dbD->ajouter(array("url"=>urldecode($doc->getFieldValue('url'))));
				    //retrouve le tag correspondant au terme
					$idT = $this->dbT->ajouter(array("code"=>$term->text));
					//on ajoute le lien entre le tag et le doc avec le poids
					$this->dbTD->ajouter(array("tag_id"=>$idT, "doc_id"=>$idDoc,"poids"=>$freq));
					//on ajoute le lein entre le tag l'utilisateur et le doc
					$this->dbUTD->ajouter(array("uti_id"=>$this->user, "tag_id"=>$idT, "doc_id"=>$idDoc));						
			    }        		
        	}
        }
	}
	
}
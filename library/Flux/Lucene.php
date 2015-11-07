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

    function normalize ($string){
    	$a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ
ßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
    	$b = 'aaaaaaaceeeeiiiidnoooooouuuuy
bsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
    	$string = utf8_decode($string);
    	$string = strtr($string, utf8_decode($a), $b);
    	$string = strtolower($string);
    	return utf8_encode($string);
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
	   	$hits = false;//$this->cache->load($c);
        if(!$hits){
			$hits = $this->index->find($query);
			$this->cache->save($hits, $c);
        }
        return $hits;
	}
	
	function getTermPositions($term, $fields=array("title", "url"), $find=false) {

	    $c1 = str_replace("::", "_", __METHOD__).$term['field']."_".md5($term['texte'])."_".$find; 
		foreach ($fields as $f) {
	    		$c1 .= "_".$f;
	    }
	   	$result = $this->cache->load($c1);
        if($result){
        		return $result;
        }
	    
        if($find){
			//recherche la requête
			$c = str_replace("::", "_", __METHOD__)."_find_".$term['field']."_".md5($term['texte']); 
		   	$posis = $this->cache->load($c);
	        if(!$posis){
	        		$posis = $this->find($term['field'].":".$term['texte']);
	        		$this->cache->save($posis, $c);			
	        }        	
        }else{
			$objTerm = new Zend_Search_Lucene_Index_Term(strtolower($term['text']), $term['field']);
			//récupère les positions du term
			$c = str_replace("::", "_", __METHOD__)."_getPosis_".$term['field']."_".md5($term['texte']); 
		   	$posis = $this->cache->load($c);
	        if(!$posis){
				$posis = $this->index->termPositions($objTerm);
	        		$this->cache->save($posis, $c);			
	        }        	
        }
		
		        
		$result = array();
		foreach ($posis as $kD => $ps) {
			//on récupère le document lucene
	        if($find)			
		    		$doc = $this->index->getDocument($ps->id);
		    else
		    		$doc = $this->index->getDocument($kD);
		    $url = urldecode($doc->getFieldValue('url'));
		    	//récupère le contenu du document
		    	if($term['field']=="cours"){
			    	$html = $doc->getFieldValue('cours');		    		
		    	}else{
			    	$html = $this->getUrlBodyContent($url);		    		
		    	}
	
		    	//récupère les segments de phrase
		    	$segs = $this->getSegments($html, $term['texte']);

			/**
			 * TODO:
			 * régler le problème de case sensitive qui fait que Lucène renvoit des valeurs et pas $this->getSegments
			 */
		    	if(count($segs)>1){
		    	
		    		$phrases = $this->getPhrases($segs, $find, $ps);
			    $r = "";
			    foreach ($fields as $f) {
			    	if($f=="url")
				    	$r[$f]=urldecode($doc->getFieldValue($f));
			    	else
				    	$r[$f]=$doc->getFieldValue($f);
			    }
			    $r["taille"]=strlen($html);
			    $r["phrases"]=$phrases;
			    $result[] = $r;
		    	}		    
		}
        $this->cache->save($result, $c1);			
		
		return $result;
	}

	function getPhrases($segs, $find, $ps){
		
	    	//on calcule les phrases
		    $pHTML=0;$phrases=array();
		    for ($i = 0; $i < count($segs); $i++) {
		    	//vérifie si on traite le premier élément
				if($i==0){
					//on récupère le début de la phrase
					$deb = $this->cutStr($segs[$i],$this->nbCaract,"","dg");
					//dans le cas d'un termPositions c'est lucène qui calcule la position
					if($find)$p = -1; else $p = $ps[$i];
					//on récupère la position manuelle 	
					$pHTML += strlen($segs[$i]);
					//on récupère le mot trouvé
					$FinFind = strpos($segs[$i+1], "</find>");
					$mot = substr($segs[$i+1], 0, $FinFind);
					//on récupère la fin de la phrase
					$fin = $this->cutStr(substr($segs[$i+1], $FinFind),$this->nbCaract,"");
				}else{
					//on récupère le début de la phrase
					$deb = $this->cutStr($segs[$i-1],$this->nbCaract,"","dg");
					if($find)$p = -1; else $p = $ps[$i-1];
					//on récupère le mot trouvé
					$FinFind = strpos($segs[$i], "</find>");
					$mot = substr($segs[$i], 0, $FinFind);
					//on calcule  la position manuelle
					$pHTML += strlen($mot)+strlen($segs[$i-1]);
					//on récupère la fin de la phrase
					$fin = $this->cutStr(substr($segs[$i], $FinFind),$this->nbCaract,"");
					//on supprime le find du segment
					$segs[$i] = substr($segs[$i], $find);
				}
				
	    		$phrases[] = array("p"=>$p, "pHTML"=> $pHTML, "deb"=> $deb,"fin"=> $fin,"mot"=> $mot);
	    		$i++;
				
		    }
		return $phrases;
	}
	
	
	function getSegments($html, $texte){
		
	    	//vérification des requêtes complexes and
	    	$arrQ = split(" and ", $texte);
	    	
	    	//vérification des requêtes complexes or
	    	if(count($arrQ)==1) $arrQ = split(" or ", $texte);
	    	
	    	$result = array();
	    	
		//tag les mots de la requête
	    	for ($i = 0; $i < count($arrQ); $i++) {
	    		//$html = preg_replace("[(".$arrQ[$i].")]", '<find>$1</find>', $html);
	    		$html = preg_replace("/".$arrQ[$i]."/i", '<find>'.$arrQ[$i].'</find>', $html);
	    		    		
	    	}
	    	//création du tableau des résultats
	    	$pattern = "[<find>]";
	    	$result = preg_split($pattern, $html);
    	
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
	        return htmlspecialchars(preg_replace("/(\r\n|\n|\r)/", " ", strip_tags($str)));
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
	    
	    return htmlspecialchars(preg_replace("/(\r\n|\n|\r)/", " ", strip_tags($str)));
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
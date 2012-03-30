<?php
/**
 * Classe qui gère les flux venant du site deleuze
 * http://www2.univ-paris8.fr/deleuze/
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Deleuze extends Flux_Site{
	
	var $root = "http://www2.univ-paris8.fr/deleuze/";

	public function __construct($idBase=false)
    {
    	parent::__construct($idBase);
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
     * @param string $term
     * 
     * @return array
     */
    function getTermPositions($term) {

	    $c = str_replace("::", "_", __METHOD__).$term; 
	   	$posis = false;//$this->cache->load($c);
        if(!$posis){
        	
	    	$lu = new Flux_Lucene();
			$lu->db = $this->db;
	    	$dbD = new Model_DbTable_flux_doc($this->db);
			if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
	    	$audio = new Flux_Audio();
	    	
			//récupère les positions du term dans les documents
			$posis = $lu->getTermPositions(array('field'=>'cours', 'text'=>$term),array("titre", "url", "mp3", "doc_id"));
	    	
			//ajoute les informations du mp3
			for ($i = 0; $i < count($posis); $i++) {
	    		//récupère le document et son contenu
				$arr = $dbD->findByTronc($posis[$i]['doc_id']);
				foreach ($arr as $doc){
					//vérifie si on traite le mp3
					if(substr($doc['url'],-3)=="mp3"){
						$posis[$i]['exi'] = "lucene";
						$posis[$i]['urlSon'] = $doc["url"];
						$pathLocal = str_replace("http://www2.univ-paris8.fr/deleuze/IMG/mp3/", ROOT_PATH."/data/deleuze/", $doc["url"]);
						$posis[$i]['mp3Infos'] = $audio->getMp3Infos($pathLocal);	    
						$posis[$i]['urlSonLocal'] = str_replace("http://www2.univ-paris8.fr/deleuze/IMG/mp3/", WEB_ROOT."/data/deleuze/", $doc["url"]);;
						$posis[$i]['text'] = htmlspecialchars(preg_replace("/(\r\n|\n|\r)/", " ", $doc["note"]));									
					}
					//vérifie si on traite une position
					if(substr($doc['note'], 0, 33)=='{"controller":"deleuze","action":'){
						$tags = $this->dbUTD->GetUtiTagDoc($this->user, $doc['doc_id']);
						$posis[$i]['posis'][] = array("idDoc"=>$doc['doc_id'], "note"=>$doc['note'], "tags"=>$tags);
					}
				}
			}
        	
        	$this->cache->save($posis, $c);			
        }
	    
    	return $posis;
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
    
}
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
	
	public function __construct($login=null, $pwd=null)
    {
    	parent::__construct();
    	
		$this->index = Zend_Search_Lucene::open('../data/flux-index');
		// Création de l'index
		//$this->index = Zend_Search_Lucene::create('../data/flux-index');
    }

    function addDoc($url) {
		$c = str_replace("::", "_", __METHOD__)."_".md5($url); 
	   	$doc = $this->cache->load($c);
        if(!$doc){
    		$doc = Zend_Search_Lucene_Document_Html::loadHTMLFile($url);
			$this->cache->save($doc, $c);
        }
		//récupère les informations du type de document
		$doc = $this->classUrl->addInfoDocLucene($url, $doc);
		$this->index->addDocument($doc);		 
	}
	
    function addBddDocs() {
    	$this->removeAllIndex();
    	if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc();
    	$arr = $this->dbD->getDistinct("url");
    	//TODO gérer dynamiquement la création de class avec une info dans la table doc
    	$this->classUrl = new Flux_Deleuze();
    	foreach ($arr as $u){
    		$this->addDoc($u["url"]);
    	}
	}
	
	function find($query) {
		/*TODO le cache ne marche pas bien
		$c = str_replace("::", "_", __METHOD__)."_".md5($query); 
	   	$hits = $this->cache->load($c);
        if(!$hits){
			$hits = $this->index->find($query);
			$this->cache->save($hits, $c);
        }
        */
		$hits = $this->index->find($query);
        return $hits;
	}
	
	function removeAllIndex(){
		$nb = $this->index->count();
		for ($i = 0; $i < $nb; $i++) {
		    $this->index->delete($i);
		}
	}
	
	
}
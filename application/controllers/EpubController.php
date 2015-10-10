<?php

class EpubController extends Zend_Controller_Action
{
	var $idBase = "spip_samszo";//"flux_livrenum";
	var $urlDomaine = "www.samszo.univ-paris8.fr/";//"localhost/samszo/";
	var $urlBook = "spip.php?page=e-guideRub&id_rubrique=";
	var $urlChap = "spip.php?page=e-guide&id_article=";
	var $urlLocal = "local/";
	
	
    public function indexAction()
    {

		 		
    }

    public function spipAction()
    {
    		$cache = false;
    		//initialisation des objets
		if($this->_getParam('domaine'))$this->urlDomaine = $this->_getParam('domaine');
    		if($this->_getParam('idBase'))$this->idBase = $this->_getParam('idBase');
		$s = new Flux_Site($this->idBase);
		$spipRubDb = new Model_DbTable_Spip_rubriques($s->db);
		$spipArtDb = new Model_DbTable_Spip_articles($s->db);
		
		$this->initUrl();		
		
		//récupère la description de l'epub
		$this->view->urlBook = $this->urlBook.$this->_getParam('idRub');
		//$this->view->htmlBook = $s->getUrlBodyContent($this->view->urlBook, false, $cache);
		//$this->view->arrBook = json_decode($html);
		$this->view->arrBook = $spipRubDb->findById_rubrique($this->_getParam('idRub'));		
		
		$this->view->Identifier = "http://idefi-creatic.net/";
		$this->view->Langue = "fr";
		$this->view->Description = "This is a brief description\nA test ePub book as an example of building a book in PHP";
		$this->view->Auteur = "CreaTIC";
		$this->view->AuteurKey = "CreaTIC";
		$this->view->Editeur = "CreaTIC";
		$this->view->EditeurUrl = "http://idefi-creatic.net/";
		$this->view->Licence = "Creative Commons BY NC SA";
		$this->view->SourceURL = $this->view->urlBook;

		//récupère les articles de la rubrique
		$arrChap = $spipArtDb->findById_rubrique($this->_getParam('idRub'));
		//pour chaque article
		for ($i = 0; $i < count($arrChap); $i++) {
			//récupère la mise en forme HTML
			$arrChap[$i]["urlChap"] = $this->urlChap.$arrChap[$i]["id_article"];
			$arrChap[$i]["html"] = $s->getUrlBodyContent($arrChap[$i]["urlChap"], false, $cache);
			//mise à jour des liens
			$arrChap[$i]["html"] = str_replace('local/', $this->urlLocal, $arrChap[$i]["html"]);
		}
		$this->view->arrChap = $arrChap;	
    }
    
    function initUrl()
    {
		$this->urlBook = "http://".$this->urlDomaine.$this->urlBook;
		$this->urlChap = "http://".$this->urlDomaine.$this->urlChap;
		$this->urlLocal= "http://".$this->urlDomaine.$this->urlLocal;
    }
    
}


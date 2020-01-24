<?php
/**
 * FluxGorafi
 * Classe qui gère les flux du site web Gorafi
 * 
 * REFERENCES
 * http://www.legorafi.fr
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Scraping
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Gorafi extends Flux_Site{

    var $urlBase = 'http://www.legorafi.fr/';
    var $urlPage = '/?debut_veille_scientifique_article=';
    var $urlCat = 'category/';
    var $categories = array(
        "sciences",
        "hi-tech",
        "politique",
        "societe",
        "monde-libre",
        "culture",
        "people",
        "sports",
        "economie"
    );
    var $lastIdDoc = 0;
    var $nbPagination = 0;
    var $nbArticleParPage = 6;
    var $nbArticle = 0;
    /**
     * Constructeur de la classe
     *
     * @param  string   $idBase
     * @param  boolean  $bTrace
     * @param  boolean  $bCache
     * 
     */
	public function __construct($idBase=false, $bTrace=true, $bCache=true)
    {
            if(!$idBase)$idBase=$this->idBaseDefault;
    		parent::__construct($idBase, $bTrace, $bCache);    	

            //on récupère la racine des documents
            $this->initDbTables();
            $this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
            $this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
            $this->idTagRoot = $this->dbT->ajouter(array("code"=>__CLASS__));
            //on enregistre les tags utiles
            $this->idTagRedacteur = $this->dbT->ajouter(array("code"=>'rédacteur',"parent"=>$this->idTagRoot));
            $this->idTagDate = $this->dbT->ajouter(array("code"=>'date',"parent"=>$this->idTagRoot));
            $this->idTagCat = $this->dbT->ajouter(array("code"=>'catégories',"parent"=>$this->idTagRoot));
            $this->idTagComment = $this->dbT->ajouter(array("code"=>'commentaire',"parent"=>$this->idTagRoot));
            $this->idTagStructure = $this->dbT->ajouter(array("code"=>'structure',"parent"=>$this->idTagRoot));
            $this->idTagChapo = $this->dbT->ajouter(array("code"=>'chapo',"parent"=>$this->idTagStructure));
            $this->idTagTitre = $this->dbT->ajouter(array("code"=>'titre',"parent"=>$this->idTagStructure));
            $this->idTagParagraphe = $this->dbT->ajouter(array("code"=>'paragraphe',"parent"=>$this->idTagStructure));
            $this->idTagListe = $this->dbT->ajouter(array("code"=>'liste',"parent"=>$this->idTagStructure));
	    	
    }

     /**
     * Enregistre les informations d'une catégorie du gorafi
     *
     * @param   string  $categorie
     * @param   int     $curset
     *
     * @return array
     */
    public function saveCategorie($categorie,$curset=1)
    {
        $this->trace(__METHOD__." - ".$categorie." - ".$curset);
        set_time_limit(0);

        //enregistre l'action        
        $idAct = $this->dbA->ajouter(array('code'=>__METHOD__));

        //enregistre le mot clef de recherche
        $idTagC = $this->dbT->ajouter(array('code'=>$categorie,'parent'=>$this->idTagCat));

        //récupère la réponse
        $url = $this->urlBase
            .$this->urlCat
            .$categorie.'/page/'.$curset;            
        $this->trace($url);
        $html = $this->getUrlBodyContent($url,false,$this->bCache);
        // Create a new DOM Document to hold our webpage structure
        $domDoc = new DOMDocument();
        $domDoc->loadHTMLFile($url);
        
        //enregistre le doc
        $idDoc = $this->dbD->ajouter(array('titre'=>'Gorafi catégorie '.$categorie.' '.$curset,'url'=>$url, 'data'=>$html, 'parent'=>$this->idDocRoot));

        //enregistre le rapport
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idTagC,"src_obj"=>"tag"
				,"dst_id"=>$idDoc,"dst_obj"=>"doc"
				,"pre_id"=>$idAct,"pre_obj"=>"acti"
    		));						

        $i=0;
        if($html){
            $dom = new Zend_Dom_Query($html);	    

            //récupère le nb de pagination
            if(!$this->nbPagination){
                $xPath = '//*[@id="main"]/div[5]/div/a[3]';
                $results = $dom->queryXpath($xPath);
                $i=0;
                foreach ($results as $result) {
                    $this->nbPagination = intval($result->nodeValue);
                } 
                //$this->nbArticle = $this->nbPagination * $this->nbArticleParPage;         
                //$this->trace("NB PAGES = ".$this->nbPagination." NB ARTICLE =".$this->nbArticle);
                $this->trace("NB PAGES = ".$this->nbPagination);
            }

            //récupère la liste des articles
            $xPath = '//*[@class="articles"]/article';
            $results = $dom->queryXpath($xPath);
            $i=0;
            foreach ($results as $result) {
                //récupère les infos de l'article
                $arr = $this->nodeToArray($domDoc, $result);
                $lien = $arr['figure']['a']['@href'];
                $titre = $arr['h2']['a'];
                $img = $arr['figure']['a']['img']['@src'];
                $niv = $arr['a'];
                //vérifie la présence du lien
                $doc = $this->dbD->existe(array('url'=>$lien));
                if(!$doc){
                    //enregistre le doc
                    $idDocArt = $this->dbD->ajouter(array('titre'=>$titre,'url'=>$lien, 'parent'=>$idDoc));
                    //enregistre l'image
                    $this->dbD->ajouter(array('titre'=>'photo','url'=>$img, 'parent'=>$idDocArt));
                    //enregistre le rapport
                    $idRapArt = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                            ,"src_id"=>$idDocArt,"src_obj"=>"doc"
                            ,"dst_id"=>$this->idTagComment,"dst_obj"=>"tag"
                            ,"pre_id"=>$idTagC,"pre_obj"=>"tag"
                            ,"niveau"=>$niv
                        ));						
                    $this->trace("ARTICLE CREE ".$i." ".$idDocArt);
                }else{
                    $this->trace("EXISTE ARTICLE ".$i." ".$doc[0]['doc_id']);
                }
                $i++;    
            }
            $newCurset = $curset+1;            	    
            if($this->nbPagination >= $newCurset){
                $this->purgeMysqlBinaryLogs();               
                $this->saveCategorie($categorie,$newCurset);                
            }else{
                $this->trace("END ARTICLE ".$i." ".$this->lastIdDoc);
            }
            $i ++;
        }
        $this->trace("END ".__METHOD__." ".$categorie.' '.$curset);

    }
    

}
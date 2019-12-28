<?php
/**
 * FluxDiplomatiegouvfr
 * Classe qui gère les flux du site web des ambassades de France pour la veille scientifiques
 * 
 * REFERENCES
 * https://www.diplomatie.gouv.fr/fr/politique-etrangere-de-la-france/diplomatie-scientifique-et-universitaire/veille-scientifique-et-technologique/
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Scraping
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Diplomatiegouvfr extends Flux_Site{

    var $urlBase = 'https://www.diplomatie.gouv.fr/fr/politique-etrangere-de-la-france/diplomatie-scientifique-et-universitaire/veille-scientifique-et-technologique/';
    var $urlPage = '/?debut_veille_scientifique_article=';
    var $urlDomaine = 'https://www.diplomatie.gouv.fr/';
    var $pays = array(
    "afrique-du-sud"
    ,"allemagne"
    ,"argentine"
    ,"australie"
    ,"autriche"
    ,"belgique"
    ,"bresil"
    ,"canada"
    ,"chine"
    ,"coree-du-sud"
    ,"espagne"
    ,"estonie"
    ,"etats-unis"
    ,"finlande"
    ,"hong-kong"
    ,"hongrie-24010"
    ,"inde"
    ,"irlande"
    ,"israel"
    ,"italie"
    ,"japon"
    ,"lettonie"
    ,"mexique"
    ,"norvege"
    ,"pays-bas"
    ,"pologne"
    ,"portugal"
    ,"republique-tcheque"
    ,"roumanie"
    ,"royaume-uni"
    ,"russie"
    ,"singapour"
    ,"slovaquie"
    ,"suede"
    ,"suisse"
    ,"taiwan"
    ,"thailande"
    ,"turquie"
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
            $this->idTagPays = $this->dbT->ajouter(array("code"=>'pays',"parent"=>$this->idTagRoot));
            $this->idTagDate = $this->dbT->ajouter(array("code"=>'date',"parent"=>$this->idTagRoot));
            $this->idTagTheme = $this->dbT->ajouter(array("code"=>'thème',"parent"=>$this->idTagRoot));
            $this->idTagTypo = $this->dbT->ajouter(array("code"=>'typologie',"parent"=>$this->idTagRoot));
            $this->idTagStructure = $this->dbT->ajouter(array("code"=>'structure',"parent"=>$this->idTagRoot));
            $this->idTagChapo = $this->dbT->ajouter(array("code"=>'chapo',"parent"=>$this->idTagStructure));
            $this->idTagTitre = $this->dbT->ajouter(array("code"=>'titre',"parent"=>$this->idTagStructure));
            $this->idTagParagraphe = $this->dbT->ajouter(array("code"=>'paragraphe',"parent"=>$this->idTagStructure));
            $this->idTagListe = $this->dbT->ajouter(array("code"=>'liste',"parent"=>$this->idTagStructure));
	    	
    }

     /**
     * Enregistre les informations d'une veille technologique sur un pays
     *
     * @param   string  $pays
     * @param   int     $curset
     *
     * @return array
     */
    public function saveVeillePays($pays,$curset=0)
    {
        $this->trace(__METHOD__." - ".$pays." - ".$curset);
        set_time_limit(0);

        //enregistre l'action        
        $idAct = $this->dbA->ajouter(array('code'=>__METHOD__));

        //enregistre le mot clef de recherche
        $idTagP = $this->dbT->ajouter(array('code'=>$pays,'parent'=>$this->idTagPays));

        //récupère la réponse
        $url = $this->urlBase
            .$pays
            .$this->urlPage.$curset;            
        $this->trace($url);
        $html = $this->getUrlBodyContent($url,false,$this->bCache);

        //enregistre le doc
        $idDoc = $this->dbD->ajouter(array('titre'=>'Veille diplomatique '.$pays.' '.$curset,'url'=>$url, 'data'=>$html, 'parent'=>$this->idDocRoot));

        //enregistre le rapport
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idTagP,"src_obj"=>"tag"
				,"dst_id"=>$idDoc,"dst_obj"=>"doc"
				,"pre_id"=>$idAct,"pre_obj"=>"acti"
    		));						

        $i=0;
        if($html){
            $dom = new Zend_Dom_Query($html);	    

            //récupère le nb de pagination
            if(!$this->nbPagination){
                $xPath = '//div[@class="pagination ajax"]/span/a';
                $results = $dom->queryXpath($xPath);
                $i=0;
                foreach ($results as $result) {
                    $this->nbPagination = intval($result->nodeValue);
                } 
                $this->nbArticle = $this->nbPagination * $this->nbArticleParPage;         
                $this->trace("NB PAGES = ".$this->nbPagination." NB ARTICLE =".$this->nbArticle);
            }

            //récupère la liste des articles
            $xPath = '//article[@class="item-carte entry contenu hentry mtm"]/strong/a';
            $results = $dom->queryXpath($xPath);
            $i=0;
            foreach ($results as $result) {
                //$this->trace($result->nodeValue);
                $lien = $this->urlDomaine.$result->getAttribute('href');
                //vérifie la présence du lien
                $doc = $this->dbD->existe(array('url'=>$lien));
                if(!$doc){
                    $titre = $result->getAttribute('title');
                    $this->saveVeilleArticle($lien,$titre,$idDoc,$idRap);    
                }else{
                    $this->trace("EXISTE ARTICLE ".$i." ".$doc[0]['doc_id']);
                }
                $i++;    
            }
            $newCurset = $curset+$this->nbArticleParPage;            	    
            if($this->nbArticle >= $newCurset){
                $this->lastIdDoc = $doc[0]['doc_id'];
                $this->purgeMysqlBinaryLogs();               
                $this->saveVeillePays($pays,$newCurset);                
            }else{
                $this->trace("END ARTICLE ".$i." ".$this->lastIdDoc);
            }
        }
        $this->trace("END ".__METHOD__." ".$pays);

    }

     /**
     * Récupère les info d'une page de veille
     *
     * @param   string  $url
     * @param   string  $titre
     * @param   int     $idDocParent
     * @param   int     $idRapport
     *
     * @return array
     */
    public function saveVeilleArticle($url,$titre,$idDocParent=false, $idRapport=0)
    {
        $this->trace(__METHOD__." - ".$url);

        if(!$idDocParent)$idDocParent=$this->idDocRoot;

        //enregistre l'action        
        $idAct = $this->dbA->ajouter(array('code'=>__METHOD__));

        //récupère la réponse
        $html = $this->getUrlBodyContent($url,false,$this->bCache);

        //enregistre le doc
        $idDoc = $this->dbD->ajouter(array('titre'=>$titre, 'tronc'=>"page", 'url'=>$url, 'data'=>$html, 'parent'=>$idDocParent));

        //enregistre le rapport
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idRapport,"src_obj"=>"rapport"
				,"dst_id"=>$idDoc,"dst_obj"=>"doc"
				,"pre_id"=>$idAct,"pre_obj"=>"acti"
            ));						

        if($html){
            $dom = new Zend_Dom_Query($html);	    

            //récupère la typologie du document
            $xPath = '//span[@class="typologie"]/a';
            $results = $dom->queryXpath($xPath);
            foreach ($results as $result) {
                $val = $result->nodeValue;
                if($val){
                    $idTag = $this->dbT->ajouter(array('code'=>$val,'parent'=>$this->idTagTypo));
                    $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                            ,"src_id"=>$idDoc,"src_obj"=>"doc"
                            ,"dst_id"=>$idTag,"dst_obj"=>"tag"
                            ,"pre_id"=>$idAct,"pre_obj"=>"acti"
                        ));		
                }
            }
            //récupère les mots clefs thématiques
            $xPath = '//span[@class="mots_cles_domaine_thematique"]/a';
            $results = $dom->queryXpath($xPath);
            foreach ($results as $result) {
                $val = $result->nodeValue;
                if($val){
                    $idTag = $this->dbT->ajouter(array('code'=>$val,'parent'=>$this->idTagTheme));
                    $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                            ,"src_id"=>$idDoc,"src_obj"=>"doc"
                            ,"dst_id"=>$idTag,"dst_obj"=>"tag"
                            ,"pre_id"=>$idAct,"pre_obj"=>"acti"
                        ));		
                }
            }
            //récupère la date de publication
            $xPath = '//span[@class="date_veille"]/a';
            $results = $dom->queryXpath($xPath);
            foreach ($results as $result) {
                $val = $result->nodeValue;
                if($val){
                    //$d = strtotime($val);
                    $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                            ,"src_id"=>$idDoc,"src_obj"=>"doc"
                            ,"dst_id"=>$this->idTagDate,"dst_obj"=>"tag"
                            ,"pre_id"=>$idAct,"pre_obj"=>"acti"
                            ,"value"=>$val
                        ));		
                }
            }
            //récupère le chapo
            $xPath = '//div[@class="chapo"]/p';
            $results = $dom->queryXpath($xPath);
            foreach ($results as $result) {
                $val = $result->nodeValue;
                if($val){
                    $idDocChapo = $this->dbD->ajouter(array('titre'=>'Chapo : '.$titre, 'tronc'=>"chapo", 'note'=>$val, 'parent'=>$idDoc));
                    $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                            ,"src_id"=>$idDoc,"src_obj"=>"doc"
                            ,"dst_id"=>$idDocChapo,"dst_obj"=>"doc"
                            ,"pre_id"=>$this->idTagChapo,"pre_obj"=>"tag"
                        ));		
                }
            }
            //récupère les paragraphes
            $xPath = '//div[@class="texte"]/div[@class="texte"]/p';
            $results = $dom->queryXpath($xPath);
            $i = 1;
            foreach ($results as $result) {
                $val = $result->nodeValue;
                if($val){
                    $idDocPara = $this->dbD->ajouter(array('titre'=>'Paragraphe '.$i, 'tronc'=>"paragraphe", 'note'=>$val, 'parent'=>$idDoc));
                    $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                            ,"src_id"=>$idDoc,"src_obj"=>"doc"
                            ,"dst_id"=>$idDocPara,"dst_obj"=>"doc"
                            ,"pre_id"=>$this->idTagParagraphe,"pre_obj"=>"tag"
                        ));
                    /*vérifie le paragraphe concerne un rédacteur
                    PAS ASSEZ PERFORMANT CAR TROP DE SPACIFICITES
                    $pos = strpos($val, "Rédact");
                    if ($pos === false) {
                        $r = false;
                    }else{
                        //extraction du rédacteur
                        $r = substr($val,strpos($val, ": "));
                        $arr = explode(',',$r);
                        $idExi = $this->dbE->ajouter(array("nom"=>$arr[0],"data"=>$r));
                        //création du rapport entre le doc et le rédacteur
                        $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                                ,"src_id"=>$idDoc,"src_obj"=>"doc"
                                ,"dst_id"=>$idExi,"dst_obj"=>"exi"
                                ,"pre_id"=>$this->idTagRedacteur,"pre_obj"=>"tag"
                            ));						
                    }
                    */                    	
                }
                $i++;
            }
            //récupère les titres
            for ($i=1; $i < 6; $i++) { 
                $xPath = '//div[@class="texte"]/div[@class="texte"]/h'.$i;
                $results = $dom->queryXpath($xPath);
                $j = 1;
                foreach ($results as $result) {
                    $val = $result->nodeValue;
                    if($val){
                        $idDocTitre = $this->dbD->ajouter(array('titre'=>'Titre '.$i.'-'.$j, 'tronc'=>"titre", 'note'=>$val, 'parent'=>$idDoc));
                        $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                                ,"src_id"=>$idDoc,"src_obj"=>"doc"
                                ,"dst_id"=>$idDocTitre,"dst_obj"=>"doc"
                                ,"pre_id"=>$this->idTagTitre,"pre_obj"=>"tag"
                            ));                            
                    }
                    $i++;
                }          
            }  
            //récupère les listes
            $xPath = '//div[@class="texte"]/div[@class="texte"]/ul';
            $results = $dom->queryXpath($xPath);
            $i = 1;
            foreach ($results as $result) {
                $j=1;
                foreach($result->childNodes as $cn){
                    $val = $cn->nodeValue;
                    if($val && $cn->localName=="li"){
                        $idDocListe = $this->dbD->ajouter(array('titre'=>'Liste '.$i.'-'.$j, 'tronc'=>"liste", 'note'=>$val, 'parent'=>$idDoc));
                        $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                                ,"src_id"=>$idDoc,"src_obj"=>"doc"
                                ,"dst_id"=>$idDocListe,"dst_obj"=>"doc"
                                ,"pre_id"=>$this->idTagListe,"pre_obj"=>"tag"
                            ));                            
                    }
                    $j++;
                }		
                $i++;
            }          
            				

        }
        $this->trace("END ".__METHOD__.' '.$idDoc);        
        return $idDoc;

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

	    //récupère les infos 	    
	    $arr = $this->getArtefactInfos();
	    $nb = count($arr);
	    
	    //foreach ($arrH as $h) {
	    for ($i = 0; $i < $nb; $i++) {
	        $h = $arr[$i];
            //récupère l'item set du parent
            //$is = $this->dbIS->getByIdentifier($this->idBase."-flux_doc-doc_id-".$h["parent"]);	            
            $path_parts = pathinfo($h["titreI"]);
            if(substr($h["url"],0,4)=="http"){ 
                $arrItem[] = array("itemSet"=>1,"owner"=>"collections@musee-mccord.qc.ca" ,"dcterms:title"=>$h["titre"]
    	                ,"dcterms:isReferencedBy"=>$h["urlI"]
    	                ,"dcterms:identifier"=>$this->idBase."-flux_doc-doc_id-".$h["doc_id"]
                        ,"file"=>$path_parts["basename"]
                        ,"dcterms:date"=>$h["dateA"]
                        ,"dcterms:date"=>$h["dateS"]
                        ,"dcterms:creator"=>$h["nom"]
                        ,"dcterms:isReferencedBy"=>$h["note"]
                        ,"dcterms:provenance"=>$h["url"]
                        ,"dcterms:source"=>$h["urlI"]
                        ,"gv:imagePropertiesAnnotation"=>$h["gv1note"]        	        
                        ,"gv:faceAnnotations"=>$h["gv2note"]	        	        
                        ,"gv:landmarkAnnotations"=>$h["gv3note"]	        	        
                        ,"gv:logoAnnotations"=>$h["gv4note"]	        	        
                        ,"gv:textAnnotations"=>$h["gv5note"]	        	        
                );
                //$this->trace("faceAnnotations=".$h["gv2note"]);	        	        
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
	    $this->trace("FIN ".__METHOD__);
        
    }
    

}
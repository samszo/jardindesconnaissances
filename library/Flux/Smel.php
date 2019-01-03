<?php
/**
 * FluxSMEL
 * Classe qui gère les flux du projet SMEL
 * 
 * REFERENCES
 * http://collections.musee-mccord.qc.ca/scripts/search_results.php?keywords=houdini&Lang=2
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Scraping
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Smel extends Flux_Site{

	var $formatResponse = "json";
	var $searchUrl = '';
	var $rs;
	var $doublons;
	var $idDocRoot;
	var $idMonade;
	var $idBaseDefault="flux_smel";
	
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
            $this->idTagAuteur = $this->dbT->ajouter(array("code"=>'auteur',"parent"=>$this->idTagRoot));
            $this->idTagType = $this->dbT->ajouter(array("code"=>'type',"parent"=>$this->idTagRoot));
            $this->idTagDate = $this->dbT->ajouter(array("code"=>'date',"parent"=>$this->idTagRoot));
            $this->idTagSiecle = $this->dbT->ajouter(array("code"=>'siècle',"parent"=>$this->idTagRoot));
            $this->idTagRef = $this->dbT->ajouter(array("code"=>'référence',"parent"=>$this->idTagRoot));

	    	
    }

     /**
     * Enregistre les informations d'une recherche dans le site McCord
     *
     * @param   string  $query
     * @param   int     $curset
     *
     * @return array
     */
    public function saveMcCordSearch($query,$curset=1)
    {
        $this->trace(__METHOD__." - ".$query." - ".$curset);

        //enregistre l'action        
        $idAct = $this->dbA->ajouter(array('code'=>__METHOD__));

        //enregistre le mot clef de recherche
        $idTagQ = $this->dbT->ajouter(array('code'=>$query,'parent'=>$this->idTagRoot));

        //récupère la réponse
        $url = 'http://collections.musee-mccord.qc.ca/scripts/search_results.php?Lang=2'
            .'&order=1&curset='.$curset
            .'&keywords='.$query;            
        $html = $this->getUrlBodyContent($url,false,$this->bCache);

        //enregistre le doc
        $idDoc = $this->dbD->ajouter(array('titre'=>'McCord Search '.$query.' '.$curset,'url'=>$url, 'data'=>$html, 'parent'=>$this->idDocRoot));

        //enregistre le rapport
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idTagQ,"src_obj"=>"tag"
				,"dst_id"=>$idDoc,"dst_obj"=>"doc"
				,"pre_id"=>$idAct,"pre_obj"=>"acti"
    		));						

        $i=0;
        if($html){
            $dom = new Zend_Dom_Query($html);	    

            //récupère la liste des réponses
            $xPath = '//div[@class="groupe_image"]';
            $results = $dom->queryXpath($xPath);

            foreach ($results as $result) {
                $this->trace($result->nodeValue);
                foreach($result->childNodes as $cn){
                    $a = $cn->getAttribute('class');
                    $this->trace($a);
                    if($a=='image'){
                        //récupère les informations de ref
                        $ref = explode(" | ",$cn->firstChild->getAttribute('title'));
                        $this->trace('ref:'.$ref[0].' - titre:'.$ref[1].' - type:'.$ref[2].' - auteur:'.$ref[3]);
                    }
                    if($a=='groupe_image_droite'){
                        $ima = $cn->firstChild->nodeValue;
                        //récupère la date en supprimant les informations de ref
                        foreach ($ref as $r) {
                            $ima = str_replace($r,"",$ima);
                        }
                        $ima = str_replace('© Musée McCord',"",$ima);
                        $dates = explode(',',$ima);
                        foreach ($dates as $d) {
                            $this->trace($d);
                        }
                    }                        
                }
                //récupère l'image en grand format
                $taille = 'large';
                $rsDocTof = $this->getMcCordImage($ref[0],$taille,false,$idRap);
                //création du rapport entre les résultat de la recherche, la photo et l'activité
                $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                        ,"src_id"=>$rsDocTof["parent"],"src_obj"=>"doc"
                        ,"dst_id"=>$rsDocTof["doc_id"],"dst_obj"=>"doc"
                        ,"pre_id"=>$idRap,"pre_obj"=>"rapport"
                    ));		
                //enregistre l'auteur
                if($ref[3]){
                    $idExi = $this->dbE->ajouter(array("nom"=>$ref[3]));
                    //création du rapport entre l'image est l'auteur
                    $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                            ,"src_id"=>$rsDocTof["parent"],"src_obj"=>"doc"
                            ,"dst_id"=>$idExi,"dst_obj"=>"exi"
                            ,"pre_id"=>$this->idTagAuteur,"pre_obj"=>"tag"
                        ));						
                }                    
                //création des rapports
                if($dates[0]){
                        $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                        ,"src_id"=>$rsDocTof["parent"],"src_obj"=>"doc"
                        ,"dst_id"=>$this->idTagDate,"dst_obj"=>"tag"
                        ,"valeur"=>$dates[0]
                    ));						
                }
                if($dates[1]){
                    $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                        ,"src_id"=>$rsDocTof["parent"],"src_obj"=>"doc"
                        ,"dst_id"=>$this->idTagSiecle,"dst_obj"=>"tag"
                        ,"valeur"=>$dates[1]
                    ));						
                }
                $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                    ,"src_id"=>$rsDocTof["parent"],"src_obj"=>"doc"
                    ,"dst_id"=>$this->idTagRef,"dst_obj"=>"tag"
                    ,"valeur"=>$ref[0]
                ));						
                $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                    ,"src_id"=>$rsDocTof["parent"],"src_obj"=>"doc"
                    ,"dst_id"=>$this->idTagType,"dst_obj"=>"tag"
                    ,"valeur"=>$ref[2]
                ));						

                $i++;
            }	    
            if($i)$this->saveMcCordSearch($query,$curset+1);
        }
        $this->trace("END ".__METHOD__);

    }

     /**
     * Récupère les d'une référence du site McCord
     *
     * @param   string  $ref
     * @param   string  $taille
     * @param   int     $idDocParent
     * @param   int     $idRapport
     *
     * @return array
     */
    public function getMcCordImage($ref,$taille='large',$idDocParent=false, $idRapport=0)
    {
        $this->trace(__METHOD__." - ".$ref." - ".$taille);

        if(!$idDocParent)$idDocParent=$this->idDocRoot;

        //enregistre l'action        
        $idAct = $this->dbA->ajouter(array('code'=>__METHOD__));

        //récupère la réponse
        $url = 'http://collections.musee-mccord.qc.ca/fr/collection/artefacts/'
            .$ref;            
        $html = $this->getUrlBodyContent($url,false,$this->bCache);

        //enregistre le doc
        $idDoc = $this->dbD->ajouter(array('titre'=>'McCord Artefact '.$ref, 'tronc'=>"artefact", 'note'=>$ref, 'url'=>$url, 'data'=>$html, 'parent'=>$idDocParent));

        //enregistre le rapport
		$idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
				,"src_id"=>$idRapport,"src_obj"=>"rapport"
				,"dst_id"=>$idDoc,"dst_obj"=>"doc"
				,"pre_id"=>$idAct,"pre_obj"=>"acti"
            ));						

        if($html){
            $dom = new Zend_Dom_Query($html);	    

            //récupère le titre du doc
            $xPath = '/html/head/title';
            $results = $dom->queryXpath($xPath);
            foreach ($results as $result) {
                $titre = $result->nodeValue;
            }
            if($titre)$this->dbD->edit($idDoc,array('titre'=>$titre));

            //récupère l'adresse de l'artefact
            $xPath = '//*[@class="download"]/a/@href';
            $results = $dom->queryXpath($xPath);
            foreach ($results as $result) {
                $urlDld = $result->nodeValue;
            }
            if($urlDld){
                //création du lien de téléchargement
                $urlDld = "http://collections.musee-mccord.qc.ca".$urlDld."&format=".$taille;
                $htmlDld = $this->getUrlBodyContent($urlDld,false,$this->bCache);

                //enregistre le doc
                $idDocDld = $this->dbD->ajouter(array('titre'=>'McCord Download '.$ref,'url'=>$urlDld, 'tronc'=>"download", 'note'=>$ref, 'data'=>$html, 'parent'=>$idDoc));
        
                //enregistre le rapport
                $idRapDld = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                        ,"src_id"=>$idRap,"src_obj"=>"rapport"
                        ,"dst_id"=>$idDocDld,"dst_obj"=>"doc"
                        ,"pre_id"=>$idAct,"pre_obj"=>"acti"
                    ));						
        
                if($htmlDld){
                    $domDld = new Zend_Dom_Query($htmlDld);	                
                    //récupère le titre du doc
                    $xPath = '/html/head/title';
                    $results = $dom->queryXpath($xPath);
                    foreach ($results as $result) {
                        $titre = $result->nodeValue;
                    }
                    if($titre)$this->dbD->edit($idDocDld,array('titre'=>$titre));

                    //récupère l'adresse de l'artefact
                    $xPath = '//img/@src';
                    $results = $domDld->queryXpath($xPath);
                    foreach ($results as $result) {
                        //la dernière image est la bonne
                        $urlImage = $result->nodeValue;
                    }                   
                    $urlImage = "http://collections.musee-mccord.qc.ca".$urlImage;
                    //enregistre l'image
                    $img = ROOT_PATH.'/data/SMEL/McCord/photos/'.$ref."_".$taille.".jpg";
                    if (!file_exists($img)){
                        $res = file_put_contents($img, file_get_contents($urlImage));
                        $this->trace($res." ko : Fichier créé ".$urlImage." ".$img);
                    }
                    //ajoute la photo
                    $rsDocTof = $this->dbD->ajouter(array("url"=>$urlImage
                        ,"titre"=>$img
                        ,"type"=>1
                        ,"note"=>$ref
                        ,"tronc"=>'image'
                        ,"parent"=>$idDocDld),true,true);
                }
            }

        }
        $this->trace("END ".__METHOD__.' '.$urlImage);        
        return $rsDocTof;

    }

     /**
     * Récupère les analyse de google vision
     *
     * @param   int     idDoc
     * @param   boolean $all
     *
     * @return array
     */
    public function getGoogleVisionAnalyse($idDoc, $all){
        set_time_limit(0);
        $this->trace(__METHOD__.' - '.$idDoc);        
        if($all){
            $arr = $this->dbD->findByTronc('image');
        }else{
            $arr = $this->dbD->findBydoc_id($idDoc);
        }
        $gVision = new Flux_Gvision($this->idBase,$this->bTrace);
        $gVision->saveAnalyses($arr,'titre');    

        $this->trace("END ".__METHOD__);        
    }


	/**
	 * supprime les doublons créer lors de l'import SMEL
     *
     * @param   int     idDoc
     * @param   boolean $all
     *
     * @return array
     */
    public function supprimeDoublons(){
        $this->trace(__METHOD__);        

        $sql = "SELECT 
                COUNT(*) nb, GROUP_CONCAT(doc_id) ids, parent, titre
            FROM
                flux_doc
            WHERE
                note <> ''
            GROUP BY tronc , parent , titre , note
            HAVING nb > 1
            ORDER BY nb DESC";
        $arr = $this->dbD->exeQuery($sql);
        $this->trace('nb doublons ='.count($arr));
        foreach ($arr as $r) {
            $this->trace('',$r);
            $ids = explode(',',$r["ids"]);
            $first = true;
            foreach ($ids as $i) {
                if(!$first){
                    $nb = $this->dbD->remove($i);
                    $this->trace('suppression = '.$nb);
                }
                $first = false;
            }
        }
        $this->trace("END ".__METHOD__);        
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
    
	/**
	 * récupére les informations des artefacts
	 *
     * @param   string $separateur
	 *
	 * @return array
	 */
    public function getArtefactInfos($separateur='#'){

        /**
         * ATTENTION le group_concat limit la taille du texte
         * pour corriger : SET group_concat_max_len = <int>
         * merci à https://stackoverflow.com/questions/529105/trouble-with-group-concat-and-longtext-in-mysql/529123#529123
         */
        $sql = "SELECT 
            d.doc_id,
            d.url,
            d.titre,
            d.note,
            dd.doc_id idDwld,
            di.doc_id idI,
            di.url urlI,
            di.titre titreI,
            di.note noteI,
            rA.dst_id,
            e.nom,
            rD.valeur dateA,
            rS.valeur siecleA,
            rT.valeur typeA,
            GROUP_CONCAT(DISTINCT dgv1.titre SEPARATOR '".$separateur."') gv1titre,
            GROUP_CONCAT(DISTINCT dgv1.note SEPARATOR '".$separateur."') gv1note,
            GROUP_CONCAT(DISTINCT dgv2.titre SEPARATOR '".$separateur."') gv2titre,
            GROUP_CONCAT(DISTINCT dgv2.note SEPARATOR '".$separateur."') gv2note,
            GROUP_CONCAT(DISTINCT dgv3.titre SEPARATOR '".$separateur."') gv3titre,
            GROUP_CONCAT(DISTINCT dgv3.note SEPARATOR '".$separateur."') gv3note,
            GROUP_CONCAT(DISTINCT dgv4.titre SEPARATOR '".$separateur."')  gv4titre,
            GROUP_CONCAT(DISTINCT dgv4.note SEPARATOR '".$separateur."') gv4note,
            GROUP_CONCAT(DISTINCT dgv5.note SEPARATOR '".$separateur."') gv5note
        FROM
            flux_doc d
                INNER JOIN
            flux_doc dd ON dd.parent = d.doc_id
                INNER JOIN
            flux_doc di ON di.parent = dd.doc_id
                LEFT JOIN
            flux_rapport rA ON rA.src_id = dd.doc_id
                AND rA.src_obj = 'doc'
                AND rA.dst_obj = 'exi'
                AND rA.pre_obj = 'tag'
                LEFT JOIN
            flux_exi e ON e.exi_id = rA.dst_id
                LEFT JOIN
            flux_rapport rD ON rD.src_id = dd.doc_id
                AND rD.src_obj = 'doc'
                AND rD.dst_obj = 'tag'
                AND rD.dst_id = 4
                LEFT JOIN
            flux_rapport rS ON rS.src_id = dd.doc_id
                AND rS.src_obj = 'doc'
                AND rS.dst_obj = 'tag'
                AND rD.dst_id = 5
                LEFT JOIN
            flux_rapport rT ON rT.src_id = dd.doc_id
                AND rT.src_obj = 'doc'
                AND rT.dst_obj = 'tag'
                AND rT.dst_id = 3
            LEFT JOIN
            flux_doc dgv1 ON dgv1.parent = dI.doc_id
                AND dgv1.titre LIKE 'imagePropertiesAnnotation%'
                LEFT JOIN
            flux_doc dgv2 ON dgv2.parent = dI.doc_id
                AND dgv2.titre LIKE 'faceAnnotations%'
                LEFT JOIN
            flux_doc dgv3 ON dgv3.parent = dI.doc_id
                AND dgv3.titre LIKE 'landmarkAnnotations%'
                LEFT JOIN
            flux_doc dgv4 ON dgv4.parent = dI.doc_id
                AND dgv4.titre LIKE 'logoAnnotations%'
                LEFT JOIN
            flux_doc dgv5 ON dgv5.parent = dI.doc_id
                AND dgv5.tronc LIKE 'textAnnotations%'
                
        WHERE
            d.tronc = 'artefact' 
        GROUP BY d.doc_id ";
        return $this->dbD->exeQuery($sql);
    }

}
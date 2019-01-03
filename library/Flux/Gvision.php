<?php
/**
 * Flux_Gbooks
 * Class qui gère les flux de l'API google vision
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\API
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Gvision extends Flux_Site{

	var $client;
	var $service;
    var $idTagLA;
    var $idTagWE;

	/*
    Likelihood Enums https://cloud.google.com/vision/docs/reference/rest/v1/images/annotate#Likelihood
    UNKNOWN	Unknown likelihood.
    VERY_UNLIKELY	It is very unlikely that the image belongs to the specified vertical.
    UNLIKELY	It is unlikely that the image belongs to the specified vertical.
    POSSIBLE	It is possible that the image belongs to the specified vertical.
    LIKELY	It is likely that the image belongs to the specified vertical.
    VERY_LIKELY	It is very likely that the image belongs to the specified vertical.
	 */

	/*pour la liste des TYPE cf. https://cloud.google.com/vision/docs/reference/rest/v1/images/annotate#Feature
	 * TYPE_UNSPECIFIED	Unspecified feature type.
	 FACE_DETECTION	Run face detection.
	 LANDMARK_DETECTION	Run landmark detection.
	 LOGO_DETECTION	Run logo detection.
	 LABEL_DETECTION	Run label detection.
	 TEXT_DETECTION	Run OCR.
	 DOCUMENT_TEXT_DETECTION	Run dense text document OCR. Takes precedence when both DOCUMENT_TEXT_DETECTION and TEXT_DETECTION are present.
	 SAFE_SEARCH_DETECTION	Run computer vision models to compute image safe-search properties.
	 IMAGE_PROPERTIES	Compute a set of image properties, such as the image's dominant colors.
	 CROP_HINTS	Run crop hints.
	 WEB_DETECTION	Run web detection.
	 */
	
	/*
	Type Enums	
	Face landmark (feature) type. Left and right are defined from the vantage of the viewer of the image without considering mirror projections typical of photos. So, LEFT_EYE, typically, is the person's right eye.
    UNKNOWN_LANDMARK	Unknown face landmark detected. Should not be filled.
    LEFT_EYE	Left eye.
    RIGHT_EYE	Right eye.
    LEFT_OF_LEFT_EYEBROW	Left of left eyebrow.
    RIGHT_OF_LEFT_EYEBROW	Right of left eyebrow.
    LEFT_OF_RIGHT_EYEBROW	Left of right eyebrow.
    RIGHT_OF_RIGHT_EYEBROW	Right of right eyebrow.
    MIDPOINT_BETWEEN_EYES	Midpoint between eyes.
    NOSE_TIP	Nose tip.
    UPPER_LIP	Upper lip.
    LOWER_LIP	Lower lip.
    MOUTH_LEFT	Mouth left.
    MOUTH_RIGHT	Mouth right.
    MOUTH_CENTER	Mouth center.
    NOSE_BOTTOM_RIGHT	Nose, bottom right.
    NOSE_BOTTOM_LEFT	Nose, bottom left.
    NOSE_BOTTOM_CENTER	Nose, bottom center.
    LEFT_EYE_TOP_BOUNDARY	Left eye, top boundary.
    LEFT_EYE_RIGHT_CORNER	Left eye, right corner.
    LEFT_EYE_BOTTOM_BOUNDARY	Left eye, bottom boundary.
    LEFT_EYE_LEFT_CORNER	Left eye, left corner.
    RIGHT_EYE_TOP_BOUNDARY	Right eye, top boundary.
    RIGHT_EYE_RIGHT_CORNER	Right eye, right corner.
    RIGHT_EYE_BOTTOM_BOUNDARY	Right eye, bottom boundary.
    RIGHT_EYE_LEFT_CORNER	Right eye, left corner.
    LEFT_EYEBROW_UPPER_MIDPOINT	Left eyebrow, upper midpoint.
    RIGHT_EYEBROW_UPPER_MIDPOINT	Right eyebrow, upper midpoint.
    LEFT_EAR_TRAGION	Left ear tragion.
    RIGHT_EAR_TRAGION	Right ear tragion.
    LEFT_EYE_PUPIL	Left eye pupil.
    RIGHT_EYE_PUPIL	Right eye pupil.
    FOREHEAD_GLABELLA	Forehead glabella.
    CHIN_GNATHION	Chin gnathion.
    CHIN_LEFT_GONION	Chin left gonion.
    CHIN_RIGHT_GONION	Chin right gonion.
    */	
	
	
    /**
     * Constructeur de la classe
     *
     * @param  string 	$idBase
     * @param  boolean 	$bTrace
     * 
     */
	public function __construct($idBase=false, $bTrace=false)
    {
    		parent::__construct($idBase, $bTrace);    	

    		//TODO:implémenter google cloud plateform
    		/*ATTENTION on utilise l'API sans le client
    		$this->client = new Google_Client();
		$this->client->setClientId(KEY_GOOGLE_CLIENT_ID);
		$this->client->setClientSecret(KEY_GOOGLE_CLIENT_SECRET);
		*/
    		
    		//on récupère la racine des documents
    		$this->initDbTables();
    		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
    		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
            $this->idTagRoot = $this->dbT->ajouter(array("code"=>__CLASS__));
            
            //récupération des tags utiles
            $this->idTagLA = $this->dbT->ajouter(array('code'=>'labelAnnotations','parent'=>$this->idTagRoot));
            $this->idTagWE = $this->dbT->ajouter(array('code'=>'webEntities','parent'=>$this->idTagRoot));

    		
    		
    }

    /**
     * Fonction pour analyser une image
     * 
     * @param  	string 		$url
     * 
     * @return	string
     * 
     */
    function analyseImage($url){

        $this->trace(__METHOD__." ".$url);
        
        $im = file_get_contents($url);
        $imdata = base64_encode($im);
        //ATTENTION il y a un prix pour chaque type de détection cf. https://cloud.google.com/vision/pricing
        $json = '{
		 "requests": [
		  {
		      "image":{
		        "content":"'.$imdata.'"
		      },
			  "features": [
				{"type": "FACE_DETECTION"}
				,{"type": "LANDMARK_DETECTION"}
				,{"type": "LABEL_DETECTION"}
				,{"type": "TEXT_DETECTION"}
				,{"type": "LOGO_DETECTION"}
				,{"type": "IMAGE_PROPERTIES"}
				,{"type": "CROP_HINTS"}
				,{"type": "WEB_DETECTION"}
			  ]
		  }
		 ]
		}';
        //$this->trace("requête envoyée = ".$json);
        
        //gestion du cache
        $uMd5 = md5($url);
        $response = $this->cache->load($uMd5);        
        if(!$response){        
            $this->trace($uMd5);
            $url = "https://vision.googleapis.com/v1/images:annotate?key=".KEY_GOOGLE_SERVER."&fields=responses";
            $response = $this->getUrlBodyContent($url,false,false,Zend_Http_Client::POST,array("value"=>$json, "type"=>'application/json'));
            $this->cache->save($response, $uMd5);
        }
        //$this->trace("reponse de google = ".$response);	
        return $response;      	    	
    }
    
    /**
     * enregistre les analyses de photo faite par google
     * @param   array   $arr - la liste des photos
     * @param   string  $champ
     * 
     * @return array
     *
     */
    function saveAnalyses($arr, $champ='url'){
        $this->trace(__METHOD__);
        /** ATTENTION il faut vérifier les bin log car il sont trop nombreux
         * sudo du -a -h /usr/local/mysql-8.0.12-macos10.13-x86_64/data 
         * pour supprimer les logs executer dans mysql workbench
         * purge binary logs before '2019-10-01 12:00:00';
         * avant l'execution changer le paramètre du server dans phpmyadmin
         * sql log off  = ON
         */
        $numItem = 0;
        foreach ($arr as $item) {
            if($item["doc_id"]>=1160){
                $this->trace('doc_id='.$item["doc_id"]);
                $c = json_decode($this->analyseImage($item[$champ]), true);
                foreach ($c['responses'][0] as $k => $r) {
                    $this->trace($numItem.' '.$k);            	            
                    switch ($k) {        	                
                        case 'textAnnotations':
                            $i=0;
                            foreach ($r as $ta) {
                                //création d'un document pour les annotation
                                $titre = $ta['description'];
                                if(strlen($titre)>5000)$titre=substr($ta['description'],0,4000).'[...]';
                                $this->dbD->ajouter(array("parent"=>$item["doc_id"],"titre"=>$titre, 'tronc'=>$k." ".$numItem." ".$i,"note"=>json_encode($ta)));
                                $i++;
                            }
                            break;
                        case 'fullTextAnnotation':
                            $i=0;
                            foreach ($r as $fta) {
                                //création d'un document pour l'analyse OCR
                                if($fta['text'])$txt=utf8_encode($fta['text']);
                                else $txt = 'inconnu';
                                $this->dbD->ajouter(array("parent"=>$item["doc_id"],"titre"=>$txt, 'tronc'=>$k." ".$numItem." ".$i,"note"=>json_encode($fta)));
                                $i++;
                            }
                            break;                            
                        case 'logoAnnotations':
                            $i=0;
                            foreach ($r as $fa) {
                                //création d'un document par visage
                                $this->dbD->ajouter(array("parent"=>$item["doc_id"],"titre"=>$k." ".$numItem." ".$i, 'tronc'=>'logo',"note"=>json_encode($fa)));
                                $i++;
                            }
                            break;
                        case 'landmarkAnnotations':
                            $i=0;
                            foreach ($r as $fa) {
                                //création d'un document par visage
                                $this->dbD->ajouter(array("parent"=>$item["doc_id"],"titre"=>$k." ".$numItem." ".$i, 'tronc'=>'paysage',"note"=>json_encode($fa)));
                                $i++;
                            }
                            break;
                        case 'faceAnnotations':
                            $i=0;
                            foreach ($r as $fa) {
                                //création d'un document par visage
                                $this->dbD->ajouter(array("parent"=>$item["doc_id"],"titre"=>$k." ".$numItem." ".$i, 'tronc'=>'visage',"note"=>json_encode($fa)));
                                $i++;
                            }        	                    
                            break;
                        case 'labelAnnotations':
                            foreach ($r as $la) {
                                $idTag = $this->dbT->ajouter(array('code'=>$la['description'],'uri'=>$la['mid'],'parent'=>$this->idTagLA));
                                $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                                    ,"src_id"=>$item["doc_id"],"src_obj"=>"doc"
                                    ,"dst_id"=>$idTag,"dst_obj"=>"tag"
                                    ,"pre_id"=>$this->idMonade,"pre_obj"=>"monade"
                                    ,"valeur"=>$la['score']
                                ));        	  
                            }
                            break;
                        case 'imagePropertiesAnnotation':
                            //enregistre l'analyse
                            $this->dbD->ajouter(array("parent"=>$item["doc_id"],"titre"=>$k." ".$numItem,"note"=>json_encode($r)));
                            break;
                        case 'cropHintsAnnotations':
                            $this->dbD->ajouter(array("parent"=>$item["doc_id"],"titre"=>$k." ".$numItem,"note"=>json_encode($r)));
                            break;
                        case 'webDetection':
                            foreach ($r['webEntities'] as $we) {
                                if(isset($we['description'])){
                                    $idTag = $this->dbT->ajouter(array('code'=>$we['description'],'uri'=>$we['entityId'],'parent'=>$this->idTagWE));
                                    if($we['score'])$score = $we['score']; 
                                    else $score = 0; 
                                    $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                                        ,"src_id"=>$item["doc_id"],"src_obj"=>"doc"
                                        ,"dst_id"=>$idTag,"dst_obj"=>"tag"
                                        ,"pre_id"=>$this->idMonade,"pre_obj"=>"monade"
                                        ,"valeur"=>$score
                                    ));
                                }            	                        
                            }
                            break;
                    }
                }
            }
        }
        $numItem++;
    }

		/**
		 * décompose l'analyse des visages de google
		 *
		 *
		 * @return array
		 *
		 */
		function exploseGoogleVisage(){
			$this->trace(__METHOD__);
			set_time_limit(0);
			
			$dbVisage = new Model_DbTable_Flux_Visage($this->db);
			$dbRepere = new Model_DbTable_Flux_Repere($this->db);
			
			$sql = "SELECT
			d.doc_id,
			d.titre,
			d.note,
			d.parent
		FROM
			flux_doc d
				INNER JOIN
			flux_doc dp ON dp.doc_id = d.parent
				LEFT JOIN
			flux_visage v ON v.doc_id = d.doc_id
		WHERE
			d.tronc = 'visage' AND v.doc_id is null
		ORDER BY d.doc_id";
			/*pour ensuite mettre à jour la table des visages avec les url
				UPDATE flux_visage v
					INNER JOIN
				omk_valarnum1.value ov ON ov.value LIKE 'flux_valarnum-flux_doc-doc_id-%'
					AND SUBSTRING(ov.value, 31) = v.doc_id
					INNER JOIN
				omk_valarnum1.media om ON om.item_id = ov.resource_id 
			SET 
				v.url = CONCAT('http://gapai.univ-paris8.fr/ValArNum/omks/files/original/',
						om.storage_id,
						'.',
						om.extension),
				v.source = om.source
				*/
			
			
			$this->trace($sql);
			$arr = $this->dbD->exeQuery($sql);
			//foreach ($arr as $h) {
			$nb = count($arr);
			$arrItem = array();
			for ($i = 0; $i < $nb; $i++) {
				$this->trace($i." ".$h["doc_id"]." ".$h["titre"]);
				$h = $arr[$i];
				$data = json_decode($h["note"]);
				$v = $data->boundingPoly->vertices;
				for ($j = 0; $j < 4; $j++) {
					if(!isset($v[$j]->x)) $v[$j]->x=0;
					if(!isset($v[$j]->y)) $v[$j]->y=0;
				}
				$r = array("doc_id"=>$h["doc_id"],
				"rollAngle"=>$data->rollAngle,
					"panAngle"=>$data->panAngle,
					"tiltAngle"=>$data->tiltAngle,
					"detectionConfidence"=>$data->detectionConfidence,
					"landmarkingConfidence"=>$data->landmarkingConfidence,
					"joy"=>$data->joyLikelihood,
					"sorrow"=>$data->sorrowLikelihood,
					"anger"=>$data->angerLikelihood,
					"surprise"=>$data->surpriseLikelihood,
					"underExposed"=>$data->underExposedLikelihood,
					"blurred"=>$data->blurredLikelihood,
					"headwear"=>$data->headwearLikelihood,
					"v0x"=>$v[0]->x,
					"v0y"=>$v[0]->y,
					"v1x"=>$v[1]->x,
					"v1y"=>$v[1]->y,
					"v2x"=>$v[2]->x,
					"v2y"=>$v[2]->y,
					"v3x"=>$v[3]->x,
					"v3y"=>$v[3]->y);        	            
				$dbVisage->ajouter($r);
				foreach ($data->landmarks as $l) {
					$p = $l->position;
					if(!isset($p->x)) $p->x=0;
					if(!isset($p->y)) $p->y=0;
					if(!isset($p->z)) $p->z=0;        	            
					$dbRepere->ajouter(array("doc_id"=>$h["doc_id"],"type"=>$l->type,"x"=>$p->x, "y"=>$p->y, "z"=>$p->z));
				}
				
			}
							
		}
        	
        	/**
        	 * migre les analyses de photo faite par google
        	 *
        	 * @param  $idBaseSrc    string
        	 * @param  $idBaseDst    string
        	 * 
        	 * @return array
        	 *
        	 */
        	function migreAnalyseGooglePhoto($idBaseSrc, $idBaseDst){
        	    $this->trace(__METHOD__);
        	    set_time_limit(0);

        	    $dbDst = $this->getDb($idBaseDst);
        	    $dbDocDst = new Model_DbTable_Flux_Doc($dbDst);
        	    
        	    $dbSrc = $this->getDb($idBaseSrc);
        	    $dbDocSrc = new Model_DbTable_Flux_Doc($dbSrc);
        	    
        	    /* Problème de manque
        	    $sql = "SELECT 
                    d.doc_id,
                    d.url,
                    d.titre,
                    COUNT(DISTINCT dv.doc_id) nbDv,
                    GROUP_CONCAT(DISTINCT dv.doc_id) dvIds,
                    COUNT(DISTINCT dp.doc_id) nbDp,
                    GROUP_CONCAT(DISTINCT dp.doc_id) dpIds
                FROM
                    flux_doc d
                        INNER JOIN
                    flux_doc dv ON dv.parent = d.doc_id
                        AND (dv.titre LIKE 'imagePropertiesAnnotation%'
                        OR dv.titre LIKE 'faceAnnotations%'
                        OR dv.titre LIKE 'landmarkAnnotations%'
                        OR dv.titre LIKE 'logoAnnotations%')
                        INNER JOIN
                    ".$idBaseDst.".flux_doc dp ON dp.url = d.url
                GROUP BY d.doc_id
                ORDER BY d.doc_id";
        	    $arr = $dbDocSrc->exeQuery($sql);
        	    */
        	    
        	    $sql = "SELECT 
                    d.doc_id,
                    dpv.titre, dpv.tronc, dpv.note
                FROM
                    flux_doc d
                        INNER JOIN
                    ".$idBaseSrc.".flux_doc dp ON dp.url = d.url
                        INNER JOIN
                    ".$idBaseSrc.".flux_doc dpv ON dpv.parent = dp.doc_id
                        AND (dpv.titre LIKE 'imagePropertiesAnnotation%'
                        OR dpv.titre LIKE 'faceAnnotations%'
                        OR dpv.titre LIKE 'landmarkAnnotations%'
                        OR dpv.titre LIKE 'logoAnnotations%')
                        LEFT JOIN
                    flux_doc dv ON dv.parent = d.doc_id
                        AND (dv.titre LIKE 'imagePropertiesAnnotation%'
                        OR dv.titre LIKE 'faceAnnotations%'
                        OR dv.titre LIKE 'landmarkAnnotations%'
                        OR dv.titre LIKE 'logoAnnotations%')
                WHERE
                    d.type = 1 AND dv.doc_id IS NULL
                ORDER BY d.doc_id";
        	    $this->trace($sql);        	    
        	    $arr = $dbDocDst->exeQuery($sql);
        	    
        	    foreach ($arr as $v) {
        	        if($v['doc_id'] >= -1){
    	                $id = $dbDocDst->ajouter(
    	                    array("titre"=>$v['titre'],"parent"=>$v['doc_id']
    	                       ,'tronc'=>$v['tronc'] ? $v['tronc'] : 'Google Vision'
    	                       ,"note"=>$v['note'])
    	                    ,false);
    	                $this->trace('--- '.$id.' = '.$v['doc_id']." ".$v['titre']);
    	            }
        	    }
        	
        	}
        	
        	/**
        	 * migre les mots clefs de photo faite par google
        	 *
        	 * @param  $idBaseSrc    string
        	 * @param  $idBaseDst    string
        	 *
        	 * @return array
        	 *
        	 */
        	function migreAnalyseGooglePhotoMC($idBaseSrc, $idBaseDst){
        	    $this->trace(__METHOD__);
        	    set_time_limit(0);
        	            	    
        	    $dbSrc = $this->getDb($idBaseSrc);
        	    $dbDocSrc = new Model_DbTable_Flux_Doc($dbSrc);
        	    
        	    $dbDst = $this->getDb($idBaseDst);
        	    $dbDocDst = new Model_DbTable_Flux_Doc($dbDst);
        	    
        	    $g = new Flux_Gvision($idBaseDst);
        	    
        	    $arrTagP['labelAnnotations'] = $this->dbT->ajouter(array('code'=>'labelAnnotations','parent'=>$g->idTagRoot));
        	    $arrTagP['webEntities'] = $this->dbT->ajouter(array('code'=>'webEntities','parent'=>$g->idTagRoot));
        	    
        	    $sql = "SELECT 
                    r.pre_id,
                    r.valeur,
                    t.code,
                    t.uri,
                    tp.code TagP,
                    dp.doc_id
                FROM
                    flux_doc d
                        INNER JOIN
                    ".$idBaseDst.".flux_doc dp ON dp.url = d.url
                        INNER JOIN
                    flux_rapport r ON r.src_id = d.doc_id
                        AND r.src_obj = 'doc'
                        AND r.dst_obj = 'tag'
                        AND r.pre_obj = 'monade'
                        INNER JOIN
                    flux_tag t ON t.tag_id = r.dst_id
                        INNER JOIN
                    flux_tag tp ON tp.tag_id = t.parent
                        AND tp.code IN ('webEntities' , 'labelAnnotations')
                WHERE dp.type = 1
                ORDER BY dp.doc_id";
        	    $this->trace($sql);
        	    $arr = $dbDocSrc->exeQuery($sql);
        	    
        	    foreach ($arr as $v) {
        	        if($v['doc_id'] >= -1){
        	            $idTag = $this->dbT->ajouter(array('code'=>$v['code'],'uri'=>$v['uri'],'parent'=>$arrTagP['TagP']));
        	            $id = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
        	                ,"src_id"=>$v['doc_id'],"src_obj"=>"doc"
        	                ,"dst_id"=>$idTag,"dst_obj"=>"tag"
        	                ,"pre_id"=>$g->idMonade,"pre_obj"=>"monade"
        	                ,"valeur"=>$v['valeur']
        	            ),false);
        	            $this->trace('--- '.$id.' = '.$v['doc_id']." ".$v['code']);
        	        }
        	    }
        	    
        	}
    

}
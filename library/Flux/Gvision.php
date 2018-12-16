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
        $this->trace("requête envoyée = ".$json);
        
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
        set_time_limit(0);
        
        $numItem = 0;
        foreach ($arr as $item) {
            if($item["doc_id"]>=299){
                $this->trace('doc_id='.$item["doc_id"]);
                $c = json_decode($this->analyseImage($item[$champ]), true);
                foreach ($c['responses'][0] as $k => $r) {
                    $this->trace($numItem.' '.$k);            	            
                    switch ($k) {        	                
                        case 'textAnnotations':
                            $i=0;
                            foreach ($r as $ta) {
                                //création d'un document pour les annotations
                                $this->dbD->ajouter(array("parent"=>$item["doc_id"],"titre"=>$ta['description'], 'tronc'=>$k." ".$numItem." ".$i,"note"=>json_encode($ta)));
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

    
}
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
        $this->trace("reponse de google = ".$response);	
        return $response;      	    	
	}
    
}
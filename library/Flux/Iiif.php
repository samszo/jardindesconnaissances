<?php
/**
 * Flux_Iiif
 * Class qui gère les flux d'un serveur IIIF
 * http://iiif.io/
 * notamment à travers le module IIIF pour Omeka S : https://github.com/Daniel-KM/Omeka-S-module-IiifServer
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\API
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Iiif extends Flux_Site{

	var $urlRoot;
	
    /**
     * Constructeur de la classe
     *
     * @param  string 	$urlRoot
     * @param  string 	$idBase
     * @param  boolean 	$bTrace
     * 
     */
	public function __construct($urlRoot="", $idBase=false, $bTrace=false)
    {
    		parent::__construct($idBase, $bTrace);    	
    		
    		$this->urlRoot = $urlRoot;
    }

    /**
     * Fonction pour récupérer une collection Omeka S
     * 
     * @param  	int 		$idCol
     * 
     * @return	array
     * 
     */
    function getOmkCollection($idCol){

        $this->trace(__METHOD__." ".$idCol);
        $data= array();        
        //récupère la description de la collection
        $url = $this->urlRoot."/collection/".$idCol;
        $this->trace($url);
        $col = json_decode($this->getUrlBodyContent($url,false,false));
        $this->trace("col",$col);
        $data[] = array("label"=>$col->label,"id"=>"root","value"=>"");
         $i=1;
        foreach ($col->manifests as $m) {
            $data[]=$this->getTofInfos($m->{'@id'},$i);            
            /*récupère les infos de la photo
            $p = json_decode($this->getUrlBodyContent($m->{'@id'}));
            $arrMtd = array();
            //récupère les metadata
            foreach ($p->metadata as $mtd) {
                $arrMtd[$mtd->label]=$mtd->value;
            }
            //enregistre les informations
            $r = $p->sequences[0]->canvases[0]->images[0]->resource;
            $ori = $r->{'@id'};
            $img = $r->service->{'@id'};
            $id = substr($img, strrpos($img, "/")+1);
            $data[] = array("id"=>"root.".$i,"idCol"=>$idCol,"value"=>"","label"=>$p->label
                ,"original"=>$ori,"idOmk"=>$id,"imgOmk"=>$img,"imgFull"=>$img.'/full/full/0/default.jpg'
                ,"width"=>$r->width,"height"=>$r->height
                ,"metadata"=>$arrMtd
                );
            */
            $i++;
        }
        
        return $data;
        
	}
	
	/**
	 * Fonction pour récupérer les informations IIF d'une photo
	 *
	 * @param  	int 		$id
	 * @param  	int 		$i
	 *
	 * @return	array
	 *
	 */
	function getTofInfos($id,$i){
	    
	    $this->trace(__METHOD__." ".$id);
	    
	    //récupère les infos de la photo
	    $p = json_decode($this->getUrlBodyContent($id));
	    $arrMtd = array();
	    //récupère les metadata
	    foreach ($p->metadata as $mtd) {
	        $arrMtd[$mtd->label]=$mtd->value;
	    }
	    //enregistre les informations
	    $r = $p->sequences[0]->canvases[0]->images[0]->resource;
	    $ori = $r->{'@id'};
	    $img = $r->service->{'@id'};
	    $idM = substr($img, strrpos($img, "/")+1);
	    $data = array("id"=>"root.".$i,"idCol"=>$idCol,"value"=>"","label"=>$p->label
	        ,"original"=>$ori,"idOmkItem"=>$id,"idOmkMedia"=>$idM,"imgOmk"=>$img,"imgFull"=>$img.'/full/full/0/default.jpg'
	        ,"w"=>$r->width,"h"=>$r->height
	        ,"metadata"=>$arrMtd
	    );
	    
	    return $data;
	}
	
	/*
	 * 
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
        
        $url = "https://vision.googleapis.com/v1/images:annotate?key=".KEY_GOOGLE_SERVER."&fields=responses";
        $response = $this->getUrlBodyContent($url,false,true,Zend_Http_Client::POST,array("value"=>$json, "type"=>'application/json'));
        $this->trace("reponse de google = ".$response);	
        return $response;      	
	 */
    
}
<?php
/**
 * Classe qui gère les flux Google Drive
 *
 * merci à :
 * https://developers.google.com/drive/v2/reference/files/get
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Gdrive extends Flux_Site{

	var $client;
	var $service;
	
	public function __construct($token, $idBase=false)
    {
	    	parent::__construct($idBase);
    	
		$this->client = new Google_Client();				
		$this->client->setAccessToken($token);		
	    	$this->token = $token;	
	    	$this->service = new Google_Service_Drive($this->client);    		
    }

	/**
	 * Print a file's metadata.
	 *
	 * @param string $fileId ID of the file to print metadata for.
	 */
	function printFile($fileId) {
	  try {
	    $file = 	$this->service->files->get($fileId);
	
	    print "Title: " . $file->getTitle();
	    print "Description: " . $file->getDescription();
	    print "MIME type: " . $file->getMimeType();
	  } catch (Exception $e) {
	    print "An error occurred: " . $e->getMessage();
	  }
	}
	
	
	/**
	 * Download a file's content.
	 *
	 * @param string $fileId ID of the file to print metadata for.
	 * @param string $type le type de fichier à télécharger
	 * @param File $file Drive File instance.
	 
	 * @return String The file's content if successful, null otherwise.
	 */
	function downloadFile($fileId=null, $type=null, $file=null) {
		$c = str_replace("::", "_", __METHOD__)."_".md5($fileId.$type); 
	   	$content = $this->cache->load($c);
        if(!$content){
			if(!$file)$file = $this->service->files->get($fileId);
		  	if(!$type)$downloadUrl = $file->getDownloadUrl();
		  	else $downloadUrl = $file->exportLinks[$type];
			if ($downloadUrl) {
				$request = new Google_Http_Request($downloadUrl, 'GET', null, null);
			    $httpRequest = 	$this->service->getClient()->getAuth()->authenticatedRequest($request);
			    if ($httpRequest->getResponseHttpCode() == 200) {
			      	$content = $httpRequest->getResponseBody();
					$this->cache->save($this->feed, $c);
			      	return $content;
			    } else {
			      // An error occurred.
			      return null;
			    }
			} else {
			    // The file doesn't have any content stored on Drive.
			    return null;
			}
        }else{
        		return $content;        	
        }
	}    
    
		
}
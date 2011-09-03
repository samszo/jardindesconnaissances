<?php
/**
 * Classe qui gère les flux Zotero
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Zotero{

	var $cache;
	var $forceCalcul = false;
	var $user;
	var $idUser;
	var $del;
	var $dbUT;
	var $dbD;
	var $dbT;
	var $dbUD;
	var $dbTD;
	var $dbED;
	var $dbET;
	var $dbTT;
	
	function SaveRdf($data) {
		try {
			if($data['objName']=="model_rapport"){
				$rep = SEP_PATH.'data'.SEP_PATH.'rapports'.SEP_PATH.'models';    
			}else{
				$rep = SEP_PATH.'data'.SEP_PATH.'upload';    
			}
		
			$adapter = new Zend_File_Transfer_Adapter_Http();
		        //echo ROOT_PATH.'/data/upload';
		    $adapter->setDestination(ROOT_PATH.$rep);
		    //echo ROOT_PATH.$rep;
		    
			if (!$adapter->receive()) {
				$messages = $adapter->getMessages();
				echo implode("Mauvaise réception\n", $messages);
		      }else{
				// Retourne toutes les informations connues sur le fichier
				$files = $adapter->getFileInfo();
				foreach ($files as $file => $info) {
					// Les validateurs sont-ils OK ?
					if (!$adapter->isValid($file)) {
						print "Désolé mais $file ne correspond à ce que nous attendons";
						continue;
					}
					//renomme le fichier pour éviter les doublons
					$tabDecomp = explode('.', $info["name"]);
					$extention = ".".strtolower($tabDecomp[sizeof($tabDecomp)-1]);
					$new_name = uniqid().$extention;
			        $path = ROOT_PATH.$rep.SEP_PATH.$new_name;					
			        $url = WEB_ROOT.$rep.SEP_PATH.$new_name;

			        $dataDoc = array(
			    		"url"=>$url,"titre"=>$info["name"],"content_type"=>$adapter->getMimeType()
			    		,"path_source"=>$path
			    		,"tronc"=>$data['objName']
			    		);
					
			    	rename(ROOT_PATH.$rep.'/'.$info["name"],$path);
			    		
			    		
					$this->saveDoc($data, $dataDoc);
					//print_r($info);					
				}
		      }
		      
		}catch (Zend_Exception $e) {
			// Appeler Zend_Loader::loadClass() sur une classe non-existante
          	//entrainera la levée d'une exception dans Zend_Loader
          	echo "Récupère exception: " . get_class($e) . "\n";
          	echo "Message: " . $e->getMessage() . "\n";
          	// puis tout le code nécessaire pour récupérer l'erreur
		}
     	//echo json_encode(array("error"=>$info["error"]));   
     	echo json_encode($info);   
	}
	
}
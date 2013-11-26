<?php
require_once( "../application/configs/config.php" );

try {
	$application->bootstrap();

	/*
	$o = new Flux_tagOcrible();
	$arr = $o->getTags("luckysemiosis", "flux_zotero");
	*/
	
	$server = new Zend_Amf_Server();
	
	//$server->setClass('Flux_tagOcrible');
	
	$response = $server->handle();

}catch (Zend_Exception $e) {
	echo "Récupère exception: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
}
   		
echo $response;

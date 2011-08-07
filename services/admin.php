<?php
require_once( "../application/configs/config.php" );

try {
	
	//paramètrage du cache
	$frontendOptions = array(
    	'lifetime' => 31536000, //  temps de vie du cache de 1 an
        'automatic_serialization' => true
	);
   	$backendOptions = array(
		// Répertoire où stocker les fichiers de cache
   		'cache_dir' => ROOT_PATH.'/tmp/'
	);
	// créer un objet Zend_Cache_Core
	$cache = Zend_Cache::factory('Core','File',$frontendOptions,$backendOptions);				
	
	
	$application->bootstrap();

	$user = "luckysemiosis";
	$pwd = "xxxxx";
	
	$f = new Flux_Dbpedia();
	$f->cache = $cache;
	$f->GetTagsLinks($user);
	
	//
	$f = new Flux_Delicious($cache);
	$f->cache = $cache;
	$f->SaveUserPost($user, $pwd);
	$f->SaveUserPostUser($user, $pwd);
	$f->user = $user;
	//$f->GetHtmlDetailUrl(array("doc_id"=>6668,"url"=>"http://www.worldcat.org/"));
	//
	$s = new Flux_Stats;
	//$arr = $s->GetTypeRelaUser(1);
	
	
	$server = new Zend_Amf_Server();

	// *ZAMFBROWSER IMPLEMENTATION*
	$server->setClass( "ZendAmfServiceBrowser" );
	ZendAmfServiceBrowser::$ZEND_AMF_SERVER = $server;
	
	$server->setClass('Flux_delicious');
	
	$response = $server->handle();

}catch (Zend_Exception $e) {
	echo "Récupère exception: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
}
   		
echo $response;

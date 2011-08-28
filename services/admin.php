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
	$pwd = "Samszo0";
	
	$zot = new Flux_Zotero();
	$zot->SaveRdf($_REQUEST);
	
	//$f = new Flux_Dbpedia();
	//$f->cache = $cache;
	//$f->SaveUserTagsLinks($user);
	
	//$d = new Model_DbTable_Flux_Doc();
	//$d->remove(7641);
	
	//
	$f = new Flux_Delicious($user, $pwd);
	$f->cache = $cache;	
	//$f->SaveUserFan($user, $pwd);
	//$f->SaveUserNetwork($user, $pwd);
	//$f->SaveUserPost($user, $pwd);
	//$f->SaveUserPostUser($user, $pwd);
	//$f->UpdateUserBase($user, $pwd);
	/*
	$f->user = $user;
	$f->idUser = 1;
	$f->SaveHtmlDetailUrl("http://www.freebase.com/");
	*/
	
	//$s = new Flux_Stats;
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

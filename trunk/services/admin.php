<?php
require_once( "../application/configs/config.php" );

try {
	
	$application->bootstrap();
	
	$user = "luckysemiosis";
	
	$zot = new Flux_Zotero();
	//$zot->SaveRdf($_REQUEST);
	
	//$f = new Flux_Dbpedia();
	//$f->SaveUserTagsLinks($user);
	
	//$d = new Model_DbTable_Flux_Doc();
	//$d->remove(7641);
	
	//
	$f = new Flux_Delicious();
	//$f->forceCalcul = true;
	//$f->SaveUserFan($user, $pwd);
	//$f->SaveUserNetwork($user, $pwd);
	//$f->SaveUserPost($user);
	//$f->SaveUserPostUser($user, $pwd);
	//$f->UpdateUserBase($user, $pwd);
	//
	$f->user = $user;
	$f->idUser = 1;
	$f->SaveDetailUrl("www.worldcat.org/");
	//
	
	$s = new Flux_Stats;
	$s->forceCalcul = true;
	//$s->update("simple");
	$arr = $s->GetTagUserNetwork('bibliothèque', array("login"=>$user, "pwd"=>"Samszo0"));
	
	
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

<?php
require_once( "../application/configs/config.php" );

try {
	
	$application->bootstrap();
	
	$user = "luckysemiosis";
	$pwd = "";
	
	$lu = new Flux_Lucene();
	//$lu->addBddDocs();
	$hits = $lu->find("spinoza");
	$arr="";
	foreach ($hits as $hit) {
	    $arr[] = array("score"=>$hit->score,"title"=>$hit->title,"titre"=>$hit->titre,"url"=>$hit->url,"mp3"=>$hit->mp3);
	}
	print_r($arr);
	
	/* pour importer les classeurs google doc
	//$gd = new Flux_Gdata($user, $pwd);
	//$arr = $gd->saveSpreadsheetsTags();
		
	/* pour importer les rdf de Zotero
	//$zot = new Flux_Zotero();
	//$zot->SaveRdf($_REQUEST);
	
	// pour importer les flux Wikipedia
	//$f = new Flux_Dbpedia();
	//$f->SaveUserTagsLinks($user);
	
	//$d = new Model_DbTable_Flux_Doc();
	//$d->remove(7641);
	
	//
	$f = new Flux_Delicious();
	/*
	$f->forceCalcul = true;
	$f->SaveUserFan($user, $pwd);
	$f->SaveUserNetwork($user, $pwd);
	$f->SaveUserPost($user);
	$f->SaveUserPostUser($user, $pwd);
	$f->UpdateUserBase($user, $pwd);
	$f->user = $user;
	$f->idUser = 1;
	$f->SaveDetailUrl("www.worldcat.org/");
	
	$s = new Flux_Stats;
	$s->forceCalcul = true;
	//$s->update("simple");
	$arr = $s->GetTagUserNetwork('bibliothèque', array("login"=>$user, "pwd"=>"Samszo0"));
	*/
	
	
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

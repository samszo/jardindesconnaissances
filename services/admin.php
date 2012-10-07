<?php
require_once( "../application/configs/config.php" );

try {
	$application->bootstrap();
	$user = "luckysemiosis";
	$pwd = "samszo";

	/*
	$zotero = new Flux_Zotero($user);
	$zotero->sauveAmazonInfo();
	$zotero->saveAll();
	*/
	/*
	$d = new Flux_Amazon("flux_diigo");
	$d->sauveActuLivre();	
	$d = new Flux_Decitre("flux_diigo");
	$d->sauveActuLivre();
	*/
	
	/*
	$g = new Flux_Gurl("samszon@gmail.com", "Janvier2010", "flux_urlcourtes");
	$g->saveUserHistory("", 0, true);
    */
	
	/*
	$s = new Flux_Site("flux_gmail_intelligence_collective");
	$dbDoc = new Model_DbTable_Flux_Doc($s->db);
	$arrD = $dbDoc->findByTronc(3037);
	//$arr = $s->getKWAlchemy($arrD[0]['note']);	
	$arr = $s->getKWYahoo($arrD[0]['note']);	
	*/
	
	/*$arrD =  $dbDoc->findByType(57);
	$i = 0;
	foreach ($arrD as $doc_id) {
		$d = $dbDoc->findBydoc_id($doc_id);
		$d = $d[0];
		$s->saveKW($d['doc_id'], $texte,"","yahoo");
		$i++;
	}
	*/
	
	
    //pour gérer les compte mail
    //$gm = new Flux_Gmail("samszon","Janvier2010","flux_gmail_intelligence_collective");
    //$gm->saveFolderMessages("veille/art", "image");
    //$gm->saveFolderMessages("veille/intelligence collective", "google_alerte");
    //$gm->getMessagesByFolderName("veille/intelligence collective");
    //$gm->getDossiers();
	
	
	//$s = new Flux_Site("flux_zotero");
	//$dbD = new Model_DbTable_flux_tagdoc($s->db);
	//$rs = $dbD->findDocTroncByTagId("10", array("intelligence","collective"));
	
	//$s = new Flux_Stats("flux_zotero");
	//$stats = $s->GetMatriceTagAssos(array("intelligence","collective"),-1);	    
	
	
    //$diigo = new Flux_Diigo($user,$pwd,"flux_diigo");
	//$diigo->saveAll();
    //$diigo->saveArchiveRss("http://localhost/jardindesconnaissances/data/182018_xml_2012_01_15_5b713.xml");
    //$diigo->getGroupeRss("bulles");
	//$diigo->getRequest(array("user"=>$user,"count"=>100));
    //$diigo->getRequest(array("user"=>$user,"count"=>100, "tags"=>"actulivre", "start"=>500));
	
	//$tp = new Flux_Tweetpalette('flux_tweetpalette');
	//$json = $tp->getPaletteClics('samszo', 'no', "../data/tweetpalette/idee_taxo.svg", 'no', true);
	
		
	//$audio = new Flux_Audio(false);
	//$audio->getOggInfos("c:\\wamp\\www\\jardindesconnaissances\\data\\deleuze\\mini\\106-.ogg");
	//$audio->convertMp3ToOgg("c:\\wamp\\www\\jardindesconnaissances\\data\\deleuze\\mini\\106-.mp3", "c:\\wamp\\www\\jardindesconnaissances\\data\\deleuze\\mini\\106-.ogg");
	//$audio->coupe("c:\\wamp\\www\\jardindesconnaissances\\data\\deleuze\\mini\\106-.mp3", "c:\\wamp\\www\\jardindesconnaissances\\data\\deleuze\\mini\\106_100_10.ogg", 400, 10);
	
	
	//$response = "OK bootstrap<br/><br/>";
	/*
	$oD = new Flux_Deleuze();
	$oD->user = 2;
	$oD->convertMp3ToOgg();
	$arr = $oD->getTermPositions("intuition");
	
	$oD->getLocalMp3();

	
	    
    /*
	$site = new Flux_Site();
    $db = $site->getDb("fluxDeleuzeSpinoza");
	$dbUTD = new Model_DbTable_Flux_UtiTagDoc($db);
	$arr = $dbUTD->getAllInfo();
	*/
		
	//pour gérer les index Lucene
	/*
	$lu = new Flux_Lucene();
	$lu->index->optimize();
	$lu->getDb("flux_diigo");
	$lu->getUser(array("login"=>"Flux_Lucene"));
	//$hits = $lu->find("intelligence collective");
	//$result = $lu->getTermPositions(array('field'=>'body', 'text'=>'intelligence'));
	$Terms[] = new Zend_Search_Lucene_Index_Term(strtolower('intelligence'), 'body');
	$Terms[] = new Zend_Search_Lucene_Index_Term(strtolower('collective'), 'body');
	$lu->saveDocsTerms($Terms);
	
	//$lu->addDocsInfos();
	//$result = $lu->getTermPositions(array('field'=>'body', 'text'=>'intelligence'));
	foreach ($hits as $hit) {
	    $arr[] = array("score"=>$hit->score,"title"=>$hit->title,"url"=>$hit->url);
	}

	$terms = $lu->index->terms();	
	$lu->getTermPositions(array('field'=>'body', 'text'=>'Justine'));
	*/
	
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
	
	/*
	
	$server = new Zend_Amf_Server();

	// *ZAMFBROWSER IMPLEMENTATION*
	$server->setClass( "ZendAmfServiceBrowser" );
	ZendAmfServiceBrowser::$ZEND_AMF_SERVER = $server;
	
	$server->setClass('Flux_delicious');
	*/
	//$response = $server->handle();

}catch (Zend_Exception $e) {
	echo "Récupère exception: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
}
   		
echo $response;

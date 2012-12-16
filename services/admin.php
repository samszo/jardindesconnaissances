<?php
require_once( "../application/configs/config.php" );

try {
	
	$application->bootstrap();
	$user = "luckysemiosis";
	$pwd = "samszo";
	
	$s = new Flux_Site('flux_tweetpalette');
	//$s->sauveUtiByImage('../data/etudiants/CDNL_12-13', "étudiants CDNL 12-13");
	$dbU = new Model_DbTable_Flux_Uti($s->db);
	$roles = $dbU->getRolesUtis();
	
	
	/*
	$tp = new Flux_Tweetpalette('flux_tweetpalette');
	$json = $tp->getPaletteClics('bernard stiegler', 'http://www.capdigital.com/evenements/enmi/', "../data/tweetpalette/AxePertiClair.png", 'ENMI', true);
	*/
	/*
	$zotero = new Flux_Zotero($user);
	//$zotero->saveAll();
	$zotero->sauveOCLCInfo();
	$zotero->sauveAmazonInfo();
	*/
	
	/*
	$d = new Flux_Amazon("flux_diigo");
	$d->sauveActuLivre();	
	$d = new Flux_Decitre("flux_diigo");
	$d->sauveActuLivre();
	*/
	
	/*
	$ieml = new Flux_IEML("flux_ieml");
	$ieml->genereSvgPlanSeq();	
	//$ieml->genereSequences(2, true);
	//$ieml->saveParse();
	//$ieml->saveBinary();
	//$ieml->saveScriptValeur();
	$bin1 = "1100110001000010000010001000010000011111000100011001000111110001000110010001111110001100001011111111111111111111111101000111111111111111111111111110011000100001000001000100001000001111100010001100100011111000100011001000111111000110000101111111111111111111111100010001101000011111000010110000101111111111111";
	$bin2 = "1100110001000010000010001000010000011111000100011001000111110001000110010001111110001100001011111111111111111111111101000111111111111111111111111110011000100001000001000100001000001111100010001100100011111000100011001000111111000110000101111111111111111111111100010001101000011111000010110000101111111111111110110001000010000010001000010000011111000100011001000111110001000110010001111100010110100011111111111111111111111010000011010001111111111111111111111";
	$d1 = $ieml->getDistanceHamming($bin1, $bin2);
	$d1 = $ieml->getDistanceHamming("1", "11");
	*/	
	
	//$s = new Flux_Stats();
	//$a = $s->permute(array('U','A','S','B','T','E'));
	
	/*générateur couche 1
	$t1 = array('E','U','A','S','B','T');
	$t2 = array('E','U','A','S','B','T');
	$t3 = array('E','U','A','S','B','T');
	$x = new ArrayMixer(":");
	$x->append($t1);
	$x->append($t2);
	$x->append($t3);
	$x->proceed();
	$c1 = $x->result();	
	print_r($c1);
	*/
	
	/*générateur couche 2
	$x = new ArrayMixer(".");
	$x->append($c1);
	$x->append($c1);
	$x->append($c1);
	$x->proceed();
	$c2 = $x->result();	
	print_r($c2);
	*/
	/*
	$s = new Flux_Site();
	$arr = $s->getKWCEPT("ontology", "term2bitmap");
	
	$tp = new Flux_Tweetpalette("flux_frontieres");
	$db = $tp->db;
	//$arr = $tp->getFondStat(1218);
	
    $dbGUD = new Model_DbTable_Flux_UtiGeoDoc($db);
    $arr["indTerreForDoc"] = $dbGUD->calcIndTerreForDoc();
    $arr["indTerreForUti"] = $dbGUD->calcIndTerreForUti();
	$dbUTD = new Model_DbTable_Flux_UtiTagDoc($db);

	//calcul les indices des tags cloud
    //$arr["UtisNbTagNbDoc"] = $dbUTD->GetUtisNbTagNbDoc();
    $arr["indTerreTagForUti"] = $dbUTD->calcIndTerreTagForUti();
    $arr["indTerreTagForDoc"] = $dbUTD->calcIndTerreTagForDoc();
    $arr["indTerreTagForTag"] = $dbUTD->calcIndTerreTagForTag();
	*/
	
							
	/*
	$p ='<XLS
    xmlns:xls="http://www.opengis.net/xls"
    xmlns:gml="http://www.opengis.net/gml"
    xmlns="http://www.opengis.net/xls"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    version="1.2"
    xsi:schemaLocation="http://www.opengis.net/xls http://schemas.opengis.net/ols/1.2/olsAll.xsd">
  <RequestHeader/>
  <Request requestID="1" version="1.2" methodName="LocationUtilityService">
   <GeocodeRequest returnFreeForm="false">
     <Address countryCode="PositionOfInterest">
       <freeFormAddress>rennes</freeFormAddress>
     </Address>
   </GeocodeRequest>
  </Request>
</XLS>';
	$s = new Flux_Site();
	$s->getUrlBodyContent("http://gpp3-wxs.ign.fr/c4p0y3y827jx03whl91uk689/geoportail/ols",array("xsl"=>$p, "output"=>"json"),false);
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

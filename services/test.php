<?php
require_once( "../application/configs/config.php" );

try {
	$application->bootstrap();
	$s = new Flux_Site(false,true);
	$s->trace("DEBUT TEST");		
	
	$user = "luckysemiosis";
	$pwd = "samszo";

	/*
	$client = new Google_Client();
	$client->setClientId(KEY_GOOGLE_CLIENT_ID);
	$client->setClientSecret(KEY_GOOGLE_CLIENT_SECRET);
	$client->setRedirectUri('http://localhost/jdc/public/auth/google');
	$client->addScope("https://www.google.com/calendar/feeds/");
	$url = $client->createAuthUrl();
	*/
	
	/*
	$ieml = new Flux_IEML("flux_ieml");
	//$ieml->genereSequences(3,true);
	$ieml->genereSvgPlanSeq();		
	*/
	
	/*
	set_time_limit(9800);
	$s = new Flux_Site();
	$addit = new Flux_Adit("flux_sic");
	$addit->getAllBE();
	*/
	
	/*
	$eval = new Flux_Eval('flux_gapaii');
	$eval->saveSem('flux_gapaii', $idDoc, $idUti, $sem);
	*/
	

	/*
	$s = new Flux_Site('flux_zotero');
	$dbUTD = new Model_DbTable_Flux_UtiTagDoc($s->db);
	$arr = $dbUTD->getDeweyTagDoc(638);
	$dbT = new Model_DbTable_Flux_Tag($s->db);
	$arr = $dbT->getFullPath(1970);
	*/
	
	/*
	$s = new Flux_Site('flux_tweetpalette');
	//$s->sauveUtiByImage('../data/etudiants/CDNL_12-13', "étudiants CDNL 12-13");
	//$dbU = new Model_DbTable_Flux_Uti($s->db);
	//$roles = $dbU->getRolesUtis();
	print_r($roles);
	//$dbUU = new Model_DbTable_Flux_UtiUti($s->db);
	//$dbUU->ajouter(array("uti_id_src"=>2, "uti_id_dst"=>94, "eval"=>1));
	*/
	
	/*
	$tp = new Flux_Tweetpalette('flux_tweetpalette');
	$json = $tp->getPaletteClics('bernard stiegler', 'http://www.capdigital.com/evenements/enmi/', "../data/tweetpalette/AxePertiClair.png", 'ENMI', true);
	*/
	/*
	$zotero = new Flux_Zotero($user);
	$arr = $zotero->getDeweyTagDoc();
	print_r($arr);
	*/
	/*
	//$zotero->saveAll();
	//$zotero->sauveOCLCInfo();
	//$zotero->sauveAmazonInfo();
	$code = simplexml_load_string("<DataTable>
<Table1><tag_id>1574</tag_id><code>853</code></Table1>
<Table1><tag_id>1810</tag_id><code>415</code></Table1>	
</DataTable>
	");
	$time_start = microtime(true);
	foreach ($code->Table1 as $tag) {
		$zotero->sauveDeweyHierarchie($tag->code."");
		//$zotero->setDeweyTagDocHierarchie($tag->doc_id."");
		//$dbT->edit($tag->tag_id."", array("desc"=>$tag->lib.""));
		//$dbT->remove($tag->tag_id);
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		echo $time." ".$tag->tag_id." : ".$tag->code." - ".$tag->desc."<br/>";
	}
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
	
	/*
	$arrD =  $dbDoc->findByType(57);
	$i = 0;
	foreach ($arrD as $doc_id) {
		$d = $dbDoc->findBydoc_id($doc_id);
		$d = $d[0];
		$s->saveKW($d['doc_id'], $texte,"","yahoo");
		$i++;
	}
	*/

	/*
	$s = new Flux_Site("flux_h2ptm");
	$dbDoc = new Model_DbTable_Flux_Doc($s->db);
	$arrD = $dbDoc->getAll();
	$i = 0;
	foreach ($arrD as $d) {
		$s->saveKW($d['doc_id'], $d['data'],"","autokeyword");
		$i++;
	}
	*/
	
    //pour g�rer les compte mail
    /*
	$gm = new Flux_Gmail("samszon","F�vrier2013","flux_gmail_intelligence_collective");
    $gm->saveFolderMessages("veille/art", "image");
	$gm->saveFolderMessages("veille/intelligence collective", "google_alerte");
    $gm->getMessagesByFolderName("veille/intelligence collective");
    $gm->getDossiers();
	*/
	
	//$s = new Flux_Site("flux_zotero");
	//$dbD = new Model_DbTable_flux_tagdoc($s->db);
	//$rs = $dbD->findDocTroncByTagId("10", array("intelligence","collective"));
	
	
	//$s = new Flux_Stats("flux_sic");
	//$stats = $s->GetTagHisto(array("information","communication"),"", "Y");	    
	//$stats = $s->GetMatriceTagAssos(array("intelligence","collective"),-1);	    
	//$s->forceCalcul = true;
	//$s->update("simple");
	//$arr = $s->GetTagUserNetwork('bibliothèque', array("login"=>$user, "pwd"=>"Samszo0"));
	
	/*
    $diigo = new Flux_Diigo("luckysemiosis","samszo","flux_evaletu",true);
    $diigo->bTraceFlush = true;
    $arrCompte = array("ernestovi","Arso972","milounis","crazyyoshi","Ghislainguy","noelno","wumiolabisi","BCottereau","carolinemourer","DjamelMeziane","elographicdesigner","nazadounet","Sissiwiki","arzouz");
    $arrCompte = array("FatihiZakaria","jean888","ettanass","elyaagoubimhamed","thypbast","kesraoui","Elalami90","Yannmahuet","herrhilmi","mkrayem","babachir","darkmido","elmounjide","abdel1314","elmiloudiasmae","taoufik072","NKalmouni","faizaelmoufid","samiaMALKI","brenda78","BDalila","yasmina1");
    foreach ($arrCompte as $c) {
    		$diigo->saveAll($c);
	}
    $diigo->saveArchiveRss("http://localhost/jardindesconnaissances/data/182018_xml_2012_01_15_5b713.xml");
    $diigo->getGroupeRss("bulles");
	$diigo->getRequest(array("user"=>$user,"count"=>100));
    $diigo->getRequest(array("user"=>$user,"count"=>100, "tags"=>"actulivre", "start"=>500));	
	*/
		
	//$audio = new Flux_Audio(false);
	//$audio->getOggInfos("c:\\wamp\\www\\jardindesconnaissances\\data\\deleuze\\mini\\106-.ogg");
	//$audio->convertMp3ToOgg("c:\\wamp\\www\\jardindesconnaissances\\data\\deleuze\\mini\\106-.mp3", "c:\\wamp\\www\\jardindesconnaissances\\data\\deleuze\\mini\\106-.ogg");
	//$audio->coupe("c:\\wamp\\www\\jardindesconnaissances\\data\\deleuze\\mini\\106-.mp3", "c:\\wamp\\www\\jardindesconnaissances\\data\\deleuze\\mini\\106_100_10.ogg", 400, 10);
	//$arr = $audio->getWave("../data/audios/De_0954881269_10112013_18h02_527fbc5098a68.wav");	    
	
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
	*/
	/* pour importer les flux Wikipedia
	$f = new Flux_Dbpedia();
	//$f->SaveUserTagsLinks($user);
	$rep = $f->getBio("Adam_Smith");
	*/
	
	//$d = new Model_DbTable_Flux_Doc();
	//$d->remove(7641);
	
	/*
	$f = new Flux_Delicious();
	$f->forceCalcul = true;
	$f->SaveUserFan($user, $pwd);
	$f->SaveUserNetwork($user, $pwd);
	$f->SaveUserPost($user);
	$f->SaveUserPostUser($user, $pwd);
	$f->UpdateUserBase($user, $pwd);
	$f->user = $user;
	$f->idUser = 1;
	$f->SaveDetailUrl("www.worldcat.org/");
	*/
	
	/*
	$server = new Zend_Amf_Server();
	// *ZAMFBROWSER IMPLEMENTATION*
	$server->setClass( "ZendAmfServiceBrowser" );
	ZendAmfServiceBrowser::$ZEND_AMF_SERVER = $server;
	$server->setClass('Flux_delicious');
	*/
	//$response = $server->handle();

	/*
	$bnf = new Flux_Databnf();
	//$rs = $bnf->getSudocAutoriteByISBN("2-7073-0307-0");
	//$rs = $bnf->getRameauByIdBnf("11983292");
	$rs = $bnf->getGallicaByTerm($_GET["term"]);
	//$s->trace(json_encode($rs));
	*/
	
	/*
	$s->getDb("flux_biolographes");
	$s->dbD = new Model_DbTable_Flux_ExiTagDoc($s->db);
	$rs = $s->dbD->GetExiTagDoc(1,2);
	*/
	/*
	$cairn = new Flux_Cairn("spip_e-educ_proverbes",true);
	$dt = $cairn->sauve100Citations("http://localhost/jdc/data/gapaii/Les%20100%20citations%20de%20la%20philosophie%20-%20Cairn.info.html",15);
	*/	
	
	/*
	$g = new Flux_Gknowledgegraph();
	$rep = $g->getQuery('zappa');	
	*/

	/*
	$s = new Flux_Skos('spip_e-educ_proverbes',true);
	$id = $s->getUriByLabel('Droits humains');
	$s->sauveToSpip("http://skos.um.es/unescothes/CS000/json",5);		
	*/
	
	/*
	$rmn = new Flux_Rmngp();
	$json = $rmn->getAutocomplete('troy');
	$s->trace($json);			
	*/
	
	$im = file_get_contents('1404571653141.jpg');
    $imdata = base64_encode($im); 
    
    $json = '{
	  "requests":[
	    {
	      "image":{
	        "content":'.$imdata.'
	      },
	      "features":[
	        {
	          "type":"LABEL_DETECTION",
	          "maxResults":1
	        }
	      ]
	    }
	  ]
	}';
	$url = "https://vision.googleapis.com/v1/images:annotate?key=".KEY_GOOGLE_BROWSER;
    $response = $s->getUrlBodyContent($url,array("requests"=>$json),false,Zend_Http_Client::POST);//"Content-Type: application/json"
	//$ curl -v -k -s -H "Content-Type: application/json" https://vision.googleapis.com/v1/images:annotate?key=browser_key --data-binary @request_filename
    
	$s->trace("FIN TEST");			
	
}catch (Zend_Exception $e) {
	 echo "<h1>Erreur d'exécution</h1>
  <h2>".$e->message."</h2>
  <h3>Exception information:</h3>
  <p><b>Message:</b>".$e->exception->getMessage()."</p>
  <h3>Stack trace:</h3>
  <pre>".$e->exception->getTraceAsString()."</pre>
  <h3>Request Parameters:</h3>
  <pre>".var_export($this->request->getParams(), true)."</pre>";
}
   		

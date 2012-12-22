<?php
require_once( "../application/configs/config.php" );

try {
	
	$application->bootstrap();
	$user = "luckysemiosis";
	$pwd = "samszo";
	
	/*
	$s = new Flux_Site('flux_tweetpalette');
	//$s->sauveUtiByImage('../data/etudiants/CDNL_12-13', "étudiants CDNL 12-13");
	//$dbU = new Model_DbTable_Flux_Uti($s->db);
	//$roles = $dbU->getRolesUtis();
	$dbUU = new Model_DbTable_Flux_UtiUti($s->db);
	$dbUU->ajouter(array("uti_id_src"=>2, "uti_id_dst"=>94, "eval"=>1));
	*/
	
	/*
	$tp = new Flux_Tweetpalette('flux_tweetpalette');
	$json = $tp->getPaletteClics('bernard stiegler', 'http://www.capdigital.com/evenements/enmi/', "../data/tweetpalette/AxePertiClair.png", 'ENMI', true);
	*/
	//
	$zotero = new Flux_Zotero($user);
	//$zotero->saveAll();
	//$zotero->sauveOCLCInfo();
	//$zotero->sauveAmazonInfo();
	$code = simplexml_load_string("<DataTable>
<code>306.01</code>
<code>712.01</code>
<code>153.753</code>
<code>005.13</code>
<code>194</code>
<code>199.492</code>
<code>126</code>
<code>193</code>
<code>499.99</code>
<code>153.15</code>
<code>025.4</code>
<code>025.04</code>
<code>408.9</code>
<code>001.4226</code>
<code>100</code>
<code>306</code>
<code>121</code>
<code>153.4</code>
<code>128</code>
<code>330</code>
<code>306.3</code>
<code>230.2</code>
<code>302.2</code>
<code>153</code>
<code>570.1</code>
<code>152.4</code>
<code>003.54</code>
<code>302</code>
<code>006.332</code>
<code>121.3</code>
<code>006.3</code>
<code>700</code>
<code>701.03</code>
<code>170.924</code>
<code>741.2</code>
<code>910.01</code>
<code>303</code>
<code>115</code>
<code>128.3</code>
<code>701.1</code>
<code>380</code>
<code>303.3</code>
<code>303.4833</code>
<code>025.0427</code>
<code>539.1</code>
<code>006.696</code>
<code>005.1</code>
<code>306.46</code>
<code>801.9510904</code>
<code>069.40944</code>
<code>300</code>
<code>409</code>
<code>621.366</code>
<code>006.6</code>
<code>004.678</code>
<code>006.331</code>
<code>020</code>
<code>006.312</code>
<code>302.231</code>
<code>121.4</code>
<code>155.26</code>
<code>150.195</code>
<code>801</code>
<code>601</code>
<code>306.6</code>
<code>170</code>
<code>169</code>
<code>813.54</code>
<code>005.7</code>
<code>371.337</code>
<code>190</code>
<code>005.12</code>
<code>025</code>
<code>323.042</code>
<code>004</code>
<code>794.8019</code>
<code>370.15</code>
<code>371.3</code>
<code>005.131</code>
<code>133</code>
<code>501</code>
<code>191</code>
<code>154</code>
<code>006.8</code>
<code>300.1</code>
<code>780.92</code>
<code>704.94603</code>
<code>843.5</code>
<code>840.915</code>
<code>701</code>
<code>160</code>
<code>192</code>
<code>189.5</code>
<code>843</code>
<code>910.285</code>
<code>111</code>
<code>901</code>
<code>710</code>
<code>199.2</code>
<code>910.92</code>
<code>370</code>
<code>352.367</code>
<code>808</code>
<code>401.3</code>
<code>162</code>
<code>412</code>
<code>809</code>
<code>740</code>
<code>371.33</code>
<code>001</code>
<code>005.74</code>
<code>411</code>
<code>304.2</code>
<code>570</code>
<code>530.01</code>
<code>306.42</code>
<code>712.6094409033</code>
<code>514</code>
<code>001.9</code>
<code>306.44</code>
<code>301.14</code>
<code>121.68</code>
<code>658.403</code>
<code>890</code>
<code>153.69</code>
<code>616.8914</code>
<code>658.402</code>
<code>658.45</code>
<code>511.3</code>
<code>400</code>
<code>301.21</code>
<code>843.4</code>
<code>591.56</code>
<code>150</code>
<code>711.40944361</code>
<code>111.85</code>
<code>005</code>
<code>001.30285</code>
<code>510</code>
<code>510.1</code>
<code>843.914</code>
<code>301.1</code>
<code>659.2</code>
<code>020.1</code>
<code>759.06</code>
<code>291.13</code>
<code>848.8</code>
<code>401.43</code>
<code>530.12</code>
<code>025.1</code>
<code>910.03</code>
<code>001.42</code>
<code>025.177</code>
<code>150.19501</code>
<code>152.46</code>
<code>712.6094436</code>
<code>801.95</code>
<code>709</code>
<code>302.23</code>
<code>303.4</code>
<code>153.6</code>
<code>152.14</code>
<code>001.4226028566</code>
<code>370.152</code>
<code>006.7</code>
<code>833</code>
<code>741.5</code>
<code>818.5407</code>
<code>199</code>
<code>401</code>
<code>520</code>
<code>120</code>
<code>301.51</code>
<code>230</code>
<code>574</code>
<code>157</code>
<code>704.03</code>
<code>701.17</code>
<code>708.531</code>
<code>194.9</code>
<code>001.5</code>
<code>303.483</code>
<code>801.93</code>
<code>005.754</code>
<code>303.484</code>
<code>658.312</code>
<code>750</code>
<code>759.046</code>
<code>841.914</code>
<code>181.12</code>
<code>409.4</code>
<code>153.42</code>
<code>650.1</code>
<code>142.7</code>
<code>530.11</code>
<code>149.94</code>
<code>302.54</code>
<code>892.7</code>
<code>195</code>
<code>297.39</code>
<code>658.406</code>
<code>401.41</code>
<code>306.45</code>
<code>609</code>
<code>291.37</code>
<code>809.93364</code>
<code>366.1</code>
<code>132.2422</code>
<code>149.946</code>
<code>720.1</code>
<code>743.99</code>
<code>574.4</code>
<code>306.0904</code>
<code>760</code>
<code>415</code>
<code>853</code>
<code>526.8</code>
<code>233.1</code>
<code>621.3827</code>
<code>003.857</code>
<code>709.033</code>
<code>189.4</code>
<code>712</code>
<code>500.285</code>
<code>111.092</code>
<code>658.4038</code>
<code>306.47</code>
<code>301.01</code>
<code>759</code>
<code>110</code>
<code>709.2</code>
<code>303.4834</code>
<code>700.1</code>
<code>410.92</code>
<code>146.44</code>
<code>909.82</code>
</DataTable>");
	
	foreach ($code->code as $code) {
		$zotero->getDeweyHierarchie($code."");
		echo $code."<br/>";
	}
	
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

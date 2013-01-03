<?php
require_once( "../application/configs/config.php" );

try {
	
	$application->bootstrap();
	$user = "luckysemiosis";
	$pwd = "samszo";

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
	$dbUU = new Model_DbTable_Flux_UtiUti($s->db);
	$dbUU->ajouter(array("uti_id_src"=>2, "uti_id_dst"=>94, "eval"=>1));
	*/
	
	/*
	$tp = new Flux_Tweetpalette('flux_tweetpalette');
	$json = $tp->getPaletteClics('bernard stiegler', 'http://www.capdigital.com/evenements/enmi/', "../data/tweetpalette/AxePertiClair.png", 'ENMI', true);
	*/
	//
	$zotero = new Flux_Zotero($user);
	$arr = $zotero->getDeweyTagDoc();
	//$zotero->saveAll();
	//$zotero->sauveOCLCInfo();
	//$zotero->sauveAmazonInfo();
	$code = simplexml_load_string("<DataTable>
<Table1><tag_id>1574</tag_id><code>853</code></Table1>
<Table1><tag_id>1576</tag_id><code>306.01</code></Table1>
<Table1><tag_id>1577</tag_id><code>712.01</code></Table1>
<Table1><tag_id>1578</tag_id><code>153.753</code></Table1>
<Table1><tag_id>1581</tag_id><code>199.492</code></Table1>
<Table1><tag_id>1583</tag_id><code>126</code></Table1>
<Table1><tag_id>1584</tag_id><code>193</code></Table1>
<Table1><tag_id>1585</tag_id><code>499.99</code></Table1>
<Table1><tag_id>1587</tag_id><code>153.15</code></Table1>
<Table1><tag_id>1592</tag_id><code>520</code></Table1>
<Table1><tag_id>1594</tag_id><code>100</code></Table1>
<Table1><tag_id>1598</tag_id><code>120</code></Table1>
<Table1><tag_id>1600</tag_id><code>330</code></Table1>
<Table1><tag_id>1612</tag_id><code>003.54</code></Table1>
<Table1><tag_id>1614</tag_id><code>006.332</code></Table1>
<Table1><tag_id>1618</tag_id><code>704.03</code></Table1>
<Table1><tag_id>1619</tag_id><code>701.03</code></Table1>
<Table1><tag_id>1622</tag_id><code>910.01</code></Table1>
<Table1><tag_id>1624</tag_id><code>115</code></Table1>
<Table1><tag_id>1627</tag_id><code>701.17</code></Table1>
<Table1><tag_id>1628</tag_id><code>380</code></Table1>
<Table1><tag_id>1630</tag_id><code>303.4833</code></Table1>
<Table1><tag_id>1631</tag_id><code>025.0427</code></Table1>
<Table1><tag_id>1633</tag_id><code>006.696</code></Table1>
<Table1><tag_id>1635</tag_id><code>306.46</code></Table1>
<Table1><tag_id>1643</tag_id><code>621.366</code></Table1>
<Table1><tag_id>1644</tag_id><code>621.3827</code></Table1>
<Table1><tag_id>1646</tag_id><code>004.678</code></Table1>
<Table1><tag_id>1647</tag_id><code>006.331</code></Table1>
<Table1><tag_id>1649</tag_id><code>006.312</code></Table1>
<Table1><tag_id>1651</tag_id><code>302.231</code></Table1>
<Table1><tag_id>1653</tag_id><code>155.26</code></Table1>
<Table1><tag_id>1656</tag_id><code>801.93</code></Table1>
<Table1><tag_id>1657</tag_id><code>601</code></Table1>
<Table1><tag_id>1660</tag_id><code>169</code></Table1>
<Table1><tag_id>1661</tag_id><code>813.54</code></Table1>
<Table1><tag_id>1663</tag_id><code>005.754</code></Table1>
<Table1><tag_id>1664</tag_id><code>371.337</code></Table1>
<Table1><tag_id>1665</tag_id><code>303.484</code></Table1>
<Table1><tag_id>1666</tag_id><code>190</code></Table1>
<Table1><tag_id>1667</tag_id><code>005.12</code></Table1>
<Table1><tag_id>1669</tag_id><code>323.042</code></Table1>
<Table1><tag_id>1671</tag_id><code>658.312</code></Table1>
<Table1><tag_id>1675</tag_id><code>005.131</code></Table1>
<Table1><tag_id>1676</tag_id><code>133</code></Table1>
<Table1><tag_id>1677</tag_id><code>501</code></Table1>
<Table1><tag_id>1678</tag_id><code>003.857</code></Table1>
<Table1><tag_id>1679</tag_id><code>191</code></Table1>
<Table1><tag_id>1680</tag_id><code>154</code></Table1>
<Table1><tag_id>1683</tag_id><code>780.92</code></Table1>
<Table1><tag_id>1685</tag_id><code>750</code></Table1>
<Table1><tag_id>1689</tag_id><code>759.046</code></Table1>
<Table1><tag_id>1690</tag_id><code>709.033</code></Table1>
<Table1><tag_id>1691</tag_id><code>160</code></Table1>
<Table1><tag_id>1692</tag_id><code>192</code></Table1>
<Table1><tag_id>1696</tag_id><code>841.914</code></Table1>
<Table1><tag_id>1697</tag_id><code>910.285</code></Table1>
<Table1><tag_id>1699</tag_id><code>901</code></Table1>
<Table1><tag_id>1700</tag_id><code>710</code></Table1>
<Table1><tag_id>1703</tag_id><code>181.12</code></Table1>
<Table1><tag_id>1704</tag_id><code>910.92</code></Table1>
<Table1><tag_id>1706</tag_id><code>352.367</code></Table1>
<Table1><tag_id>1707</tag_id><code>808</code></Table1>
<Table1><tag_id>1710</tag_id><code>162</code></Table1>
<Table1><tag_id>1711</tag_id><code>412</code></Table1>
<Table1><tag_id>1713</tag_id><code>740</code></Table1>
<Table1><tag_id>1714</tag_id><code>153.42</code></Table1>
<Table1><tag_id>1718</tag_id><code>005.74</code></Table1>
<Table1><tag_id>1720</tag_id><code>411</code></Table1>
<Table1><tag_id>1725</tag_id><code>530.11</code></Table1>
<Table1><tag_id>1726</tag_id><code>530.01</code></Table1>
<Table1><tag_id>1727</tag_id><code>306.42</code></Table1>
<Table1><tag_id>1729</tag_id><code>514</code></Table1>
<Table1><tag_id>1731</tag_id><code>306.44</code></Table1>
<Table1><tag_id>1734</tag_id><code>302.54</code></Table1>
<Table1><tag_id>1735</tag_id><code>121.68</code></Table1>
<Table1><tag_id>1737</tag_id><code>890</code></Table1>
<Table1><tag_id>1739</tag_id><code>195</code></Table1>
<Table1><tag_id>1740</tag_id><code>153.69</code></Table1>
<Table1><tag_id>1741</tag_id><code>616.8914</code></Table1>
<Table1><tag_id>1742</tag_id><code>297.39</code></Table1>
<Table1><tag_id>1743</tag_id><code>658.402</code></Table1>
<Table1><tag_id>1744</tag_id><code>658.45</code></Table1>
<Table1><tag_id>1745</tag_id><code>658.406</code></Table1>
<Table1><tag_id>1746</tag_id><code>658.4038</code></Table1>
<Table1><tag_id>1747</tag_id><code>401.41</code></Table1>
<Table1><tag_id>1749</tag_id><code>400</code></Table1>
<Table1><tag_id>1750</tag_id><code>306.45</code></Table1>
<Table1><tag_id>1751</tag_id><code>609</code></Table1>
<Table1><tag_id>1754</tag_id><code>306.47</code></Table1>
<Table1><tag_id>1756</tag_id><code>591.56</code></Table1>
<Table1><tag_id>1757</tag_id><code>809.93364</code></Table1>
<Table1><tag_id>1760</tag_id><code>111.85</code></Table1>
<Table1><tag_id>1765</tag_id><code>843.914</code></Table1>
<Table1><tag_id>1767</tag_id><code>301.01</code></Table1>
<Table1><tag_id>1770</tag_id><code>759.06</code></Table1>
<Table1><tag_id>1776</tag_id><code>401.43</code></Table1>
<Table1><tag_id>1778</tag_id><code>530.12</code></Table1>
<Table1><tag_id>1781</tag_id><code>110</code></Table1>
<Table1><tag_id>1783</tag_id><code>025.177</code></Table1>
<Table1><tag_id>1785</tag_id><code>152.46</code></Table1>
<Table1><tag_id>1795</tag_id><code>303.4834</code></Table1>
<Table1><tag_id>1799</tag_id><code>152.14</code></Table1>
<Table1><tag_id>1800</tag_id><code>760</code></Table1>
<Table1><tag_id>1801</tag_id><code>001.4226028566</code></Table1>
<Table1><tag_id>1802</tag_id><code>370.152</code></Table1>
<Table1><tag_id>1803</tag_id><code>410.92</code></Table1>
<Table1><tag_id>1804</tag_id><code>146.44</code></Table1>
<Table1><tag_id>1806</tag_id><code>833</code></Table1>
<Table1><tag_id>1808</tag_id><code>818.5407</code></Table1>
<Table1><tag_id>1809</tag_id><code>909.82</code></Table1>
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
	//
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

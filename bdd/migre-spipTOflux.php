<?php

try {
	
	require_once( "../application/configs/config.php" );
	$application->bootstrap();
	
	$bddFlux = "flux_DeleuzeSpinoza"; 
	$bddSpip = "deleuze"; 
	
	//initialisation des tables
	$s = new Flux_Site($bddSpip);
	$bdArt = new Model_DbTable_Spip_Articles($s->db);
	$s->getDb($bddFlux);
	$bdDoc = new Model_DbTable_Flux_Doc($s->db);
	
	//récupère les données des articles
	$arrArt = $bdArt->getAll();
	
	//migre les données
	$idArtO = -1;
	foreach ($arrArt as $art) {
		if($art["id_article"]!=$idArtO){
			//création de l'article
			$url = "http://www2.univ-paris8.fr/deleuze/article.php3?id_article=".$art["id_article"];
			$idDoc = $bdDoc->ajouter(array("url"=>$url,"titre"=>$art["titre"],"tronc"=>$art["rubTitre"],"type"=>48));			
			//met à jour le texte car problème d'ajout l'intégralité du texte
			//attention il faut convertir les blob en text dans phpmyadmin
			$sql = "UPDATE ".$bddFlux.".flux_doc d SET d.note = (SELECT texte FROM ".$bddSpip.".spip_articles WHERE id_article = ".$art["id_article"].") WHERE d.doc_id =".$idDoc;
			$stmt = $s->db->query($sql);
			
			$idArtO = $art["id_article"];
		}
		//création du mp3
		$url = "http://www2.univ-paris8.fr/deleuze/".$art["fichier"];
		$bdDoc->ajouter(array("url"=>$url,"tronc"=>$idDoc,"type"=>14));			
		
	}

}catch (Zend_Exception $e) {
	 echo "<h1>Erreur d'exécution</h1>
  <h2>".$this->message."</h2>
  <h3>Exception information:</h3>
  <p><b>Message:</b>".$this->exception->getMessage()."</p>
  <h3>Stack trace:</h3>
  <pre>".$this->exception->getTraceAsString()."</pre>
  <h3>Request Parameters:</h3>
  <pre>".var_export($this->request->getParams(), true)."</pre>";
}

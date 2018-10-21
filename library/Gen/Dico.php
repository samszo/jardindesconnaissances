<?php
/**
 * Generateur Framework
 *
/**
 * Concrete class for generating Fragment for Generateur.
 *
 * @category   Generateur
 * @package    Import
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Gen_Dico
{

	/**
	 * @xml le dictionnaire au format xml
	 */
	var $xml;
	var $xmlRoot;
	var $xmlTmp;
	var $xmlDesc;
	var $url;
	var $urlS;
	var $pathS;
	var $type;
	var $Xtype;
	var $id;
	var $nom;
	var $dbConj;
	var $dbTrm;
	var $dbDtm;
	var $dbCpm;
	var $dbSyn;
	var $dbCon;
	var $dbVer;
	var $dbAdj;
	var $dbSub;
	var $dbGen;
	var $dbConVer;
	var $dbConAdj;
	var $dbConSub;
	var $dbConSyn;
	var $dbConGen;
	var $dbDicos;
	var $dbPro;
	var $dbNeg;
	
	/**
	 * Le constructeur initialise le dictionnaire.
	 */
	public function __construct($urlDesc="") {

		if($urlDesc=="")$urlDesc=APPLICATION_PATH.'/configs/LangageDescripteur.xml';
		$this->urlDesc = $urlDesc;	
			
	}

	public function importCSV($idDico, $docInfos){
	try {
		$this->dbCon = new Model_DbTable_Gen_concepts();
		$this->dbGen = new Model_DbTable_Gen_generateurs();
		$this->dbSub = new Model_DbTable_Gen_substantifs();
		$this->dbAdj = new Model_DbTable_Gen_adjectifs();
		$this->dbVer = new Model_DbTable_Gen_verbes();
		$this->dbConj = new Model_DbTable_Gen_conjugaisons();
		$this->dbOdu = new Model_DbTable_Gen_oeuvresxdicosxutis();
		
	    //chargement du fichier
		$arr = $this->csvToArray($docInfos['path_source']);
		$cpt = "";
		$first = true;
		$type = "";
		foreach ($arr as $v) {
			//if(count($v)==3) throw new Exception("Le fichier est mal formaté : il n'y a pas 3 colonnes.");
			if(count($v)==3) $type="gen";
			if(count($v)==5) $type="ver";
			if(count($v)==7) $type="sub";
			if(count($v)==8) $type="adj";
			
			if(!$first){
				//vérifie si on traite le même concept
				if($cpt!=$v[0]){
					$idC = $this->dbCon->ajouter(array("id_dico"=>$idDico,"lib"=>$v[0],"type"=>$v[1]));
					$cpt=$v[0];						
				}
				switch ($type) {
					case "gen":
						//pour gérer les , dans le champ valeur
						$val = str_replace('§',',',$v[2]);
			    		$this->dbGen->ajouter($idC, array("id_dico"=>$idDico,"valeur"=>$val));
						break;
					case "sub":
			    		$this->dbSub->ajouter(array("id_concept"=>$idC), array("id_dico"=>$idDico,"elision"=>$v[2],"genre"=>$v[3],"prefix"=>$v[4],"s"=>$v[5],"p"=>$v[6]));
						break;
					case "adj":
			    		$this->dbAdj->ajouter(array("id_concept"=>$idC), array("id_dico"=>$idDico,"elision"=>$v[2],"prefix"=>$v[3],"m_s"=>$v[4],"f_s"=>$v[5],"m_p"=>$v[6],"f_p"=>$v[7]));
						break;
					case "ver":
						if(!$idConj){
							//recherche le dico de conjugaison
							$arrConj = $this->dbOdu->findByDicoType($idDico, "conjugaisons");
							if(!$arrConj)$idConj=1;
							else{
								//recherche le modèle
								if(is_int($v[4]))
									$arrConj = $this->dbConj->findByDicoNum($arrConj[0]["id_dico"], $v[4]);
								else
									$arrConj = $this->dbConj->findByDicoModele($arrConj[0]["id_dico"], $v[4]);
								if(!$arrConj)$idConj=1;
								else $idConj = $arrConj[0]["id_conj"];
							}
						}
			    		$this->dbVer->ajouterCpt(array("id_concept"=>$idC), array("id_dico"=>$idDico,"elision"=>$v[2],"prefix"=>$v[3],"id_conj"=>$idConj));
						break;
					default:
						break;
				}
			}
			$first = false;
		}
		// print the array of the csv file.
		//print_r($arr);

	}catch (Zend_Exception $e) {
          echo "Récupère exception: " . get_class($e) . "\n";
          echo "Message: " . $e->getMessage() . "\n";
	}
			
	}

	function csvToArray($file){
		# Open the File.
	    if (($handle = fopen($file, "r")) !== FALSE) {
	        while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
	            $csvarray[] = $data;
	        }
	        fclose($handle);
	    }
     	return $csvarray;		
	}
	
	public function GetMacToXml($idDico){

	try {
		
		//récupération des infos du dico
		$this->id = $idDico;
		$dbDico = new Model_DbTable_Gen_dicos();		
		$arr = $dbDico->findByIdDico($this->id);
		$this->urlS = $arr[0]['url_source'];
		$this->type = $arr[0]['type'];
		$this->Xtype = $this->RemoveAccents($this->type);
		$this->pathS = $arr[0]['path_source'];
		$this->nom = $arr[0]['nom'];
		
		//chargement de la configuration du langage
		$this->xmlDesc = simplexml_load_file($this->urlDesc);

		//initialisation du xml de stockage du dico
		$this->InitXmlDico();
		
		//chargement du dictionnaire
		$chaines = file($this->pathS);
		
		//chargement du traitement défini dans la description du langage
		$trtmt = $this->xmlDesc->xpath("//dico[@type='".$this->type."']/traitements");		
		
		// parcourt toute les ligne du dictionnaire
		foreach ($chaines as $line_num => $chaine) {
			//applique le traitement pour chaque ligne
			foreach ($trtmt[0]->children() as $action) {
				//pour faciliter la gestion des explode
				$chaine = str_replace("\r","- -",$chaine);
				//supprime les retours chariots en trop
				$chaine = str_replace("- -- -- -","- -- -",$chaine);
				
				//pour l'encode des caractère
				//$chaine = mb_convert_encoding($chaine, "UTF-8", "macintosh");
				//$chaine = iconv('UTF-8', 'macintosh////IGNORE', $chaine);
				//$chaine = htmlentities($chaine);
				//$chaine = html_entity_decode($chaine); 
				//$chaine = utf8_encode($chaine);
				//http://sebastienguillon.com/journal/2006/03/convertir-macroman-en-utf-8
				// en C : http://alienryderflex.com/utf-8/
				$chaine = $this->MacRoman_to_utf8($chaine);
				
				$this->TraiteAction($chaine, $action);
			}
		}
		$this->Save();
       
	}catch (Zend_Exception $e) {
          // Appeler Zend_Loader::loadClass() sur une classe non-existante
          //entrainera la levée d'une exception dans Zend_Loader
          echo "Récupère exception: " . get_class($e) . "\n";
          echo "Message: " . $e->getMessage() . "\n";
          // puis tout le code nécessaire pour récupérer l'erreur
	}
		
	}

	public function Save(){

	try {
    	
		if($this->xml){
			$this->xml->save(ROOT_PATH."/data/dicos/".$this->Xtype."_".$this->id.".xml");
			$this->url = WEB_ROOT."/data/dicos/".$this->Xtype."_".$this->id.".xml";
		}else{
			$this->url = "";
		}
			
		//création des objects de bd
		$dbDico = new Model_DbTable_Gen_dicos();
		
		//enregistre le dico
		if($this->id){
			$dbDico->edit($this->id, array("url"=>$this->url));
		}else{
			$pk = $dbDico->ajouter(array("url"=>$this->url, "nom"=>$this->nom, "type"=>$this->type, "url_source"=>$this->urlS, "path_source"=>$this->pathS));
			$this->id = $pk;
		}					
       
	}catch (Zend_Exception $e) {
          // Appeler Zend_Loader::loadClass() sur une classe non-existante
          //entrainera la levée d'une exception dans Zend_Loader
          echo "Récupère exception: " . get_class($e) . "\n";
          echo "Message: " . $e->getMessage() . "\n";
          // puis tout le code nécessaire pour récupérer l'erreur
	}
		
	}

	public function SaveBdd($idDico, $idDicoConj=-1, $idDicoMerge=-1){
		
	try {
		
		//récupère les infos du dico
		$dbDico = new Model_DbTable_Dicos();
		$this->id = $idDico;
		$this->idConj = $idDicoConj;
		$arrD = $dbDico->obtenirDico($this->id);		
		
		//parcourt le xml
		$path = str_replace(WEB_ROOT,ROOT_PATH,$arrD['url']);
		$this->xml = simplexml_load_file($path);

		//création des objets de base
		switch ($arrD['type']) {
			case 'conjugaisons':
				$this->dbConj = new Model_DbTable_Conjugaisons();
				$this->dbTrm = new Model_DbTable_Terminaisons();
				//ajoute le lien entre le dictionnaire général et le dictionnaire de référence
				$this->dbDicos = new Model_DbTable_DicosDicos();
				$this->dbDicos->ajouterDicoGenDicoRef($idDico,$idDico);
				break;
			case 'déterminants':
				$this->dbDtm = new Model_DbTable_Determinants();
				break;
			case 'compléments':
				$this->dbCpm = new Model_DbTable_Complements();
				break;
			case 'syntagmes':
				$this->dbSyn = new Model_DbTable_Syntagmes();
				break;
			case 'concepts':
				$this->dbCon = new Model_DbTable_Concepts();
				$this->dbVer = new Model_DbTable_Verbes();
				$this->dbAdj = new Model_DbTable_Adjectifs();
				$this->dbSub = new Model_DbTable_Substantifs();
				$this->dbSyn = new Model_DbTable_Syntagmes();
				$this->dbGen = new Model_DbTable_Generateurs();
				$this->dbConVer = new Model_DbTable_ConceptsVerbes();
				$this->dbConAdj = new Model_DbTable_ConceptsAdjectifs();
				$this->dbConSub = new Model_DbTable_ConceptsSubstantifs();
				$this->dbConSyn = new Model_DbTable_ConceptsSyntagmes();
				$this->dbConGen = new Model_DbTable_ConceptsGenerateurs();
				//récupère le dico de référence pour les conjugaisons
				$this->dbConj = new Model_DbTable_Conjugaisons();
				//ajoute le lien entre le dictionnaire général et le dictionnaire de référende
				$this->dbDicos = new Model_DbTable_DicosDicos();
				$this->dbDicos->ajouterDicoGenDicoRef($idDico,$idDicoConj);
				break;
			case 'pronoms_complement':
				$this->dbPro = new Model_DbTable_Pronoms();
				break;
			case 'pronoms_sujet':
				$this->dbPro = new Model_DbTable_Pronoms();
				break;
			case 'negations':
				$this->dbNeg = new Model_DbTable_Negations();
				break;
		}

		foreach ($this->xml->children() as $k => $n) {
			$this->SaveItem($k,$n);
			if($idDicoMerge!=-1){
				$this->id = $idDicoMerge;
				$this->SaveItem($k,$n);
				$this->id = $idDico;
			}
		}
		
	}catch (Zend_Exception $e) {
          // Appeler Zend_Loader::loadClass() sur une classe non-existante
          //entrainera la levée d'une exception dans Zend_Loader
          echo "Récupère exception: " . get_class($e) . "\n";
          echo "Message: " . $e->getMessage() . "\n";
          // puis tout le code nécessaire pour récupérer l'erreur
	}
		
	}
	
	private function SaveItem($k,$n){
		/*
		echo $k."<br/>";
		print_r($n);
		echo "<br/>";
		*/
		switch ($k) {
			case 'conjugaison':
				$pkV = $this->dbConj->ajouterConjugaison($this->id,$n['num'],$n['modele']);
				$i=0;
				foreach ($n->terminaisons->terminaison as $kTrm => $nTrm) {
					$this->dbTrm->ajouterTerminaison($pkV,$i,$nTrm);
					$i++;
				}
				break;
			case 'determinants':
				$num = $n['num'];
				$i=0;
				foreach ($n->determinant as $kDtm => $nDtm) {
					$this->dbDtm->ajouterDeterminant($this->id,$num,$i,$nDtm);
					$i++;
				}
				break;
			case 'complements':
				$num = $n['num'];
				$i=0;
				foreach ($n->complement as $kCpm => $nCpm) {
					$this->dbCpm->ajouterComplement($this->id,$num,$i,$nCpm);
					$i++;
				}
				break;
			case 'syntagmes':
				$num = $n['num'];
				$i=0;
				foreach ($n->syntagme as $kSyn => $nSyn) {
					$this->dbSyn->ajouterSyntagme($this->id,$num,$i,$nSyn);
					$i++;
				}
				break;
			case 'concept':
				$idC = $this->dbCon->ajouterConcept($this->id,$n['lib']."",$n['type']."");
				if($n['type']=="thl"){
					$to = 1;
				}
				foreach ($n->children() as $kCon => $nCon) {
					//ajout suivant le type de concept
			    	switch ($kCon) {
			    		case 'verbe':
			    			//récupère l'identifiant de conjugaison
			    			$idConj = $this->dbConj->obtenirConjugaisonIdByNumModele($this->idConj,$nCon["modele"]."");
			    			$idT = $this->dbVer->ajouterVerbe($this->id,$idConj,$nCon["eli"],$nCon["pref"]);
			    			$this->dbConVer->ajouterConceptVerbe($idC,$idT);
			    			break;				    		
			    		case 'adj':
			    			$idT = $this->dbAdj->ajouterAdjectif($this->id,$nCon["eli"],$nCon["pref"],$nCon["ms"],$nCon["fs"],$nCon["mp"],$nCon["fp"]);
			    			$this->dbConAdj->ajouterConceptAdjectif($idC,$idT);
			    			break;				    		
			    		case 'syn':
			    			$idT = $this->dbSyn->ajouterSyntagme($this->id,-1,-1,$nCon);
			    			$this->dbConSyn->ajouterConceptSyntagme($idC,$idT);
			    			break;				    		
			    		case 'sub':
			    			$idT = $this->dbSub->ajouterSubstantif($this->id,$nCon["eli"],$nCon["pref"],$nCon["s"],$nCon["p"],$nCon["genre"]);
			    			$this->dbConSub->ajouterConceptSubstantif($idC,$idT);
			    			break;				    		
			    		case 'gen':
			    			$idT = $this->dbGen->ajouterGenerateur($this->id,$nCon[0]);
			    			$this->dbConGen->ajouterConceptGenerateur($idC,$idT);
			    			break;				    		
			    	}
				}
				break;
			case 'pronom_complement':
				if($n['lib']){
					$num = $n['num']."";
					$lib = $n['lib']."";
					$lib_eli = $n['lib_eli']."";
					$this->dbPro->ajouterPronom($this->id,$num,$lib,$lib_eli,"complement");
				}
				break;
			case 'pronom_sujet':
				if($n['lib']){
					$num = $n['num']."";
					$lib = $n['lib']."";
					$lib_eli = $n['lib_eli']."";
					$this->dbPro->ajouterPronom($this->id,$num,$lib,$lib_eli,"sujet");
				}
				break;
			case 'negation':
				if($n['lib']){
					$num = $n['num']."";
					$lib = $n['lib']."";
					$this->dbNeg->ajouterNegation($this->id,$num,$lib);
				}
				break;
		}		
		
	}
	
	public function TraiteAction($chaine, $action){

		switch ($action['type']) {
			case 'explode':
				//pour améliorer le explode des saut de ligne
				$c = str_replace("\\r","- -",$action['char']);
				//met la chaine dans un tableau
				$arr = explode($c, $chaine);
				//boucle sur les fragments de chaine optenus
				for ($i = 0 ; $i < count($arr); $i++) {
					//foreach ($arr as $frag) {
					$frag = $arr[$i];
					//vérifie si le traitement comporte des sous actions
					if(count($action->children())==0){
						$this->AjoutFrag($frag, $action);
					}else{
						//boucle sur les sous actions de l'action
						foreach ($action->children() as $act) {
							//vérifie si l'action est liée à la fin du tableau
							switch ($act['type']) {
								case "VerifFin":
									if($i==(count($arr)-1)){
										$this->TraiteAction($frag, $act->action);					
									}else{
										$this->TraiteAction($frag, $act->NoVerifAction);					
									}
									break;								
								case "VerifDeb":
									if($i==0){
										$this->TraiteAction($frag, $act->action);					
									}else{
										$this->TraiteAction($frag, $act->NoVerifAction);					
									}
									break;								
								default:
									$this->TraiteAction($frag, $act);								
									break;
							}							
						}
					}
				}
				break;				
			case 'VerifSubstr':
				$c = substr($chaine, $action['deb'],$action['length']);
				if($c==$action['val']){
					//on exécute l'action suivante
					$this->TraiteAction($chaine, $action->action);					
				}else{
					$this->TraiteAction($chaine, $action->NoVerifAction);					
				}
				break;

			case 'SetNoeud':
				$this->SetNoeud($chaine, $action);
				break;
								
			case 'SetConjugaison':
				$this->SetConjugaison($chaine, $action);
				break;
				
			case 'SetModele':
				$this->SetModele($chaine, $action);
				break;
			
			case 'SetConcept':
				$this->SetConcept($chaine, $action);
				break;
		
			case 'SetTerminaison':
				$this->SetTerminaison($chaine, $action);
				break;
			
			case 'SetTerminaisonFin':
				$this->SetTerminaison($chaine, $action, true);
				break;
			case 'SetPronom':	
				$this->SetPronom($chaine, $action);
				break;
			case 'SetNegation':	
				$this->SetNegation($chaine, $action);
				break;
		}

	}

	public function SetNegation($chaine, $action){
		
		$xFrag = $this->xml->createElement("negation","");	
		
		$c = $action['char']."";
		$arr = explode($c, $chaine);
		$arrAtrib = explode($c, $action['attributs']);
		//boucle sur les fragments de chaine optenus
		for ($i = 0 ; $i < count($arr); $i++) {
			//calcul les attributs
			$xAtt = $this->xml->createAttribute($arrAtrib[$i]);
			$nText = $this->xml->createTextNode($arr[$i]);
			$xAtt->appendChild($nText); 				
			$xFrag->appendChild($xAtt);
			
		}
		//ajoute le modèle à la racine		
		$this->xmlRoot->appendChild($xFrag);
		
	}
	
	public function SetPronom($chaine, $action){
		
		//création du noeud verbe
		if($this->type=='pronoms_complement'){
			$xFrag = $this->xml->createElement("pronom_complement","");	
		}
		if($this->type=='pronoms_sujet'){
			$xFrag = $this->xml->createElement("pronom_sujet","");	
		}
		
		$c = $action['char']."";
		$arr = explode($c, $chaine);
		$arrAtrib = explode($c, $action['attributs']);
		//boucle sur les fragments de chaine optenus
		for ($i = 0 ; $i < count($arrAtrib); $i++) {
			//calcul les attributs
			$xAtt = $this->xml->createAttribute($arrAtrib[$i]);
			$nText = $this->xml->createTextNode($arr[$i]);
			$xAtt->appendChild($nText); 				
			$xFrag->appendChild($xAtt);
			
		}
		//ajoute le modèle à la racine		
		$this->xmlRoot->appendChild($xFrag);
		
	}
	
	public function SetConjugaison($chaine, $action){
		
		//calcul les attributs
		$c = $action['char']."";
		$n = strpos($chaine, $c);
		$num = substr($chaine,1,strpos($chaine, $c)-1);
		$verbe = substr($chaine,strpos($chaine, $c)+1,-1);
		//création du noeud verbe
		$xFrag = $this->xml->createElement('conjugaison',$chaine);
		$xAtt = $this->xml->createAttribute('num');
		$nText = $this->xml->createTextNode($num);
		$xAtt->appendChild($nText); 				
		$xFrag->appendChild($xAtt);
		$xAtt = $this->xml->createAttribute('modele');
		$nText = $this->xml->createTextNode($verbe);
		$xAtt->appendChild($nText);
		$xFrag->appendChild($xAtt);
		//ajoute les terminaisons au verbe
		$xFrag->appendChild($this->xmlTmp);
		//met à jour le xml temporaire
		$this->xmlTmp = $xFrag;
		
	}
	
	public function SetTerminaison($chaine, $action, $fin=false){

		if(!$this->xmlTmp){
			//initialise le noeud
			$this->xmlTmp = $this->xml->createElement("terminaisons");			
		}
		//calcul le noeud
		$n = $this->xml->createElement("terminaison",$chaine);		

		//vérifie si le noeud conjugaison est créé
		$conj = $this->xmlTmp->getElementsByTagName('terminaisons');
		if($conj->length == 0){		
			//ajoute le noeud à la racine du xml temporaire
			$this->xmlTmp->appendChild($n);
		}else{
			//ajoute le noeud à terminaisons
			$conj->item(0)->appendChild($n);
		}

		//on vérifie si c'est la dernière conjugaison
		if($fin){
			//ajoute le modèle à la racine		
			$this->xmlRoot->appendChild($this->xmlTmp);
			//réiniitialise l'xml temporaire
			$this->xmlTmp = false;			
		}
				
	}	
	
	public function SetNoeud($chaine, $action){

		if(!$this->xmlTmp){
			//initialise le noeud
			$this->xmlTmp = $this->xml->createElement($this->Xtype);			
		}
		//calcul le noeud
		if($this->type=="concepts") {
			$this->SetTypeConcept($this->xmlTmp->getAttribute('type'), $chaine);			
		}else{
			$n = $this->xml->createElement(substr($this->Xtype,0,-1),$chaine);		
			//ajoute le noeud
			$this->xmlTmp->appendChild($n);		
		}
				
	}

	public function SetTypeConcept($type, $chaine){
				
		if(strpos($chaine,"[")===false){
			switch ($type) {
				case "a":
					//création d'un adjectif
					$this->SetAdjectif($chaine);		
					break;
				case "m":
					//création d'un substantif
					$this->SetSubstantif($chaine);		
					break;
				case "s":
					//création d'un substantif
					$this->SetSyntagme($chaine);		
					break;
				case "v":
					//création d'un substantif
					$this->SetVerbe($chaine);		
					break;
				default:
					//vérifie si la	 chaine est un substantif
					if(substr($chaine,1,1)=="(" && substr($chaine,3,1)==")" && strpos($chaine,"|")){
						$this->SetSubstantif($chaine);
					//vérifie si la	 chaine est un adjectif
					}elseif(substr($chaine,1,1)==")" && strpos($chaine,"|")){
						$this->SetAdjectif($chaine);
					}else{
						//création d'un générateur de concept
						$this->SetGen($chaine);
					}		
				break;
			}
		}else{
			//création d'un générateur de concept
			$this->SetGen($chaine);		
		}
						
	}
	
	
	public function SetVerbe($chaine){
		

		//calcul les attributs
		$elision = substr($chaine,0,1);		
		$arr = explode("|",substr($chaine,2));
		$prefix =  $arr[0];
		$modele = $arr[1];

		//création du noeud verbe
		$xFrag = $this->xml->createElement('verbe');
    
		//création des attributs
		$xAtt = $this->xml->createAttribute('eli');
		$nText = $this->xml->createTextNode($elision);
		$xAtt->appendChild($nText); 				
		$xFrag->appendChild($xAtt);
		
		$xAtt = $this->xml->createAttribute('pref');
		$nText = $this->xml->createTextNode($prefix);
		$xAtt->appendChild($nText); 				
		$xFrag->appendChild($xAtt);
		
		$xAtt = $this->xml->createAttribute('modele');
		$nText = $this->xml->createTextNode($modele);
		$xAtt->appendChild($nText);
		$xFrag->appendChild($xAtt);

		$this->xmlTmp->appendChild($xFrag);		
		
	}
	
	
	public function SetSubstantif($chaine){
		

		//calcul les attributs
		$elision = substr($chaine,0,1);
		$genre = substr($chaine,2,1);
		$c = substr($chaine,4);
		$arr = explode("|",$c);
		$prefix =  $arr[0];
		
		if(count($arr)==1){
			$arr[1] = "";
		}
		
		$accords = explode(" ",$arr[1]);
		if(count($accords)==2){
			$s = $accords[0];
			$p = $accords[1];
		}else{
			$s = "";
			if($accords[0]==="0"){
				$p = "";
			}else{
				$p = $accords[0];				
			}
		}
		
		//création du noeud substantif
		$xFrag = $this->xml->createElement('sub');
    
		//création des attributs
		$xAtt = $this->xml->createAttribute('eli');
		$nText = $this->xml->createTextNode($elision);
		$xAtt->appendChild($nText); 				
		$xFrag->appendChild($xAtt);
		
		$xAtt = $this->xml->createAttribute('genre');
		$nText = $this->xml->createTextNode($genre);
		$xAtt->appendChild($nText); 				
		$xFrag->appendChild($xAtt);

		$xAtt = $this->xml->createAttribute('pref');
		$nText = $this->xml->createTextNode($prefix);
		$xAtt->appendChild($nText); 				
		$xFrag->appendChild($xAtt);
		
		$xAtt = $this->xml->createAttribute('s');
		$nText = $this->xml->createTextNode($s);
		$xAtt->appendChild($nText);
		$xFrag->appendChild($xAtt);

		$xAtt = $this->xml->createAttribute('p');
		$nText = $this->xml->createTextNode($p);
		$xAtt->appendChild($nText);
		$xFrag->appendChild($xAtt);

		$this->xmlTmp->appendChild($xFrag);		
		
	}
	
	public function SetAdjectif($chaine){
		

		//calcul les attributs
		$elision = substr($chaine,0,1);
		$c = substr($chaine,strpos($chaine, ")")+1);
		$arr = explode("|",$c);
		$prefix =  $arr[0];
		$accords = explode(" ",$arr[1]);
		$m_s = $accords[0];
		$f_s = $accords[1];
		$m_p = $accords[2];
		$f_p = $accords[3];
		
		//création du noeud adjectif
		$xFrag = $this->xml->createElement('adj');
		
		//création des attributs
		$xAtt = $this->xml->createAttribute('eli');
		$nText = $this->xml->createTextNode($elision);
		$xAtt->appendChild($nText); 				
		$xFrag->appendChild($xAtt);
		
		$xAtt = $this->xml->createAttribute('pref');
		$nText = $this->xml->createTextNode($prefix);
		$xAtt->appendChild($nText);
		$xFrag->appendChild($xAtt);

		$xAtt = $this->xml->createAttribute('ms');
		$nText = $this->xml->createTextNode($m_s);
		$xAtt->appendChild($nText);
		$xFrag->appendChild($xAtt);

		$xAtt = $this->xml->createAttribute('fs');
		$nText = $this->xml->createTextNode($f_s);
		$xAtt->appendChild($nText);
		$xFrag->appendChild($xAtt);

		$xAtt = $this->xml->createAttribute('mp');
		$nText = $this->xml->createTextNode($m_p);
		$xAtt->appendChild($nText);
		$xFrag->appendChild($xAtt);

		$xAtt = $this->xml->createAttribute('fp');
		$nText = $this->xml->createTextNode($f_p);
		$xAtt->appendChild($nText);
		$xFrag->appendChild($xAtt);
		
		$this->xmlTmp->appendChild($xFrag);		
				
	}
	
	public function SetSyntagme($chaine){
		
		
		//création du noeud adjectif
		$xFrag = $this->xml->createElement('syn',$chaine);
		
		$this->xmlTmp->appendChild($xFrag);		
				
	}
	
	public function SetGen($chaine){
		
		$xFrag = $this->xml->createElement('gen');
		$cm = $this->xml->createTextNode($chaine);
		$ct = $this->xml->createCDATASection($chaine);
		$xFrag->appendChild($ct);

		$this->xmlTmp->appendChild($xFrag);		
	}
		
	
	public function SetModele($chaine, $action){
		
		if(!$this->xmlTmp){
			//initialise le noeud
			$this->xmlTmp = $this->xml->createElement($this->Xtype);			
		}
		//création de l'attribut 
		$xAtt = $this->xml->createAttribute('num');
		$nText = $this->xml->createTextNode($chaine);
		$xAtt->appendChild($nText); 				
		$this->xmlTmp->appendChild($xAtt);
		//ajoute le modèle à la racine		
		$this->xmlRoot->appendChild($this->xmlTmp);
		//réiniitialise l'xml temporaire
		$this->xmlTmp = false;
		
	}
		
	public function SetConcept($chaine, $action){
		
		//initialise l'xml temporaire
		$this->xmlTmp = $this->xml->createElement(substr($this->Xtype,0,-1));			
		
		//création de l'attribut type
		$xAtt = $this->xml->createAttribute('type');
		$arrType = $this->GetTypeConcept($chaine);
		$nText = $this->xml->createTextNode($arrType["lib"]);
		$xAtt->appendChild($nText); 				
		$this->xmlTmp->appendChild($xAtt);
		
		//création de l'attribut lib
		$xAtt = $this->xml->createAttribute('lib');
		$lib = substr($chaine,$arrType["fin"]);
		$nText = $this->xml->createTextNode($lib);
		$xAtt->appendChild($nText); 				
		$this->xmlTmp->appendChild($xAtt);
		
		//ajoute le modèle à la racine		
		$this->xmlRoot->appendChild($this->xmlTmp);
		
	}
	
	public function GetTypeConcept($chaine){
		
		$type="";
		$fin=0;
		
		if(strpos($chaine, "_")){
			$type = substr($chaine,0,strpos($chaine, "_"));
			$fin = strpos($chaine, "_")+1;
		}else{
			if(substr($chaine,0,5)=="carac"){
				$type = "carac";
				$fin = 5;
			}
			if(strpos($chaine, "-")){
				$type = substr($chaine,0,strpos($chaine, "-"));
				$fin = strpos($chaine, "-")+1;
			}
			if(strpos($chaine, ".")){
				$type = substr($chaine,0,strpos($chaine, "."));
				$fin = strpos($chaine, "-")+1;
			}
		}		
		return array("lib"=>$type, "fin"=>$fin);
	}
	
	public function InitXmlDico(){
		//initialisation du fichier xml
		$this->xml = new DOMDocument('1.0', 'UTF-8');
		$this->xml->appendChild(new DOMComment('dictionnaire '.$this->type.' pour le générateur de texte'));
		$this->xmlRoot = $this->xml->appendChild(new DOMElement('dico'));
		$xAtt = $this->xml->createAttribute('type');
		$this->xmlRoot->appendChild($xAtt);
		$nText = $this->xml->createTextNode($this->type);
		$xAtt->appendChild($nText); 				
		$xAtt = $this->xml->createAttribute('urlS');
		$this->xmlRoot->appendChild($xAtt);
		$nText = $this->xml->createTextNode($this->urlS);
		$xAtt->appendChild($nText); 				
	}
	
	public function AjoutFrag($frag, $action){
		//création du noeud xml
		$xFrag = $this->xml->createElement('frag',$frag);
		$dom_sxe = dom_import_simplexml($action);
		$dom_sxe = $this->xml->importNode($dom_sxe, true);
		$this->xmlRoot->appendChild($dom_sxe);		
		$this->xmlRoot->appendChild($xFrag);		
	}


	function MacRoman_to_utf8($str, $break_ligatures='none')
	{
		// $break_ligatures : 'none' | 'fifl' | 'all'
		// 'none' : don't break any MacRoman ligatures, transform them into their utf-8 counterparts
		// 'fifl' : break only fi ("\xDE" => "fi") and fl ("\xDF"=>"fl")
		// 'all' : break fi, fl and also AE ("\xAE"=>"AE"), ae ("\xBE"=>"ae"), OE ("\xCE"=>"OE") and oe ("\xCF"=>"oe")
	
		if($break_ligatures == 'fifl')
		{
			$str = strtr($str, array("\xDE"=>"fi", "\xDF"=>"fl"));
		}
		
		if($break_ligatures == 'all')
		{
			$str = strtr($str, array("\xDE"=>"fi", "\xDF"=>"fl", "\xAE"=>"AE", "\xBE"=>"ae", "\xCE"=>"OE", "\xCF"=>"oe"));
		}
	
		$str = strtr($str, array("\x7F"=>"\x20", "\x80"=>"\xC3\x84", "\x81"=>"\xC3\x85",
		"\x82"=>"\xC3\x87", "\x83"=>"\xC3\x89", "\x84"=>"\xC3\x91", "\x85"=>"\xC3\x96",
		"\x86"=>"\xC3\x9C", "\x87"=>"\xC3\xA1", "\x88"=>"\xC3\xA0", "\x89"=>"\xC3\xA2",
		"\x8A"=>"\xC3\xA4", "\x8B"=>"\xC3\xA3", "\x8C"=>"\xC3\xA5", "\x8D"=>"\xC3\xA7",
		"\x8E"=>"\xC3\xA9", "\x8F"=>"\xC3\xA8", "\x90"=>"\xC3\xAA", "\x91"=>"\xC3\xAB",
		"\x92"=>"\xC3\xAD", "\x93"=>"\xC3\xAC", "\x94"=>"\xC3\xAE", "\x95"=>"\xC3\xAF",
		"\x96"=>"\xC3\xB1", "\x97"=>"\xC3\xB3", "\x98"=>"\xC3\xB2", "\x99"=>"\xC3\xB4",
		"\x9A"=>"\xC3\xB6", "\x9B"=>"\xC3\xB5", "\x9C"=>"\xC3\xBA", "\x9D"=>"\xC3\xB9",
		"\x9E"=>"\xC3\xBB", "\x9F"=>"\xC3\xBC", "\xA0"=>"\xE2\x80\xA0", "\xA1"=>"\xC2\xB0",
		"\xA2"=>"\xC2\xA2", "\xA3"=>"\xC2\xA3", "\xA4"=>"\xC2\xA7", "\xA5"=>"\xE2\x80\xA2",
		"\xA6"=>"\xC2\xB6", "\xA7"=>"\xC3\x9F", "\xA8"=>"\xC2\xAE", "\xA9"=>"\xC2\xA9",
		"\xAA"=>"\xE2\x84\xA2", "\xAB"=>"\xC2\xB4", "\xAC"=>"\xC2\xA8", "\xAD"=>"\xE2\x89\xA0",
		"\xAE"=>"\xC3\x86", "\xAF"=>"\xC3\x98", "\xB0"=>"\xE2\x88\x9E", "\xB1"=>"\xC2\xB1",
		"\xB2"=>"\xE2\x89\xA4", "\xB3"=>"\xE2\x89\xA5", "\xB4"=>"\xC2\xA5", "\xB5"=>"\xC2\xB5",
		"\xB6"=>"\xE2\x88\x82", "\xB7"=>"\xE2\x88\x91", "\xB8"=>"\xE2\x88\x8F", "\xB9"=>"\xCF\x80",
		"\xBA"=>"\xE2\x88\xAB", "\xBB"=>"\xC2\xAA", "\xBC"=>"\xC2\xBA", "\xBD"=>"\xCE\xA9",
		"\xBE"=>"\xE6", "\xBF"=>"\xC3\xB8", "\xC0"=>"\xC2\xBF", "\xC1"=>"\xC2\xA1",
		"\xC2"=>"\xC2\xAC", "\xC3"=>"\xE2\x88\x9A", "\xC4"=>"\xC6\x92", "\xC5"=>"\xE2\x89\x88",
		"\xC6"=>"\xE2\x88\x86", "\xC7"=>"\xC2\xAB", "\xC8"=>"\xC2\xBB", "\xC9"=>"\xE2\x80\xA6",
		"\xCA"=>"\xC2\xA0", "\xCB"=>"\xC3\x80", "\xCC"=>"\xC3\x83", "\xCD"=>"\xC3\x95",
		"\xCE"=>"\xC5\x92", "\xCF"=>"\xC5\x93", "\xD0"=>"\xE2\x80\x93", "\xD1"=>"\xE2\x80\x94",
		"\xD2"=>"\xE2\x80\x9C", "\xD3"=>"\xE2\x80\x9D", "\xD4"=>"\xE2\x80\x98", "\xD5"=>"\xE2\x80\x99",
		"\xD6"=>"\xC3\xB7", "\xD7"=>"\xE2\x97\x8A", "\xD8"=>"\xC3\xBF", "\xD9"=>"\xC5\xB8",
		"\xDA"=>"\xE2\x81\x84", "\xDB"=>"\xE2\x82\xAC", "\xDC"=>"\xE2\x80\xB9", "\xDD"=>"\xE2\x80\xBA",
		"\xDE"=>"\xEF\xAC\x81", "\xDF"=>"\xEF\xAC\x82", "\xE0"=>"\xE2\x80\xA1", "\xE1"=>"\xC2\xB7",
		"\xE2"=>"\xE2\x80\x9A", "\xE3"=>"\xE2\x80\x9E", "\xE4"=>"\xE2\x80\xB0", "\xE5"=>"\xC3\x82",
		"\xE6"=>"\xC3\x8A", "\xE7"=>"\xC3\x81", "\xE8"=>"\xC3\x8B", "\xE9"=>"\xC3\x88",
		"\xEA"=>"\xC3\x8D", "\xEB"=>"\xC3\x8E", "\xEC"=>"\xC3\x8F", "\xED"=>"\xC3\x8C",
		"\xEE"=>"\xC3\x93", "\xEF"=>"\xC3\x94", "\xF0"=>"\xEF\xA3\xBF", "\xF1"=>"\xC3\x92",
		"\xF2"=>"\xC3\x9A", "\xF3"=>"\xC3\x9B", "\xF4"=>"\xC3\x99", "\xF5"=>"\xC4\xB1",
		"\xF6"=>"\xCB\x86", "\xF7"=>"\xCB\x9C", "\xF8"=>"\xC2\xAF", "\xF9"=>"\xCB\x98",
		"\xFA"=>"\xCB\x99", "\xFB"=>"\xCB\x9A", "\xFC"=>"\xC2\xB8", "\xFD"=>"\xCB\x9D",
		"\xFE"=>"\xCB\x9B", "\xFF"=>"\xCB\x87", "\x00"=>"\x20", "\x01"=>"\x20",
		"\x02"=>"\x20", "\x03"=>"\x20", "\x04"=>"\x20", "\x05"=>"\x20",
		"\x06"=>"\x20", "\x07"=>"\x20", "\x08"=>"\x20", "\x0B"=>"\x20",
		"\x0C"=>"\x20", "\x0E"=>"\x20", "\x0F"=>"\x20", "\x10"=>"\x20",
		"\x11"=>"\x20", "\x12"=>"\x20", "\x13"=>"\x20", "\x14"=>"\x20",
		"\x15"=>"\x20", "\x16"=>"\x20", "\x17"=>"\x20", "\x18"=>"\x20",
		"\x19"=>"\x20", "\x1A"=>"\x20", "\x1B"=>"\x20", "\x1C"=>"\x20",
		"\1D"=>"\x20", "\x1E"=>"\x20", "\x1F"=>"\x20", "\xF0"=>""));
		
		return $str;
	}
	
	function RemoveAccents($str, $charset='utf-8')
	{
	    $str = htmlentities($str, ENT_NOQUOTES, $charset);
	    
	    $str = preg_replace('#\&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml)\;#', '\1', $str);
	    $str = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $str); // pour les ligatures e.g. '&oelig;'
	    $str = preg_replace('#\&[^;]+\;#', '', $str); // supprime les autres caractères
	    
	    return $str;
	}
	
	
}
?>
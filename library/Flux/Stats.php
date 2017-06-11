<?php
/**
 * Flux_Stats
 * Classe qui gère les flux statistiques
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Stats  extends Flux_Site{

	
 	public function __construct($idBase=false)
    {
	    	parent::__construct($idBase);
    	
    }
	
	/**
     * Mise à jour des calculs statistiques 
     * 
     * @param varchar $code
     * 
     */
    function update($code) {
        //vérifie le code de sécurité d'administration
    	if($code != CODE_ADMIN) return "vous n'êtes pas autorisé a éxécuter cette action !";
    	
    	$message = "";
    	
    	//excution des requêtes pour la relation entre utilisateur et tag
		$sql = "TRUNCATE TABLE  flux_utitag";
    	$stmt = $this->db->query($sql);
		$message .= $sql."<br/>";
		$message .= $stmt->rowCount()." ligne(s) affectée(s)<br/>"; 
    	
		$sql = "INSERT INTO flux_utitag (tag_id, uti_id, poids)
			SELECT utd.tag_id, uti_id, count(distinct utd.doc_id) nb
			FROM flux_utitagdoc utd
			GROUP BY utd.tag_id, uti_id";
    	$stmt = $this->db->query($sql);
		$message .= $sql."<br/>"; 
		$message .= $stmt->rowCount()." ligne(s) affectée(s)<br/>"; 
		
    	//excution des requêtes pour la relation entre utilisateur et document
		$sql = "TRUNCATE TABLE  flux_utidoc";
    	$stmt = $this->db->query($sql);
		$message .= $sql."<br/>"; 
		$message .= $stmt->rowCount()." ligne(s) affectée(s)<br/>"; 
		
		$sql = "INSERT INTO flux_utidoc (doc_id, uti_id, maj, poids)
			SELECT doc_id, uti_id, maj, count(distinct tag_id) nb
			FROM flux_utitagdoc utd
			GROUP BY doc_id, uti_id";
    	$stmt = $this->db->query($sql);
		$message .= $sql."<br/>"; 
		$message .= $stmt->rowCount()." ligne(s) affectée(s)<br/>"; 

    	//excution des requêtes pour la relation entre tag et document
		$sql = "TRUNCATE TABLE flux_tagdoc";
    	$stmt = $this->db->query($sql);
		$message .= $sql."<br/>"; 
		$message .= $stmt->rowCount()." ligne(s) affectée(s)<br/>"; 
		$sql = "INSERT INTO flux_tagdoc (tag_id, doc_id, poids)
			SELECT utd.tag_id, utd.doc_id, count(distinct uti_id) nb
			FROM flux_utitagdoc utd
			GROUP BY utd.tag_id, utd.doc_id";
    	$stmt = $this->db->query($sql);
		$message .= $sql."<br/>"; 
		$message .= $stmt->rowCount()." ligne(s) affectée(s)<br/>"; 

		return $message;		
    }
    
	/**
     * Récupère les statistiques d'un network étendu pour un tag 
     *
     * @param string $tag
     * @param string $user
     * 
     * @return array
     */
	function GetTagUserNetwork($tag, $user) {
		
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc();
		
		//vérifie s'il faut mettre à jour les données
		if($this->forceCalcul){
			//récupère les infos de l'utilisateur
			if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti();
			$u = $this->dbU->findByLogin($user["login"]);		
			//recherche les post de l'utilisateur pour le tag
			$f = new Flux_Delicious($user["login"], $user['pwd']);
			$f->forceCalcul = $this->forceCalcul;
			$f->idUser = $u['uti_id'];
			$f->SaveUserPost($user["login"],true,$tag);
			//mise à jour des stats
			$this->update(CODE_ADMIN);
		}
		
		//exécution de la requête
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag();
		$t = $this->dbT->findByCode($tag);
		$arrStat = $this->dbUTD->findStatByTags($t['tag_id']);

		return $arrStat;
		
	}
	
	/**
     * Récupère la typologie des relations entre utilisateur pour un utilisateur  
     *
     * @param string $idUser
     * 
     * @return array
     */
	function GetTypeRelaUser($idUser) {

		//défiition de la requête
		$sql = "SELECT COUNT(*) nb, fan, post, network
			FROM flux_UtiUti
			WHERE uti_id_src = ".$idUser."
			GROUP BY fan, post, network";
    	$stmt = $this->db->query($sql);
    	return $stmt->fetchAll();
		
	}

	
	/**
     * Permutation de tous les élément d'un tableau
     * merci à http://stackoverflow.com/questions/5506888/permutations-all-possible-sets-of-numbers
     * 
     * @param array $items
     * 
     * @return array
     */
	function permute($items, $perms = array( )) {
	    if (empty($items)) {
	        $return = array($perms);
	    }  else {
	        $return = array();
	        for ($i = count($items) - 1; $i >= 0; --$i) {
	             $newitems = $items;
	             $newperms = $perms;
	         list($foo) = array_splice($newitems, $i, 1);
	             array_unshift($newperms, $foo);
	             $return = array_merge($return, $this->permute($newitems, $newperms));
	         }
	    }
	    return $return;
	}


	
	/**
     * Récupère les tags associés à un document et leur poids
     *
     * @param string $uti
     * @param string $url
     * 
     * @return array
     */
	function GetUtiTagDoc($uti, $url) {

		//récupération des identifiants
		//création des tables
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
		$rUti = $this->dbU->findByLogin($uti);
		$rDoc = $this->dbD->findByUrl($url);

		return $this->dbUTD->GetUtiTagDoc($rUti["uti_id"], $rDoc[0]["doc_id"]);
    	
	}
		
	/**
     * Calcul l'historique de l'utilisation d'une liste de tags
     *
     * @param array 	$lstTags
     * @param int 		$tronc
     * @param string 	$dateUnit unité de la date : year, month...
     * @param string 	$q requête full text
     * 
     * @return array
     */
	function GetTagHisto($lstTags, $tronc=-1, $dateUnit="", $q="") {
		
		$c = str_replace("::", "_", __METHOD__); 
		$c .= "_".$this->getParamString($lstTags);
		$c .= "_".$tronc."_".$dateUnit;
		$arr = false;//$this->cache->load($c);
        if(!$arr){
	   		
			$dbT = new Model_DbTable_Flux_Tag($this->db);
			$dbD = new Model_DbTable_Flux_Doc($this->db);
			
			//récupère l'intervale de temps des documents
			$arrDocInt = $dbD->getHistoInterval("",$dateUnit);
			
			//récupère l'historique des tags associés
			$arrTags = $dbT->getTagHisto($lstTags, $tronc, $dateUnit, $q);	    
	
			//création du tableau des dates
			$result = array();
			$oCode = "";
			$nb=0;$maxNb=0;
			$nbTag=count($arrTags);
			$nbInt=count($arrDocInt);
			$t = $this->initArrInt($nbInt);
			for ($i = 0; $i < $nbTag; $i++) {
				$d = $arrTags[$i];
				if($i==0) $oCode=$d['code'];
				if($oCode!=$d['code']){
					$r = array("tag_id"=>$d['tag_id'], "code"=>$d['code'], "score"=>$d['score'], "nb"=>$nb, "temps"=>$t);
					$result[]=$r;
					$t = $this->initArrInt($nbInt);
					$nb=0;
					$oCode=$d['code'];
				}
				$k = $this->getKeyDocInt($arrDocInt, $d['temps']);
				$t[$k] = intval($d['nb']);
				if($maxNb<$t[$k])$maxNb=$t[$k];
				$nb += $t[$k];
			}
			$arr = array("maxTagNb"=>$maxNb, "dateDocInt"=>$arrDocInt, "tagHisto"=>$result);
			$this->cache->save($arr, $c);
        }
			
		return $arr;
	}
	
	function initArrInt($nb){
		//initialise le tableau des intervals
		$t = "";
		for ($j = 0; $j < $nb; $j++) {
			$t[] = 0;
		}
		return $t;
	}
	
	function getKeyDocInt($arr, $temps){
		
		foreach ($arr as $k => $val) {
			if($val['temps']==$temps) return intval($k);
		}
	}
	
	/**
     * Calcul les tags associés à une liste de tag
     *
     * @param array $lstTags
     * @param int $tronc
     * 
     * @return array
     */
	function GetTagAssos($lstTags, $tronc=-1) {
		
		$dbT = new Model_DbTable_Flux_Tag($this->db);
		
		//récupère la liste des tags associés
		$arrTags = $dbT->getTagAssos($lstTags, $tronc);	    

		return $arrTags;
	}

	/**
     * Calcul les tags pour un utilisateur
     * 
     * @return array
     */
	function GetTagUti($idUti) {
		
		$dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
		//récupère la liste des tags associés
		$arrTags = $dbUTD->GetUtiTagDoc($idUti);	    

		return $arrTags;
	}
	
	/**
     * Corrige les tags suivant les paramètres défini dans une fichier csv
     *
     * @param string 	$file
     * 
     * 
     */
	function corrigeTag($file) {

    	$this->bTrace = true; // pour afficher les traces
    	$this->temps_debut = microtime(true);
    	$this->trace("DEBUT ".__METHOD__);
		
		//initialisation des objets
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_flux_tagdoc($this->db);
		if(!$this->dbUT)$this->dbUT = new Model_DbTable_flux_utitag($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
		//récupère le contenu du fichier dans un tableau
		$arr = $this->csvToArray($file);
		$nb = count($arr);
		
		$this->trace("execute les modifications spécifiés");
		for ($i = 1; $i < $nb; $i++) {
			$r =$arr[$i];
			/*			
			if($r[7]){
    			$this->trace("TAG : ".$r[1]." (".$r[0].") à copier vers ".$r[7]);
				$this->copieLienTag($r[0], $r[7]);
				
				//suppression de l'ancien tag
				$this->dbT->remove($r[0]);
				$this->trace("tag supprimé ".$r[1]);
			}
			//		
			if($r[9]){
    			$this->trace("TAG : ".$r[1]." (".$r[0].") à exploser par ".$r[9]);    			
    			$arrExp1 = explode($r[9], $r[1]);
    			foreach ($arrExp1 as $t1) {
    				//vérifie la deuxième couche d'explode
    				if($r[10]){
		    			$this->trace("TAG : ".$t1." à exploser par ".$r[10]);    			
		    			$arrExp2 = explode($r[10], $t1);
		    			foreach ($arrExp2 as $t2) {
							$this->copieLienTag($r[0], null, $t2);
		    			}    					
    				}else{
						$this->copieLienTag($r[0], null, $t1);    					
    				}
    			}
				//suppression de l'ancien tag
				$this->dbT->remove($r[0]);
				$this->trace("tag supprimé ".$r[1]);
			}
			if($r[12]=="oui"){
				//suppression du tag
				$this->dbT->remove($r[0]);
				$this->trace("tag supprimé ".$r[1]);
			}
			*/
			if($r[11]){
				//suppression du tag
				$this->copieLienTag($r[0], null, $r[11]);    					
			}
		}
		
    	$this->trace("FIN ".__METHOD__);
		
	}

	
	/**
     * Corrige les utilisateur suivant les paramètres défini dans une fichier csv
     *
     * @param string 	$file
     * 
     * 
     */
	function corrigeUti($file) {

    	$this->bTrace = true; // pour afficher les traces
    	$this->temps_debut = microtime(true);
    	$this->trace("DEBUT ".__METHOD__);
		
		//initialisation des objets
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbUT)$this->dbUT = new Model_DbTable_flux_utitag($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
		//récupère le contenu du fichier dans un tableau
		$arr = $this->csvToArray($file);
		$nb = count($arr);
		
		$this->trace("execute les modifications spécifiés");
		//uti_id	login	rôle	mail	collaborateurs
		$oLog = "";
		$oId = "";
		for ($i = 1; $i < $nb; $i++) {
			$r =$arr[$i];
			if($oLog!=$r[1]){
				$oLog = trim($r[1]);
				$oId  = $r[0];
				//met à jour l'utilisateur
				$this->dbU->edit($oId, array("login"=>trim($oLog),"role"=>trim($r[2]),"email"=>trim($r[3])));
				$this->trace("met à jour l'utilisateur ".$oLog.'('.$idTagDst.')');				
			}else{
				//copie les liens
				$this->copieLienUti($r[0], $oId);
				$this->trace("utilisateur copié : ".trim($r[1]).'('.$r[0].')');				
				//suppression de l'utilisateur
				$this->dbU->remove($r[0]);
				$this->trace("utilisateur supprimé : ".trim($r[1]).'('.$r[0].')');				
			}
			//copie les collaborateurs
			if($r[4]){
				$arrU = explode(",", $r[4]);
				foreach ($arrU as $u) {
					$this->copieLienUti($oId, null, trim($u));
				}
			}
		}
		
    	$this->trace("FIN ".__METHOD__);
		
	}	
	/**
	 * copieLienUti 
	 * copie les relations d'un tag avec les utilisateurs et les documents
	 *
	 * @param int 		$idUtiSrc
	 * @param int 		$idUtiDst
	 * @param string	$logDst
	 *
	 */
	function copieLienUti($idUtiSrc, $idUtiDst, $logDst=""){

		//initialisation des objets
		if(!$this->dbU)$this->dbU = new Model_DbTable_Flux_Uti($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbUT)$this->dbUT = new Model_DbTable_flux_utitag($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
				
		//récupère les liens du uti ancien
		$arrUD = $this->dbUD->findByUti_id($idUtiSrc);
		$arrUT = $this->dbUT->findByUti_id($idUtiSrc);
		$arrUTD = $this->dbUTD->findByUti_id($idUtiSrc);

		if($logDst){
			//création ou récupération du tag
			$idUtiDst = $this->dbU->ajouter(array("login"=>trim($logDst)));
			$this->trace("nouveau utiliateur ".$logDst.'('.$idUtiDst.')');
		}		
		
		//création des nouveaux liens
		foreach ($arrUD as $ud) {
			$this->dbUD->ajouter(array("uti_id"=>$idUtiDst, "doc_id"=>$ud['doc_id'], "poids"=>$ud['poids']));
		}
		foreach ($arrUT as $ut) {
			$this->dbUT->ajouter(array("tag_id"=>$ut['tag_id'], "uti_id"=>$idUtiDst, "poids"=>$ut['poids']));
		}
		foreach ($arrUTD as $utd) {
			$this->dbUTD->ajouter(array("tag_id"=>$utd['uti_id'], "uti_id"=>$idUtiDst, "doc_id"=>$utd['doc_id'], "poids"=>$ut['poids']));
		}
		
	}
	
	/**
	 * copieLienTag 
	 * copie les relations d'un tag avec les utilisateurs et les documents
	 *
	 * @param int 		$idTagSrc
	 * @param int 		$idTagDst
	 * @param string 	$codeSrc
	 *
	 */
	function copieLienTag($idTagSrc, $idTagDst, $codeDst=""){

		//initialisation des objets
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_flux_tagdoc($this->db);
		if(!$this->dbUT)$this->dbUT = new Model_DbTable_flux_utitag($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
		//récupère les liens du tag ancien
		$arrTD = $this->dbTD->findByTag_id($idTagSrc);
		$arrUT = $this->dbUT->findByTag_id($idTagSrc);
		$arrUTD = $this->dbUTD->findByTagId($idTagSrc);
		
		if($codeDst){
			//création ou récupération du tag
			$idTagDst = $this->dbT->ajouter(array("code"=>trim($codeDst),"desc"=>"domaine"));
			$this->trace("nouveau tag ".trim($codeDst).'('.$idTagDst.')');
		}		
							
		//création des nouveaux liens
		foreach ($arrTD as $td) {
			$this->dbTD->ajouter(array("tag_id"=>$idTagDst, "doc_id"=>$td['doc_id'], "poids"=>$td['poids']));
		}
		foreach ($arrUT as $ut) {
			$this->dbUT->ajouter(array("tag_id"=>$idTagDst, "uti_id"=>$ut['uti_id'], "poids"=>$ut['poids']));
		}
		foreach ($arrUTD as $utd) {
			$this->dbUTD->ajouter(array("tag_id"=>$idTagDst, "uti_id"=>$utd['uti_id'], "doc_id"=>$utd['doc_id'], "poids"=>$ut['poids']));
		}
		
	}
	
	/**
	 * kwTags 
	 * sépare les tags de la base avec un analyseur de mot clef
	 *
	 *
	 */
	function kwTags(){
		
    	$this->bTrace = true; // pour afficher les traces
    	$this->temps_debut = microtime(true);
    	$this->trace("DEBUT ".__METHOD__);
		
		//initialisation des objets
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbUT)$this->dbUT = new Model_DbTable_flux_utitag($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
		//récupère le contenu du fichier dans un tableau
		$arr = $this->dbT->getAll();
		$nb = count($arr);
		
		$this->trace("parcours l'ensemble des $nb tags");
		for ($i = 1; $i < $nb; $i++) {
			$t = $arr[$i];
			$this->trace("tag ".$t['code']);				
			$tags = $this->getKW($t['code']);
			if(count($tags) > 1){
				foreach ($tags as $k=> $v) {
					$this->copieLienTag($t['tag_id'],null,$k);
				}
				//suppression de l'ancien tag
				$this->dbT->remove($t["tag_id"]);
				$this->trace("tag supprimé ".$t['code']);				
			}
		}
		
		
	}
	

	/**
	 * explodeTag 
	 * sépare les tags avec une forme composée de séparateur
	 *
	 * @param string $caract
	 *
	 */
	function explodeTag($caract){
    	$this->bTrace = true; // pour afficher les traces
    	$this->temps_debut = microtime(true);
    	$this->trace("DEBUT ".__METHOD__);
		
		//initialisation des objets
		if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_flux_tagdoc($this->db);
		if(!$this->dbUT)$this->dbUT = new Model_DbTable_flux_utitag($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		
		//récupère les tags avec le caractère
		$arrT = $this->dbT->findByCodeLike($caract);
		foreach ($arrT as $t) {
			//on ne prend en compte que les tag de domaine
			if($t['desc']=="domaine"){
				$this->trace("tag a traiter ".$t['code'].'('.$t['tag_id'].')');
				
				//récupère les liens du tag
				$arrTD = $this->dbTD->findByTagId($t['tag_id']);
				$arrUT = $this->dbUT->findByTag_id($t['tag_id']);
				$arrUTD = $this->dbUTD->findByTagId($t['tag_id']);
				//sépare les tags
				$arr = explode($caract, $t['code']);
				foreach ($arr as $nT) {
					//création ou récupération du tag
					$idT = $this->dbT->ajouter(array("code"=>trim($nT),"desc"=>"domaine"));
					$this->trace("nouveau tag ".trim($nT).'('.$idT.')');
					
					//création des nouveaux liens
					foreach ($arrTD as $td) {
						$this->dbTD->ajouter(array("tag_id"=>$idT, "doc_id"=>$td['doc_id'], "poids"=>$td['poids']));
					}
					foreach ($arrUT as $ut) {
						$this->dbUT->ajouter(array("tag_id"=>$idT, "uti_id"=>$ut['uti_id'], "poids"=>$ut['poids']));
					}
					foreach ($arrUTD as $utd) {
						$this->dbUTD->ajouter(array("tag_id"=>$idT, "uti_id"=>$utd['uti_id'], "doc_id"=>$utd['doc_id'], "poids"=>$ut['poids']));
					}
				}
				//suppression de l'ancien tag
				$this->dbT->remove($t['tag_id']);
				$this->trace("tag supprimé ".$t['code']);
			}
		}
		
    	$this->trace("FIN ".__METHOD__);
		
	}	
		
	/**
     * Calcul le score de pertinence fulltext pour une requête et une description
     *
     * @param string $query
     * @param string $desc
     * 
     * @return array
     */
	function GetTagNbDocFT($query, $desc) {
		
		if($query!="tout"){
			$score = "MATCH (titre, note) AGAINST ('".$query."')";
		}else{
			$score = 1;
		}
		
		//définition de la requête
		$sql = "SELECT t.tag_id, t.code
				, count(DISTINCT utd.doc_id) nbDoc
			 	, SUM(".$score.") AS score
			    FROM flux_tag t
			         INNER JOIN flux_utitagdoc utd ON utd.tag_id = t.tag_id
			         INNER JOIN flux_doc d ON d.doc_id = utd.doc_id 
			   WHERE t.desc = '".$desc."' 
			    AND ".$score."
			GROUP BY t.tag_id";
    	$stmt = $this->db->query($sql);
    	return $stmt->fetchAll();
		
	}
	
	/**
     * Calcul le nombre de document par tag et pour une description
     *
     * @param string $tags
     * @param string $desc
     * 
     * @return array
     */
	function GetTagNbDoc($tags="", $desc="") {
		
		
		//construction de la condition des tags
		$where = " ";
		if($tags){
			foreach ($tags as $tag) {
				$where .= " tD.code = '".$tag."' OR ";
			}
			$where = substr($where, 0, -3);
			$where = ' AND ('.$where.') '; 
		}
		
		//définition de la requête
		$sql = "SELECT t.tag_id, t.code
				, count(DISTINCT utd.doc_id) nbDoc
			 	, SUM(utd.poids) AS score
			    FROM flux_tag t
			         INNER JOIN flux_utitagdoc utd ON utd.tag_id = t.tag_id
			         INNER JOIN flux_doc d ON d.doc_id = utd.doc_id 
					INNER JOIN flux_utitagdoc utdD ON utdD.doc_id = d.doc_id
					INNER JOIN flux_tag tD ON tD.tag_id = utdD.tag_id
			   WHERE t.desc = '".$desc."' 
			   ".$where."
			GROUP BY t.tag_id";
		
		$stmt = $this->db->query($sql);
    	return $stmt->fetchAll();
		
	}
	
	
	/**
     * Calcul la matrice des tags associés à une liste de tag
     *
     * @param array $lstTags
     * @param int $tronc
     * 
     * @return array
     */
	function GetMatriceTagAssos($lstTags, $tronc=-1) {
		
		$dbT = new Model_DbTable_Flux_Tag($this->db);
		
		//récupère la liste des tags associés
		$arrTags = $dbT->getTagAssos($lstTags, $tronc);	    
		//récupère les tags associés aux documents
		$arrLiens = $dbT->getTagAssosByDoc($lstTags, $tronc);
		//initialisation des colonnes de la matrice
		$nbColo = count($arrTags);
		$colo = array();
		for ($j = 0; $j < $nbColo; $j++) {
			$colo[]=0;
		}
		//initialisation de la matrice
		$matrice = array();
		$keyTag = array();
		$i = 0;
		foreach ($arrTags as $tag) {
			$matrice[] = $colo;
			$keyTag[$tag["tag_id"]]['num']=$i;
			$keyTag[$tag["tag_id"]]['nbRela']=0;
			$keyTag[$tag["tag_id"]]['nbCoo']=0;
			$i++;			
		}
		//valorisation de la matrice
		foreach ($arrLiens as $l) {
			//récupère les tags lié un document
			$tags = explode(",", $l['tags']);
			foreach ($tags as $idSrc) {
				//récupère la place du tag
				$pSrc = $keyTag[$idSrc]['num'];
				//met à jour la valeur de ce tag pour chaque tag lié et réciproquement
				foreach ($tags as $idDst) {
					if($idSrc!=$idDst){
						$pDst = $keyTag[$idDst]['num'];
						$matrice[$pSrc][$pDst] ++;
						//vérifie le nombre maximum de co-occurence
						if($matrice[$pSrc][$pDst] > $keyTag[$idSrc]['nbCoo']) $keyTag[$idSrc]['nbCoo']=$matrice[$pSrc][$pDst];
						//incrémente la valeur le nb de Relation
						$keyTag[$idDst]['nbRela']++;
					}
				}
			}
		}
		//mets à jour le nombre de tag avec les calculs de relation
		foreach ($keyTag as $kt) {
			$arrTags[$kt['num']]['nbRela']=$kt['nbRela'];			
			$arrTags[$kt['num']]['nbCoo']=$kt['nbCoo'];			
		}
		
		$result = array("tags"=>$arrTags, "matrice"=>$matrice);
		return $result;	    
	}	
	
	/*
	//vérifier pourquoi certaine url ne sont utilisée qu'une seule fois
SELECT count(*) nb, ud.doc_id, d.url
FROM flux_UtiDoc ud
INNER JOIN flux_Doc d ON d.doc_id = ud.doc_id
GROUP BY ud.doc_id
HAVING nb =1
ORDER BY nb DESC
	*/	
/*
 * 	//récupère les données d'une url
 SELECT d.doc_id, d.url, d.titre, u.login, t.code  
FROM `flux_Doc` d 
INNER JOIN flux_UtiDoc ud ON ud.doc_id = d.doc_id
INNER JOIN flux_Uti u ON u.uti_id = ud.uti_id
INNER JOIN flux_TagDoc td ON td.doc_id = d.doc_id
INNER JOIN flux_Tag t ON t.tag_id = td.tag_id 
WHERE `url` LIKE '%http://opencrs.com/%'
*/

	
	/**
	 * Calcul des dates suivant un interval
	 *
	 * @param array	$data
	 * @param string $sqlFormatDate
	 * @param string $sqlFormatDate
	 *
	 * @return array
	 */
	function GetDatesInterval($data, $sqlFormatDate="") {
	
	
		//récupère les extrémité des dates
		$minDate = date("r");
		$maxDate = 0;
		foreach ($data as $d) {
			if($minDate>$d['MinDate'])$minDate=$d['MinDate'];
			if($maxDate<$d['MaxDate'])$maxDate=$d['MaxDate'];
		}
		$this->trace(date("r", $minDate)." -> ".date("r", $maxDate));
		//calcul le tableau des dates
		switch ($sqlFormatDate) {
			case '%Y-%m':
				$interval = new DateInterval('P1M');
				$phpFormatDate = 'Y-m';
				break;
			case '%Y':
				$interval = new DateInterval('P1Y');
				$phpFormatDate = 'Y';
				break;
		
		}
		//
		$curDate = new DateTime();
		$curDate->setTimestamp($minDate);
		$mDate = new DateTime();
		$mDate->setTimestamp($maxDate);
		$this->trace($curDate->format('Y-m-d'));
		$arrDate = array();
		while ($curDate<$mDate) {
			$arrDate[]=$curDate->format($phpFormatDate);
			$curDate->add($interval);
		}
		$this->trace("le tableau des dates",$arrDate);
		return $arrDate;
	}
	
	/**
	 * Calcul un tableau de date avec les mêmes interval 
	 * Utile pour les diagramme stream ou chaque date doit être renseignée même à vide
	 * Les data doivent avoir les champs :
	 * MinDate, MaxDate, key, type, desc, temps, score, value
	 *
	 * @param array	$data
	 * @param string $sqlFormatDate
	 *
	 * @return array
	 */
	function getDataForStream($data, $sqlFormatDate){
		//récupère les extrémité des dates
		$minDate = date("r");
		$maxDate = 0;
		foreach ($data as $d) {
			if($minDate>$d['MinDate'])$minDate=$d['MinDate'];
			if($maxDate<$d['MaxDate'])$maxDate=$d['MaxDate'];
		}
		$this->trace(date("r", $minDate)." -> ".date("r", $maxDate));
		//calcul le tableau des dates
		switch ($sqlFormatDate) {
			case '%Y-%m':
				$interval = new DateInterval('P1M');
				$phpFormatDate = 'Y-m';
				break;
			case '%Y':
				$interval = new DateInterval('P1Y');
				$phpFormatDate = 'Y';
				break;
			case '%Y-%m-%d':
				$interval = new DateInterval('P1D');
				$phpFormatDate = 'Y-m-d';
				break;
		
		}
		//gestion des temps Unix ou chaîne
		if(!is_numeric($minDate)){
			$curDate = new DateTime($minDate);
			$mDate = new DateTime($maxDate);
		}else{
			$curDate = new DateTime();
			$curDate->setTimestamp($minDate);
			$mDate = new DateTime();
			$mDate->setTimestamp($maxDate);					
		}
		$this->trace($curDate->format('Y-m-d'));
		$arrDate = array();
		while ($curDate->format($phpFormatDate) <= $mDate->format($phpFormatDate)) {
			$arrDate[]=$curDate->format($phpFormatDate);
			$curDate->add($interval);
		}
		$this->trace("le tableau des dates",$arrDate);
		//ajoute les valeurs vides pour chaque éléments
		$oTag = $data[0]['key'];
		//définir une valeur par défaut pour la consistance du graph
		$defVal = 0.01;
		$j=0; $i=0; $nbDate = count($arrDate); $nbData = count($data);
		$nData;
		//foreach ($data as $d) {
		for ($z = 0; $z < $nbData; $z++) {
			$d = $data[$z];
			if($d['type']=="voronoi"){
				$this->trace('temps '.$z.' : '.$i.' / '.$j,$d);
			}
			$this->trace('temps '.$z.' : '.$i.' / '.$j,$d);
			//on vérifie si on passe à un nouveau type
			if($oTag!=$d['key']){
				//on fini les temps restant
				for ($i = $j; $i < $nbDate; $i++) {
					$nD = array('key'=>$oD['key'],'type'=>$oD['type'],'desc'=>$oD['desc']
							,'temps'=>$arrDate[$i],'score'=>0,'value'=>$defVal
							,'MinDate'=>0,'MaxDate'=>0
					);
					$nData[]=$nD;
					$this->trace('fin nouveau temps '.$i .' / '. $j,$nD);
				}
				$j=0;
				$oTag=$d['key'];
			}
			//on calcul les temps manquant
			for ($i = $j; $i < $nbDate; $i++) {
				//$this->trace($arrDate[$i]."==".$d['temps']);
				if($arrDate[$i]==$d['temps']){
					$nData[]=$d;
					$j=$i+1;
					$i=$nbDate;
				}else{
					$nD = array('key'=>$d['key'],'type'=>$d['type'],'desc'=>$d['desc']
							,'temps'=>$arrDate[$i],'score'=>0,'value'=>$defVal
							,'MinDate'=>0,'MaxDate'=>0
					);
					$nData[]=$nD;
					$this->trace('nouveau temps '.$i .' / '. $j,$nD);
				}
			}
			$oD = $d;
		}
		//on fini les temps restant
		for ($i = $j; $i < $nbDate; $i++) {
			$nD = array('key'=>$oD['key'],'type'=>$oD['type'],'desc'=>$oD['desc']
					,'temps'=>$arrDate[$i],'score'=>0,'value'=>$defVal
					,'MinDate'=>0,'MaxDate'=>0
			);
			$nData[]=$nD;
			$this->trace('fin nouveau temps '.$i .' / '. $j,$nD);
		}
		return  $nData;
	}

	/**
	 * Calcul un tableau de date avec les valeurs de tags mis en colonne
	 * Utile pour les diagramme multiline ou chaque ligne correspond à une catégorie
	 * Les data doivent avoir les champs :
	 * tags : tag séparé par des ', temps, nbDoc
	 *
	 * @param array		$data
	 * @param string		$trans
	 * @param string		$key
	 * @param string		$val
	 *
	 * @return array
	 */
	function getDataForMultiligne($data, $trans="group", $key="tags", $val="nbDoc"){
	
		$colos = array();
		//construction des colones
		foreach ($data as $v) {
			$arr = explode(',',$v[$key]);
			foreach ($arr as $k) {
				if (!in_array($k, $colos)) {
					$colos[]=$k;
				}
			}
		}
		//construction des datas
		$ndata = array();
		$andata = array();
		$oDate=$data[0]['temps'];
		foreach ($data as $v) {
			$r['DateTimeId']=$v['temps'];
			if($trans=="group"){
				foreach ($colos as $c) {
					if(strstr($v[$key], $c))
						$r[$c]=$v[$val]+0;
						else
							$r[$c]=0;
				}			
				$ndata[]=$r;
			}
			if($trans=="liste"){
				//les données doivent triées par temps
				if($oDate!=$v['temps']){
					//vérifie la complétude des lignes
					$rN=array();						
					$rN['DateTimeId']=$oDate;
					foreach ($andata as $ad) {
						foreach ($colos as $c) {
							if(isset($ad[$c]))
								$rN[$c]+=($ad[$c]+0);
							else
								$rN[$c]=0;
						}						
					}
					$ndata[]=$rN;
						
					$andata = array();
				}
				$r[$v[$key]]=$v[$val]+0;
				$andata[]=$r;				
				$oDate=$v['temps'];
			}
				
		}
		return $ndata;
	}	
}
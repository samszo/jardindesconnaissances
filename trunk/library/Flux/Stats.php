<?php
/**
 * Classe qui gère les flux statistiques
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Flux_Stats  extends Flux_Site{

	
 	public function __construct($idBase=false)
    {
    	parent::__construct($idBase);
    	
    }
	
	/**
     * Mise à jour des calculs statistiques 
     *
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
     * 
     * @return array
     */
	function GetTagHisto($lstTags, $tronc=-1, $dateUnit="") {
		
		$c = str_replace("::", "_", __METHOD__); 
		$c .= "_".$this->getParamString($lstTags);
		$c .= "_".$tronc."_".$dateUnit;
		$arr = $this->cache->load($c);
        if(!$arr){
	   		
			$dbT = new Model_DbTable_Flux_Tag($this->db);
			$dbD = new Model_DbTable_Flux_Doc($this->db);
			
			//récupère l'intervale de temps des documents
			$arrDocInt = $dbD->getHistoInterval("",$dateUnit);
			
			//récupère l'historique des tags associés
			$arrTags = $dbT->getTagHisto($lstTags, $tronc, $dateUnit);	    
	
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
					$r = array("tag_id"=>$d['tag_id'], "code"=>$d['code'], "nb"=>$nb, "temps"=>$t);
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
	
}
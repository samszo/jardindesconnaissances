<?php
/**
 * Classe qui gère les flux delicious
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
     * Récupère les tags associés à une liste de tag 
     *
     * @param array $arrTags
     * @param boolean $docRacine
     * 
     * @return array
     */
	function GetTagAssos($arrTags, $docRacine=true) {

		//définition de la requête
		//attention la colonne value est nécessaire pour le graphique  
		$sql = "SELECT t1.tag_id, t1.code, SUM(td1.poids) value, COUNT(DISTINCT(td.doc_id)) nbDoc
			FROM flux_tag t
				INNER JOIN flux_tagdoc td ON td.tag_id = t.tag_id
				INNER JOIN flux_tagdoc td1 ON td1.doc_id = td.doc_id
				INNER JOIN flux_tag t1 ON t1.tag_id = td1.tag_id";
		//vérifie si on prend les tags du document racine ou de ces éélments
		if($docRacine){
			$sql .= " INNER JOIN flux_doc d ON d.doc_id = td.doc_id  AND d.url != ''";
		}else{
			$sql .= " INNER JOIN flux_doc d ON d.doc_id = td.doc_id  AND d.url = ''";			
		}
		//construction de la condition des tag
		$where = " WHERE ";
		foreach ($arrTags as $tag) {
			$where .= " t.code LIKE '%".$tag."%' OR ";
		}
		$where = substr($where, 0, -3);

		//construction de la fin de requête
		$sql = $sql.$where." GROUP BY t1.tag_id	ORDER BY value DESC";
    	
		$stmt = $this->db->query($sql);
		//$arr = $stmt->fetchAll();
    	//return array("code"=>racine,"tags"=>$arr);
    	return $stmt->fetchAll();
    	
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
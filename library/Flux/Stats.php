<?php
/**
 * Classe qui gère les flux delicious
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Flux_Stats{

	var $cache;
	var $forceCalcul = false;
	
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
        $db = Zend_Db_Table::getDefaultAdapter();
    	$stmt = $db->query($sql);
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
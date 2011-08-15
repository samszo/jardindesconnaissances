<?php
/**
 * Ce fichier contient la classe Flux_UtiDoc.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_UtiDoc'.
 *
 * @copyright  201=& Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_UtiDoc extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_UtiDoc';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'uti_id';

    
    /**
     * Vérifie si une entrée Flux_UtiDoc existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('uti_id'));
		$select->where('uti_id = ?', $data['uti_id']);
		$select->where('doc_id = ?', $data['doc_id']);
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->uti_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_UtiDoc.
     *
     * @param array $data
     * @param boolean $existe
     *  
     * @return integer
     */
    public function ajouter($data, $existe=true)
    {
    	$id=false;
    	if($existe)$id = $this->existe($data);
    	if(!$id){
    	 	$id = $this->insert($data);
    	}
    	return $id;
    } 
           
    /**
     * Recherche une entrée Flux_UtiDoc avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_UtiDoc.uti_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_UtiDoc avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idUti
     * @param integer $idDoc
     *
     * @return void
     */
    public function remove($idUti, $idDoc)
    {
        $this->delete('flux_UtiDoc.uti_id = ' . $idUti. ' AND flux_UtiDoc.doc_id = ' . $idDoc);
    }

    /**
     * Recherche une entrée Flux_UtiDoc avec la clef étrangère spécifiée
     * et supprime ces entrées.
     *
     * @param integer $idDoc
     *
     * @return void
     */
    public function removeDoc($idDoc)
    {
        $this->delete(' flux_UtiDoc.doc_id = ' . $idDoc);
    }
    
    /**
     * Récupère toutes les entrées Flux_UtiDoc avec certains critères
     * de tri, intervalles
	 *
     * @return array
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_UtiDoc" => "flux_UtiDoc") );
                    
        if($order != null)
        {
            $query->order($order);
        }

        if($limit != 0)
        {
            $query->limit($limit, $from);
        }

        return $this->fetchAll($query)->toArray();
    }


    /**
     * Recherche les docs pour un user
     * et retourne cette entrée.
     *
     * @param int $UtiId

     * @return array
     */
    public function findDocByUti($UtiId)
    {
    	$sql = "SELECT d.url, d.doc_id
    		FROM flux_Doc d 
    		INNER JOIN flux_UtiDoc ud ON ud.doc_id = d.doc_id 
    		INNER JOIN flux_Uti u ON u.uti_id = ud.uti_id AND u.uti_id  = ".$UtiId."
    		ORDER BY d.doc_id ";
        $db = Zend_Db_Table::getDefaultAdapter();
    	$stmt = $db->query($sql);
    	return $stmt->fetchAll();
    	
    }

    /**
     * Recherche les docs pour un user et un tag
     * et retourne cette entrée.
     *
     * @param string $uti
     * @param string $tag

     * @return array
     */
    public function findDocByUtiTag($uti, $tag)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
    		->from(array('d' => 'flux_doc'))
            ->joinInner(array('ud' => 'flux_utidoc'),
            	'ud.doc_id=d.doc_id')
            ->joinInner(array('u' => 'flux_uti'),
            	'u.uti_id = ud.uti_id')
            ->joinInner(array('td' => 'flux_tagdoc'),
            	'td.doc_id = d.doc_id')
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = td.tag_id')
            ->where( "u.login = ?", $uti)
            ->where( "t.code = ?", $tag)
            ->order("d.pubDate");
        /*
         SELECT `d`.*, `ud`.*, `u`.*, `td`.*, `t`.* FROM `flux_doc` AS `d`
 INNER JOIN `flux_utidoc` AS `ud` ON ud.doc_id=d.doc_id
 INNER JOIN `flux_uti` AS `u` ON u.uti_id = ud.uti_id
 INNER JOIN `flux_tagdoc` AS `td` ON td.doc_id = d.doc_id
 INNER JOIN `flux_tag` AS `t` ON t.tag_id = td.tag_id WHERE (u.login = 'luckysemiosis') AND (t.code = 'agent') ORDER BY `d`.`pubDate` ASC
         */
		return $this->fetchAll($query)->toArray(); 
            
    }

    /**
     * Recherche les tags pour un user et un doc
     * et retourne cette entrée.
     *
     * @param string $docId
     * @param string $utiId

     * @return array
     */
    public function findTagByDocUti($docId, $utiId)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
    		->from(array('t' => 'flux_tag'))
            ->joinInner(array('td' => 'flux_tagdoc'),
            	'td.tag_id = t.tag_id')
            ->joinInner(array('ut' => 'flux_utitag'),
            	'ut.tag_id = t.tag_id')
            ->where( "ut.uti_id = ?", $utiId)
            ->where( "td.doc_id = ?", $docId)
            ->order("t.code");
        /*
         SELECT `t`.*, `td`.*, `ut`.* 
FROM `flux_tag` AS `t`
 INNER JOIN `flux_tagdoc` AS `td` ON td.tag_id = t.tag_id
 INNER JOIN `flux_utitag` AS `ut` ON ut.tag_id = t.tag_id 
 WHERE (ut.uti_id = '1') AND (td.doc_id = '11309') ORDER BY `t`.`code` ASC
         */
		return $this->fetchAll($query)->toArray(); 
            
    }

    
    /*
     * Recherche une entrée Flux_UtiDoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id
     */
    public function findByUti_id($uti_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_UtiDoc") )                           
                    ->where( "f.uti_id = ?", $uti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_UtiDoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $doc_id
     */
    public function findByDoc_id($doc_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_UtiDoc") )                           
                    ->where( "f.doc_id = ?", $doc_id );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

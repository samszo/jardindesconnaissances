<?php
/**
 * Ce fichier contient la classe flux_utidoc.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_utidoc'.
 *
 * @copyright  201=& Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_flux_utidoc extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_utidoc';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'uti_id';

    
    /**
     * Vérifie si une entrée flux_utidoc existe.
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
     * Ajoute une entrée flux_utidoc.
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
     * Recherche une entrée flux_utidoc avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_utidoc.uti_id = ' . $id);
    }
    
    /**
     * Recherche une entrée flux_utidoc avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_utidoc.uti_id = ' . $id);
    }

    /**
     * Recherche les entrées avec la clef primaire spécifiée
     * et supprime ces entrées.
     *
     * @param integer $id
     *
     * @return void
     */
    public function removeDoc($id)
    {
        $this->delete('doc_id = ' . $id);
    }
      
    /**
     * Récupère toutes les entrées flux_utidoc avec certains critères
     * de tri, intervalles
	 *
     * @return array
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_utidoc" => "flux_utidoc") );
                    
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
    		INNER JOIN flux_utidoc ud ON ud.doc_id = d.doc_id 
    		INNER JOIN flux_Uti u ON u.uti_id = ud.uti_id AND u.uti_id  = ".$UtiId
    		." ORDER BY ud.maj DESC";
        $db = Zend_Db_Table::getDefaultAdapter();
    	$stmt = $db->query($sql);
    	return $stmt->fetchAll();
    	
    }
    
    /**
     * Recherche le doc le plus récent pour un user
     * et retourne cette entrée.
     *
     * @param int $UtiId

     * @return array
     */
    public function findDernierDocByUti($UtiId)
    {
    	$sql = "SELECT d.url, d.doc_id, ud.maj
    		FROM flux_Doc d 
    		INNER JOIN flux_utidoc ud ON ud.doc_id = d.doc_id 
    		INNER JOIN flux_Uti u ON u.uti_id = ud.uti_id AND u.uti_id  = ".$UtiId;
        $db = Zend_Db_Table::getDefaultAdapter();
    	$stmt = $db->query($sql);
    	return $stmt->fetchAll();
    	
    }
    
    /*
     * Recherche une entrée flux_utidoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $doc_id
     */
    public function findByDoc_id($doc_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utidoc") )                           
                    ->where( "f.doc_id = ?", $doc_id );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

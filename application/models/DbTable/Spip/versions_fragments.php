<?php
/**
 * Ce fichier contient la classe Spip_versions_fragments.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_versions_fragments'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_versions_fragments extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_versions_fragments';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_fragment';
	
    
    /**
     * Vérifie si une entrée Spip_versions_fragments existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_fragment'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_fragment; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_versions_fragments.
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
     * Recherche une entrée Spip_versions_fragments avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_versions_fragments.id_fragment = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_versions_fragments avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_versions_fragments.id_fragment = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_versions_fragments avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_versions_fragments" => "spip_versions_fragments") );
                    
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
     * Recherche une entrée Spip_versions_fragments avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_fragment
     *
     * @return array
     */
    public function findById_fragment($id_fragment)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_versions_fragments") )                           
                    ->where( "s.id_fragment = ?", $id_fragment );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_versions_fragments avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $version_min
     *
     * @return array
     */
    public function findByVersion_min($version_min)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_versions_fragments") )                           
                    ->where( "s.version_min = ?", $version_min );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_versions_fragments avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $version_max
     *
     * @return array
     */
    public function findByVersion_max($version_max)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_versions_fragments") )                           
                    ->where( "s.version_max = ?", $version_max );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_versions_fragments avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_article
     *
     * @return array
     */
    public function findById_article($id_article)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_versions_fragments") )                           
                    ->where( "s.id_article = ?", $id_article );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_versions_fragments avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param tinyint $compress
     *
     * @return array
     */
    public function findByCompress($compress)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_versions_fragments") )                           
                    ->where( "s.compress = ?", $compress );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_versions_fragments avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longblob $fragment
     *
     * @return array
     */
    public function findByFragment($fragment)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_versions_fragments") )                           
                    ->where( "s.fragment = ?", $fragment );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

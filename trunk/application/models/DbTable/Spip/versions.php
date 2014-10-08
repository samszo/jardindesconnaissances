<?php
/**
 * Ce fichier contient la classe Spip_versions.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_versions'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_versions extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_versions';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_article';
	
    
    /**
     * Vérifie si une entrée Spip_versions existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_article'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_article; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_versions.
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
     * Recherche une entrée Spip_versions avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_versions.id_article = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_versions avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_versions.id_article = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_versions avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_versions" => "spip_versions") );
                    
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
     * Recherche une entrée Spip_versions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_article
     *
     * @return array
     */
    public function findById_article($id_article)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_versions") )                           
                    ->where( "s.id_article = ?", $id_article );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_versions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_version
     *
     * @return array
     */
    public function findById_version($id_version)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_versions") )                           
                    ->where( "s.id_version = ?", $id_version );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_versions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date
     *
     * @return array
     */
    public function findByDate($date)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_versions") )                           
                    ->where( "s.date = ?", $date );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_versions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $id_auteur
     *
     * @return array
     */
    public function findById_auteur($id_auteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_versions") )                           
                    ->where( "s.id_auteur = ?", $id_auteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_versions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $titre_version
     *
     * @return array
     */
    public function findByTitre_version($titre_version)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_versions") )                           
                    ->where( "s.titre_version = ?", $titre_version );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_versions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param char $permanent
     *
     * @return array
     */
    public function findByPermanent($permanent)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_versions") )                           
                    ->where( "s.permanent = ?", $permanent );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_versions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $champs
     *
     * @return array
     */
    public function findByChamps($champs)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_versions") )                           
                    ->where( "s.champs = ?", $champs );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

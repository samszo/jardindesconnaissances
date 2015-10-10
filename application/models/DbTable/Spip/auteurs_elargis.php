<?php
/**
 * Ce fichier contient la classe Spip_auteurs_elargis.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_auteurs_elargis'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_auteurs_elargis extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_auteurs_elargis';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id';
	
    
    /**
     * Vérifie si une entrée Spip_auteurs_elargis existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_auteurs_elargis.
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
     * Recherche une entrée Spip_auteurs_elargis avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_auteurs_elargis.id = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_auteurs_elargis avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_auteurs_elargis.id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_auteurs_elargis avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_auteurs_elargis" => "spip_auteurs_elargis") );
                    
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
     * Recherche une entrée Spip_auteurs_elargis avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id
     *
     * @return array
     */
    public function findById($id)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_elargis") )                           
                    ->where( "s.id = ?", $id );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs_elargis avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_auteur
     *
     * @return array
     */
    public function findById_auteur($id_auteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_elargis") )                           
                    ->where( "s.id_auteur = ?", $id_auteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs_elargis avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $spip_listes_format
     *
     * @return array
     */
    public function findBySpip_listes_format($spip_listes_format)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_elargis") )                           
                    ->where( "s.spip_listes_format = ?", $spip_listes_format );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

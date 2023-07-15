<?php
/**
 * Ce fichier contient la classe Spip_auteurs_messages.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_auteurs_messages'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_auteurs_messages extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_auteurs_messages';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_auteur';
	
    
    /**
     * Vérifie si une entrée Spip_auteurs_messages existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_auteur'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_auteur; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_auteurs_messages.
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
     * Recherche une entrée Spip_auteurs_messages avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_auteurs_messages.id_auteur = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_auteurs_messages avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_auteurs_messages.id_auteur = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_auteurs_messages avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_auteurs_messages" => "spip_auteurs_messages") );
                    
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
     * Recherche une entrée Spip_auteurs_messages avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_auteur
     *
     * @return array
     */
    public function findById_auteur($id_auteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_messages") )                           
                    ->where( "s.id_auteur = ?", $id_auteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs_messages avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_message
     *
     * @return array
     */
    public function findById_message($id_message)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_messages") )                           
                    ->where( "s.id_message = ?", $id_message );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs_messages avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param char $vu
     *
     * @return array
     */
    public function findByVu($vu)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_messages") )                           
                    ->where( "s.vu = ?", $vu );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

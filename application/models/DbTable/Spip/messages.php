<?php
/**
 * Ce fichier contient la classe Spip_messages.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_messages'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_messages extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_messages';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_message';
	
    
    /**
     * Vérifie si une entrée Spip_messages existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_message'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_message; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_messages.
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
     * Recherche une entrée Spip_messages avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_messages.id_message = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_messages avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_messages.id_message = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_messages avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_messages" => "spip_messages") );
                    
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
     * Recherche une entrée Spip_messages avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_message
     *
     * @return array
     */
    public function findById_message($id_message)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_messages") )                           
                    ->where( "s.id_message = ?", $id_message );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_messages avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_messages") )                           
                    ->where( "s.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_messages avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $texte
     *
     * @return array
     */
    public function findByTexte($texte)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_messages") )                           
                    ->where( "s.texte = ?", $texte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_messages avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $type
     *
     * @return array
     */
    public function findByType($type)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_messages") )                           
                    ->where( "s.type = ?", $type );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_messages avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_heure
     *
     * @return array
     */
    public function findByDate_heure($date_heure)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_messages") )                           
                    ->where( "s.date_heure = ?", $date_heure );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_messages avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_fin
     *
     * @return array
     */
    public function findByDate_fin($date_fin)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_messages") )                           
                    ->where( "s.date_fin = ?", $date_fin );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_messages avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $rv
     *
     * @return array
     */
    public function findByRv($rv)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_messages") )                           
                    ->where( "s.rv = ?", $rv );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_messages avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $statut
     *
     * @return array
     */
    public function findByStatut($statut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_messages") )                           
                    ->where( "s.statut = ?", $statut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_messages avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_auteur
     *
     * @return array
     */
    public function findById_auteur($id_auteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_messages") )                           
                    ->where( "s.id_auteur = ?", $id_auteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_messages avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_messages") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

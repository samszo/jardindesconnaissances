<?php
/**
 * Ce fichier contient la classe Spip_auteurs_courriers.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_auteurs_courriers'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_auteurs_courriers extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_auteurs_courriers';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_auteur';
	
    
    /**
     * Vérifie si une entrée Spip_auteurs_courriers existe.
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
     * Ajoute une entrée Spip_auteurs_courriers.
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
     * Recherche une entrée Spip_auteurs_courriers avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_auteurs_courriers.id_auteur = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_auteurs_courriers avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_auteurs_courriers.id_auteur = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_auteurs_courriers avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_auteurs_courriers" => "spip_auteurs_courriers") );
                    
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
     * Recherche une entrée Spip_auteurs_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_auteur
     *
     * @return array
     */
    public function findById_auteur($id_auteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_courriers") )                           
                    ->where( "s.id_auteur = ?", $id_auteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_courrier
     *
     * @return array
     */
    public function findById_courrier($id_courrier)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_courriers") )                           
                    ->where( "s.id_courrier = ?", $id_courrier );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('a_envoyer','envoye','echec') $statut
     *
     * @return array
     */
    public function findByStatut($statut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_courriers") )                           
                    ->where( "s.statut = ?", $statut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $etat
     *
     * @return array
     */
    public function findByEtat($etat)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_courriers") )                           
                    ->where( "s.etat = ?", $etat );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_courriers") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

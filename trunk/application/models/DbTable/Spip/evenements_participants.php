<?php
/**
 * Ce fichier contient la classe Spip_evenements_participants.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_evenements_participants'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_evenements_participants extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_evenements_participants';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_evenement';
	
    
    /**
     * Vérifie si une entrée Spip_evenements_participants existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_evenement'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_evenement; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_evenements_participants.
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
     * Recherche une entrée Spip_evenements_participants avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_evenements_participants.id_evenement = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_evenements_participants avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_evenements_participants.id_evenement = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_evenements_participants avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_evenements_participants" => "spip_evenements_participants") );
                    
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
     * Recherche une entrée Spip_evenements_participants avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_evenement
     *
     * @return array
     */
    public function findById_evenement($id_evenement)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements_participants") )                           
                    ->where( "s.id_evenement = ?", $id_evenement );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements_participants avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_auteur
     *
     * @return array
     */
    public function findById_auteur($id_auteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements_participants") )                           
                    ->where( "s.id_auteur = ?", $id_auteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements_participants avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date
     *
     * @return array
     */
    public function findByDate($date)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements_participants") )                           
                    ->where( "s.date = ?", $date );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements_participants avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param char $reponse
     *
     * @return array
     */
    public function findByReponse($reponse)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements_participants") )                           
                    ->where( "s.reponse = ?", $reponse );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

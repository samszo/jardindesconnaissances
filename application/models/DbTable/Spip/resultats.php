<?php
/**
 * Ce fichier contient la classe Spip_resultats.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_resultats'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_resultats extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_resultats';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id';
	
    
    /**
     * Vérifie si une entrée Spip_resultats existe.
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
     * Ajoute une entrée Spip_resultats.
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
     * Recherche une entrée Spip_resultats avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_resultats. = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_resultats avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_resultats. = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_resultats avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_resultats" => "spip_resultats") );
                    
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
     * Recherche une entrée Spip_resultats avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param char $recherche
     *
     * @return array
     */
    public function findByRecherche($recherche)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_resultats") )                           
                    ->where( "s.recherche = ?", $recherche );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_resultats avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id
     *
     * @return array
     */
    public function findById($id)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_resultats") )                           
                    ->where( "s.id = ?", $id );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_resultats avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $points
     *
     * @return array
     */
    public function findByPoints($points)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_resultats") )                           
                    ->where( "s.points = ?", $points );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_resultats avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_resultats") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

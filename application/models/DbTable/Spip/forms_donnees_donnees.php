<?php
/**
 * Ce fichier contient la classe Spip_forms_donnees_donnees.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_forms_donnees_donnees'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_forms_donnees_donnees extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_forms_donnees_donnees';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_donnee';
	
    
    /**
     * Vérifie si une entrée Spip_forms_donnees_donnees existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_donnee'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_donnee; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_forms_donnees_donnees.
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
     * Recherche une entrée Spip_forms_donnees_donnees avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_forms_donnees_donnees.id_donnee = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_forms_donnees_donnees avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_forms_donnees_donnees.id_donnee = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_forms_donnees_donnees avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_forms_donnees_donnees" => "spip_forms_donnees_donnees") );
                    
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
     * Recherche une entrée Spip_forms_donnees_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_donnee
     *
     * @return array
     */
    public function findById_donnee($id_donnee)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees_donnees") )                           
                    ->where( "s.id_donnee = ?", $id_donnee );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $donnee_ref
     *
     * @return array
     */
    public function findByDonnee_ref($donnee_ref)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees_donnees") )                           
                    ->where( "s.donnee_ref = ?", $donnee_ref );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_donnee_liee
     *
     * @return array
     */
    public function findById_donnee_liee($id_donnee_liee)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees_donnees") )                           
                    ->where( "s.id_donnee_liee = ?", $id_donnee_liee );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

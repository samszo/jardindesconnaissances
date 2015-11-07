<?php
/**
 * Ce fichier contient la classe Spip_forms_donnees_champs.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_forms_donnees_champs'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Spip_formsxdonneessxchamps extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_forms_donnees_champs';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = array('id_donnee','champ');
	
    
    /**
     * Vérifie si une entrée Spip_forms_donnees_champs existe.
     *
     * @param array $data
     *
     * @return array
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_donnee','champ'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_forms_donnees_champs.
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
     * Recherche une entrée Spip_forms_donnees_champs avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_forms_donnees_champs. = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_forms_donnees_champs avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_forms_donnees_champs. = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_forms_donnees_champs avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_forms_donnees_champs" => "spip_forms_donnees_champs") );
                    
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
     * Recherche une entrée Spip_forms_donnees_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_donnee
     *
     * @return array
     */
    public function findById_donnee($id_donnee)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees_champs") )                           
                    ->where( "s.id_donnee = ?", $id_donnee );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $champ
     *
     * @return array
     */
    public function findByChamp($champ)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees_champs") )                           
                    ->where( "s.champ = ?", $champ );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $valeur
     *
     * @return array
     */
    public function findByValeur($valeur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees_champs") )                           
                    ->where( "s.valeur = ?", $valeur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees_champs") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

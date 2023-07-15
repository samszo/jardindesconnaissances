<?php
/**
 * Ce fichier contient la classe Spip_mots_syndic.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_mots_syndic'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_mots_syndic extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_mots_syndic';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_mot';
	
    
    /**
     * Vérifie si une entrée Spip_mots_syndic existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_mot'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_mot; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_mots_syndic.
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
     * Recherche une entrée Spip_mots_syndic avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_mots_syndic.id_mot = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_mots_syndic avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_mots_syndic.id_mot = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_mots_syndic avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_mots_syndic" => "spip_mots_syndic") );
                    
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
     * Recherche une entrée Spip_mots_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_mot
     *
     * @return array
     */
    public function findById_mot($id_mot)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots_syndic") )                           
                    ->where( "s.id_mot = ?", $id_mot );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_mots_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_syndic
     *
     * @return array
     */
    public function findById_syndic($id_syndic)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots_syndic") )                           
                    ->where( "s.id_syndic = ?", $id_syndic );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

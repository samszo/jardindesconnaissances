<?php
/**
 * Ce fichier contient la classe gen_oeuvres_utis_roles.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
*/
class Model_DbTable_Gen_oeuvresxutisxroles extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_oeuvres_utis_roles';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_our';
    
    /**
     * Vérifie si une entrée gen_oeuvres_utis_roles existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_our'));
		$select->where('uti_id = ?', $data['uti_id']);
		$select->where('id_oeu = ?', $data['id_oeu']);
		$rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_our; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée gen_oeuvres_utis_roles.
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
    	}else{
    		$this->edit($id, array("id_role"=>$data["id_role"]));
    	}
    	return $id;
    } 
           
    /**
     * Recherche une entrée gen_oeuvres_utis_roles avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_oeuvres_utis_roles.id_our = ' . $id);
    }
    
    /**
     * Recherche une entrée gen_oeuvres_utis_roles avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('gen_oeuvres_utis_roles.id_our = ' . $id);
    }

    
    /**
     * Récupère toutes les entrées gen_oeuvres_utis_roles avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_oeuvres_utis_roles" => "gen_oeuvres_utis_roles") );
                    
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
     * Recherche une entrée gen_oeuvres_utis_roles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_oeu
     *
     * @return array
     */
    public function findById_oeu($id_oeu)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_oeuvres_utis_roles") )                           
                    ->where( "g.id_oeu = ?", $id_oeu );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée gen_oeuvres_utis_roles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id
     *
     * @return array
     */
    public function findByUti_id($uti_id)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_oeuvres_utis_roles") )                           
                    ->where( "g.uti_id = ?", $uti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

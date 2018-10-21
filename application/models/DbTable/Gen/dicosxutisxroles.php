<?php
/**
 * Ce fichier contient la classe gen_dicos_utis_roles.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
*/
class Model_DbTable_Gen_dicosxutisxroles extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_dicos_utis_roles';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_dur';
    
    /**
     * Vérifie si une entrée gen_dicos_utis_roles existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_dur'));
		$select->where('uti_id = ?', $data['uti_id']);
		$select->where('id_dico = ?', $data['id_dico']);
		$rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_dur; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée gen_dicos_utis_roles.
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
    		$this->edit($id, array('id_role'=>$data['id_role']));
    	}
    	return $id;
    } 
           
    /**
     * Recherche une entrée gen_dicos_utis_roles avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_dicos_utis_roles.id_dur = ' . $id);
    }
    
    /**
     * Recherche une entrée gen_dicos_utis_roles avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('gen_dicos_utis_roles.id_dur = ' . $id);
    }

    
    /**
     * Récupère toutes les entrées gen_dicos_utis_roles avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_dicos_utis_roles" => "gen_dicos_utis_roles") );
                    
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
     * Recherche une entrée gen_dicos_utis_roles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_dico
     *
     * @return array
     */
    public function findById_dico($id_dico)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_dicos_utis_roles") )                           
                    ->where( "g.id_dico = ?", $id_dico );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée gen_dicos_utis_roles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id
     *
     * @return array
     */
    public function findByUti_id($uti_id)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_dicos_utis_roles") )                           
                    ->where( "g.uti_id = ?", $uti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

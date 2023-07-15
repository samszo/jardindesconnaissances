<?php
/**
 * Classe ORM qui représente la table 'item_set'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Omk
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Model_DbTable_Omk_ItemSet extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'item_set';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id';

    protected $_dependentTables = array();
    

        
    /**
     * Vérifie si une entrée item_set existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id'));
		$select->where('id = ?', $data['id']);
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée item_set.
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
     * Recherche une entrée item_set avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    		$this->update($data, 'item_set.id = ' . $id);
    }
    
    /**
     * Recherche une entrée item_set avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {    	
	    	$this->delete('item_set.id = ' . $id);
    }

    /**
     * Trouve les itemSet parent
     * is_part_of : property_id = 33
     *
     * @param integer $id
     *
     * @return void
     */
    public function getItemSetParent($id)
    {
    		if(!isset($this->dbV))$this->dbV = new Model_DbTable_Omk_Value($this->_db);
    	 	return $this->dbV->findByCatKeyVal(33, "resource_id", $id);
    }
    
    /**
     * Trouve les itemSet avec un titre
     *
     * @param string $titre
     *
     * @return void
     */
    public function getByTitre($titre)
    {
        if(!isset($this->dbV))$this->dbV = new Model_DbTable_Omk_Value($this->_db);
        return $this->dbV->findByCatKeyVal(1, "value", $titre);
    }

    /**
     * Trouve les itemSet avec une référence
     *
     * @param string $ref
     *
     * @return void
     */
    public function getByIdentifier($ref)
    {
        if(!isset($this->dbV))$this->dbV = new Model_DbTable_Omk_Value($this->_db);
        return $this->dbV->findByCatKeyVal(10, "value", $ref);
    }
    
}

<?php
/**
 * Classe ORM qui représente la table 'item_item_set'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Omk
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Model_DbTable_Omk_ItemItemSet extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'item_item_set';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'item_id';

    protected $_dependentTables = array();
    

        
    /**
     * Vérifie si une entrée item_item_set existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('item_id'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->item_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée item_item_set.
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
     * Recherche une entrée item_item_set avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    		$this->update($data, 'item_item_set.id = ' . $id);
    }

    /**
     * Ajoute les item_set en remontant l'arboressence is_part_of
     *
     * @param integer $idItem
     * @param integer $idItemSet
     *
     * @return void
     */
    public function ajouterTroncParBranche($idItem, $idItemSet)
    {  
    		if(!isset($this->dbIS))$this->dbIS = new Model_DbTable_Omk_ItemSet($this->_db);
    		$rs = $this->dbIS->getItemSetParent($idItemSet);
    		if($rs){
	    		foreach ($rs as $v) {
	    			$this->ajouter(array("item_id"=>$idItem,"item_set_id"=>$v["value_resource_id"]));
	    			$this->ajouterTroncParBranche($idItem, $v["value_resource_id"]);
	    		}    		
    		}
    	}

    	 
    /**
     * Recherche une entrée item_item_set avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {    	
	    	$this->delete('item_item_set.id = ' . $id);
    }
    
}

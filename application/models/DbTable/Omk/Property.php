<?php
/**
 * Classe ORM qui représente la table 'property'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Omk
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Model_DbTable_Omk_Property extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'property';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id';

    protected $_dependentTables = array();
    

        
    /**
     * Vérifie si une entrée property existe.
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
     * Ajoute une entrée property.
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
     * Recherche une entrée property avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    		$this->update($data, 'property.id = ' . $id);
    }
    
    /**
     * Recherche une entrée property avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {    	
	    	$this->delete('property.id = ' . $id);
    }
    
}

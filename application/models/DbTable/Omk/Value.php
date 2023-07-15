<?php
/**
 * Classe ORM qui représente la table 'value'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Omk
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Model_DbTable_Omk_Value extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'value';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id';

    protected $_dependentTables = array();
    

        
    /**
     * Vérifie si une entrée value existe.
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
     * Ajoute une entrée value.
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
     * Recherche une entrée value avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    		$this->update($data, 'value.id = ' . $id);
    }
    
    /**
     * Recherche une entrée value avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {    	
	    	$this->delete('value.id = ' . $id);
    }
    
    /**
     * Recherche une entrée  avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uri
     * 
     * @return array
     * 
     **/
    public function findByUri($uri)
    {
	    	$query = $this->select()
	    	->from( array("f" => "value") )
	    ->where( "f.uri = ?", $uri);
	    $arr = $this->fetchAll($query)->toArray();
    		return count($arr) ? $arr[0] : false;
    }
    
    /**
     * Recherche une entrée  avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int 		$prop
     * @param string		$key
     * @param string		$val
     * 
     * @return array
     * 
     **/
    public function findByCatKeyVal($prop, $key, $val)
    {
	    	$query = $this->select()
		    	->from( array("f" => "value") )
		    	->where( "f.property_id = ?", $prop)
		    	->where( "f.".$key."= ?", $val);
	    	$arr = $this->fetchAll($query)->toArray();
	    	return count($arr) ? $arr : false;
    }
    
}

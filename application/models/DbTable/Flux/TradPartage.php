<?php


/**
 * Classe ORM qui représente la table 'flux_tradpartage'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 * @ignore
 */

class Model_DbTable_flux_tradpartage extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_tradpartage';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'trad_id';

    
    /**
     * Vérifie si une entrée flux_tradpartage existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('trad_id'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->trad_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée flux_tradpartage.
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
     * Recherche une entrée flux_tradpartage avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_tradpartage.trad_id = ' . $id);
    }
    
    /**
     * Recherche une entrée flux_tradpartage avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_tradpartage.trad_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées flux_tradpartage avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_tradpartage" => "flux_tradpartage") );
                    
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
     * Récupère les spécifications des colonnes flux_tradpartage 
     */
    public function getCols(){

    	$arr = array("cols"=>array(
    	   	array("titre"=>"trad_id","champ"=>"trad_id","visible"=>true),
    	array("titre"=>"uti_id","champ"=>"uti_id","visible"=>true),
        	
    		));    	
    	return $arr;
		
    }     
    
    /*
     * Recherche une entrée flux_tradpartage avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $trad_id
     */
    public function findByTrad_id($trad_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tradpartage") )                           
                    ->where( "f.trad_id = ?", $trad_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée flux_tradpartage avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id
     */
    public function findByUti_id($uti_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tradpartage") )                           
                    ->where( "f.uti_id = ?", $uti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

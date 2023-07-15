<?php

/**
 * Classe ORM qui représente la table 'flux_trad'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 * @ignore
 */

class Model_DbTable_flux_trad extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_trad';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'trad_id';

    
    /**
     * Vérifie si une entrée flux_trad existe.
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
     * Ajoute une entrée flux_trad.
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
     * Recherche une entrée flux_trad avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_trad.trad_id = ' . $id);
    }
    
    /**
     * Recherche une entrée flux_trad avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_trad.trad_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées flux_trad avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_trad" => "flux_trad") );
                    
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
     * Récupère les spécifications des colonnes flux_trad 
     */
    public function getCols(){

    	$arr = array("cols"=>array(
    	   	array("titre"=>"trad_id","champ"=>"trad_id","visible"=>true),
    	array("titre"=>"ieml_id","champ"=>"ieml_id","visible"=>true),
    	array("titre"=>"tag_id","champ"=>"tag_id","visible"=>true),
    	array("titre"=>"trad_date","champ"=>"trad_date","visible"=>true),
    	array("titre"=>"trad_post","champ"=>"trad_post","visible"=>true),
        	
    		));    	
    	return $arr;
		
    }     
    
    /*
     * Recherche une entrée flux_trad avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $trad_id
     */
    public function findByTrad_id($trad_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_trad") )                           
                    ->where( "f.trad_id = ?", $trad_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée flux_trad avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $ieml_id
     */
    public function findByIeml_id($ieml_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_trad") )                           
                    ->where( "f.ieml_id = ?", $ieml_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée flux_trad avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id
     */
    public function findByTag_id($tag_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_trad") )                           
                    ->where( "f.tag_id = ?", $tag_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée flux_trad avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param date $trad_date
     */
    public function findByTrad_date($trad_date)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_trad") )                           
                    ->where( "f.trad_date = ?", $trad_date );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée flux_trad avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param tinyint $trad_post
     */
    public function findByTrad_post($trad_post)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_trad") )                           
                    ->where( "f.trad_post = ?", $trad_post );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

<?php

/**
 * Classe ORM qui représente la table 'flux_UtiExi'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 * @ignore
 */

class Model_DbTable_Flux_UtiExi extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_utiexi';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'utiexi_id';

    
    /**
     * Vérifie si une entrée flux_utiexi existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('uti_id'));
		foreach($data as $k=>$v){
			if($k!='eval')
				$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->uti_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée flux_utiexi.
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
     * Recherche une entrée flux_utiexi avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $idUti
     * @param integer $idExi
     * @param array $data
     *
     * @return void
     */
    public function edit($idUti, $idExi, $data)
    {        
        $this->update($data, 'flux_utiexi.uti_id = ' . $idUti.' AND flux_utiexi.exi_id = ' . $idExi);
    }
    
    /**
     * Recherche une entrée flux_utiexi avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idUti
     * @param integer $idExi
     *
     * @return void
     */
    public function remove($idUti, $idExi)
    {
        $this->delete('flux_utiexi.uti_id = ' . $idUti.' AND flux_utiexi.exi_id = ' . $idExi);
    }

    /**
     * Récupère toutes les entrées flux_utiexi avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_utiexi" => "flux_utiexi") );
                    
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
    
   
    
}

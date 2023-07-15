<?php
/**
 * Classe ORM qui représente la table 'flux_complexe'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Model_DbTable_Flux_Complexe extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_complexe';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'complexe_id';

    
    /**
     * Vérifie si une entrée flux_complexe existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('complexe_id'));
		foreach($data as $k=>$v){
			if($k=='obj_id')$select->where($k.' = ?', $v);
			if($k=='obj_type')$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->complexe_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée flux_complexe.
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
            if(!isset($data["maj"])) $data["maj"] = new Zend_Db_Expr('NOW()');
	    	if(!$id){
                $id = $this->insert($data);
	    	}else{
                $this->edit($id,$data);
            }
	    	return $id;
    } 
           
    /**
     * Recherche une entrée flux_complexe avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_complexe.complexe_id = ' . $id);
    }
    
    /**
     * Recherche une entrée flux_complexe avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_complexe.complexe_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées flux_complexe avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_complexe" => "flux_complexe") );
                    
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

<?php

/**
 * Classe ORM qui représente la table 'flux_UtiUti'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 * @ignore
 */

class Model_DbTable_Flux_UtiUti extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_utiuti';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'uti_id_src';

    
    /**
     * Vérifie si une entrée flux_utiuti existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('uti_id_src'));
		foreach($data as $k=>$v){
			if($k!='eval')
				$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->uti_id_src; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée flux_utiuti.
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
     * Recherche une entrée flux_utiuti avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $idSrc
     * @param integer $idDst
     * @param array $data
     *
     * @return void
     */
    public function edit($idSrc, $idDst, $data)
    {        
        $this->update($data, 'flux_utiuti.uti_id_src = ' . $idSrc.' AND flux_utiuti.uti_id_dst = ' . $idDst);
    }
    
    /**
     * Recherche une entrée flux_utiuti avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idSrc
     * @param integer $idDst
     *
     * @return void
     */
    public function remove($idSrc, $idDst)
    {
        $this->delete('flux_utiuti.uti_id_src = ' . $idSrc.' AND flux_utiuti.uti_id_dst = ' . $idDst);
    }
    
    /**
     * Récupère toutes les entrées flux_utiuti avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_utiuti" => "flux_utiuti") );
                    
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
    
    /*
     * Recherche une entrée flux_utiuti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id_src
     */
    public function findByUti_id_src($uti_id_src)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utiuti") )                           
                    ->where( "f.uti_id_src = ?", $uti_id_src );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée flux_utiuti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id_dst
     */
    public function findByUti_id_dst($uti_id_dst)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utiuti") )                           
                    ->where( "f.uti_id_dst = ?", $uti_id_dst );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée flux_utiuti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $network
     */
    public function findByNetwork($network)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utiuti") )                           
                    ->where( "f.network = ?", $network );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée flux_utiuti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $fan
     */
    public function findByFan($fan)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utiuti") )                           
                    ->where( "f.fan = ?", $fan );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

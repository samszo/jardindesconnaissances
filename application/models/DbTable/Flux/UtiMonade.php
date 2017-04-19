<?php


/**
 * Classe ORM qui représente la table 'flux_utimonade'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 * @ignore
 */

class Model_DbTable_flux_UtiMonade extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_utimonade';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'uti_id';

    
    /**
     * Vérifie si une entrée flux_utimonade existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('uti_id'));
		$select->where('monade_id = ?', $data['monade_id']);
		$select->where('uti_id = ?', $data['uti_id']);
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->uti_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée flux_utimonade.
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
     * Recherche une entrée flux_utimonade avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_utimonade.uti_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées flux_utimonade avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_utimonade" => "flux_utimonade") );
                    
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
     * Recherche les Monade avec la valeur spécifiée
     * et retourne les entrées.
     *
     * @param string $uti
     * 
     * @return array
     */
    public function findTagByUti($uti)
    {
        $query = $this->select()
	     	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('um' => 'flux_utimonade'))
            ->joinInner(array('m' => 'flux_monade'),
            	'um.monade_id = m.monade_id', array('titre'))
            ->joinInner(array('u' => 'flux_uti'),
            	'u.uti_id = um.uti_id ', array('login'))
	     	->where("u.uti_id = ? ", $uti)
            ->order(array("u.login", "m.titre"));
            
        return $this->fetchAll($query)->toArray(); 

    }

    /**
     * Renvoi les Monades et les utilisateurs
     *
     * 
     * @return array
     */
    public function findUtiMonade()
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('um' => 'flux_utimonade'))
            ->joinInner(array('m' => 'flux_monade'),
            	'um.monade_id = m.monade_id', array('titre'))
            ->joinInner(array('u' => 'flux_uti'),
            	'u.uti_id = um.uti_id ', array('login'))
            ->order(array("u.login", "m.titre"));
            
        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche une entrée flux_utimonade avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id
     */
    public function findByTag_id($tag_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utimonade") )                           
                    ->where( "f.tag_id = ?", $tag_id );

        return $this->fetchAll($query)->toArray(); 
    }

    /**
     * Recherche une entrée flux_utimonade avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id
     */
    public function findByUti_id($uti_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utimonade") )                           
                    ->where( "f.uti_id = ?", $uti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

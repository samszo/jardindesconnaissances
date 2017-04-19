<?php

/**
 * Classe ORM qui représente la table 'flux_utitag'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 * @ignore
 */

class Model_DbTable_flux_utitag extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_utitag';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'uti_id';

    
    /**
     * Vérifie si une entrée flux_utitag existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('uti_id'));
		$select->where('tag_id = ?', $data['tag_id']);
		$select->where('uti_id = ?', $data['uti_id']);
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->uti_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée flux_utitag.
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
    	}else{
    		//met à jour le poids
    		$this->ajoutPoids($data);
    	}
       	return $id;
    } 

    /**
     * mise à jour du poids.
     *
     * @param array $data
     *
     * @return void
     */
    public function ajoutPoids($data)
    {
    	if(!isset($data["poids"]))$data["poids"]=1;        
		$sql = 'UPDATE flux_utitag SET poids = poids + '.$data["poids"].' WHERE tag_id = '.$data["tag_id"].' AND uti_id ='.$data["uti_id"];
    	$this->_db->query($sql);    
    }
    
    
    
    /**
     * Recherche une entrée flux_utitag avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_utitag.uti_id = ' . $id);
    }
    
    /**
     * Recherche une entrée flux_utitag avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_utitag.uti_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées flux_utitag avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_utitag" => "flux_utitag") );
                    
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
     * Recherche les Tag avec la valeur spécifiée
     * et retourne les entrées.
     *
     * @param string $uti
     * @param string $flux
     * 
     * @return array
     */
    public function findTagByUti($uti, $flux="")
    {
        $query = $this->select()
	     	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('ut' => 'flux_utitag'))
            ->joinInner(array('t' => 'Flux_Tag'),
            	'ut.tag_id = t.tag_id', array('code'))
            ->joinInner(array('u' => 'Flux_Uti'),
            	'u.uti_id = ut.uti_id ', array('login'))
            ->where("u.login = ? ", $uti)
            ->order(array("u.login", "t.code"));
        if($flux)$query->where("u.flux = ? ", $flux);    
        return $this->fetchAll($query)->toArray(); 

    }

    /**
     * Renvoi les tags et les utilisateurs
     *
     * 
     * @return array
     */
    public function findTagUti()
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('ut' => 'flux_utitag'))
            ->joinInner(array('t' => 'Flux_Tag'),
            	'ut.tag_id = t.tag_id', array('code'))
            ->joinInner(array('u' => 'Flux_Uti'),
            	'u.uti_id = ut.uti_id ', array('login'))
            ->order(array("u.login", "t.code"));
            
        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche une entrée flux_utitag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id
     */
    public function findByTag_id($tag_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utitag") )                           
                    ->where( "f.tag_id = ?", $tag_id );

        return $this->fetchAll($query)->toArray(); 
    }

    /**
     * Recherche une entrée flux_utitag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int 		$uti_id
     * @param string 	$flux
     * 
     * @return array
     */
    public function findTagByUtiId($uti_id, $flux="")
    {
        $query = $this->select()
	     	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('ut' => 'flux_utitag'))
            ->joinInner(array('t' => 'Flux_Tag'),
            	'ut.tag_id = t.tag_id', array('code'))
            ->joinInner(array('u' => 'Flux_Uti'),
            	'u.uti_id = ut.uti_id ', array('login'))
            ->where("u.uti_id = ? ", $uti_id)
            ->order(array("t.code"));
        if($flux)$query->where("u.flux = ? ", $flux);    
            
        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche une entrée flux_utitag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $poids
     */
    public function findByPoids($poids)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utitag") )                           
                    ->where( "f.poids = ?", $poids );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

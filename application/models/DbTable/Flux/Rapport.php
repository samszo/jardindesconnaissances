<?php
/**
 * Ce fichier contient la classe Flux_rapport.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Model_DbTable_Flux_Rapport extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'flux_rapport';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'rapport_id';

    
    /**
     * Vérifie si une entrée Flux_rapport existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('rapport_id'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->rapport_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_rapport.
     *
     * @param array $data
     * @param boolean $existe
     *  
     * @return integer
     */
    public function ajouter($data, $existe=true)
    {
    		//print_r($data);
	    	$id=false;
	    	if($existe)$id = $this->existe($data);
	    	if(!$id){
	    		if(!isset($data["maj"])) $data["maj"] = new Zend_Db_Expr('NOW()');    		
	    	 	$id = $this->insert($data);
	    	}
	    	return $id;
    } 

    
    /**
     * Recherche une entrée Flux_rapport avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'flux_rapport.rapport_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_rapport avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
	    	//supprime le rapport
    		$this->delete('flux_rapport.rapport_id = ' . $id);
		//supprime les rapports en lien avec le rapport
		$where = '(src_id = '.$id.' AND src_obj = "rapport") OR (dst_id = '.$id.' AND dst_obj = "rapport") OR (pre_id = '.$id.' AND pre_obj = "rapport")'; 		
	    return $this->delete($where);
    	
    }

    /**
     * Recherche une entrée Flux_rapport avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function removeMonade($id)
    {
	    	$this->delete('flux_rapport.monade_id = ' . $id);
    }

    /**
     * Recherche une entrée Flux_rapport avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return integer
     */
    public function removeDoc($id)
    {
    		//récupère les rapports en référence à ce doc
    		$nbSup = 0;
    		$where = '(src_id = '.$id.' AND src_obj = "doc") OR (dst_id = '.$id.' AND dst_obj = "doc") OR (pre_id = '.$id.' AND pre_obj = "doc")'; 
		$sql = 'SELECT *
		FROM flux_rapport
		WHERE '.$where;    		
	    	$stmt = $this->_db->query($sql);
	    	$arr =  $stmt->fetchAll();
		foreach ($arr as $r){
	    		//supprime les rapports en lien avec le doc
			$nbSup += $this->remove($r["rapport_id"]);			
		}
		return $nbSup;
    }

    /**
     * Recherche une entrée Flux_rapport avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return integer
     */
    public function removeExi($id)
    {
        //récupère les rapports en référence 
        $nbSup = 0;
        $where = '(src_id = '.$id.' AND src_obj = "exi") OR (dst_id = '.$id.' AND dst_obj = "exi") OR (pre_id = '.$id.' AND pre_obj = "exi")';
        $sql = 'SELECT *
		FROM flux_rapport
		WHERE '.$where;
        $stmt = $this->_db->query($sql);
        $arr =  $stmt->fetchAll();
        foreach ($arr as $r){
            //supprime les rapports en lien avec le doc
            $nbSup += $this->remove($r["rapport_id"]);
        }
        return $nbSup;
    }
    
    /**
     * Recherche une entrée Flux_rapport avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return integer
     */
    public function removeTag($id)
    {
        //récupère les rapports en référence à ce doc
        $nbSup = 0;
        $where = '(src_id = '.$id.' AND src_obj = "tag") OR (dst_id = '.$id.' AND dst_obj = "tag") OR (pre_id = '.$id.' AND pre_obj = "tag")';
        $sql = 'SELECT *
		FROM flux_rapport
		WHERE '.$where;
        $stmt = $this->_db->query($sql);
        $arr =  $stmt->fetchAll();
        foreach ($arr as $r){
            //supprime les rapports en lien avec le doc
            $nbSup += $this->remove($r["rapport_id"]);
        }
        return $nbSup;
    }
    
    
    /**
     * Récupère toutes les entrées Flux_rapport avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("flux_rapport" => "flux_rapport") );
                    
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
     * Recherche une entrée Flux_rapport avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id
     *
     * @return array
     */
    public function findByIdRapport($id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_rapport") )                           
                    ->where( "f.rapport_id = ?", $id );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_rapport avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id
     *
     * @return array
     */
    public function findByIdMonade($id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_rapport") )                           
                    ->where( "f.monade_id = ?", $id);

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_rapport avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id
     *
     * @return array
     */
    public function findByIdUtitagdoc($id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_rapport") )                           
                    ->where( "f.utitagdoc_id = ?", $id);

                    
                    
        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_rapport avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_rapport") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
 
}

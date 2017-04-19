<?php


/**
 * Classe ORM qui représente la table 'flux_tagtag'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 * @ignore
 */

class Model_DbTable_Flux_TagTag extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_tagtag';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'tag_id_src';

    
    /**
     * Vérifie si une entrée Flux_tagtag existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('tag_id_src'));
		foreach($data as $k=>$v){
			if($k!="maj" && $k!="poids")
				$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->tag_id_src; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_tagtag.
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
	    		if(!isset($data["poids"])) $data["poids"] = 1;
	    		$id = $this->insert($data);
	    	}else{
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
		$sql = 'UPDATE flux_tagtag SET poids = poids + '.$data["poids"].' WHERE tag_id_src = '.$data["tag_id_src"].' AND tag_id_dst ='.$data["tag_id_dst"];
    		$this->_db->query($sql);    
    }    
    
    /**
     * Recherche une entrée Flux_tagtag avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_tagtag.tag_id_src = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_tagtag avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_tagtag.tag_id_src = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Flux_tagtag avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_tagtag" => "flux_tagtag") );
                    
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
     * Récupère les spécifications des colonnes Flux_tagtag 
     */
    public function getCols(){

    	$arr = array("cols"=>array(
    	   	array("titre"=>"tag_id_src","champ"=>"tag_id_src","visible"=>true),
    	array("titre"=>"tag_id_dst","champ"=>"tag_id_dst","visible"=>true),
        	
    		));    	
    	return $arr;
		
    }     
    
    /*
     * Recherche une entrée Flux_tagtag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id_src
     */
    public function findByTag_id_src($tag_id_src)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tagtag") )                           
                    ->where( "f.tag_id_src = ?", $tag_id_src );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_tagtag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id_dst
     */
    public function findByTag_id_dst($tag_id_dst)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tagtag") )                           
                    ->where( "f.tag_id_dst = ?", $tag_id_dst );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

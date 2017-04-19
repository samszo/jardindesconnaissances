<?php

/**
 * Classe ORM qui représente la table 'flux_docdoc'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 * @ignore
 */

class Model_DbTable_Flux_DocDoc extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_docdoc';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = array('doc_id_src','doc_id_dst');

    
    /**
     * Vérifie si une entrée Flux_docdoc existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('doc_id_src'));
		foreach($data as $k=>$v){
			if($k!="maj" && $k!="poids")
				$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->doc_id_src; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_docdoc.
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
     * Recherche une entrée avec la clef étrangère spécifiée
     * et supprime ces entrées.
     *
     * @param integer $idDoc
     *
     * @return void
     */
    public function removeDoc($idDoc)
    {
        $this->delete(' doc_id_src = ' . $idDoc);
        $this->delete(' doc_id_dst = ' . $idDoc);
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
		$sql = 'UPDATE flux_docdoc SET poids = poids + '.$data["poids"].' WHERE doc_id_src = '.$data["doc_id_src"].' AND doc_id_dst ='.$data["doc_id_dst"];
    		$this->_db->query($sql);    
    }
    
    /**
     * Recherche une entrée Flux_docdoc avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_docdoc.doc_id_src = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_docdoc avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_docdoc.doc_id_src = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Flux_docdoc avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_docdoc" => "flux_docdoc") );
                    
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
     * Récupère les spécifications des colonnes Flux_docdoc 
     */
    public function getCols(){

    	$arr = array("cols"=>array(
    	   	array("titre"=>"doc_id_src","champ"=>"doc_id_src","visible"=>true),
    	array("titre"=>"doc_id_dst","champ"=>"doc_id_dst","visible"=>true),
        	
    		));    	
    	return $arr;
		
    }     
    
    /*
     * Recherche une entrée Flux_docdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $doc_id_src
     */
    public function findBydoc_id_src($doc_id_src)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_docdoc") )                           
                    ->where( "f.doc_id_src = ?", $doc_id_src );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_docdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $doc_id_dst
     */
    public function findBydoc_id_dst($doc_id_dst)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_docdoc") )                           
                    ->where( "f.doc_id_dst = ?", $doc_id_dst );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

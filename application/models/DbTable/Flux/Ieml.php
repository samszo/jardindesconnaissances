<?php

/**
 * Classe ORM qui représente la table 'flux_ieml'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Model_DbTable_Flux_Ieml extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_ieml';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'ieml_id';

    
    /**
     * Vérifie si une entrée flux_ieml existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('ieml_id'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
	    	echo($k." = ".$v);       
		}
	    $rows = $this->fetchAll($select); 
	    if($rows->count()>0)$id=$rows[0]->ieml_id; else $id=false;
	    echo($id);       
	    return $id;
    } 
        
    /**
     * Ajoute une entrée flux_ieml.
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
    		if(!isset($data['maj']))$data['maj']= new Zend_Db_Expr('NOW()');
    	 	$id = $this->insert($data);
    	}
    	return $id;
    } 
           
    /**
     * Recherche une entrée flux_ieml avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_ieml.ieml_id = ' . $id);
    }
    
    /**
     * Recherche une entrée flux_ieml avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_ieml.ieml_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées flux_ieml avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_ieml" => "flux_ieml") );
                    
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
     * Recherche une entrée flux_ieml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $ieml_id
     */
    public function findByIeml_id($ieml_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_ieml") )                           
                    ->where( "f.ieml_id = ?", $ieml_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée flux_ieml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $code
     */
    public function findByCode($code)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_ieml") )                           
                    ->where( "f.code = ?", $code );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée flux_ieml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $desc
     */
    public function findByDesc($desc)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_ieml") )                           
                    ->where( "f.desc = ?", $desc );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée flux_ieml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $niveau
     */
    public function findByNiveau($niveau)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_ieml") )                           
                    ->where( "f.niveau = ?", $niveau );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée flux_ieml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $parent
     */
    public function findByParent($parent)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_ieml") )                           
                    ->where( "f.parent = ?", $parent );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée flux_ieml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param date $maj
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_ieml") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    /*
     * Recherche une entrée flux_ieml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param string $parse
     */
    public function findByParse($parse)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_ieml") )                           
                    ->where( "f.parse = ?", $parse);

        return $this->fetchAll($query)->toArray(); 
    }
    
}

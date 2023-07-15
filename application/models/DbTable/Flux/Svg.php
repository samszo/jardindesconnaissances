<?php

/**
 * Classe ORM qui représente la table 'flux_svg'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Model_DbTable_Flux_Svg extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_svg';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'svg_id';


    /**
     * valeur par défaut des datas d'un svg
     */
    var $defData = array('Model_DbTable_Flux_Doc'=>'{"style":"fill:#ffffff;fill-opacity:1;stroke:#000000;stroke-width:2;stroke-opacity:1;"}'
    	, 'Model_DbTable_Flux_Tag'=>'{"centroid":[788, 600], "size":160,"style":"fill:#ffffff;fill-opacity:1;stroke:#000000;stroke-width:2;stroke-opacity:1;"}'
    	, 'Model_DbTable_Flux_Exi'=>'{"centroid":[788, 300], "rx":460, "ry":200,"style":"fill:#ffffff;fill-opacity:1;stroke:#000000;stroke-width:2;stroke-opacity:1;"}'
    	, 'Model_DbTable_Flux_ExiTagDoc'=>'{"style":"fill:#ffffff;fill-opacity:1;stroke:#000000;stroke-width:2;stroke-opacity:1;"}'
    	);
    
    /**
     * Vérifie si une entrée Flux_Uti svgste.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('svg_id'));
		//seul le nom est obligatoire et discriminant
		$select->where('obj_id = ?', $data['obj_id']);
		$select->where('obj_type = ?', $data['obj_type']);
		$select->where('monade_id = ?', $data['monade_id']);
		$rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->svg_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_svg.
     *
     * @param array $data
     * @param boolean $svgste
     *  
     * @return integer
     */
    public function ajouter($data, $existe=true)
    {
    	$id=false;
    	if($existe)$id = $this->existe($data);
    	if(!$id){
    		if(!isset($data["maj"]))$data["maj"]= new Zend_Db_Expr('NOW()');
    		if(!isset($data["data"]))$data["data"]= $this->defData[$data['obj_type']];
    		$id = $this->insert($data);
    	}
    	return $id;
    } 
    
     
    /**
     * Recherche une entrée Flux_svg avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_svg.svg_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_svg avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('flux_svg.svg_id = '.$id);
    }
    
    /**
     * Récupère toutes les entrées Flux_svg avec certains critères
     * de tri, intervalles
     */
    public function getAll($cols=false, $order=null, $limit=0, $from=0)
    {
    	if($cols)
        	$query = $this->select()->from( array("flux_svg" => "flux_svg"), $cols);
    	else 
    		$query = $this->select()->from( array("flux_svg" => "flux_svg") );
                    
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
     * Recherche une entrée Flux_svg avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $svg_id
     */
    public function findBysvg_id($svg_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_svg") )                           
                    ->where( "f.svg_id = ?", $svg_id );

        return $this->fetchAll($query)->toArray(); 
    }
    

    /**
     * Recherche une entrée Flux_svg avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $type
     * @param int $id
     * 
     * @return Array
     */
    public function findByTypeId($type, $id)
    {
        $query = $this->select()
			->from( array("f" => "flux_svg") )                           
            ->where( "f.obj_type = ?", $type)
            ->where( "f.obj_id = ?", $id);

        return $this->fetchAll($query)->toArray(); 
    }

   
    
}

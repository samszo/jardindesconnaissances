<?php
/**
 * Ce fichier contient la classe Flux_UtiIeml.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_UtiIeml'.
 *
 * @copyright  201=& Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_UtiIeml extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_UtiIeml';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'uti_id';

    
    /**
     * Vérifie si une entrée Flux_UtiIeml existe.
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
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->uti_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_UtiIeml.
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
     * Recherche une entrée Flux_UtiIeml avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_UtiIeml.uti_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_UtiIeml avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_UtiIeml.uti_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Flux_UtiIeml avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_UtiIeml" => "flux_UtiIeml") );
                    
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
     * Récupère les spécifications des colonnes Flux_UtiIeml 
     */
    public function getCols(){

    	$arr = array("cols"=>array(
    	   	array("titre"=>"uti_id","champ"=>"uti_id","visible"=>true),
    	array("titre"=>"ieml_id","champ"=>"ieml_id","visible"=>true),
        	
    		));    	
    	return $arr;
		
    }     
    
    /*
     * Recherche une entrée Flux_UtiIeml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id
     */
    public function findByUti_id($uti_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_UtiIeml") )                           
                    ->where( "f.uti_id = ?", $uti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_UtiIeml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $ieml_id
     */
    public function findByIeml_id($ieml_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_UtiIeml") )                           
                    ->where( "f.ieml_id = ?", $ieml_id );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

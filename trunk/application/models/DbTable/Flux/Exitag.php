<?php
/**
 * Ce fichier contient la classe Flux_exitag.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_exitag'.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_ExiTag extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_exitag';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'exi_id';

    
    /**
     * Vérifie si une entrée Flux_exitag existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('exi_id'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->exi_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_exitag.
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
     * Recherche une entrée Flux_exitag avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_exitag.exi_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_exitag avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_exitag.exi_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Flux_exitag avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_exitag" => "flux_exitag") );
                    
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
     * Récupère les spécifications des colonnes Flux_exitag 
     */
    public function getCols(){

    	$arr = array("cols"=>array(
    	   	array("titre"=>"exi_id","champ"=>"exi_id","visible"=>true),
    	array("titre"=>"tag_id","champ"=>"tag_id","visible"=>true),
        	
    		));    	
    	return $arr;
		
    }     
    
    /*
     * Recherche une entrée Flux_exitag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $exi_id
     */
    public function findByExi_id($exi_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exitag") )                           
                    ->where( "f.exi_id = ?", $exi_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_exitag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id
     */
    public function findByTag_id($tag_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exitag") )                           
                    ->where( "f.tag_id = ?", $tag_id );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

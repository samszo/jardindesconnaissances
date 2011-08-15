<?php
/**
 * Ce fichier contient la classe Flux_Acti.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_Acti'.
 *
 * @copyright  201=& Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_Acti extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_Acti';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'acti_id';

    
    /**
     * Vérifie si une entrée Flux_Acti existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('acti_id'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->acti_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_Acti.
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
     * Recherche une entrée Flux_Acti avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_Acti.acti_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_Acti avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_Acti.acti_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Flux_Acti avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_Acti" => "flux_Acti") );
                    
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
     * Récupère les spécifications des colonnes Flux_Acti 
     */
    public function getCols(){

    	$arr = array("cols"=>array(
    	   	array("titre"=>"acti_id","champ"=>"acti_id","visible"=>true),
    	array("titre"=>"code","champ"=>"code","visible"=>true),
    	array("titre"=>"desc","champ"=>"desc","visible"=>true),
        	
    		));    	
    	return $arr;
		
    }     
    
    /*
     * Recherche une entrée Flux_Acti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $acti_id
     */
    public function findByActi_id($acti_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Acti") )                           
                    ->where( "f.acti_id = ?", $acti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Acti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $code
     */
    public function findByCode($code)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Acti") )                           
                    ->where( "f.code = ?", $code );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Acti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $desc
     */
    public function findByDesc($desc)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Acti") )                           
                    ->where( "f.desc = ?", $desc );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}
<?php
/**
 * Ce fichier contient la classe Flux_Uti.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_Uti'.
 *
 * @copyright  201=& Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_Uti extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_Uti';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'uti_id';

    
    /**
     * Vérifie si une entrée Flux_Uti existe.
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
     * Ajoute une entrée Flux_Uti.
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
     * Recherche une entrée Flux_Uti avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_Uti.uti_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_Uti avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_Uti.uti_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Flux_Uti avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_Uti" => "flux_Uti") );
                    
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
     * Récupère les spécifications des colonnes Flux_Uti 
     */
    public function getCols(){

    	$arr = array("cols"=>array(
    	   	array("titre"=>"uti_id","champ"=>"uti_id","visible"=>true),
    	array("titre"=>"login","champ"=>"login","visible"=>true),
    	array("titre"=>"maj","champ"=>"maj","visible"=>true),
    	array("titre"=>"flux","champ"=>"flux","visible"=>true),
        	
    		));    	
    	return $arr;
		
    }     
    
    /*
     * Recherche une entrée Flux_Uti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id
     */
    public function findByuti_id($uti_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Uti") )                           
                    ->where( "f.uti_id = ?", $uti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Uti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $login
     */
    public function findByLogin($login)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Uti") )                           
                    ->where( "f.login = ?", $login );
		$arr = $this->fetchAll($query)->toArray();
        return $arr[0]; 
    }
    /*
     * Recherche une entrée Flux_Uti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $maj
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Uti") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Uti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $flux
     */
    public function findByFlux($flux)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Uti") )                           
                    ->where( "f.flux = ?", $flux );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

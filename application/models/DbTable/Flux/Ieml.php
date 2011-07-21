<?php
/**
 * Ce fichier contient la classe Flux_Ieml.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_Ieml'.
 *
 * @copyright  201=& Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_Ieml extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_Ieml';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'ieml_id';

    
    /**
     * Vérifie si une entrée Flux_Ieml existe.
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
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->ieml_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_Ieml.
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
     * Recherche une entrée Flux_Ieml avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_Ieml.ieml_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_Ieml avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_Ieml.ieml_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Flux_Ieml avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_Ieml" => "flux_Ieml") );
                    
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
     * Récupère les spécifications des colonnes Flux_Ieml 
     */
    public function getCols(){

    	$arr = array("cols"=>array(
    	   	array("titre"=>"ieml_id","champ"=>"ieml_id","visible"=>true),
    	array("titre"=>"code","champ"=>"code","visible"=>true),
    	array("titre"=>"desc","champ"=>"desc","visible"=>true),
    	array("titre"=>"niveau","champ"=>"niveau","visible"=>true),
    	array("titre"=>"parent","champ"=>"parent","visible"=>true),
    	array("titre"=>"maj","champ"=>"maj","visible"=>true),
        	
    		));    	
    	return $arr;
		
    }     
    
    /*
     * Recherche une entrée Flux_Ieml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $ieml_id
     */
    public function findByIeml_id($ieml_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Ieml") )                           
                    ->where( "f.ieml_id = ?", $ieml_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Ieml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $code
     */
    public function findByCode($code)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Ieml") )                           
                    ->where( "f.code = ?", $code );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Ieml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $desc
     */
    public function findByDesc($desc)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Ieml") )                           
                    ->where( "f.desc = ?", $desc );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Ieml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $niveau
     */
    public function findByNiveau($niveau)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Ieml") )                           
                    ->where( "f.niveau = ?", $niveau );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Ieml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $parent
     */
    public function findByParent($parent)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Ieml") )                           
                    ->where( "f.parent = ?", $parent );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Ieml avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param date $maj
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Ieml") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

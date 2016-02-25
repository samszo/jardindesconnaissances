<?php
/**
 * Ce fichier contient la classe Flux_rapport.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
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
    	$this->delete('flux_rapport.rapport_id = ' . $id);
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

<?php
/**
 * Ce fichier contient la classe Gen_terminaisons.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Model_DbTable_Gen_terminaisons extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_terminaisons';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_trm';

    protected $_referenceMap    = array(
        'Lieux' => array(
            'columns'           => 'id_lieu',
            'refTableClass'     => 'Models_DbTable_Gevu_lieux',
            'refColumns'        => 'id_lieu'
        )
    );	

    public function obtenirConjugaisonByConjNum($idConj,$num)
    {
        $query = $this->select()
            ->where( "id_conj = ?",$idConj)
        	->where( "num = ?",$num)
        	;
		$r = $this->fetchRow($query);        
    	if (!$r) {
            return new Exception("La terminaison - $num - de la conjugaison - $idConj - n'a pas été trouvé");
        }
        return $r->toArray();
    }
    

    /**
     * Vérifie si une entrée Gen_terminaisons existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_trm'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_trm; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Gen_terminaisons.
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
     * Recherche une entrée Gen_terminaisons avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_terminaisons.id_trm = ' . $id);
    }
    
    /**
     * Recherche une entrée Gen_terminaisons avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('gen_terminaisons.id_trm = ' . $id);
    }

    /**
     * Recherche les entrées de Gen_terminaisons avec la clef de conj
     * et supprime ces entrées.
     *
     * @param integer $idConj
     *
     * @return int
     */
    public function removeConj($idConj)
    {
		return $this->delete('id_conj = ' . $idConj);
    }
    
    /**
     * Récupère toutes les entrées Gen_terminaisons avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_terminaisons" => "gen_terminaisons") );
                    
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
     * Recherche une entrée Gen_terminaisons avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_trm
     *
     * @return array
     */
    public function findById_trm($id_trm)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_terminaisons") )                           
                    ->where( "g.id_trm = ?", $id_trm );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_terminaisons avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_conj
     *
     * @return array
     */
    public function findById_conj($id_conj)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_terminaisons") )                           
                    ->where( "g.id_conj = ?", $id_conj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_terminaisons avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $num
     *
     * @return array
     */
    public function findByNum($num)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_terminaisons") )                           
                    ->where( "g.num = ?", $num );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_terminaisons avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lib
     *
     * @return array
     */
    public function findByLib($lib)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_terminaisons") )                           
                    ->where( "g.lib = ?", $lib );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

<?php
/**
 * Ce fichier contient la classe Gen_concepts_generateurs.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Model_DbTable_Gen_conceptsxgenerateurs extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_concepts_generateurs';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_concept';

    protected $_referenceMap    = array(
        'Lieux' => array(
            'columns'           => 'id_lieu',
            'refTableClass'     => 'Models_DbTable_Gevu_lieux',
            'refColumns'        => 'id_lieu'
        )
    );	
    
    /**
     * Vérifie si une entrée Gen_concepts_generateurs existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_concept'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_concept; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Gen_concepts_generateurs.
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
     * Recherche une entrée Gen_concepts_generateurs avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_concepts_generateurs.id_concept = ' . $id);
    }
    
    /**
     * Recherche une entrée Gen_concepts_generateurs avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return int
     */
    public function removeGen($id)
    {
    	return $this->delete('gen_concepts_generateurs.id_concept = ' . $id);
    }

    /**
     * Recherche une entrée Gen_concepts_generateurs avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idGen
     * @param integer $idCpt
     *
     * @return void
     */
    public function remove($idGen, $idCpt)
    {
    	$this->delete('gen_concepts_generateurs.id_concept = ' . $idCpt.' AND gen_concepts_generateurs.id_gen = '.$idGen);
    }
    
    /**
     * Récupère toutes les entrées Gen_concepts_generateurs avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_concepts_generateurs" => "gen_concepts_generateurs") );
                    
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
     * Recherche une entrée Gen_concepts_generateurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_concept
     *
     * @return array
     */
    public function findById_concept($id_concept)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_concepts_generateurs") )                           
                    ->where( "g.id_concept = ?", $id_concept );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_concepts_generateurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_gen
     *
     * @return array
     */
    public function findById_gen($id_gen)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_concepts_generateurs") )                           
                    ->where( "g.id_gen = ?", $id_gen );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

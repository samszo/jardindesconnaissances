<?php
/**
 * Ce fichier contient la classe Gen_concepts_adjectifs.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Model_DbTable_Gen_conceptsxadjectifs extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_concepts_adjectifs';
    
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
     * Vérifie si une entrée Gen_concepts_adjectifs existe.
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
     * Ajoute une entrée Gen_concepts_adjectifs.
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
     * Recherche une entrée Gen_concepts_adjectifs avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_concepts_adjectifs.id_concept = ' . $id);
    }

    /**
     * modifie des entrées avec les nouvelles données.
     *
     * @param array $data
     *
     * @return void
     */
    public function editMulti($data)
    {
    	$dbA = new Model_DbTable_Gen_adjectifs();
   		$dbA->editMulti($data);
    }
    
    
    /**
     * Recherche une entrée Gen_concepts_adjectifs avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id_concept
     *
     * @return void
     */
    public function remove($id_concept)
    {
    	//vérifie s'il faut supprimer les adjectifs liés
    	$arr = $this->findNbUseAdjByConcept($id_concept);
    	$dbA = new Model_DbTable_Gen_adjectifs();
    	foreach ($arr as $r) {
    		if($r['nb']==1){
    			$dbA->remove($r['id_adj']);	
    		}
    	}    	
    	$this->delete('gen_concepts_adjectifs.id_concept = ' . $id_concept);
    }

    /**
     * Recherche une entrée avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idGen
     * 
     * @return int
     */
    public function removeGen($id)
    {
    	return $this->delete('id_concept = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Gen_concepts_adjectifs avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_concepts_adjectifs" => "gen_concepts_adjectifs") );
                    
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
     * Recherche une entrée Gen_concepts_adjectifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_adj
     *
     * @return array
     */
    public function findById_adj($id_adj)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_concepts_adjectifs") )                           
                    ->where( "g.id_adj = ?", $id_adj );

        return $this->fetchAll($query)->toArray(); 
    }
        
	/**
     * Recherche une entrée Gen_concepts_adjectifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_concept
     *
     * @return array
     */
    public function findNbUseAdjByConcept($id_concept)
    {
    	$sql ="SELECT COUNT(DISTINCT cal.id_concept) nb, cal.id_adj
		FROM gen_concepts_adjectifs ca
			INNER JOIN gen_concepts_adjectifs cal ON cal.id_adj = ca.id_adj
			WHERE ca.id_concept = ".$id_concept." 
			GROUP BY cal.id_adj";
		$db = $this->getAdapter()->query($sql);
        return $db->fetchAll();
    }
    
}

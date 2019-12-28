<?php
/**
 * Ce fichier contient la classe Gen_negations.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Model_DbTable_Gen_negations extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_negations';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_negation';

    protected $_referenceMap    = array(
        'Verbe' => array(
            'columns'           => 'id_dico',
            'refTableClass'     => 'Model_DbTable_Gen_dicos',
            'refColumns'        => 'id_dico'
        )
    );	    

    public function obtenirNegationByDicoNum($idDico,$num)
    {
        $query = $this->select()
            ->where( "id_dico IN (?)",$idDico)
        	->where( "num = ?",$num)
            ;
		$r = $this->fetchRow($query);        
    	if (!$r) {
            throw new Exception("Count not find rs $idDico,$num");
        }
        return $r->toArray();
    }

    /**
     * Vérifie si une entrée est utilisée 
     *
     * @param int 		$idDico
     * @param string 	$num
     *
     * @return array
     */
    public function utilise($idDico, $num)
    {
    	$sql ="SELECT COUNT(DISTINCT g.id_gen) nbGen
    		, COUNT(DISTINCT oduA.id_oeu) nbOeu
			, COUNT(DISTINCT g.id_dico) nbDico
			, COUNT(DISTINCT oduA.uti_id) nbUti
			, GROUP_CONCAT(DISTINCT oduA.uti_id) idsUti
		FROM gen_negations n
			INNER JOIN gen_oeuvres_dicos_utis odu ON odu.id_dico = n.id_dico
			INNER JOIN gen_oeuvres_dicos_utis oduA ON oduA.id_oeu = odu.id_oeu
			LEFT JOIN gen_generateurs g ON g.valeur LIKE '%[".$num."_______|%]%' AND n.id_dico = oduA.id_dico
			WHERE n.num = ".$num." AND n.id_dico = ".$idDico."
			GROUP BY n.id_negation";
    	$db = $this->getAdapter()->query($sql);
    	return $db->fetchAll();
    
    }
    
    
    
    /**
     * Vérifie si une entrée Gen_negations existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_negation'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_negation; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Gen_negations.
     *
     * @param array $data
     * @param boolean $existe
     *  
     * @return integer
     */
    public function ajouter($data, $existe=true)
    {
    	if(!isset($data['num'])){
    		//recherche le dernier num du dictionnaire
    		$mn = $this->findMaxNumByIdDico($data['id_dico']);
    		$data['num'] = $mn[0]['maxNum']+1;
    	}
    	 
    	$id=false;
    	if($existe)$id = $this->existe($data);
    	if(!$id){
    	 	$id = $this->insert($data);
    	}
    	return $id;
    } 

    /**
     * Recherche le plus grand num pour un dictionnaire
     *
     * @param int $idDico
     *
     * @return array
     */
    public function findMaxNumByIdDico($idDico)
    {
    	$query = $this->select()
    	->from( array("g" => "gen_negations"),array("maxNum"=>"MAX(num)"))
    	->where( "g.id_dico = ?", $idDico);
    	 
    	return $this->fetchAll($query)->toArray();
    }
    
    
    
    /**
     * Recherche une entrée Gen_negations avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_negations.id_negation = ' . $id);
    }
    
    /**
     * Recherche une entrée Gen_negations avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('gen_negations.id_negation = ' . $id);
    }

    
    /**
     * Récupère toutes les entrées Gen_negations avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_negations" => "gen_negations") );
                    
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
     * Recherche une entrée Gen_negations avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_negation
     *
     * @return array
     */
    public function findById_negation($id_negation)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_negations") )                           
                    ->where( "g.id_negation = ?", $id_negation );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_negations avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_dico
     *
     * @return array
     */
    public function findByIdDico($id_dico)
    {
        $query = $this->select()
			->from( array("g" => "gen_negations") )                           
            ->where( "g.id_dico = ?", $id_dico );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_negations avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lib
     *
     * @return array
     */
    public function findByLib($lib)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_negations") )                           
                    ->where( "g.lib = ?", $lib );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_negations avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $num
     *
     * @return array
     */
    public function findByNum($num)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_negations") )                           
                    ->where( "g.num = ?", $num );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

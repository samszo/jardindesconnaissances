<?php
/**
 * Ce fichier contient la classe Gen_generateurs.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Model_DbTable_Gen_generateurs extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_generateurs';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_gen';

    protected $_referenceMap    = array(
        'Lieux' => array(
            'columns'           => 'id_lieu',
            'refTableClass'     => 'Models_DbTable_Gevu_lieux',
            'refColumns'        => 'id_lieu'
        )
    );	
    

    /**
     * Vérifie si une entrée est utilisée.
     *
     * @param int	$idGen
     *
     * @return array
     */
    public function utilise($idGen)
    {
    	$sql ="SELECT COUNT(DISTINCT g.id_gen) nbGen
    		, COUNT(DISTINCT oduA.id_oeu) nbOeu
			, COUNT(DISTINCT c.id_dico) nbDico
			, COUNT(DISTINCT oduA.uti_id) nbUti
			, GROUP_CONCAT(DISTINCT oduA.uti_id) idsUti
		FROM gen_concepts c
			INNER JOIN gen_oeuvres_dicos_utis odu ON odu.id_dico = c.id_dico
			INNER JOIN gen_oeuvres_dicos_utis oduA ON oduA.id_oeu = odu.id_oeu
			INNER JOIN gen_concepts_generateurs gc ON c.id_concept = gc.id_concept 
      INNER JOIN gen_generateurs g ON g.id_gen = gc.id_gen AND g.id_dico = oduA.id_dico 
			WHERE g.id_gen = ".$idGen."
			GROUP BY c.id_concept";
    	$db = $this->getAdapter()->query($sql);
    	return $db->fetchAll();
    
    }
    
    /**
     * Vérifie si une entrée Gen_generateurs existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_gen'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_gen; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Gen_generateurs.
     *
     * @param int $idCpt
     * @param array $data
     * @param boolean $existe
     * @param boolean $modif
     *  
     * @return integer
     */
    public function ajouter($idCpt, $data, $existe=true, $modif=false)
    {
    	
    	$id=false;
    	if($existe)$id = $this->existe($data);
    	if(!$id){
    	 	$id = $this->insert($data);
    	}elseif ($modif){
    		$this->edit($id, $data);
    	}
    	//création du lien entre le générateur et le concept
    	$dbCptGen = new Model_DbTable_Gen_conceptsxgenerateurs($this->_db);
    	$dbCptGen->ajouter(array("id_concept"=>$idCpt,"id_gen"=>$id));
    	
    	return $idCpt;
    } 
           
    /**
     * Recherche une entrée Gen_generateurs avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_generateurs.id_gen = ' . $id);
    }
    
    
    /**
     * Recherche une entrée Gen_generateurs avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idGen
     * @param integer $idCpt
     *
     * @return void
     */
    public function remove($idGen, $idCpt)
    {
    	$dbCptGen = new Model_DbTable_Gen_conceptsxgenerateurs();
    	$dbCptGen->remove($idGen, $idCpt);
    	
    	//vérifie si le générateur est lié à d'autres concepts
		$arr = $dbCptGen->findById_gen($idGen);
    	
		if(count($arr)==0){
			//supprime le generateur
			$this->delete('gen_generateurs.id_gen = ' . $idGen);				
		}
		
    }
    
    /**
     * Récupère toutes les entrées Gen_generateurs avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_generateurs" => "gen_generateurs") );
                    
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
     * Recherche une entrée Gen_verbes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_concept
     *
     * @return array
     */
    public function findByIdConcept($id_concept)
    {
        $query = $this->select()
        	->from( array("cg" => "gen_concepts_generateurs") )                           
	        ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        ->joinInner(array('g' => 'gen_generateurs'),
        		'g.id_gen = cg.id_gen')
        ->where("cg.id_concept = ?", $id_concept )
        ->order("g.valeur");
        
        return $this->fetchAll($query)->toArray(); 
    }
    
    	/**
     * Recherche une entrée Gen_generateurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_gen
     *
     * @return array
     */
    public function findById_gen($id_gen)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_generateurs") )                           
                    ->where( "g.id_gen = ?", $id_gen );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_generateurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_dico
     *
     * @return array
     */
    public function findById_dico($id_dico)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_generateurs") )                           
                    ->where( "g.id_dico = ?", $id_dico );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_generateurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $valeur
     *
     * @return array
     */
    public function findByValeur($valeur)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_generateurs") )                           
                    ->where( "g.valeur = ?", $valeur );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

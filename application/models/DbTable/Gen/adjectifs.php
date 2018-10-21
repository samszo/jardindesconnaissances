<?php
/**
 * Ce fichier contient la classe Gen_adjectifs.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Gen_adjectifs extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_adjectifs';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_adj';

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
     * @param int 		$idCpt
     * @param string	$val
     *
     * @return array
     */
    public function utilise($idCpt, $val)
    {
    	/**/
    	$sql ="SELECT COUNT(DISTINCT g.id_gen) nbGen
    		, COUNT(DISTINCT oduA.id_oeu) nbOeu
			, COUNT(DISTINCT g.id_dico) nbDico
			, COUNT(DISTINCT oduA.uti_id) nbUti
			, GROUP_CONCAT(DISTINCT oduA.uti_id) idsUti
		FROM gen_concepts c
			INNER JOIN gen_oeuvres_dicos_utis odu ON odu.id_dico = c.id_dico
			INNER JOIN gen_oeuvres_dicos_utis oduA ON oduA.id_oeu = odu.id_oeu
			LEFT JOIN gen_generateurs g ON g.valeur LIKE '%[a_".$val."]%' AND g.id_dico = oduA.id_dico
			WHERE c.id_concept = ".$idCpt." 
			GROUP BY c.id_concept";
		$db = $this->getAdapter()->query($sql);
        return $db->fetchAll();

    } 
    
    
    /**
     * Vérifie si une entrée Gen_adjectifs existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_adj'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_adj; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Gen_adjectifs.
     *
     * @param array $dCpt
     * @param array $dAdj
     * @param boolean $existe
     *  
     * @return array
     */
    public function ajouter($dCpt, $dAdj, $existe=true)
    {
    	$dbCpt = new Model_DbTable_Gen_concepts();
    	
    	//vérifie s'il faut créer le concept
    	if(!isset($dCpt['id_concept'])){
	    	//vérifie si le concept n'existe pas déjà
	    	$e = $dbCpt->existe($dCpt);
	    	if($e){
	    		//on ne peut pas créer deux concepts avec le même nom dans un dictionnaire
	    		return "Le concept existe déjà";
	    	}else{
	    		//création du concept
	    		$idCpt = $dbCpt->ajouter($dCpt, false);		
	    	}    		
    	}else 
	    	$idCpt = $dCpt['id_concept'];		
    	    	
    	//création de l'adjectif
    	$idAdj=false;
    	if($existe)$idAdj = $this->existe($dAdj);
    	if(!$idAdj){
    	 	$idAdj = $this->insert($dAdj);
    	}
    	//création du lien entre l'adjectif et le concept
    	$dbCptAdj = new Model_DbTable_Gen_conceptsxadjectifs();
    	$dbCptAdj->ajouter(array("id_concept"=>$idCpt,"id_adj"=>$idAdj));
    	
    	return $idCpt;
    } 
           
    /**
     * Recherche une entrée Gen_adjectifs avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {           	
    	$this->update($data, 'gen_adjectifs.id_adj = ' . $id);
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
   		foreach ($data as $c) {
   			$this->edit($c["id_adj"], $c["val"]);
   		}
    }
    
    /**
     * Recherche une entrée Gen_adjectifs avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('gen_adjectifs.id_adj = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Gen_adjectifs avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_adjectifs" => "gen_adjectifs") );
                    
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
     * Recherche une entrée Gen_adjectifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_adj
     *
     * @return array
     */
    public function findById_adj($id_adj)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_adjectifs") )                           
                    ->where( "g.id_adj = ?", $id_adj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_adjectifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_dico
     *
     * @return array
     */
    public function findById_dico($id_dico)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_adjectifs") )                           
                    ->where( "g.id_dico = ?", $id_dico );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_adjectifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $elision
     *
     * @return array
     */
    public function findByElision($elision)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_adjectifs") )                           
                    ->where( "g.elision = ?", $elision );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_adjectifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $prefix
     *
     * @return array
     */
    public function findByPrefix($prefix)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_adjectifs") )                           
                    ->where( "g.prefix = ?", $prefix );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_adjectifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $m_s
     *
     * @return array
     */
    public function findByM_s($m_s)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_adjectifs") )                           
                    ->where( "g.m_s = ?", $m_s );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_adjectifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $f_s
     *
     * @return array
     */
    public function findByF_s($f_s)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_adjectifs") )                           
                    ->where( "g.f_s = ?", $f_s );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_adjectifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $m_p
     *
     * @return array
     */
    public function findByM_p($m_p)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_adjectifs") )                           
                    ->where( "g.m_p = ?", $m_p );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_adjectifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $f_p
     *
     * @return array
     */
    public function findByF_p($f_p)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_adjectifs") )                           
                    ->where( "g.f_p = ?", $f_p );

        return $this->fetchAll($query)->toArray(); 
    }
    
	/**
     * Recherche une entrée Gen_adjectifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_concept
     *
     * @return array
     */
    public function findByIdConcept($id_concept)
    {
        $query = $this->select()
        	->from( array("ca" => "gen_concepts_adjectifs") )                           
	        ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        ->joinInner(array('a' => 'gen_adjectifs'),
        		'a.id_adj = ca.id_adj')
        ->where("ca.id_concept = ?", $id_concept );
        
        return $this->fetchAll($query)->toArray(); 
    }
    
}

<?php
/**
 * Ce fichier contient la classe Gen_verbes.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Model_DbTable_Gen_verbes extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_verbes';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_verbe';

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
    	$sql ="SELECT COUNT(DISTINCT g.id_gen) nbGen
    		, COUNT(DISTINCT oduA.id_oeu) nbOeu
			, COUNT(DISTINCT g.id_dico) nbDico
			, COUNT(DISTINCT oduA.uti_id) nbUti
			, GROUP_CONCAT(DISTINCT oduA.uti_id) idsUti
		FROM gen_concepts c
			INNER JOIN gen_oeuvres_dicos_utis odu ON odu.id_dico = c.id_dico
			INNER JOIN gen_oeuvres_dicos_utis oduA ON oduA.id_oeu = odu.id_oeu
			LEFT JOIN gen_generateurs g ON g.valeur LIKE '%".$val."%' AND g.id_dico = oduA.id_dico
			WHERE c.id_concept = ".$idCpt."
			GROUP BY c.id_concept";
		$db = $this->getAdapter()->query($sql);
        return $db->fetchAll();

    } 
            
    /**
     * Vérifie si une entrée Gen_verbes existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_verbe'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_verbe; else $id=false;
        return $id;
    } 

    /**
     * Ajoute une entrée Gen_syntagmes.
     *
     * @param array $data
     * @param boolean $existe
     * @param boolean $bData
     *
     * @return mixte
     */
    public function ajouter($data, $existe=true, $bData=false)
    {
    	$id=false;
    	if($existe)$id = $this->existe($data);
    	if(!$id){
    		$id = $this->insert($data);
    		$data['id_syn']=$id;
    	}
    	if ($bData)
    		return $data;
    	else
    		return $id;
    }
    
    /**
     * Ajoute une entrée Gen_verbes.
     *
     * @param array $dCpt
     * @param array $d
     * @param boolean $existe
     *
     * @return array
     */
    public function ajouterCpt($dCpt, $d, $existe=true)
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
    	 
    	$id = $this->ajouter($d);
    
    	//création du lien entre l'adjectif et le concept
    	$dbCptV = new Model_DbTable_Gen_conceptsxverbes();
    	$dbCptV->ajouter(array("id_concept"=>$idCpt,"id_verbe"=>$id));
    	 
    	return $idCpt;
    }
    
    /**
     * Recherche une entrée Gen_verbes avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_verbes.id_verbe = ' . $id);
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
   			$this->edit($c["id_verbe"], $c["val"]);
   		}
    }
    
    /**
     * Recherche une entrée Gen_verbes avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('gen_verbes.id_verbe = ' . $id);
    }

    /**
     * Recherche les entrées de Gen_verbes avec la clef de conj
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
     * Récupère toutes les entrées Gen_verbes avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_verbes" => "gen_verbes") );
                    
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
     * @param int $id_verbe
     *
     * @return array
     */
    public function findById_verbe($id_verbe)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_verbes") )                           
                    ->where( "g.id_verbe = ?", $id_verbe );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_verbes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_conj
     *
     * @return array
     */
    public function findById_conj($id_conj)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_verbes") )                           
                    ->where( "g.id_conj = ?", $id_conj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_verbes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_dico
     *
     * @return array
     */
    public function findById_dico($id_dico)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_verbes") )                           
                    ->where( "g.id_dico = ?", $id_dico );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_verbes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $elision
     *
     * @return array
     */
    public function findByElision($elision)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_verbes") )                           
                    ->where( "g.elision = ?", $elision );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_verbes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $prefix
     *
     * @return array
     */
    public function findByPrefix($prefix)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_verbes") )                           
                    ->where( "g.prefix = ?", $prefix );

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
        	->from( array("cv" => "gen_concepts_verbes") )                           
	        ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        ->joinInner(array('v' => 'gen_verbes'), 'v.id_verbe = cv.id_verbe')
        ->joinInner(array('c' => 'gen_conjugaisons'), 'c.id_conj = v.id_conj')
        ->joinInner(array('dc' => 'gen_dicos'), 'dc.id_dico = c.id_dico', array("dicoConj"=>"nom"))
        ->where("cv.id_concept = ?", $id_concept );
        
        return $this->fetchAll($query)->toArray(); 
    }
    
}

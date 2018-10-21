<?php
/**
 * Ce fichier contient la classe Gen_syntagmes.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Model_DbTable_Gen_syntagmes extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_syntagmes';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_syn';

    protected $_referenceMap    = array(
        'Lieux' => array(
            'columns'           => 'id_lieu',
            'refTableClass'     => 'Models_DbTable_Gevu_lieux',
            'refColumns'        => 'id_lieu'
        )
    );	
    
    /**
     * Vérifie si une entrée Gen_syntagmes existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_syn'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_syn; else $id=false;
        return $id;
    } 

    /**
     * Vérifie si une entrée Gen_syntagmes est utilisée.
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
		FROM gen_syntagmes s
			INNER JOIN gen_oeuvres_dicos_utis odu ON odu.id_dico = s.id_dico
			INNER JOIN gen_oeuvres_dicos_utis oduA ON oduA.id_oeu = odu.id_oeu
			LEFT JOIN gen_generateurs g ON g.valeur LIKE '%[".$num."#]%' AND g.id_dico = oduA.id_dico
			WHERE s.num = ".$num." AND s.id_dico = ".$idDico."
			GROUP BY s.id_syn";
		$db = $this->getAdapter()->query($sql);
        return $db->fetchAll();

    } 

    /**
     * Vérifie si une entrée Gen_syntagmes est utilisée.
     *
     * @param int 		$idDico
     * @param string 	$val
     *
     * @return array
     */
    public function utiliseCpt($idCpt, $val)
    {
    	$dbC = new Model_DbTable_Gen_concepts();
    	return $dbC->utilise($idCpt, $val);
    
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
    	if(!isset($data['num'])){
    		//recherche le dernier num du dictionnaire
    		$mn = $this->findMaxNumByIdDico($data['id_dico']);
    		$data['num'] = $mn[0]['maxNum']+1;
    	}
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
     * Ajoute un syntagme
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
    	$dbCptSyn = new Model_DbTable_Gen_conceptsxsyntagmes();
    	$dbCptSyn->ajouter(array("id_concept"=>$idCpt,"id_syn"=>$id));
    	
    	return $idCpt;
    } 
    
    /**
     * Recherche une entrée Gen_syntagmes avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_syntagmes.id_syn = ' . $id);
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
   			$this->edit($c["id_syn"], $c["val"]);
   		}
    }

    /**
     * Recherche une entrée Gen_syntagmes avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('gen_syntagmes.id_syn = ' . $id);
    }

    
    /**
     * Récupère toutes les entrées Gen_syntagmes avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_syntagmes" => "gen_syntagmes") );
                    
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
     * @param string $idsDico
     *
     * @return array
     */
    public function findByIdConcept($id_concept, $idsDico)
    {
        $query = $this->select()
        	->from( array("cs" => "gen_concepts_syntagmes") )                           
	        ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        ->joinInner(array('s' => 'gen_syntagmes'),
        		's.id_syn = cs.id_syn')
        ->where("cs.id_concept = ?", $id_concept );
        //->where("s.id_dico IN (".$idsDico.")" );
        
        return $this->fetchAll($query)->toArray(); 
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
			->from( array("g" => "gen_syntagmes"),array("maxNum"=>"MAX(num)"))                           
            ->where( "g.id_dico = ?", $idDico);

        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche une entrée Gen_syntagmes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_syn
     *
     * @return array
     */
    public function findById_syn($id_syn)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_syntagmes") )                           
                    ->where( "g.id_syn = ?", $id_syn );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_syntagmes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_dico
     *
     * @return array
     */
    public function findByIdDico($id_dico)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_syntagmes") )                           
                    ->where( "g.id_dico = ?", $id_dico );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_syntagmes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $num
     *
     * @return array
     */
    public function findByNum($num)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_syntagmes") )                           
                    ->where( "g.num = ?", $num );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_syntagmes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $ordre
     *
     * @return array
     */
    public function findByOrdre($ordre)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_syntagmes") )                           
                    ->where( "g.ordre = ?", $ordre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_syntagmes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lib
     *
     * @return array
     */
    public function findByLib($lib)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_syntagmes") )                           
                    ->where( "g.lib = ?", $lib );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

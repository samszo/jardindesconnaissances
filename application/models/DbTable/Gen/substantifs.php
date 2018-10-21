<?php
/**
 * Ce fichier contient la classe Gen_substantifs.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Model_DbTable_Gen_substantifs extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_substantifs';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_sub';

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
			LEFT JOIN gen_generateurs g ON g.valeur LIKE '%[m_".$val."]%' AND g.id_dico = oduA.id_dico
			WHERE c.id_concept = ".$idCpt." 
			GROUP BY c.id_concept";
		$db = $this->getAdapter()->query($sql);
        return $db->fetchAll();

    } 
    
    /**
     * Vérifie si une entrée Gen_substantifs existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_sub'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_sub; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Gen_adjectifs.
     *
     * @param array $dCpt
     * @param array $dSub
     * @param boolean $existe
     *  
     * @return array
     */
    public function ajouter($dCpt, $dSub, $existe=true)
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
    	
    	
    	//création du substantif
    	$idSub=false;
    	if($existe)$idSub = $this->existe($dSub);
    	if(!$idSub){
    	 	$idSub = $this->insert($dSub);
    	}
    	//création du lien entre le substantif et le concept
    	$dbCptSub = new Model_DbTable_Gen_conceptsxsubstantifs();
    	$dbCptSub->ajouter(array("id_concept"=>$idCpt,"id_sub"=>$idSub));
    	
    	return $idCpt;
    } 
               
    /**
     * Recherche une entrée Gen_substantifs avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_substantifs.id_sub = ' . $id);
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
   			$this->edit($c["id_sub"], $c["val"]);
   		}
    }
    
    /**
     * Recherche une entrée Gen_substantifs avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	//supprime le lien avec le concept
    	$dbCS = new Model_DbTable_Gen_conceptsxsubstantifs();
    	$dbCS->delete('id_sub = ' . $id);
    	//supprime le substantif
    	$this->delete('gen_substantifs.id_sub = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Gen_substantifs avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_substantifs" => "gen_substantifs") );
                    
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
     * Recherche une entrée Gen_substantifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_sub
     *
     * @return array
     */
    public function findById_sub($id_sub)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_substantifs") )                           
                    ->where( "g.id_sub = ?", $id_sub );

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
        	->from( array("cs" => "gen_concepts_substantifs") )                           
	        ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        ->joinInner(array('s' => 'gen_substantifs'),
        		's.id_sub = cs.id_sub')
        ->where("cs.id_concept = ?", $id_concept )
        ->order("s.prefix");
        
        return $this->fetchAll($query)->toArray(); 
    }
    
    	/**
     * Recherche une entrée Gen_substantifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_dico
     *
     * @return array
     */
    public function findById_dico($id_dico)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_substantifs") )                           
                    ->where( "g.id_dico = ?", $id_dico );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_substantifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $elision
     *
     * @return array
     */
    public function findByElision($elision)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_substantifs") )                           
                    ->where( "g.elision = ?", $elision );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_substantifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $prefix
     *
     * @return array
     */
    public function findByPrefix($prefix)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_substantifs") )                           
                    ->where( "g.prefix = ?", $prefix );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_substantifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $genre
     *
     * @return array
     */
    public function findByGenre($genre)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_substantifs") )                           
                    ->where( "g.genre = ?", $genre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_substantifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $s
     *
     * @return array
     */
    public function findByS($s)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_substantifs") )                           
                    ->where( "g.s = ?", $s );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_substantifs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $p
     *
     * @return array
     */
    public function findByP($p)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_substantifs") )                           
                    ->where( "g.p = ?", $p );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

<?php
/**
 * Ce fichier contient la classe Gen_dicos.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Model_DbTable_Gen_dicos extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_dicos';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_dico';

    protected $_dependentTables = array(
       	"Model_DbTable_Gen_adjectifs"
       	,"Model_DbTable_Gen_complements"
       	,"Model_DbTable_Gen_concepts"
       	,"Model_DbTable_Gen_conjugaisons"
       	,"Model_DbTable_Gen_determinants"
       	,"Model_DbTable_Gen_dicosxdicos"
        ,"Model_DbTable_Gen_dicosxutisxroles"
        ,"Model_DbTable_Gen_generateurs"
        ,"Model_DbTable_Gen_negations"
        ,"Model_DbTable_Gen_pronoms"
        ,"Model_DbTable_Gen_substantifs"
        ,"Model_DbTable_Gen_syntagmes"
        ,"Model_DbTable_Gen_verbes"
        ,"Model_DbTable_Gen_oeuvresxdicosxutis"
        );    
    
    /**
     * Vérifie si une entrée Gen_dicos existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_dico'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_dico; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Gen_dicos.
     *
     * @param array $data
     * @param int $idUti
     * @param boolean $existe
     *  
     * @return integer
     */
    public function ajouter($data, $idUti, $existe=true)
    {
    	
    	$id=false;
    	if($existe)$id = $this->existe($data);
    	if(!$id){
    	 	$id = $this->insert($data);
    	}
    	//vérifie s'il faut créer le lien avec l'utilisateur
    	if($idUti){
    		$dbDUR = new Model_DbTable_Gen_dicosxutisxroles();
    		$dbDUR->ajouter(array("id_dico"=>$id,"uti_id"=>$idUti,"id_role"=>4));
    	}
    	
    	return $id;
    } 
           
    /**
     * Recherche une entrée Gen_dicos avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_dicos.id_dico = ' . $id);
    }
    
    /**
     * Recherche une entrée Gen_dicos avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return int
     */
    public function remove($id)
    {
		//suppression des données lieés
        $dt = $this->getDependentTables();
        $nbTot = 0;
        foreach($dt as $t){
        	$dbT = new $t($this->_db);
        	if($t=="Model_DbTable_Gen_concepts" || $t=="Model_DbTable_Gen_conjugaisons" )
	        	$nb = $dbT->removeDico($id);
	        elseif ($t=="Model_DbTable_Gen_dicosxdicos"){
	        	$nb = $dbT->delete('id_dico_gen = ' . $id); 
	        	$nb = $dbT->delete('id_dico_ref = ' . $id); 
	        }else
	        	$nb = $dbT->delete('id_dico = ' . $id);
	        echo $t." : $nb supprimé(s) ! <br/>";
	        $nbTot += $nb;
        }        
       	
    	$nbTot += $this->delete('gen_dicos.id_dico = ' . $id);
    	
    	return $nbTot;
    }
    
    /**
     * Récupère toutes les entrées Gen_dicos avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_dicos" => "gen_dicos") );
                    
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
     * Recherche une entrée Gen_dicos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_dico
     *
     * @return array
     */
    public function findByIdDico($id_dico)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_dicos") )                           
                    ->where( "g.id_dico = ?", $id_dico );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_dicos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $nom
     *
     * @return array
     */
    public function findByNom($nom)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_dicos") )                           
                    ->where( "g.nom = ?", $nom );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_dicos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url
     *
     * @return array
     */
    public function findByUrl($url)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_dicos") )                           
                    ->where( "g.url = ?", $url );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_dicos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $type
     *
     * @return array
     */
    public function findByType($type)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_dicos") )                           
                    ->where( "g.type = ?", $type );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_dicos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_dicos") )                           
                    ->where( "g.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_dicos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url_source
     *
     * @return array
     */
    public function findByUrl_source($url_source)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_dicos") )                           
                    ->where( "g.url_source = ?", $url_source );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_dicos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $path_source
     *
     * @return array
     */
    public function findByPath_source($path_source)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_dicos") )                           
                    ->where( "g.path_source = ?", $path_source );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_dicos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $langue
     *
     * @return array
     */
    public function findByLangue($langue)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_dicos") )                           
                    ->where( "g.langue = ?", $langue );

        return $this->fetchAll($query)->toArray(); 
    }
    /**
     * retourne les différentes langues du dictiionnaire
     *
     *
     * @return array
     */
    public function getLangue()
    {
    	$query = $this->select()
	    	->distinct()
	    	->from(array('d' => 'gen_dicos'), 'langue')
    		->order('langue');
    
    	return $this->fetchAll($query)->toArray();
    }
    /**
     * retourne les différentes type de dictiionnaire
     *
     *
     * @return array
     */
    public function getType()
    {
    	$query = $this->select()
    	->distinct()
    	->from(array('d' => 'gen_dicos'), 'type')
    	->order('type');
    	 
    	return $this->fetchAll($query)->toArray();
    }    
    
    /**
     * exporte un dictionnaire au format csv
     * 
     * @param int 		$idDico
     * @param string 	$type
     * @param int 		$idCpt
     *
     * @return array
     */
    public function exporter($idDico, $type, $idCpt=false)
    {
    	if($idCpt)
			$where = " WHERE c.id_concept = ".$idCpt;
		else 
			$where = " WHERE d.id_dico = ".$idDico;
    	
		$sql = "SELECT c.lib as concept, c.type ";
		switch ($type) {
			case "gen":
				$sql .= ", g.valeur
					FROM gen_dicos d
					INNER JOIN gen_concepts c ON d.id_dico = c.id_dico
					INNER JOIN gen_concepts_generateurs cg ON c.id_concept = cg.id_concept
					INNER JOIN gen_generateurs g ON cg.id_gen = g.id_gen";
			break;
			case "adj":
				$sql .= ", a.elision, a.prefix, a.m_s, a.f_s, a.m_p, a.f_p
					FROM gen_dicos d
					INNER JOIN gen_concepts c ON d.id_dico = c.id_dico
					INNER JOIN gen_concepts_adjectifs ca ON c.id_concept = ca.id_concept
					INNER JOIN gen_adjectifs a ON a.id_adj = ca.id_adj";
				break;
			case "sub":
				$sql .= ", s.elision, s.prefix, s.genre, s.s, s.p
					FROM gen_dicos d
					INNER JOIN gen_concepts c ON d.id_dico = c.id_dico
					INNER JOIN gen_concepts_substantifs cs ON c.id_concept = cs.id_concept
					INNER JOIN gen_substantifs s ON s.id_sub = cs.id_sub";
			break;
			case "ver":
				$sql .= ", v.id_conj, v.elision, v.prefix			
				FROM gen_dicos d
				INNER JOIN gen_concepts c ON d.id_dico = c.id_dico
				INNER JOIN gen_concepts_verbes cv ON c.id_concept = cv.id_concept
				INNER JOIN gen_verbes v ON v.id_verbe = cv.id_verbe";
			break;
		}	
		$sql .= $where;	
		$stmt = $this->_db->query($sql);
    	return $stmt->fetchAll();
    }    
    
}

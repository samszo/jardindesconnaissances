<?php
/**
 * Ce fichier contient la classe Gen_conjugaisons.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Model_DbTable_Gen_conjugaisons extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_conjugaisons';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_conj';

    protected $_referenceMap    = array(
        'Dicos' => array(
            'columns'           => 'id_dico',
            'refTableClass'     => 'Model_DbTable_Gen_dicos',
            'refColumns'        => 'id_dico'
        )
    );	

    /**
     * Vérifie si une entrée est utilisée.
     *
     * @param int 		$idDico
     * @param int 		$idConj
     *
     * @return array
     */
    public function utilise($idDico, $idConj)
    {
    	/**TODO TROP GOURMAND : optimiser en indexant les generateur par les identifiants qui les composent
    	 *  
    	 */
    	$sql ="SELECT COUNT(DISTINCT g.id_gen) nbGen
    		, COUNT(DISTINCT oduA.id_oeu) nbOeu
			, COUNT(DISTINCT g.id_dico) nbDico
			, COUNT(DISTINCT oduA.uti_id) nbUti
			, GROUP_CONCAT(DISTINCT oduA.uti_id) idsUti
		FROM gen_conjugaisons c
			INNER JOIN gen_verbes v ON v.id_conj = c.id_conj
			INNER JOIN gen_oeuvres_dicos_utis odu ON odu.id_dico = v.id_dico
			INNER JOIN gen_oeuvres_dicos_utis oduA ON oduA.id_oeu = odu.id_oeu
			LEFT JOIN gen_generateurs g ON g.valeur LIKE CONCAT('%v_',v.prefix,'%') AND g.id_dico = oduA.id_dico
			WHERE c.id_conj = ".$idConj." AND c.id_dico = ".$idDico."
			GROUP BY c.id_conj";
		$db = $this->getAdapter()->query($sql);
        return $db->fetchAll();

    } 
    
    
    /**
     * Vérifie si une entrée Gen_conjugaisons existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_conj'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_conj; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Gen_conjugaisons.
     *
     * @param array $data
     * @param array $terms
     *  
     * @return integer
     */
    public function ajouter($data, $terms)
    {
    	
		if(!isset($data['num'])){
	    	//recherche le dernier num du dictionnaire
	    	$mn = $this->findMaxNumByIdDico($data['id_dico']);
	    	$data['num'] = $mn[0]['maxNum']+1;
	    }    		
    	$id = $this->insert($data);
    		
	    $dbTerm = new Model_DbTable_Gen_terminaisons();        
	   	foreach ($terms as $k=>$t) {
	   			$dbTerm->ajouter(array("id_conj"=>$id,"num"=>$k,"lib"=>$t),false);
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
			->from( array("g" => "gen_conjugaisons"),array("maxNum"=>"MAX(num)"))                           
            ->where( "g.id_dico = ?", $idDico);

        return $this->fetchAll($query)->toArray(); 
    }
    
    
    /**
     * Recherche une entrée Gen_conjugaisons avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
    	$this->update($data, 'gen_conjugaisons.id_conj = ' . $id);
    }
    
    /**
     * Recherche une entrée Gen_conjugaisons avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param array $data
     *
     * @return void
     */
    public function editTerms($data)
    {
    	$dbTerm = new Model_DbTable_Gen_terminaisons();        
   		foreach ($data as $c) {
   			$dbTerm->edit($c["id_trm"], array("lib"=>$c["lib"]));
   		}
    }
    
    /**
     * Recherche une entrée Gen_conjugaisons avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return int
     */
    public function remove($id)
    {
    	$nb = 0;
    	//suprime les terminaisons
    	$dbTerms = new Model_DbTable_Gen_terminaisons();
    	$nb += $dbTerms->removeConj($id);
    	//suprime les verbes
    	$dbV = new Model_DbTable_Gen_verbes();
    	$nb += $dbV->removeConj($id);
    	//suprime la conjugaison
    	$nb += $this->delete('gen_conjugaisons.id_conj = ' . $id);
    	
    	return $nb;
    }

    /**
     * Recherche une entrée Gen_conjugaisons avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idDico
     *
     * @return void
     */
    public function removeDico($idDico)
    {
    	$arr = $this->findByIdDico($idDico);
    	foreach ($arr as $v) {
    		$this->remove($v["id_conj"]);
    	}
    }
    
    /**
     * Récupère toutes les entrées Gen_conjugaisons avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
        $query = $this->select()
            ->distinct()        
            ->from( array("c" => "gen_conjugaisons") )
            ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->joinInner(array('cc' => 'gen_conjugaisons'),'c.id_conj = cc.id_conj',array('recid'=>'id_conj'))			
            ;
                    
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
     * Recherche une entrée Gen_conjugaisons avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_conj
     *
     * @return array
     */
    public function findById_conj($id_conj)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_conjugaisons") )                           
                    ->where( "g.id_conj = ?", $id_conj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_conjugaisons avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_dico
     *
     * @return array
     */
    public function findByIdDico($id_dico)
    {
        $query = $this->select()
			->from( array("g" => "gen_conjugaisons") )                           
            ->where( "g.id_dico = ?", $id_dico )
        	->order("modele");

        return $this->fetchAll($query)->toArray(); 
    }
   	/**
     * Recherche une entrée Gen_conjugaisons avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_dico
     *
     * @return array
     */
    public function findByIdDicoVerbe($id_dico)
    {
        $query = $this->select()
			->from( array("g" => "gen_conjugaisons") )                           
	        ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
	        ->joinInner(array('dd' => 'gen_dicos_dicos'),
	        		'dd.id_dico_ref = g.id_dico')
	        ->where( "dd.id_dico_gen = ?", $id_dico )
	        ->order("modele");
            
        return $this->fetchAll($query)->toArray(); 
    }
    /**
     * Recherche les terminaisons associées à un modèle
     * et retourne ces entrées.
     *
     * @param int $id_conj
     *
     * @return array
     */
    public function findTermByIdConj($id_conj)
    {
    	$query = $this->select()
    	->from( array("c" => "gen_conjugaisons"),array())
    	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
    		->joinInner(array('t' => 'gen_terminaisons'), 't.id_conj = c.id_conj', array("num","id_trm","lib"))
    	->where( "c.id_conj = ?", $id_conj)
    	->order("t.num");
    
    	return $this->fetchAll($query)->toArray();
    }

    /**
     * Recherche les verbes associée à un modèle
     * et retourne ces entrées.
     *
     * @param int $id_conj
     *
     * @return array
     */
    public function findVerbeByIdConj($id_conj)
    {
    	$query = $this->select()
    	->from( array("c" => "gen_conjugaisons"),array())
    	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
    	->joinInner(array('v' => 'gen_verbes'), 'v.id_conj = c.id_conj', array("id_verbe","elision","prefix"))
    	->joinInner(array('cv' => 'gen_concepts_verbes'), 'cv.id_verbe = v.id_verbe', array())
    	->joinInner(array('cpt' => 'gen_concepts'), 'cpt.id_concept = cv.id_concept', array("lib","id_concept"))
    	->where( "c.id_conj = ?", $id_conj)
    	->order("cpt.lib");
    
    	return $this->fetchAll($query)->toArray();
    }
    
        
    /**
     * Recherche une entrée Gen_conjugaisons avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $num
     *
     * @return array
     */
    public function findByNum($num)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_conjugaisons") )                           
                    ->where( "g.num = ?", $num );

        return $this->fetchAll($query)->toArray(); 
    }
    /**
     * Recherche une entrée Gen_conjugaisons avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $modele
     *
     * @return array
     */
    public function findByModele($modele)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_conjugaisons") )                           
                    ->where( "g.modele = ?", $modele );

        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche une entrée Gen_conjugaisons avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int 		$idDico
     * @param varchar 	$modele
     *
     * @return array
     */
    public function findByDicoModele($idDico, $modele)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_conjugaisons") )                           
                    ->where( "g.id_dico =".$idDico." AND g.modele = ?", $modele );

        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche une entrée Gen_conjugaisons avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int 		$idDico
     * @param varchar 	$num
     *
     * @return array
     */
    public function findByDicoNum($idDico, $num)
    {
        $query = $this->select()
			->from( array("g" => "gen_conjugaisons") )                           
            ->where( "g.id_dico =".$idDico." AND g.num = ?", $num);

        return $this->fetchAll($query)->toArray(); 
    }
    
}

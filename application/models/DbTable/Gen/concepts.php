<?php
/**
 * Ce fichier contient la classe Gen_concepts.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Gen
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Model_DbTable_Gen_concepts extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_concepts';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_concept';

    protected $_referenceMap    = array(
        'Dicos' => array(
            'columns'           => 'id_dico',
            'refTableClass'     => 'Model_DbTable_Gen_dicos',
            'refColumns'        => 'id_dico'
        )
    );	

    protected $_dependentTables = array(
       "Model_DbTable_Gen_conceptsxadjectifs"
       ,"Model_DbTable_Gen_conceptsxgenerateurs"
       ,"Model_DbTable_Gen_conceptsxsubstantifs"
       ,"Model_DbTable_Gen_conceptsxsyntagmes"
       ,"Model_DbTable_Gen_conceptsxverbes"
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
    	/**TODO TROP GOURMAND : optimiser en indexant les generateur par les identifiants qui les composent
    	 *  
    	 */
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
     * Vérifie si une entrée Gen_concepts existe.
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
     * Ajoute une entrée Gen_concepts.
     *
     * @param array $data
     * @param boolean $existe
     * @param boolean $obj
     *  
     * @return integer
     */
    public function ajouter($data, $existe=true, $obj=false)
    {
    	
    	$id=false;
    	if($existe)$id = $this->existe($data);
    	if(!$id){
    	 	$id = $this->insert($data);
    	}
    	if($obj){
    		$arr = $this->findById_concept($id);
    		return $arr[0];
    	}else
    		return $id;
    } 
           
    /**
     * Recherche une entrée Gen_concepts avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_concepts.id_concept = ' . $id);
    }
    
    /**
     * Recherche une entrée Gen_concepts avec la clef primaire spécifiée
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
        $nb = 0;
        foreach($dt as $t){
        	$dbT = new $t($this->_db);
        	$nb += $dbT->removeGen($id);
        }        
    	$nb += $this->delete('gen_concepts.id_concept = ' . $id);
    	return $nb;
    }

    /**
     * Recherche une entrée Gen_concepts avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idDico
     *
     * @return int
     */
    public function removeDico($idDico)
    {
        $arr = $this->findByIdDico($idDico);
        $nb = 0;
        foreach($arr as $v){
        	$nb += $this->remove($v["id_concept"]);
        }
        return $nb;        
    }
    
    /**
     * Récupère toutes les entrées Gen_concepts avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_concepts" => "gen_concepts") );
                    
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
     * Recherche une entrée Gen_concepts avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_concept
     *
     * @return array
     */
    public function findById_concept($id_concept)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_concepts") )                           
                    ->where( "g.id_concept = ?", $id_concept );

        return $this->fetchAll($query)->toArray(); 
    }
    /**
     * Recherche une entrée Gen_concepts avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param string $idsDicos
     *
     * @return array
     */
    public function findByIdDico($idsDicos)
    {
        $query = $this->select()
			->from( array("g" => "gen_concepts") )                           
            ->where( "g.id_dico IN (".$idsDicos.")" )
            ->order("lib");

        $rows = $this->fetchAll($query)->toArray();
        return $rows; 
    }

    /**
     * Recherche toutes les entrées conceptuelles pour les dictionnaires de
     * - concepts
     * - syntagmes
     * - pronoms
     * et retourne ces entrées.
     *
     * @param array		$dicos
     * @param boolean	$merge
     *
     * @return array
     */
    public function findAllByDicos($dicos, $merge=true)
    {

        //récupère les concepts
    	$sql ="SELECT id_concept as id
    		, lib
			, type
			, 0 as num 
    		, '' as ordre	  
		FROM gen_concepts c
		WHERE c.id_dico IN (".$dicos["concepts"].")";
		$smtp = $this->_db->query($sql);
        $concepts = $smtp->fetchAll();

        //récupère les syntagmes
    	$sql ="SELECT id_syn as id
    		, lib
			, 'syntagmes' as type
			, num
    		, ordre 
		FROM gen_syntagmes 
		WHERE id_dico IN (".$dicos["syntagmes"].")";
		$smtp = $this->_db->query($sql);
        $syns = $smtp->fetchAll();

        //récupère les pronoms
    	$sql ="SELECT id_pronom as id
    		, lib
			, CONCAT('pronom ',type) as type
			, num 
    		, '' as ordre	  
		FROM gen_pronoms 
		WHERE id_dico IN (".$dicos["pronoms"].") AND type='complément'";
		$smtp = $this->_db->query($sql);
        $prosComp = $smtp->fetchAll();

    	$sql ="SELECT id_pronom as id
    		, lib
			, CONCAT('pronom ',type) as type
			, num 
    		, '' as ordre	  
		FROM gen_pronoms 
		WHERE id_dico IN (".$dicos["pronoms"].") AND type='sujet'";
		$smtp = $this->_db->query($sql);
        $prosSujet = $smtp->fetchAll();

    	$sql ="SELECT id_pronom as id
    		, lib
			, CONCAT('pronom ',type) as type
			, num 
    		, '' as ordre	  
		FROM gen_pronoms 
		WHERE id_dico IN (".$dicos["pronoms"].") AND type='sujet_indefini'";
		$smtp = $this->_db->query($sql);
        $prosSujetInd = $smtp->fetchAll();

        //récupère les déterminants
    	$sql ="SELECT id_dtm as id
    		, lib
			, 'déterminant' as type
			, num 
    		, ordre	
		FROM gen_determinants 
		WHERE id_dico IN (".$dicos["déterminants"].")";
		$smtp = $this->_db->query($sql);
        $dets = $smtp->fetchAll();
        
        //récupère les négations
    	$sql ="SELECT id_negation as id
    		, lib
			, 'négation' as type
			, num
    		, '' as ordre	  
		FROM gen_negations 
		WHERE id_dico IN (".$dicos["negations"].")";
		$smtp = $this->_db->query($sql);
        $negs = $smtp->fetchAll();
        $nb = count($concepts)+count($syns)+count($pros)+count($dets)+count($negs);
        if($merge)
	        $rows = array_merge($concepts, $syns, $prosComp, $prosSujet, $prosSujetInd, $dets, $negs);
    	else
    		$rows = array("concepts"=>$concepts,"syntagmes"=>$syns,"pronomsComp"=>$prosComp, "pronomsSujet"=>$prosSujet, "pronomsSujetInd"=>$prosSujetInd,"determinants"=>$dets,"negations"=>$negs);
    		
        return $rows; 
    }
    

    /**
     * Recherche les entrées Gen_concepts dans un dictionnaire
     *
     * @param interger  $idDico
     * @param varchar   $type
     *
     * @return array
     */
    public function getAllByDico($idDico)
    {
        $sql = "SELECT 
            c.id_concept recid, c.id_concept, c.lib, c.type, COUNT(c.id_concept) nbForme
        FROM
            gen_concepts c
                LEFT JOIN
            gen_concepts_adjectifs ca ON ca.id_concept = c.id_concept
                LEFT JOIN
            gen_concepts_substantifs csu ON csu.id_concept = c.id_concept
                LEFT JOIN
            gen_concepts_syntagmes csy ON csy.id_concept = c.id_concept
                LEFT JOIN
            gen_concepts_verbes cv ON cv.id_concept = c.id_concept
        WHERE
            c.id_dico = ".$idDico."
        GROUP BY c.id_concept
        ORDER BY c.lib, c.type";
        $smtp = $this->_db->query($sql);
        return $smtp->fetchAll();

    }
    


    /**
     * Recherche une entrée Gen_concepts avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lib
     *
     * @return array
     */
    public function findByLib($lib)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_concepts") )                           
                    ->where( "g.lib = ?", $lib );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_concepts avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $type
     *
     * @return array
     */
    public function findByType($type)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_concepts") )                           
                    ->where( "g.type = ?", $type );

        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche les entrées Gen_concepts pour diffuser une oeurvre
     *
     * @param int $idDico
     *
     * @return array
     */
    public function getCptForDiffusion($idDico)
    {
        $query = $this->select()
        	->from( array("c" => "gen_concepts") )                           
        ->where( "c.id_dico = ?", $idDico)
        ->where( "c.type NOT IN ('a','m','s','v')")
        ->order("c.lib");
    	
        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche les tables liées à une entrée de Gen_concepts
     *
     * @param int $idCpt
     *
     * @return array
     */
    public function findTablesLies($idCpt)
    {
        //récupère les déterminants
    	$sql ="SELECT c.lib, c.type
			, COUNT(ca.id_concept) nbA
			, COUNT(csu.id_concept) nbM
			, COUNT(csy.id_concept) nbS
			, COUNT(cv.id_concept) nbV
			FROM gen_concepts c
			LEFT JOIN gen_concepts_adjectifs ca ON c.id_concept = ca.id_concept
			LEFT JOIN gen_concepts_substantifs csu ON c.id_concept = csu.id_concept
			LEFT JOIN gen_concepts_syntagmes csy ON c.id_concept = csy.id_concept
			LEFT JOIN gen_concepts_verbes cv ON c.id_concept = cv.id_concept
			WHERE c.id_concept = ".$idCpt;
		$smtp = $this->_db->query($sql);
        return $smtp->fetchAll();
    }

    /**
     * renvoie le texte génératif conrrespondant à un concept
     *
     * @param array $idCpt
     *
     * @return string
     */
    public function getGenTexte($cpt)
    {
    	$txtGen = "";
    	switch ($cpt["type"]) {
			case "a":
				$txtGen = " [".$cpt["type"]. "_".$cpt["lib"]. "]";
				break; 
			case "m": 
				$txtGen = " [".$cpt["type"]. "_".$cpt["lib"]. "]";
				break; 
			case "s":
				$txtGen = " [".$cpt["type"]. "_".$cpt["lib"]. "]";
				break;
			case "v": 
				$txtGen = " [".$cpt["type"]. "_".$cpt["lib"]. "]";
				break; 
			case "carac": 
				$txtGen = "[".$cpt["type"].$cpt["lib"]. "]";
				break; 
			case "caract": 
				$txtGen = "[".$cpt["type"].$cpt["lib"]. "]";
				break; 
			default: 
				$txtGen = "[".$cpt["type"]. "-".$cpt["lib"]. "]";
				break; 
        }
    	return $txtGen;
    }

    /**
     * renvoie les items lié génératif conrrespondant à un concept
     *
     * @param int       $id
     * @param string    $type
     *
     * @return string
     */
    public function getItemByType($id, $type)
    {
    	switch ($type) {
            case "a":
                $db = new Model_DbTable_Gen_adjectifs($this->_db);
                $rs = $db->findByIdConcept($id);
				break; 
			case "m": 
                $db = new Model_DbTable_Gen_substantifs($this->_db);
                $rs = $db->findByIdConcept($id);
				break; 
			case "s":
                $db = new Model_DbTable_Gen_syntagmes($this->_db);
                $rs = $db->findByIdConcept($id);
				break;
			case "v": 
                $db = new Model_DbTable_Gen_verbes($this->_db);
                $rs = $db->findByIdConcept($id);
				break; 
			case "carac": 
				$txtGen = "[".$cpt["type"].$cpt["lib"]. "]";
				break; 
			case "caract": 
				$txtGen = "[".$cpt["type"].$cpt["lib"]. "]";
				break; 
			default: 
				$txtGen = "[".$cpt["type"]. "-".$cpt["lib"]. "]";
				break; 
        }
    	return $rs;
    }

    /**
     * trouve dans les générateurs les concepts correspondant à une recherche pour un dictionnaire
     *
     * @param string 	$txt
     * @param int 		$idDico
     *
     * @return Array
     */
    public function findByGenDico($txt, $idDico)
    {
    	$txt = str_replace("'", " ", $txt);
    	$sql ="SELECT c.lib, c.type, c.id_concept
			FROM gen_concepts c
			INNER JOIN gen_concepts_generateurs cg ON c.id_concept = cg.id_concept
			INNER JOIN gen_generateurs g ON g.id_gen = cg.id_gen AND (g.valeur LIKE '%".$txt."%' OR g.id_gen = '".$txt."')
			WHERE c.id_dico = ".$idDico."
			GROUP BY c.id_concept";
		$smtp = $this->_db->query($sql);
        return $smtp->fetchAll();
    }
    
    /**
     * Récupère les concepts par type.
     *
     * @param integer $idDico
     * @param array $arrClass
     *
     * @return array
     */
    public function obtenirConceptDescription($idDico, $arrClass){

        $cpt = $this->obtenirConceptByDicoLibType($idDico,$arrClass[1],$arrClass[0]);
        //vérifie l'exeption
        if(get_class($cpt)=="Exception"){
            return $cpt;
        }
        $arrCpt = $cpt->toArray();
        
        //cherche les enfants suivant le type de concept
        $arrEnf = array();
        /*
        $tType ="";
        if($arrClass[0]=="a")$tType="Adjectifs";
        if($arrClass[0]=="v")$tType="Verbes";
        if($arrClass[0]=="m" || $arrClass[0]=="carac" || $arrClass[0]=="dis")$tType="Substantifs";
        if($arrClass[0]=="s")$tType="Syntagmes";
        if($arrClass[0]=="age" || $arrClass[0]=="univers")$tType="Generateurs";
        if($tType!=""){
            $enfants = $cpt->findManyToManyRowset('Model_DbTable_'.$tType,
                                                 'Model_DbTable_Concepts'.$tType);
            $arrEnf = $enfants->toArray();
        }
        */
        $enfants = $cpt->findManyToManyRowset('Model_DbTable_Gen_adjectifs', 'Model_DbTable_Gen_conceptsxadjectifs');
        $arr = $enfants->toArray();
        if(count($arr)>0)$arrEnf = array_merge($arrEnf,$arr);
        $enfants = $cpt->findManyToManyRowset('Model_DbTable_Gen_verbes', 'Model_DbTable_Gen_conceptsxverbes');
        $arr = $enfants->toArray();
        if(count($arr)>0)$arrEnf = array_merge($arrEnf,$arr);
        $enfants = $cpt->findManyToManyRowset('Model_DbTable_Gen_substantifs', 'Model_DbTable_Gen_conceptsxsubstantifs');
        $arr = $enfants->toArray();
        if(count($arr)>0)$arrEnf = array_merge($arrEnf,$arr);
        $enfants = $cpt->findManyToManyRowset('Model_DbTable_Gen_syntagmes', 'Model_DbTable_Gen_conceptsxsyntagmes');
        $arr = $enfants->toArray();
        if(count($arr)>0)$arrEnf = array_merge($arrEnf,$arr);
        
        //cherche les générateurs
        $gens = $cpt->findManyToManyRowset('Model_DbTable_Gen_generateurs',
                                                 'Model_DbTable_Gen_conceptsxgenerateurs');    	
        //cherche les générateurs
        //$cptgens = $this->findGensByIdconcept($arrCpt["id_concept"]);    	
        //return array("src"=>$arrCpt,"dst"=>array_merge($arrEnf,$gens->toArray(),$cptgens));
        
        return array("src"=>$arrCpt,"dst"=>array_merge($arrEnf,$gens->toArray()));
    }

    /**
     * Récupère les concepts par type.
     *
     * @param integer $idDico
     * @param string $lib
     * @param string $type
     *
     * @return array
     */
    public function obtenirConceptByDicoLibType($idDico,$lib,$type)
    {
        $query = $this->select()
            ->where( "id_dico IN ($idDico)")
        	->where( "lib = ?",$lib)
            ->where( "type = ?",$type)
            ;
        $sql = $query->__toString();
		$r = $this->fetchRow($query);        
    	if (!$r) {
            $r = new Exception("La classe - $lib - de type - $type - n'a pas été trouvé dans le(s) dictionnaire(s) - $idDico -");
        }
        return $r;
    }
}

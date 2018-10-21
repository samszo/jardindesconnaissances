<?php
/**
 * Ce fichier contient la classe Gen_pronoms.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Model_DbTable_Gen_pronoms extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_pronoms';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_pronom';

    
    /**
     * Vérifie si une entrée est utilisée comme sujet.
     *
     * @param int 		$idDico
     * @param string 	$num
     *
     * @return array
     */
    public function utilise_sujet($idDico, $num)
    {
    	$sql ="SELECT COUNT(DISTINCT g.id_gen) nbGen
    		, COUNT(DISTINCT oduA.id_oeu) nbOeu
			, COUNT(DISTINCT g.id_dico) nbDico
			, COUNT(DISTINCT oduA.uti_id) nbUti
			, GROUP_CONCAT(DISTINCT oduA.uti_id) idsUti
		FROM gen_pronoms p
			INNER JOIN gen_oeuvres_dicos_utis odu ON odu.id_dico = p.id_dico
			INNER JOIN gen_oeuvres_dicos_utis oduA ON oduA.id_oeu = odu.id_oeu
			LEFT JOIN gen_generateurs g ON g.valeur LIKE '%[__".$num."_____|%]%' AND p.id_dico = oduA.id_dico
			WHERE p.num = ".$num." AND p.id_dico = ".$idDico."
			GROUP BY p.id_pronom";
    	$db = $this->getAdapter()->query($sql);
    	return $db->fetchAll();
    
    }

    /**
     * Vérifie si une entrée est utilisée comme complément.
     *
     * @param int 		$idDico
     * @param string 	$num
     *
     * @return array
     */
    public function utilise_comp($idDico, $num)
    {
    	//vérifie la taille du num
    	if(strlen($num)==1)$num = "0".$num; 
    	$sql ="SELECT COUNT(DISTINCT g.id_gen) nbGen
    		, COUNT(DISTINCT oduA.id_oeu) nbOeu
			, COUNT(DISTINCT g.id_dico) nbDico
			, COUNT(DISTINCT oduA.uti_id) nbUti
			, GROUP_CONCAT(DISTINCT oduA.uti_id) idsUti
		FROM gen_pronoms p
			INNER JOIN gen_oeuvres_dicos_utis odu ON odu.id_dico = p.id_dico
			INNER JOIN gen_oeuvres_dicos_utis oduA ON oduA.id_oeu = odu.id_oeu
			LEFT JOIN gen_generateurs g ON g.valeur LIKE '%[____".$num."__|%]%' AND p.id_dico = oduA.id_dico
			WHERE p.num = ".$num." AND p.id_dico = ".$idDico."
			GROUP BY p.id_pronom";
    	$db = $this->getAdapter()->query($sql);
    	return $db->fetchAll();
    
    }
    
    /**
     * Vérifie si une entrée Gen_pronoms existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_pronom'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_pronom; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Gen_pronoms.
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
    		$mn = $this->findMaxNumByIdDicoType($data['id_dico'], $data['type']);
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
     * @param string $type
     *
     * @return array
     */
    public function findMaxNumByIdDicoType($idDico, $type)
    {
    	$query = $this->select()
    	->from( array("g" => "gen_pronoms"),array("maxNum"=>"MAX(num)"))
    	->where( "g.id_dico = ?", $idDico)
    	->where( "g.type = ?", $type);
    	
    	return $this->fetchAll($query)->toArray();
    }
    
    
    /**
     * Recherche une entrée Gen_pronoms avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_pronoms.id_pronom = ' . $id);
    }
    
    /**
     * Recherche une entrée Gen_pronoms avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('gen_pronoms.id_pronom = ' . $id);
    }

    
    /**
     * Récupère toutes les entrées Gen_pronoms avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_pronoms" => "gen_pronoms") );
                    
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
     * Recherche une entrée Gen_pronoms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_pronom
     *
     * @return array
     */
    public function findById_pronom($id_pronom)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_pronoms") )                           
                    ->where( "g.id_pronom = ?", $id_pronom );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_pronoms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_dico
     *
     * @return array
     */
    public function findByIdDico($id_dico)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_pronoms") )                           
                    ->where( "g.id_dico = ?", $id_dico );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_pronoms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $type
     *
     * @return array
     */
    public function findByType($type)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_pronoms") )                           
                    ->where( "g.type = ?", $type );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_pronoms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $num
     *
     * @return array
     */
    public function findByNum($num)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_pronoms") )                           
                    ->where( "g.num = ?", $num );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_pronoms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lib
     *
     * @return array
     */
    public function findByLib($lib)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_pronoms") )                           
                    ->where( "g.lib = ?", $lib );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_pronoms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lib_eli
     *
     * @return array
     */
    public function findByLib_eli($lib_eli)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_pronoms") )                           
                    ->where( "g.lib_eli = ?", $lib_eli );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

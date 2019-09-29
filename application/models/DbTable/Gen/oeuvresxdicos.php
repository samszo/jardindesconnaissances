<?php
/**
 * Ce fichier contient la classe Gen_oeuvres_dicos.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Model_DbTable_Gen_oeuvresxdicos extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_oeuvres_dicos';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_oeu';

    protected $_dependentTables = array(
       "Model_DbTable_Gen_oeuvresxutis"
       ,"Model_DbTable_Gen_oeuvresxdicos"
       );
        
    /**
     * Vérifie si une entrée Gen_oeuvres_dicos existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_oeu'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_oeu; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Gen_oeuvres_dicos.
     *
     * @param int $idOeu
     * @param int $idUti
     * @param array $params
     * @param boolean $existe
     *  
     * @return integer
     */
    public function ajouter($idOeu, $idUti, $params, $existe=true)
    {
    	$dbDU = new Model_DbTable_Gen_dicosxutis();
    	foreach ($params as $idDico) {
   	    	$id=false;
   	    	$data= array("id_oeu"=>$idOeu, "id_dico"=>$idDico);
	    	if($existe)$id = $this->existe($data);
	    	if(!$id){
	    	 	$id = $this->insert($data);
	    	}
	    	//ajoute l'utilisateur au dictionnaire
	    	$dbDU->ajouter(array("id_dico"=>$id,"uti_id"=>$idUti));
    	}
    	return $id;
    } 
           
    /**
     * Recherche une entrée Gen_oeuvres_dicos avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_oeuvres_dicos.id_oeu = ' . $id);
    }
    
    /**
     * Recherche une entrée Gen_oeuvres_dicos avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idOeu
     * @param integer $idDico
     *
     * @return void
     */
    public function remove($idOeu, $idDico)
    {
    	$this->delete('gen_oeuvres_dicos.id_oeu = ' . $idOeu.' AND gen_oeuvres_dicos.id_dico = ' . $idDico);
    }
    
    /**
     * Récupère toutes les entrées Gen_oeuvres_dicos avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("gen_oeuvres_dicos" => "gen_oeuvres_dicos") );
                    
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
     * Recherche une entrée Gen_oeuvres_dicos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_oeu
     *
     * @return array
     */
    public function findByIdOeu($id_oeu)
    {
        $query = $this->select()
        	->from( array("od" => "gen_oeuvres_dicos") )                           
	        ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        ->joinInner(array('d' => 'gen_dicos'),'d.id_dico = od.id_dico'
            ,array('recid'=>'d.id_dico','nom','type','langue','general','licence'))
        ->where( "od.id_oeu = ?", $id_oeu )
        ->order(array("d.general DESC", "d.type"));
        
        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_oeuvres_dicos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_dico
     *
     * @return array
     */
    public function findById_dico($id_dico)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_oeuvres_dicos") )                           
                    ->where( "g.id_dico = ?", $id_dico );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

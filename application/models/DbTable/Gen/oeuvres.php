<?php
/**
 * Ce fichier contient la classe Gen_oeuvres.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Model_DbTable_Gen_oeuvres extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_oeuvres';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_oeu';

    protected $_dependentTables = array(
       "Model_DbTable_Gen_oeuvresxutisxroles"
       ,"Model_DbTable_Gen_oeuvresxdicos"
       );
               
    /**
     * Vérifie si une entrée Gen_oeuvres existe.
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
     * Ajoute une entrée Gen_oeuvres.
     *
     * @param array $data
     * @param int $idUti
     * @param boolean $existe
     *  
     * @return integer
     */
    public function ajouter($data, $idUti=false, $existe=true)
    {
    	
    	$id=false;
    	if($existe)$id = $this->existe($data);
    	if(!$id){
    		if(!isset($data['maj']))$data['maj'] = new Zend_Db_Expr('NOW()');
    	 	$id = $this->insert($data);
    	}
    	//vérifie s'il faut créer le lien avec l'utilisateur
    	if($idUti){
    		$dbOU = new Model_DbTable_Gen_oeuvresxutisxroles();
    		$dbOU->ajouter(array("id_oeu"=>$id,"uti_id"=>$idUti,"id_role"=>4));
    	}
    	return $id;
    } 
           
    /**
     * Recherche une entrée Gen_oeuvres avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_oeuvres.id_oeu = ' . $id);
    }
    
    /**
     * Recherche une entrée Gen_oeuvres avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	//suppression des données lieés
    	$dt = $this->getDependentTables();
    	foreach($dt as $t){
    		$dbT = new $t($this->_db);
    		$dbT->delete('id_oeu = ' . $id);
    	}
    	$this->delete('gen_oeuvres.id_oeu = ' . $id);
    }

    
    /**
     * Récupère toutes les entrées Gen_oeuvres avec certains critères
     * de tri, intervalles
     */
    public function getAll($order="lib", $limit=0, $from=0)
    {
   	
    	$query = $this->select()
            ->from( array("go" => "gen_oeuvres") )
            ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->joinInner(array('o' => 'gen_oeuvres'),'go.id_oeu = o.id_oeu',array('recid'=>'id_oeu'))			
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
     * Recherche une entrée Gen_oeuvres avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_oeu
     *
     * @return array
     */
    public function findByIdOeu($id_oeu)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_oeuvres") )                           
                    ->where( "g.id_oeu = ?", $id_oeu );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_oeuvres avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lib
     *
     * @return array
     */
    public function findByLib($lib)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_oeuvres") )                           
                    ->where( "g.lib = ?", $lib );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Gen_oeuvres avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("g" => "gen_oeuvres") )                           
                    ->where( "g.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

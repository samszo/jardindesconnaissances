<?php
/**
 * Ce fichier contient la classe Flux_Uti.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_Uti'.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_Uti extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_uti';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'uti_id';

    protected $_dependentTables = array(
       "Model_DbTable_flux_actiuti"
       ,"Model_DbTable_flux_utidoc"
       ,"Model_DbTable_Flux_UtiGeoDoc"
       ,"Model_DbTable_flux_utiieml"
       ,"Model_DbTable_Flux_UtiTagDoc"
       ,"Model_DbTable_flux_utitag"
       ,"Model_DbTable_flux_utitagrelated"
       ,"Model_DbTable_Flux_UtiUti"
       );
    
    /**
     * Vérifie si une entrée Flux_Uti existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('uti_id'));
		foreach($data as $k=>$v){
			if($k!="date_inscription" && $k!="mdp")
				$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->uti_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_Uti.
     *
     * @param array $data
     * @param boolean $existe
     *  
     * @return integer
     */
    public function ajouter($data, $existe=true)
    {
    	$id=false;
    	if($existe)$id = $this->existe($data);
    	if(!$id){
    	 	$id = $this->insert($data);
    	}
    	return $id;
    } 
           
    /**
     * Recherche une entrée Flux_Uti avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_uti.uti_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_Uti avec la clef primaire spécifiée
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
        	if($t!="Model_DbTable_Flux_UtiUti")
	        	$dbT->delete('uti_id = '.$id);
	        else
	        	$dbT->delete('uti_id_src = '.$id.' OR uti_id_dst = '.$id);
        }            	
    	$this->delete('flux_uti.uti_id = '.$id);
    }
    
    /**
     * Récupère toutes les entrées Flux_Uti avec certains critères
     * de tri, intervalles
     */
    public function getAll($cols=false, $order=null, $limit=0, $from=0)
    {
    	if($cols)
        	$query = $this->select()->from( array("flux_uti" => "flux_uti"), $cols);
    	else 
    		$query = $this->select()->from( array("flux_uti" => "flux_uti") );
                    
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
    
    /*
     * Recherche une entrée Flux_Uti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id
     */
    public function findByuti_id($uti_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_uti") )                           
                    ->where( "f.uti_id = ?", $uti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Uti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $login
     */
    public function findByLogin($login)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_uti") )                           
                    ->where( "f.login = ?", $login );
		$arr = $this->fetchAll($query)->toArray();
        return $arr[0]; 
    }
    /*
     * Recherche une entrée Flux_Uti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $maj
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_uti") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Uti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $flux
     */
    public function findByFlux($flux)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_uti") )                           
                    ->where( "f.flux = ?", $flux );

        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche des entrée avec une liste de paramètres
     *
     * @param array $params
     *
     * @return array
     */
    public function findByParams($params)
    {
		$select = $this->select();
		$select->from($this);
		foreach($params as $k=>$v){
			$select->where($k.' = ?', $v);
		}
		return $this->fetchAll($select)->toArray();        
    } 
            
}
<?php
/**
 * Ce fichier contient la classe Flux_exi.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_exi'.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_Exi extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_exi';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'exi_id';

    
    /**
     * Vérifie si une entrée Flux_exi existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('exi_id'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->exi_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_exi.
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
     * Recherche une entrée Flux_exi avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_exi.exi_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_exi avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_exi.exi_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Flux_exi avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_exi" => "flux_exi") );
                    
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
     * Récupère les spécifications des colonnes Flux_exi 
     */
    public function getCols(){

    	$arr = array("cols"=>array(
    	   	array("titre"=>"exi_id","champ"=>"exi_id","visible"=>true),
    	array("titre"=>"nom","champ"=>"nom","visible"=>true),
    	array("titre"=>"url","champ"=>"url","visible"=>true),
    	array("titre"=>"mail","champ"=>"mail","visible"=>true),
    	array("titre"=>"mdp","champ"=>"mdp","visible"=>true),
    	array("titre"=>"mdp_sel","champ"=>"mdp_sel","visible"=>true),
    	array("titre"=>"role","champ"=>"role","visible"=>true),
        	
    		));    	
    	return $arr;
		
    }     
    
    /*
     * Recherche une entrée Flux_exi avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $exi_id
     */
    public function findByExi_id($exi_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exi") )                           
                    ->where( "f.exi_id = ?", $exi_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_exi avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $nom
     */
    public function findByNom($nom)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exi") )                           
                    ->where( "f.nom = ?", $nom );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_exi avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url
     */
    public function findByUrl($url)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exi") )                           
                    ->where( "f.url = ?", $url );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_exi avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $mail
     */
    public function findByMail($mail)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exi") )                           
                    ->where( "f.mail = ?", $mail );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_exi avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $mdp
     */
    public function findByMdp($mdp)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exi") )                           
                    ->where( "f.mdp = ?", $mdp );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_exi avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $mdp_sel
     */
    public function findByMdp_sel($mdp_sel)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exi") )                           
                    ->where( "f.mdp_sel = ?", $mdp_sel );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_exi avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $role
     */
    public function findByRole($role)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exi") )                           
                    ->where( "f.role = ?", $role );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

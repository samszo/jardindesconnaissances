<?php
/**
 * Ce fichier contient la classe Flux_UtiTag.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_UtiTag'.
 *
 * @copyright  201=& Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_UtiTag extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_UtiTag';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'uti_id';

    
    /**
     * Vérifie si une entrée Flux_UtiTag existe.
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
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->uti_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_UtiTag.
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
     * Recherche une entrée Flux_UtiTag avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_UtiTag.uti_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_UtiTag avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_UtiTag.uti_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Flux_UtiTag avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_UtiTag" => "flux_UtiTag") );
                    
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
     * Recherche les Tag avec la valeur spécifiée
     * et retourne les entrées.
     *
     * @param string $uti
     * 
     * @return array
     */
    public function findTagByUti($uti)
    {
		$sql = "SELECT t.code, u.login, ut.poids, ut.uti_id
			FROM flux_utitag ut
				INNER JOIN flux_tag t ON t.tag_id = ut.tag_id
				INNER JOIN flux_uti u ON u.uti_id = ut.uti_id AND u.login = '".$uti."'
			ORDER BY t.code";
        $db = Zend_Db_Table::getDefaultAdapter();
    	$stmt = $db->query($sql);
    	return $stmt->fetchAll();
    }

    /**
     * Renvoi les tags et les utilisateurs
     *
     * 
     * @return array
     */
    public function findTagUti()
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('ut' => 'flux_UtiTag'))
            ->joinInner(array('t' => 'Flux_Tag'),
            	'ut.tag_id = t.tag_id', array('code'))
            ->joinInner(array('u' => 'Flux_Uti'),
            	'u.uti_id = ut.uti_id ', array('login'))
            ->order(array("u.login", "t.code"));
            
        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche une entrée Flux_UtiTag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id
     */
    public function findByTag_id($tag_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_UtiTag") )                           
                    ->where( "f.tag_id = ?", $tag_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_UtiTag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $poids
     */
    public function findByPoids($poids)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_UtiTag") )                           
                    ->where( "f.poids = ?", $poids );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

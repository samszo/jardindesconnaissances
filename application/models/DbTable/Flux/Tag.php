<?php
/**
 * Ce fichier contient la classe Flux_Tag.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_Tag'.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_Tag extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_Tag';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'tag_id';

    
    /**
     * Vérifie si une entrée Flux_Tag existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('tag_id'));
		$select->where(' code = ?', $data['code']);
		if(isset($data['parent']))$select->where(' parent = ?', $data['parent']);
		$rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->tag_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_Tag.
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
     * Recherche une entrée Flux_Tag avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_Tag.tag_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_Tag avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_Tag.tag_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Flux_Tag avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_Tag" => "flux_Tag") );
                    
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
     * Recherche une entrée Flux_Tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id
     */
    public function findByTag_id($tag_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Tag") )                           
                    ->where( "f.tag_id = ?", $tag_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $code
     */
    public function findByCode($code)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Tag") )                           
                    ->where( "f.code = ?", $code );

		$arr = $this->fetchAll($query)->toArray();
        return $arr[0]; 
    }
    /*
     * Recherche une entrée Flux_Tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $desc
     */
    public function findByDesc($desc)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Tag") )                           
                    ->where( "f.desc = ?", $desc );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $niveau
     */
    public function findByNiveau($niveau)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Tag") )                           
                    ->where( "f.niveau = ?", $niveau );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param char $parent
     */
    public function findByParent($parent)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Tag") )                           
                    ->where( "f.parent = ?", $parent );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

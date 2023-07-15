<?php
/**
 * Ce fichier contient la classe Spip_ortho_cache.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_ortho_cache'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_ortho_cache extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_ortho_cache';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'lang';
	
    
    /**
     * Vérifie si une entrée Spip_ortho_cache existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('lang'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->lang; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_ortho_cache.
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
     * Recherche une entrée Spip_ortho_cache avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_ortho_cache.lang = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_ortho_cache avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_ortho_cache.lang = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_ortho_cache avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_ortho_cache" => "spip_ortho_cache") );
                    
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
     * Recherche une entrée Spip_ortho_cache avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lang
     *
     * @return array
     */
    public function findByLang($lang)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_ortho_cache") )                           
                    ->where( "s.lang = ?", $lang );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_ortho_cache avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $mot
     *
     * @return array
     */
    public function findByMot($mot)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_ortho_cache") )                           
                    ->where( "s.mot = ?", $mot );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_ortho_cache avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param tinyint $ok
     *
     * @return array
     */
    public function findByOk($ok)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_ortho_cache") )                           
                    ->where( "s.ok = ?", $ok );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_ortho_cache avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $suggest
     *
     * @return array
     */
    public function findBySuggest($suggest)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_ortho_cache") )                           
                    ->where( "s.suggest = ?", $suggest );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_ortho_cache avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_ortho_cache") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

<?php
/**
 * Ce fichier contient la classe Spip_referers_articles.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_referers_articles'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_referers_articles extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_referers_articles';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_article';
	
    
    /**
     * Vérifie si une entrée Spip_referers_articles existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_article'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_article; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_referers_articles.
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
     * Recherche une entrée Spip_referers_articles avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_referers_articles.id_article = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_referers_articles avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_referers_articles.id_article = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_referers_articles avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_referers_articles" => "spip_referers_articles") );
                    
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
     * Recherche une entrée Spip_referers_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_article
     *
     * @return array
     */
    public function findById_article($id_article)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_referers_articles") )                           
                    ->where( "s.id_article = ?", $id_article );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_referers_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $referer_md5
     *
     * @return array
     */
    public function findByReferer_md5($referer_md5)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_referers_articles") )                           
                    ->where( "s.referer_md5 = ?", $referer_md5 );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_referers_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $referer
     *
     * @return array
     */
    public function findByReferer($referer)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_referers_articles") )                           
                    ->where( "s.referer = ?", $referer );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_referers_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $visites
     *
     * @return array
     */
    public function findByVisites($visites)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_referers_articles") )                           
                    ->where( "s.visites = ?", $visites );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_referers_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_referers_articles") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

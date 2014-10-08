<?php
/**
 * Ce fichier contient la classe Spip_syndic_articles.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_syndic_articles'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_syndicarticles extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_syndic_articles';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_syndic_article';
	
    
    /**
     * Vérifie si une entrée Spip_syndic_articles existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_syndic_article'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_syndic_article; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_syndic_articles.
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
     * Recherche une entrée Spip_syndic_articles avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_syndic_articles.id_syndic_article = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_syndic_articles avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_syndic_articles.id_syndic_article = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_syndic_articles avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_syndic_articles" => "spip_syndic_articles") );
                    
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
     * Recherche une entrée Spip_syndic_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_syndic_article
     *
     * @return array
     */
    public function findById_syndic_article($id_syndic_article)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic_articles") )                           
                    ->where( "s.id_syndic_article = ?", $id_syndic_article );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_syndic
     *
     * @return array
     */
    public function findById_syndic($id_syndic)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic_articles") )                           
                    ->where( "s.id_syndic = ?", $id_syndic );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic_articles") )                           
                    ->where( "s.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url
     *
     * @return array
     */
    public function findByUrl($url)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic_articles") )                           
                    ->where( "s.url = ?", $url );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date
     *
     * @return array
     */
    public function findByDate($date)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic_articles") )                           
                    ->where( "s.date = ?", $date );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $lesauteurs
     *
     * @return array
     */
    public function findByLesauteurs($lesauteurs)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic_articles") )                           
                    ->where( "s.lesauteurs = ?", $lesauteurs );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic_articles") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $statut
     *
     * @return array
     */
    public function findByStatut($statut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic_articles") )                           
                    ->where( "s.statut = ?", $statut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $descriptif
     *
     * @return array
     */
    public function findByDescriptif($descriptif)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic_articles") )                           
                    ->where( "s.descriptif = ?", $descriptif );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lang
     *
     * @return array
     */
    public function findByLang($lang)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic_articles") )                           
                    ->where( "s.lang = ?", $lang );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $url_source
     *
     * @return array
     */
    public function findByUrl_source($url_source)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic_articles") )                           
                    ->where( "s.url_source = ?", $url_source );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $source
     *
     * @return array
     */
    public function findBySource($source)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic_articles") )                           
                    ->where( "s.source = ?", $source );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $tags
     *
     * @return array
     */
    public function findByTags($tags)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic_articles") )                           
                    ->where( "s.tags = ?", $tags );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

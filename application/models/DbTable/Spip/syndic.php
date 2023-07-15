<?php
/**
 * Ce fichier contient la classe Spip_syndic.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_syndic'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_syndic extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_syndic';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_syndic';
	
    
    /**
     * Vérifie si une entrée Spip_syndic existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_syndic'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_syndic; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_syndic.
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
     * Recherche une entrée Spip_syndic avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_syndic.id_syndic = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_syndic avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_syndic.id_syndic = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_syndic avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_syndic" => "spip_syndic") );
                    
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
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_syndic
     *
     * @return array
     */
    public function findById_syndic($id_syndic)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.id_syndic = ?", $id_syndic );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_rubrique
     *
     * @return array
     */
    public function findById_rubrique($id_rubrique)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.id_rubrique = ?", $id_rubrique );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_secteur
     *
     * @return array
     */
    public function findById_secteur($id_secteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.id_secteur = ?", $id_secteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $nom_site
     *
     * @return array
     */
    public function findByNom_site($nom_site)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.nom_site = ?", $nom_site );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param blob $url_site
     *
     * @return array
     */
    public function findByUrl_site($url_site)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.url_site = ?", $url_site );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param blob $url_syndic
     *
     * @return array
     */
    public function findByUrl_syndic($url_syndic)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.url_syndic = ?", $url_syndic );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $descriptif
     *
     * @return array
     */
    public function findByDescriptif($descriptif)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.descriptif = ?", $descriptif );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url_propre
     *
     * @return array
     */
    public function findByUrl_propre($url_propre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.url_propre = ?", $url_propre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $syndication
     *
     * @return array
     */
    public function findBySyndication($syndication)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.syndication = ?", $syndication );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $statut
     *
     * @return array
     */
    public function findByStatut($statut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.statut = ?", $statut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date
     *
     * @return array
     */
    public function findByDate($date)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.date = ?", $date );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_syndic
     *
     * @return array
     */
    public function findByDate_syndic($date_syndic)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.date_syndic = ?", $date_syndic );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_index
     *
     * @return array
     */
    public function findByDate_index($date_index)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.date_index = ?", $date_index );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $extra
     *
     * @return array
     */
    public function findByExtra($extra)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.extra = ?", $extra );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $moderation
     *
     * @return array
     */
    public function findByModeration($moderation)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.moderation = ?", $moderation );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $miroir
     *
     * @return array
     */
    public function findByMiroir($miroir)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.miroir = ?", $miroir );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $oubli
     *
     * @return array
     */
    public function findByOubli($oubli)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.oubli = ?", $oubli );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_syndic avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $resume
     *
     * @return array
     */
    public function findByResume($resume)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_syndic") )                           
                    ->where( "s.resume = ?", $resume );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

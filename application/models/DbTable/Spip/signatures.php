<?php
/**
 * Ce fichier contient la classe Spip_signatures.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_signatures'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_signatures extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_signatures';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_signature';
	
    
    /**
     * Vérifie si une entrée Spip_signatures existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_signature'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_signature; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_signatures.
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
     * Recherche une entrée Spip_signatures avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_signatures.id_signature = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_signatures avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_signatures.id_signature = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_signatures avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_signatures" => "spip_signatures") );
                    
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
     * Recherche une entrée Spip_signatures avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_signature
     *
     * @return array
     */
    public function findById_signature($id_signature)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_signatures") )                           
                    ->where( "s.id_signature = ?", $id_signature );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_signatures avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_article
     *
     * @return array
     */
    public function findById_article($id_article)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_signatures") )                           
                    ->where( "s.id_article = ?", $id_article );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_signatures avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_time
     *
     * @return array
     */
    public function findByDate_time($date_time)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_signatures") )                           
                    ->where( "s.date_time = ?", $date_time );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_signatures avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $nom_email
     *
     * @return array
     */
    public function findByNom_email($nom_email)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_signatures") )                           
                    ->where( "s.nom_email = ?", $nom_email );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_signatures avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $ad_email
     *
     * @return array
     */
    public function findByAd_email($ad_email)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_signatures") )                           
                    ->where( "s.ad_email = ?", $ad_email );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_signatures avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $nom_site
     *
     * @return array
     */
    public function findByNom_site($nom_site)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_signatures") )                           
                    ->where( "s.nom_site = ?", $nom_site );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_signatures avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $url_site
     *
     * @return array
     */
    public function findByUrl_site($url_site)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_signatures") )                           
                    ->where( "s.url_site = ?", $url_site );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_signatures avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $message
     *
     * @return array
     */
    public function findByMessage($message)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_signatures") )                           
                    ->where( "s.message = ?", $message );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_signatures avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $statut
     *
     * @return array
     */
    public function findByStatut($statut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_signatures") )                           
                    ->where( "s.statut = ?", $statut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_signatures avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_signatures") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

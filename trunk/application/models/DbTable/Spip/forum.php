<?php
/**
 * Ce fichier contient la classe Spip_forum.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_forum'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_forum extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_forum';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_forum';
	
    
    /**
     * Vérifie si une entrée Spip_forum existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_forum'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_forum; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_forum.
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
     * Recherche une entrée Spip_forum avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_forum.id_forum = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_forum avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_forum.id_forum = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_forum avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_forum" => "spip_forum") );
                    
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
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_forum
     *
     * @return array
     */
    public function findById_forum($id_forum)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.id_forum = ?", $id_forum );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_parent
     *
     * @return array
     */
    public function findById_parent($id_parent)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.id_parent = ?", $id_parent );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_thread
     *
     * @return array
     */
    public function findById_thread($id_thread)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.id_thread = ?", $id_thread );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_rubrique
     *
     * @return array
     */
    public function findById_rubrique($id_rubrique)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.id_rubrique = ?", $id_rubrique );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_article
     *
     * @return array
     */
    public function findById_article($id_article)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.id_article = ?", $id_article );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_breve
     *
     * @return array
     */
    public function findById_breve($id_breve)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.id_breve = ?", $id_breve );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_heure
     *
     * @return array
     */
    public function findByDate_heure($date_heure)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.date_heure = ?", $date_heure );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $texte
     *
     * @return array
     */
    public function findByTexte($texte)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.texte = ?", $texte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $auteur
     *
     * @return array
     */
    public function findByAuteur($auteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.auteur = ?", $auteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $email_auteur
     *
     * @return array
     */
    public function findByEmail_auteur($email_auteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.email_auteur = ?", $email_auteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $nom_site
     *
     * @return array
     */
    public function findByNom_site($nom_site)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.nom_site = ?", $nom_site );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $url_site
     *
     * @return array
     */
    public function findByUrl_site($url_site)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.url_site = ?", $url_site );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $statut
     *
     * @return array
     */
    public function findByStatut($statut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.statut = ?", $statut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $ip
     *
     * @return array
     */
    public function findByIp($ip)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.ip = ?", $ip );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_auteur
     *
     * @return array
     */
    public function findById_auteur($id_auteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.id_auteur = ?", $id_auteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_message
     *
     * @return array
     */
    public function findById_message($id_message)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.id_message = ?", $id_message );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_syndic
     *
     * @return array
     */
    public function findById_syndic($id_syndic)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.id_syndic = ?", $id_syndic );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forum avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_thread
     *
     * @return array
     */
    public function findByDate_thread($date_thread)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forum") )                           
                    ->where( "s.date_thread = ?", $date_thread );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

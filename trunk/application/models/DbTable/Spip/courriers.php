<?php
/**
 * Ce fichier contient la classe Spip_courriers.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_courriers'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_courriers extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_courriers';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_courrier';
	
    
    /**
     * Vérifie si une entrée Spip_courriers existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_courrier'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_courrier; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_courriers.
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
     * Recherche une entrée Spip_courriers avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_courriers.id_courrier = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_courriers avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_courriers.id_courrier = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_courriers avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_courriers" => "spip_courriers") );
                    
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
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_courrier
     *
     * @return array
     */
    public function findById_courrier($id_courrier)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.id_courrier = ?", $id_courrier );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_auteur
     *
     * @return array
     */
    public function findById_auteur($id_auteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.id_auteur = ?", $id_auteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_liste
     *
     * @return array
     */
    public function findById_liste($id_liste)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.id_liste = ?", $id_liste );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longblob $texte
     *
     * @return array
     */
    public function findByTexte($texte)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.texte = ?", $texte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longblob $message_texte
     *
     * @return array
     */
    public function findByMessage_texte($message_texte)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.message_texte = ?", $message_texte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date
     *
     * @return array
     */
    public function findByDate($date)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.date = ?", $date );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $statut
     *
     * @return array
     */
    public function findByStatut($statut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.statut = ?", $statut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $type
     *
     * @return array
     */
    public function findByType($type)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.type = ?", $type );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $email_test
     *
     * @return array
     */
    public function findByEmail_test($email_test)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.email_test = ?", $email_test );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $total_abonnes
     *
     * @return array
     */
    public function findByTotal_abonnes($total_abonnes)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.total_abonnes = ?", $total_abonnes );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $nb_emails_envoyes
     *
     * @return array
     */
    public function findByNb_emails_envoyes($nb_emails_envoyes)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.nb_emails_envoyes = ?", $nb_emails_envoyes );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $nb_emails_non_envoyes
     *
     * @return array
     */
    public function findByNb_emails_non_envoyes($nb_emails_non_envoyes)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.nb_emails_non_envoyes = ?", $nb_emails_non_envoyes );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $nb_emails_echec
     *
     * @return array
     */
    public function findByNb_emails_echec($nb_emails_echec)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.nb_emails_echec = ?", $nb_emails_echec );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $nb_emails_html
     *
     * @return array
     */
    public function findByNb_emails_html($nb_emails_html)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.nb_emails_html = ?", $nb_emails_html );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $nb_emails_texte
     *
     * @return array
     */
    public function findByNb_emails_texte($nb_emails_texte)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.nb_emails_texte = ?", $nb_emails_texte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_debut_envoi
     *
     * @return array
     */
    public function findByDate_debut_envoi($date_debut_envoi)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.date_debut_envoi = ?", $date_debut_envoi );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_fin_envoi
     *
     * @return array
     */
    public function findByDate_fin_envoi($date_fin_envoi)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.date_fin_envoi = ?", $date_fin_envoi );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_courriers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('','1','non','oui','idx') $idx
     *
     * @return array
     */
    public function findByIdx($idx)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_courriers") )                           
                    ->where( "s.idx = ?", $idx );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

<?php
/**
 * Ce fichier contient la classe Spip_listes.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_listes'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_listes extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_listes';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_liste';
	
    
    /**
     * Vérifie si une entrée Spip_listes existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_liste'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_liste; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_listes.
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
     * Recherche une entrée Spip_listes avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_listes.id_liste = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_listes avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_listes.id_liste = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_listes avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_listes" => "spip_listes") );
                    
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
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_liste
     *
     * @return array
     */
    public function findById_liste($id_liste)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.id_liste = ?", $id_liste );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $descriptif
     *
     * @return array
     */
    public function findByDescriptif($descriptif)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.descriptif = ?", $descriptif );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longblob $texte
     *
     * @return array
     */
    public function findByTexte($texte)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.texte = ?", $texte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longblob $pied_page
     *
     * @return array
     */
    public function findByPied_page($pied_page)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.pied_page = ?", $pied_page );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date
     *
     * @return array
     */
    public function findByDate($date)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.date = ?", $date );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $titre_message
     *
     * @return array
     */
    public function findByTitre_message($titre_message)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.titre_message = ?", $titre_message );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $patron
     *
     * @return array
     */
    public function findByPatron($patron)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.patron = ?", $patron );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $periode
     *
     * @return array
     */
    public function findByPeriode($periode)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.periode = ?", $periode );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lang
     *
     * @return array
     */
    public function findByLang($lang)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.lang = ?", $lang );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $statut
     *
     * @return array
     */
    public function findByStatut($statut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.statut = ?", $statut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $email_envoi
     *
     * @return array
     */
    public function findByEmail_envoi($email_envoi)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.email_envoi = ?", $email_envoi );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $message_auto
     *
     * @return array
     */
    public function findByMessage_auto($message_auto)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.message_auto = ?", $message_auto );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longblob $extra
     *
     * @return array
     */
    public function findByExtra($extra)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.extra = ?", $extra );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_listes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('','1','non','oui','idx') $idx
     *
     * @return array
     */
    public function findByIdx($idx)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_listes") )                           
                    ->where( "s.idx = ?", $idx );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

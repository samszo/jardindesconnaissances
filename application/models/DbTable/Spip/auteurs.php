<?php
/**
 * Ce fichier contient la classe Spip_auteurs.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_auteurs'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Model_DbTable_Spip_auteurs extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_auteurs';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_auteur';
	
    
    /**
     * Vérifie si une entrée Spip_auteurs existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_auteur'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_auteur; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_auteurs.
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
     * Recherche une entrée Spip_auteurs avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_auteurs.id_auteur = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_auteurs avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_auteurs.id_auteur = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_auteurs avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_auteurs" => "spip_auteurs") );
                    
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
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_auteur
     *
     * @return array
     */
    public function findById_auteur($id_auteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.id_auteur = ?", $id_auteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $nom
     *
     * @return array
     */
    public function findByNom($nom)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.nom = ?", $nom );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $bio
     *
     * @return array
     */
    public function findByBio($bio)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.bio = ?", $bio );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $email
     *
     * @return array
     */
    public function findByEmail($email)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.email = ?", $email );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $nom_site
     *
     * @return array
     */
    public function findByNom_site($nom_site)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.nom_site = ?", $nom_site );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $url_site
     *
     * @return array
     */
    public function findByUrl_site($url_site)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.url_site = ?", $url_site );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $login
     * @param boolean $bId
     *
     * @return array
     */
    public function findByLogin($login, $bId=false)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.login = ?", $login );
		$rs = $this->fetchAll($query)->toArray();
		$r = count($rs) ? $rs[0] : 0;
        return  $bId && $r ? $r["id_auteur"] : $r;
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $pass
     *
     * @return array
     */
    public function findByPass($pass)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.pass = ?", $pass );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $low_sec
     *
     * @return array
     */
    public function findByLow_sec($low_sec)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.low_sec = ?", $low_sec );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $statut
     *
     * @return array
     */
    public function findByStatut($statut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.statut = ?", $statut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param blob $pgp
     *
     * @return array
     */
    public function findByPgp($pgp)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.pgp = ?", $pgp );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param tinyblob $htpass
     *
     * @return array
     */
    public function findByHtpass($htpass)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.htpass = ?", $htpass );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $en_ligne
     *
     * @return array
     */
    public function findByEn_ligne($en_ligne)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.en_ligne = ?", $en_ligne );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $imessage
     *
     * @return array
     */
    public function findByImessage($imessage)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.imessage = ?", $imessage );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $messagerie
     *
     * @return array
     */
    public function findByMessagerie($messagerie)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.messagerie = ?", $messagerie );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $alea_actuel
     *
     * @return array
     */
    public function findByAlea_actuel($alea_actuel)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.alea_actuel = ?", $alea_actuel );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $alea_futur
     *
     * @return array
     */
    public function findByAlea_futur($alea_futur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.alea_futur = ?", $alea_futur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $prefs
     *
     * @return array
     */
    public function findByPrefs($prefs)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.prefs = ?", $prefs );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $cookie_oubli
     *
     * @return array
     */
    public function findByCookie_oubli($cookie_oubli)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.cookie_oubli = ?", $cookie_oubli );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $source
     *
     * @return array
     */
    public function findBySource($source)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.source = ?", $source );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lang
     *
     * @return array
     */
    public function findByLang($lang)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.lang = ?", $lang );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url_propre
     *
     * @return array
     */
    public function findByUrl_propre($url_propre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.url_propre = ?", $url_propre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $extra
     *
     * @return array
     */
    public function findByExtra($extra)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.extra = ?", $extra );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $cistatut
     *
     * @return array
     */
    public function findByCistatut($cistatut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.cistatut = ?", $cistatut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $cioption
     *
     * @return array
     */
    public function findByCioption($cioption)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.cioption = ?", $cioption );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $webmestre
     *
     * @return array
     */
    public function findByWebmestre($webmestre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs") )                           
                    ->where( "s.webmestre = ?", $webmestre );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

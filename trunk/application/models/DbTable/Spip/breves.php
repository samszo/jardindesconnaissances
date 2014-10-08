<?php
/**
 * Ce fichier contient la classe Spip_breves.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_breves'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_breves extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_breves';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_breve';
	
    
    /**
     * Vérifie si une entrée Spip_breves existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_breve'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_breve; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_breves.
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
     * Recherche une entrée Spip_breves avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_breves.id_breve = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_breves avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_breves.id_breve = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_breves avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_breves" => "spip_breves") );
                    
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
     * Recherche une entrée Spip_breves avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_breve
     *
     * @return array
     */
    public function findById_breve($id_breve)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_breves") )                           
                    ->where( "s.id_breve = ?", $id_breve );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_breves avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_heure
     *
     * @return array
     */
    public function findByDate_heure($date_heure)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_breves") )                           
                    ->where( "s.date_heure = ?", $date_heure );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_breves avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_breves") )                           
                    ->where( "s.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_breves avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $texte
     *
     * @return array
     */
    public function findByTexte($texte)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_breves") )                           
                    ->where( "s.texte = ?", $texte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_breves avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $lien_titre
     *
     * @return array
     */
    public function findByLien_titre($lien_titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_breves") )                           
                    ->where( "s.lien_titre = ?", $lien_titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_breves avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $lien_url
     *
     * @return array
     */
    public function findByLien_url($lien_url)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_breves") )                           
                    ->where( "s.lien_url = ?", $lien_url );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_breves avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $statut
     *
     * @return array
     */
    public function findByStatut($statut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_breves") )                           
                    ->where( "s.statut = ?", $statut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_breves avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_rubrique
     *
     * @return array
     */
    public function findById_rubrique($id_rubrique)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_breves") )                           
                    ->where( "s.id_rubrique = ?", $id_rubrique );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_breves avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lang
     *
     * @return array
     */
    public function findByLang($lang)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_breves") )                           
                    ->where( "s.lang = ?", $lang );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_breves avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $langue_choisie
     *
     * @return array
     */
    public function findByLangue_choisie($langue_choisie)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_breves") )                           
                    ->where( "s.langue_choisie = ?", $langue_choisie );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_breves avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_breves") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_breves avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $extra
     *
     * @return array
     */
    public function findByExtra($extra)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_breves") )                           
                    ->where( "s.extra = ?", $extra );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_breves avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url_propre
     *
     * @return array
     */
    public function findByUrl_propre($url_propre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_breves") )                           
                    ->where( "s.url_propre = ?", $url_propre );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

<?php
/**
 * Ce fichier contient la classe Spip_forms_donnees.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_forms_donnees'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Spip_formsxdonnees extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_forms_donnees';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_donnee';
	
    
    /**
     * Vérifie si une entrée Spip_forms_donnees existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_donnee'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_donnee; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_forms_donnees.
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
     * Recherche une entrée Spip_forms_donnees avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_forms_donnees.id_donnee = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_forms_donnees avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_forms_donnees.id_donnee = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_forms_donnees avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_forms_donnees" => "spip_forms_donnees") );
                    
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
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_donnee
     *
     * @return array
     */
    public function findById_donnee($id_donnee)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.id_donnee = ?", $id_donnee );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_donnee
     * @param bigint $id_form
     *
     * @return array
     */
    public function findByFormIdDonnee($id_form, $id_donnee)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.id_form = ?", $id_form )
                    ->where( "s.id_donnee = ?", $id_donnee );

        return $this->fetchAll($query)->toArray(); 
    }
    
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint 	$id_form
     * @param string 	$statut
     * @param boolean 	$valeur
     *
     * @return array
     */
    public function findByIdForm($id_form, $statut="publie", $valeur=true)
    {
        $query = $this->select()
                    ->from( array("fd" => "spip_forms_donnees") )                           
                    ->where( "fd.statut = ?", $statut )
                    ->where( "fd.id_form = ?", $id_form );
        if($valeur){
			$query->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
				->joinInner(array('fdc' => 'spip_forms_donnees_champs'),"fdc.id_donnee = fd.id_donnee",array('champ','valeur','maj'));
        	
        }

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date
     *
     * @return array
     */
    public function findByDate($date)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.date = ?", $date );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $ip
     *
     * @return array
     */
    public function findByIp($ip)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.ip = ?", $ip );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_auteur
     *
     * @return array
     */
    public function findById_auteur($id_auteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.id_auteur = ?", $id_auteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_article_export
     *
     * @return array
     */
    public function findById_article_export($id_article_export)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.id_article_export = ?", $id_article_export );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url
     *
     * @return array
     */
    public function findByUrl($url)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.url = ?", $url );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $confirmation
     *
     * @return array
     */
    public function findByConfirmation($confirmation)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.confirmation = ?", $confirmation );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $statut
     *
     * @return array
     */
    public function findByStatut($statut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.statut = ?", $statut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $cookie
     *
     * @return array
     */
    public function findByCookie($cookie)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.cookie = ?", $cookie );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $rang
     *
     * @return array
     */
    public function findByRang($rang)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.rang = ?", $rang );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $bgch
     *
     * @return array
     */
    public function findByBgch($bgch)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.bgch = ?", $bgch );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $bdte
     *
     * @return array
     */
    public function findByBdte($bdte)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.bdte = ?", $bdte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $niveau
     *
     * @return array
     */
    public function findByNiveau($niveau)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.niveau = ?", $niveau );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_donnees") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

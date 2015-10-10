<?php
/**
 * Ce fichier contient la classe Spip_petitions.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_petitions'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_petitions extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_petitions';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_article';
	
    
    /**
     * Vérifie si une entrée Spip_petitions existe.
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
     * Ajoute une entrée Spip_petitions.
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
     * Recherche une entrée Spip_petitions avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_petitions.id_article = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_petitions avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_petitions.id_article = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_petitions avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_petitions" => "spip_petitions") );
                    
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
     * Recherche une entrée Spip_petitions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_article
     *
     * @return array
     */
    public function findById_article($id_article)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_petitions") )                           
                    ->where( "s.id_article = ?", $id_article );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_petitions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param char $email_unique
     *
     * @return array
     */
    public function findByEmail_unique($email_unique)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_petitions") )                           
                    ->where( "s.email_unique = ?", $email_unique );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_petitions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param char $site_obli
     *
     * @return array
     */
    public function findBySite_obli($site_obli)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_petitions") )                           
                    ->where( "s.site_obli = ?", $site_obli );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_petitions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param char $site_unique
     *
     * @return array
     */
    public function findBySite_unique($site_unique)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_petitions") )                           
                    ->where( "s.site_unique = ?", $site_unique );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_petitions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param char $message
     *
     * @return array
     */
    public function findByMessage($message)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_petitions") )                           
                    ->where( "s.message = ?", $message );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_petitions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $texte
     *
     * @return array
     */
    public function findByTexte($texte)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_petitions") )                           
                    ->where( "s.texte = ?", $texte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_petitions avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_petitions") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

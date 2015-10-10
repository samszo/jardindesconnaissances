<?php
/**
 * Ce fichier contient la classe Spip_mots.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_mots'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_mots extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_mots';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_mot';
	
    
    /**
     * Vérifie si une entrée Spip_mots existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_mot'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_mot; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_mots.
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
     * Recherche une entrée Spip_mots avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_mots.id_mot = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_mots avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_mots.id_mot = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_mots avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_mots" => "spip_mots") );
                    
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
     * Recherche une entrée Spip_mots avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_mot
     *
     * @return array
     */
    public function findById_mot($id_mot)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots") )                           
                    ->where( "s.id_mot = ?", $id_mot );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_mots avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots") )                           
                    ->where( "s.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_mots avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $descriptif
     *
     * @return array
     */
    public function findByDescriptif($descriptif)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots") )                           
                    ->where( "s.descriptif = ?", $descriptif );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_mots avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $texte
     *
     * @return array
     */
    public function findByTexte($texte)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots") )                           
                    ->where( "s.texte = ?", $texte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_mots avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_groupe
     *
     * @return array
     */
    public function findById_groupe($id_groupe)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots") )                           
                    ->where( "s.id_groupe = ?", $id_groupe );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_mots avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $type
     *
     * @return array
     */
    public function findByType($type)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots") )                           
                    ->where( "s.type = ?", $type );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_mots avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $extra
     *
     * @return array
     */
    public function findByExtra($extra)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots") )                           
                    ->where( "s.extra = ?", $extra );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_mots avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url_propre
     *
     * @return array
     */
    public function findByUrl_propre($url_propre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots") )                           
                    ->where( "s.url_propre = ?", $url_propre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_mots avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

<?php
/**
 * Ce fichier contient la classe Spip_referers.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_referers'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_referers extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_referers';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'referer_md5';
	
    
    /**
     * Vérifie si une entrée Spip_referers existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('referer_md5'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->referer_md5; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_referers.
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
     * Recherche une entrée Spip_referers avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_referers.referer_md5 = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_referers avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_referers.referer_md5 = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_referers avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_referers" => "spip_referers") );
                    
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
     * Recherche une entrée Spip_referers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $referer_md5
     *
     * @return array
     */
    public function findByReferer_md5($referer_md5)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_referers") )                           
                    ->where( "s.referer_md5 = ?", $referer_md5 );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_referers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param date $date
     *
     * @return array
     */
    public function findByDate($date)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_referers") )                           
                    ->where( "s.date = ?", $date );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_referers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $referer
     *
     * @return array
     */
    public function findByReferer($referer)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_referers") )                           
                    ->where( "s.referer = ?", $referer );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_referers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $visites
     *
     * @return array
     */
    public function findByVisites($visites)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_referers") )                           
                    ->where( "s.visites = ?", $visites );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_referers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_referers") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_referers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $visites_veille
     *
     * @return array
     */
    public function findByVisites_veille($visites_veille)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_referers") )                           
                    ->where( "s.visites_veille = ?", $visites_veille );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_referers avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $visites_jour
     *
     * @return array
     */
    public function findByVisites_jour($visites_jour)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_referers") )                           
                    ->where( "s.visites_jour = ?", $visites_jour );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

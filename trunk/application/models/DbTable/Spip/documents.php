<?php
/**
 * Ce fichier contient la classe Spip_documents.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_documents'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_documents extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_documents';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_document';
	
    
    /**
     * Vérifie si une entrée Spip_documents existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_document'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_document; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_documents.
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
     * Recherche une entrée Spip_documents avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_documents.id_document = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_documents avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_documents.id_document = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_documents avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_documents" => "spip_documents") );
                    
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
     * Recherche une entrée Spip_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_document
     *
     * @return array
     */
    public function findById_document($id_document)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents") )                           
                    ->where( "s.id_document = ?", $id_document );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_vignette
     *
     * @return array
     */
    public function findById_vignette($id_vignette)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents") )                           
                    ->where( "s.id_vignette = ?", $id_vignette );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $extension
     *
     * @return array
     */
    public function findByExtension($extension)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents") )                           
                    ->where( "s.extension = ?", $extension );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents") )                           
                    ->where( "s.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date
     *
     * @return array
     */
    public function findByDate($date)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents") )                           
                    ->where( "s.date = ?", $date );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $descriptif
     *
     * @return array
     */
    public function findByDescriptif($descriptif)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents") )                           
                    ->where( "s.descriptif = ?", $descriptif );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $fichier
     *
     * @return array
     */
    public function findByFichier($fichier)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents") )                           
                    ->where( "s.fichier = ?", $fichier );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $taille
     *
     * @return array
     */
    public function findByTaille($taille)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents") )                           
                    ->where( "s.taille = ?", $taille );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $largeur
     *
     * @return array
     */
    public function findByLargeur($largeur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents") )                           
                    ->where( "s.largeur = ?", $largeur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $hauteur
     *
     * @return array
     */
    public function findByHauteur($hauteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents") )                           
                    ->where( "s.hauteur = ?", $hauteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('vignette','image','document') $mode
     *
     * @return array
     */
    public function findByMode($mode)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents") )                           
                    ->where( "s.mode = ?", $mode );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $distant
     *
     * @return array
     */
    public function findByDistant($distant)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents") )                           
                    ->where( "s.distant = ?", $distant );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

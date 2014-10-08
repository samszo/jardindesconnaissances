<?php
/**
 * Ce fichier contient la classe Spip_types_documents.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_types_documents'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_types_documents extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_types_documents';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'extension';
	
    
    /**
     * Vérifie si une entrée Spip_types_documents existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('extension'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->extension; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_types_documents.
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
     * Recherche une entrée Spip_types_documents avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_types_documents.extension = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_types_documents avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_types_documents.extension = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_types_documents avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_types_documents" => "spip_types_documents") );
                    
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
     * Recherche une entrée Spip_types_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $extension
     *
     * @return array
     */
    public function findByExtension($extension)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_types_documents") )                           
                    ->where( "s.extension = ?", $extension );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_types_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_types_documents") )                           
                    ->where( "s.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_types_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $descriptif
     *
     * @return array
     */
    public function findByDescriptif($descriptif)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_types_documents") )                           
                    ->where( "s.descriptif = ?", $descriptif );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_types_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $mime_type
     *
     * @return array
     */
    public function findByMime_type($mime_type)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_types_documents") )                           
                    ->where( "s.mime_type = ?", $mime_type );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_types_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','image','embed') $inclus
     *
     * @return array
     */
    public function findByInclus($inclus)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_types_documents") )                           
                    ->where( "s.inclus = ?", $inclus );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_types_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('oui','non') $upload
     *
     * @return array
     */
    public function findByUpload($upload)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_types_documents") )                           
                    ->where( "s.upload = ?", $upload );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_types_documents avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_types_documents") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

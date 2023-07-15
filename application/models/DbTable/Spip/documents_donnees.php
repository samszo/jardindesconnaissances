<?php
/**
 * Ce fichier contient la classe Spip_documents_donnees.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_documents_donnees'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_documents_donnees extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_documents_donnees';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_document';
	
    
    /**
     * Vérifie si une entrée Spip_documents_donnees existe.
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
     * Ajoute une entrée Spip_documents_donnees.
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
     * Recherche une entrée Spip_documents_donnees avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_documents_donnees.id_document = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_documents_donnees avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_documents_donnees.id_document = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_documents_donnees avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_documents_donnees" => "spip_documents_donnees") );
                    
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
     * Recherche une entrée Spip_documents_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_document
     *
     * @return array
     */
    public function findById_document($id_document)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents_donnees") )                           
                    ->where( "s.id_document = ?", $id_document );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents_donnees avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_donnee
     *
     * @return array
     */
    public function findById_donnee($id_donnee)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents_donnees") )                           
                    ->where( "s.id_donnee = ?", $id_donnee );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

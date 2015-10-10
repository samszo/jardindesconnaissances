<?php
/**
 * Ce fichier contient la classe Spip_documents_liens.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_documents_liens'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Spip_documentsliens extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_documents_liens';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_document';
	
    
    /**
     * Vérifie si une entrée Spip_documents_liens existe.
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
     * Ajoute une entrée Spip_documents_liens.
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
     * Recherche une entrée Spip_documents_liens avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_documents_liens.id_document = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_documents_liens avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_documents_liens.id_document = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_documents_liens avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_documents_liens" => "spip_documents_liens") );
                    
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
     * Recherche une entrée Spip_documents_liens avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_document
     *
     * @return array
     */
    public function findById_document($id_document)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents_liens") )                           
                    ->where( "s.id_document = ?", $id_document );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents_liens avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_objet
     *
     * @return array
     */
    public function findById_objet($id_objet)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents_liens") )                           
                    ->where( "s.id_objet = ?", $id_objet );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents_liens avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $objet
     *
     * @return array
     */
    public function findByObjet($objet)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents_liens") )                           
                    ->where( "s.objet = ?", $objet );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_documents_liens avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $vu
     *
     * @return array
     */
    public function findByVu($vu)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_documents_liens") )                           
                    ->where( "s.vu = ?", $vu );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

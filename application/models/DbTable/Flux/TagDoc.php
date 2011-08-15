<?php
/**
 * Ce fichier contient la classe Flux_TagDoc.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_TagDoc'.
 *
 * @copyright  201=& Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_TagDoc extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_TagDoc';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'tag_id';

    
    /**
     * Vérifie si une entrée Flux_TagDoc existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('tag_id'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->tag_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_TagDoc.
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
     * Recherche une entrée Flux_TagDoc avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_TagDoc.tag_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_TagDoc avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idTag
     * @param integer $idDoc
     *
     * @return void
     */
    public function remove($idTag, $idDoc)
    {
        $this->delete('flux_TagDoc.tag_id = ' . $idTag.' AND flux_TagDoc.doc_id = ' . $idDoc);
    }

    /**
     * Recherche une entrée Flux_UtiDoc avec la clef étrangère spécifiée
     * et supprime ces entrées.
     *
     * @param integer $idDoc
     *
     * @return void
     */
    public function removeDoc($idDoc)
    {
        $this->delete(' doc_id = ' . $idDoc);
    }
    
    /**
     * Récupère toutes les entrées Flux_TagDoc avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_TagDoc" => "flux_TagDoc") );
                    
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
    
    /*
     * Recherche une entrée Flux_TagDoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id
     */
    public function findByTag_id($tag_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_TagDoc") )                           
                    ->where( "f.tag_id = ?", $tag_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_TagDoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $doc_id
     */
    public function findByDoc_id($doc_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_TagDoc") )                           
                    ->where( "f.doc_id = ?", $doc_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
    select *
from flux_Tag t
inner join flux_UtiTag ut ON ut.tag_id = t.tag_id AND ut.uti_id = 453
inner join flux_TagDoc td ON td.tag_id = ut.tag_id
inner join flux_UtiDoc ud ON ud.doc_id = td.doc_id AND ud.uti_id = ut.uti_id
    */
}
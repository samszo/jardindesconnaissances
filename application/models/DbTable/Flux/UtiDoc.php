<?php

/**
 * Classe ORM qui représente la table 'flux_utidoc'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 * @ignore
 */

class Model_DbTable_Flux_UtiDoc extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_utidoc';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'uti_id';

    
    /**
     * Vérifie si une entrée flux_utidoc existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('uti_id'));
		$select->where('uti_id = ?', $data['uti_id']);
		$select->where('doc_id = ?', $data['doc_id']);
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->uti_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée flux_utidoc.
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
	    		if(!isset($data["maj"])) $data["maj"] = new Zend_Db_Expr('NOW()');
	    		$id = $this->insert($data);
	    	}
	    	return $id;
    } 
           
    /**
     * Recherche une entrée flux_utidoc avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_utidoc.uti_id = ' . $id);
    }
    
    /**
     * Recherche une entrée flux_utidoc avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_utidoc.uti_id = ' . $id);
    }

    /**
     * Recherche les entrées avec la clef primaire spécifiée
     * et supprime ces entrées.
     *
     * @param integer $id
     *
     * @return void
     */
    public function removeDoc($id)
    {
        $this->delete('doc_id = ' . $id);
    }
      
    /**
     * Récupère toutes les entrées flux_utidoc avec certains critères
     * de tri, intervalles
	 *
     * @return array
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_utidoc" => "flux_utidoc") );
                    
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
     * Recherche les docs pour un user
     * et retourne cette entrée.
     *
     * @param int $UtiId

     * @return array
     */
    public function findDocByUti($UtiId)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('d' => 'flux_doc'))
            ->joinInner(array('ud' => 'flux_utidoc'),
            	'ud.doc_id = d.doc_id', array('maj'))
            ->joinInner(array('u' => 'flux_uti'),
            	"u.uti_id = ud.uti_id AND u.uti_id  = ".$UtiId, array('login'));
            
        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche le doc le plus récent pour un user
     * et retourne cette entrée.
     *
     * @param int $UtiId

     * @return array
     */
    public function findDernierDocByUti($UtiId)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('d' => 'flux_doc'))
            ->joinInner(array('ud' => 'flux_utidoc'),
            	'ud.doc_id = d.doc_id', array('maj'))
            ->joinInner(array('u' => 'flux_uti'),
            	"u.uti_id = ud.uti_id AND u.uti_id  = ".$UtiId, array('login'));
            
        return $this->fetchAll($query)->toArray(); 
        
    }

    /**
     * Renvoie le nombre de document pour un ensemble d'utilisateur
     *
     * @param string $idsUti

     * @return array
     */
    public function getNbDocByUti($idsUti=false)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('u' => 'flux_uti'))
            ->joinInner(array('ud' => 'flux_utidoc'),
            	'ud.uti_id = u.uti_id', array('nbDoc'=>"count(ud.doc_id)"))
            ->group("u.uti_id");
        if($idsUti)$query->where("u.uti_id IN (".$idsUti.")");
            
        return $this->fetchAll($query)->toArray(); 
        
    }
    
    /*
     * Recherche une entrée flux_utidoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $doc_id
     */
    public function findByDoc_id($doc_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utidoc") )                           
                    ->where( "f.doc_id = ?", $doc_id );

        return $this->fetchAll($query)->toArray(); 
    }
    
    /*
     * Recherche une entrée flux_utidoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id
     */
    public function findByUti_id($uti_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utidoc") )                           
                    ->where( "f.uti_id = ?", $uti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    
}

<?php

/**
 * Classe ORM qui représente la table 'flux_tagdoc'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 * @ignore
 */

class Model_DbTable_flux_tagdoc extends Zend_Db_Table_Abstract
{     
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_tagdoc';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'tag_id';

    
    /**
     * Vérifie si une entrée flux_tagdoc existe.
     *
     * @param array $data
     *
     * @return array
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('tag_id'));
		$select->where('tag_id = ?', $data['tag_id']);
		$select->where('doc_id = ?', $data['doc_id']);
		$rows = $this->fetchAll($select);        
	    if($rows->count()>0)
	    	return $rows[0];
	    else 
	    	return false;
    } 
        
    /**
     * Ajoute une entrée flux_tagdoc.
     *
     * @param array $data
     * @param boolean $existe
     *  
     * @return array
     */
    public function ajouter($data, $existe=true)
    {
    	$ids=false;
    	if($existe)$ids = $this->existe($data);
    	if(!$ids){
    	 	$id = $this->insert($data);
    	}else{
    		//met à jour le poids
    		$this->ajoutPoids($data);
    	}
    	return $ids;
    } 

    /**
     * mise à jour du poids.
     *
     * @param array $data
     *
     * @return void
     */
    public function ajoutPoids($data)
    {
    	if(!isset($data["poids"]))$data["poids"]=1;        
		$sql = 'UPDATE flux_tagdoc SET poids = poids + '.$data["poids"].' WHERE tag_id = '.$data["tag_id"].' AND doc_id ='.$data["doc_id"];
    	$this->_db->query($sql);    
    }
    
    
    /**
     * Recherche une entrée flux_tagdoc avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_tagdoc.tag_id = ' . $id);
    }
    
    /**
     * Recherche une entrée flux_tagdoc avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idTag
     * @param integer $idDoc
     *
     * @return void
     */
    public function remove($idTag, $idDoc)
    {
        $this->delete('flux_tagdoc.tag_id = ' . $idTag.' AND flux_tagdoc.doc_id = ' . $idDoc);
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
     * Récupère toutes les entrées flux_tagdoc avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_tagdoc" => "flux_tagdoc") );
                    
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
     * Recherche une entrée flux_tagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id
     * @param array $arrTags
     * @param boolean $racine
     * 
     *@return array
     */
    public function findByTagId($tag_id, $arrTags=false, $racine=false)
    {
        $query = $this->select()
        	->from( array("td" => "flux_tagdoc") )                           
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->joinInner(array('d' => 'flux_doc'),
                'd.doc_id = td.doc_id')
            ->group("d.doc_id")
            ->order("d.titre");
		if($racine){
			$query->where( "d.tronc = 0");
		}

        $query = $this->getTagsCondition($query, $arrTags, $tag_id);
		
        return $this->fetchAll($query)->toArray(); 
    }

    /**
     * Recherche une entrée flux_tagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param string $tag_id
     * @param array $arrTags
     * 
     *@return array
     */
    public function findDocTroncByTagId($tag_id, $arrTags=false)
    {
        $query = $this->select()
        	->from( array("td" => "flux_tagdoc"),array())                           
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->joinInner(array('d' => 'flux_doc'),
                'd.doc_id = td.doc_id',array("idDocNote"=>"doc_id", "note"))
            ->joinInner(array('dP' => 'flux_doc'),
                'dP.doc_id = d.tronc',array("idDocPar"=>"doc_id", "titre"))
            ->group("d.doc_id")
            ->order("dP.titre");
		
        $query = $this->getTagsCondition($query, $arrTags, $tag_id);
        
		return $this->fetchAll($query)->toArray(); 
				
    }
    
    /**
     * construction des conditions pour récupèrer les documents
     * liés à une sélection de tag
     *
     * @param string $tag_id
     * @param array $arrTags
     * 
     *@return array
     */
    function getTagsCondition($query, $arrTags, $tag_id){

        //construction de la condition des tags
		if($arrTags){
			$where = " ";
			foreach ($arrTags as $tag) {
				$where .= " t.code LIKE '%".$tag."%' OR ";
			}
			$where = substr($where, 0, -3);
            $query->joinInner(array('td1' => 'flux_tagdoc'),
                	'td1.doc_id = td.doc_id',array())
            	->joinInner(array('t' => 'flux_tag'),
                	't.tag_id = td1.tag_id AND ('.$where.')',array());
		}

		$arrId = explode(",", $tag_id);
		$nbId = count($arrId);
		for ($i = 0; $i < $nbId; $i++) {
            $query->joinLeft(array('tdT'.$i => 'flux_tagdoc'),
                	'tdT'.$i.'.doc_id = td.doc_id  AND tdT'.$i.'.tag_id = '.$arrId[$i],array("tag_id"))
            	->having("tdT".$i.".tag_id = ".$arrId[$i]);
			
		}
    	
		return $query;
    }
    
    
    /**
     * Recherche une entrée flux_tagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $doc_id
     */
    public function findByDoc_id($doc_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tagdoc") )                           
                    ->where( "f.doc_id = ?", $doc_id );

        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche une entrée flux_tagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $doc_id
     */
    public function findByTag_id($doc_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tagdoc") )                           
                    ->where( "f.tag_id = ?", $doc_id );

        return $this->fetchAll($query)->toArray(); 
    }
    
    /*
    select *
from flux_Tag t
inner join flux_UtiTag ut ON ut.tag_id = t.tag_id AND ut.uti_id = 453
inner join flux_tagdoc td ON td.tag_id = ut.tag_id
inner join flux_UtiDoc ud ON ud.doc_id = td.doc_id AND ud.uti_id = ut.uti_id
    */
}

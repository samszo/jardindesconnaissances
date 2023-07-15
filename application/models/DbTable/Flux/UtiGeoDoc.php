<?php

/**
 * Classe ORM qui représente la table 'flux_utigeodoc'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 * @ignore
 */

class Model_DbTable_Flux_UtiGeoDoc extends Zend_Db_Table_Abstract
{
    
	/* Périmètre équatorial de la terre pour calculer l'indice de territorialité
	 * http://fr.wikipedia.org/wiki/Terre
	 */
	var $periTerre = 40075.017;
	var $indDeterre;

	public function __construct($config = array())
    {
    	parent::__construct($config);
		/*indice de déterriolisation
		 * 100% = la géolocalisation des utilisateurs est aux antipodes de la référence
		 * 0 % = la géolocalisation est égal à la référence
		 */
		$this->indDeterre = "SUM(f.note)*(100/(COUNT(*)*".($this->periTerre/2)."))";
    }
		
	
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_utigeodoc';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'geo_id';

    protected $_referenceMap    = array(
        'Lieux' => array(
            'columns'           => 'id_lieu',
            'refTableClass'     => 'Models_DbTable_Gevu_lieux',
            'refColumns'        => 'id_lieu'
        )
    );	
    
    /**
     * Vérifie si une entrée Flux_utigeodoc existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('geo_id'));
		foreach($data as $k=>$v){
			if($k!="maj")
				$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->geo_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_utigeodoc.
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
     * Recherche une entrée Flux_utigeodoc avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'flux_utigeodoc.geo_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_utigeodoc avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('flux_utigeodoc.geo_id = ' . $id);
    }

    /**
     * Recherche les entrées de Flux_utigeodoc avec la clef de lieu
     * et supprime ces entrées.
     *
     * @param integer $idLieu
     *
     * @return void
     */
    public function removeLieu($idLieu)
    {
		$this->delete('id_lieu = ' . $idLieu);
    }
    
    /**
     * Récupère toutes les entrées Flux_utigeodoc avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("flux_utigeodoc" => "flux_utigeodoc") );
                    
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
     * Récupère les entrées Flux_utigeodoc et les données liées avec certains critères
     * de tri, intervalles
     */
    public function getDataLiees($order=null, $where=null, $limit=0, $from=0)
    {
   	
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("ugd" => "flux_utigeodoc") )                           
            ->joinInner(array('d' => 'flux_doc'),
            	'd.doc_id = ugd.doc_id',array('url'))
            ->joinInner(array('g' => 'flux_geos'),
            	'g.geo_id = ugd.geo_id',array('lat', 'lng'))
            ->group("ugd.doc_id");
			
        if($order != null)
        {
            $query->order($order);
        }
        if($where != null)
        {
            $query->where($where);
        }

        if($limit != 0)
        {
            $query->limit($limit, $from);
        }

        return $this->fetchAll($query)->toArray();
    }
    
    	/**
     * Recherche une entrée Flux_utigeodoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $geo_id
     *
     * @return array
     */
    public function findByGeo_id($geo_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utigeodoc") )                           
                    ->where( "f.geo_id = ?", $geo_id );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_utigeodoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $doc_id
     *
     * @return array
     */
    public function findByDoc_id($doc_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utigeodoc") )                           
                    ->where( "f.doc_id = ?", $doc_id );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_utigeodoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id
     *
     * @return array
     */
    public function findByUti_id($uti_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utigeodoc") )                           
                    ->where( "f.uti_id = ?", $uti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_utigeodoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utigeodoc") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    

    /**
     * calcul l'indice de territorialité pour un ou tous document
     *
     * @param int $idDoc
     *
     * @return array
     */
    public function calcIndTerreForDoc($idDoc=false)
    {
    	//récupère la somme des distances pour le document
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table        
			->from(array("f" => "flux_utigeodoc"),array("doc_id","nb"=>"COUNT(*)", "somme"=>"SUM(f.note)", "indice"=>$this->indDeterre))
			->joinInner(array('d' => 'flux_doc'), "d.doc_id = f.doc_id",array("url"))
			->group("f.doc_id")
			->order("indice")
			->where("f.note > 0");                           
		if($idDoc)$query->where( "f.doc_id = ?", $idDoc);
        $result = $this->fetchAll($query)->toArray(); 
        
        return $result;
        
    }

    /**
     * calcul l'indice de territorialité pour un ou tous utilisateur
     *
     * @param int $idUti
     *
     * @return array
     */
    public function calcIndTerreForUti($idUti=false)
    {
    	//récupère la somme des distances pour le document
    	$query = $this->select()
			->from(array("f" => "flux_utigeodoc"),array("uti_id","nb"=>"COUNT(*)", "somme"=>"SUM(note)", "indice"=>$this->indDeterre))
			->group("uti_id")
			->order("indice")
			->where("note > 0");                           
		if($idUti)$query->where( "f.uti_id = ?", $idUti);
        $result = $this->fetchAll($query)->toArray(); 
        
        return $result;
        
    }
    
}

<?php
/**
 * Ce fichier contient la classe Flux_utigeodoc.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_utigeodoc'.
 *
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_UtiGeoDoc extends Zend_Db_Table_Abstract
{
    
	/* Périmètre équatorial de la terre pour calculer l'indice de territorialité
	 * http://fr.wikipedia.org/wiki/Terre
	 */
	var $periTerre = 40075.017;
	
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
     * calcul l'indice de territorialité pour un document
     *
     * @param int $idDoc
     *
     * @return array
     */
    public function calcIndTerreForDoc($idDoc=false)
    {
    	//récupère la somme des distances pour le document
        $query = $this->select()
			->from(array("f" => "flux_utigeodoc"),array("nb"=>"COUNT(*)", "somme"=>"SUM(note)", "indice"=>"COUNT(*)/SUM(note)/".$this->periTerre/2))
			->group("doc_id")
			->order("doc_id")
			->where("note > 0");                           
		if($idDoc)$query->where( "f.doc_id = ?", $idDoc);
        $result = $this->fetchAll($query)->toArray(); 
        
        return $result;
        
    }
    
}

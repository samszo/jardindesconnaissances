<?php
/**
 * Ce fichier contient la classe Flux_geos.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_geos'.
 *
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Model_DbTable_Flux_Geos extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_geos';
    
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
     * Vérifie si une entrée Flux_geos existe.
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
			if($k!="maj")$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->geo_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_geos.
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
     * Recherche une entrée Flux_geos avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'flux_geos.geo_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_geos avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('flux_geos.geo_id = ' . $id);
    }

    /**
     * Recherche les entrées de Flux_geos avec la clef de lieu
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
     * Récupère toutes les entrées Flux_geos avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("flux_geos" => "flux_geos") );
                    
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
     * Recherche une entrée Flux_geos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $geo_id
     *
     * @return array
     */
    public function findByGeo_id($geo_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_geos") )                           
                    ->where( "f.geo_id = ?", $geo_id );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_geos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_instant
     *
     * @return array
     */
    public function findById_instant($id_instant)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_geos") )                           
                    ->where( "f.id_instant = ?", $id_instant );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_geos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param decimal $lat
     *
     * @return array
     */
    public function findByLat($lat)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_geos") )                           
                    ->where( "f.lat = ?", $lat );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_geos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param decimal $lng
     *
     * @return array
     */
    public function findByLng($lng)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_geos") )                           
                    ->where( "f.lng = ?", $lng );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_geos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $zoom_min
     *
     * @return array
     */
    public function findByZoom_min($zoom_min)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_geos") )                           
                    ->where( "f.zoom_min = ?", $zoom_min );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_geos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $zoom_max
     *
     * @return array
     */
    public function findByZoom_max($zoom_max)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_geos") )                           
                    ->where( "f.zoom_max = ?", $zoom_max );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_geos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $adresse
     *
     * @return array
     */
    public function findByAdresse($adresse)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_geos") )                           
                    ->where( "f.adresse = ?", $adresse );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_geos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $codepostal
     *
     * @return array
     */
    public function findByCodepostal($codepostal)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_geos") )                           
                    ->where( "f.codepostal = ?", $codepostal );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_geos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $ville
     *
     * @return array
     */
    public function findByVille($ville)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_geos") )                           
                    ->where( "f.ville = ?", $ville );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_geos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $pays
     *
     * @return array
     */
    public function findByPays($pays)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_geos") )                           
                    ->where( "f.pays = ?", $pays );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_geos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $kml
     *
     * @return array
     */
    public function findByKml($kml)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_geos") )                           
                    ->where( "f.kml = ?", $kml );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_geos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $type_carte
     *
     * @return array
     */
    public function findByType_carte($type_carte)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_geos") )                           
                    ->where( "f.type_carte = ?", $type_carte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_geos avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_geos") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

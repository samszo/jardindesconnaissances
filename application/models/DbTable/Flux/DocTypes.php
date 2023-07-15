<?php


/**
 * Classe ORM qui représente la table 'flux_doctypes'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Model_DbTable_Flux_DocTypes extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_doctypes';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_type';

    protected $_referenceMap    = array(
        'Lieux' => array(
            'columns'           => 'id_lieu',
            'refTableClass'     => 'Models_DbTable_Gevu_lieux',
            'refColumns'        => 'id_lieu'
        )
    );	
    
    /**
     * Vérifie si une entrée Flux_doctypes existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_type'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_type; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_doctypes.
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
     * Recherche une entrée Flux_doctypes avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'flux_doctypes.id_type = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_doctypes avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('flux_doctypes.id_type = ' . $id);
    }

    /**
     * Recherche les entrées de Flux_doctypes avec la clef de lieu
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
     * Récupère toutes les entrées Flux_doctypes avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("flux_doctypes" => "flux_doctypes") );
                    
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
     * Recherche une entrée Flux_doctypes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_type
     *
     * @return array
     */
    public function findById_type($id_type)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doctypes") )                           
                    ->where( "f.id_type = ?", $id_type );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_doctypes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doctypes") )                           
                    ->where( "f.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_doctypes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $descriptif
     *
     * @return array
     */
    public function findByDescriptif($descriptif)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doctypes") )                           
                    ->where( "f.descriptif = ?", $descriptif );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_doctypes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $extension
     *
     * @return array
     */
    public function findByExtension($extension)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doctypes") )                           
                    ->where( "f.extension = ?", $extension );

        return $this->fetchAll($query)->toArray(); 
    }

    	/**
     * Recherche une entrée Flux_doctypes avec la valeur spécifiée
     * et retourne son identifiant.
     *
     * @param varchar $extension
     *
     * @return integer
     */
    public function getIdByExtension($extension)
    {
        $arr = $this->findByExtension($extension);
        if(isset($arr[0]['id_type']))$idType = $arr[0]['id_type'];
        else $idType = -1;
        
        return $idType; 
    }
    
    /**
     * Recherche une entrée Flux_doctypes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $mime_type
     *
     * @return array
     */
    public function findByMime_type($mime_type)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doctypes") )                           
                    ->where( "f.mime_type = ?", $mime_type );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_doctypes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','image','embed') $inclus
     *
     * @return array
     */
    public function findByInclus($inclus)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doctypes") )                           
                    ->where( "f.inclus = ?", $inclus );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_doctypes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('oui','non') $upload
     *
     * @return array
     */
    public function findByUpload($upload)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doctypes") )                           
                    ->where( "f.upload = ?", $upload );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Flux_doctypes avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doctypes") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

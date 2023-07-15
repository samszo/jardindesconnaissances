<?php
/**
 * Ce fichier contient la classe Flux_Spip.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 * @ignore
 */

class Model_DbTable_Flux_Spip extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'flux_spip';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_flux_spip';
    
    /**
     * Vérifie si une entrée flux_spip existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_flux_spip'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_flux_spip; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée flux_spip.
     *
     * @param array $data
     * @param boolean $existe
     * @param boolean $rs
     *  
     * @return integer
     */
    public function ajouter($data, $existe=true, $rs=false)
    {
    	
	    	$id=false;
	    	if($existe)$id = $this->existe($data);
	    	if(!$id){
	    	 	$id = $this->insert($data);
	    	}
	    	if($rs)
			return $this->findById_spip($id);
	    	else
		    	return $id;
    } 
           
    /**
     * Recherche une entrée flux_spip avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'flux_spip.id_flux_spip = ' . $id);
    }
    
    /**
     * Recherche une entrée flux_spip avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
  	  	$this->delete('flux_spip.id_flux_spip = ' . $id);
    }

    
    /**
     * Récupère toutes les entrées flux_spip avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("flux_spip" => "flux_spip") );
                    
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
     * Recherche une entrée flux_spip avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_spip
     *
     * @return array
     */
    public function findById_spip($id_spip)
    {
        $query = $this->select()
            ->from( array("p" => "flux_spip"))                           
            ->where("p.id_spip = ?", $id_spip);
                    
        return $this->fetchAll($query)->toArray(); 
    }

    	/**
     * Recherche une entrée flux_spip avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $idflux
     * @param string $objflux
     * @param string $objSpip
     *
     * @return array
     */
    public function findIdSpip($idflux, $objflux, $objSpip)
    {
        $query = $this->select()
            ->from( array("p" => "flux_spip"),"id_spip")                           
            ->where("p.id_flux = ?", $idflux)
            ->where("p.obj_flux = ?", $objflux)
            ->where("p.obj_spip = ?", $objSpip);
        return $this->fetchAll($query)->toArray(); 
    }
    
    	/**
     * Recherche une entrée flux_spip avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $idflux
     * @param string $objflux
     * @param string $objSpip
     * @param string $idBase
     *
     * @return array
     */
    public function findArtSpip($idflux, $objflux, $objSpip, $idBase)
    {
        $query = $this->select()
            ->from( array("p" => "flux_spip"),"id_spip")                           
			->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->joinInner(array("a" => $idBase.".spip_articles"),
                'a.id_article = p.id_spip', array("id_article","surtitre","titre","soustitre"
            		,"id_rubrique","descriptif","chapo","texte","ps","date","statut","id_secteur"
            		,"maj","date_redac","lang","id_trad"))
            ->where("p.id_flux = ?", $idflux)
            ->where("p.obj_flux = ?", $objflux)
            ->where("p.obj_spip = ?", $objSpip);
        return $this->fetchAll($query)->toArray(); 
    }
    
}
<?php
/**
 * Ce fichier contient la classe Spip_mots_liens.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_mots_liens'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Spip_motsliens extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_mots_liens';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = array('id_objet','id_mot','objet');
	
    
    /**
     * Vérifie si une entrée Spip_mots_liens existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_objet','id_mot','objet'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_objet; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_mots_liens.
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
     * Recherche une entrée Spip_mots_liens avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_mots_liens.id_mot = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_mots_liens avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idObjet
     * @param integer $idmot
     * @param string $objet
     *
     * @return void
     */
    public function remove($idObjet,$idmot,$objet)
    {
    		$this->delete('spip_mots_liens.id_objet = '.$idObjet.' AND id_mot ='.$idmot.' AND objet="'.$objet.'"' );
    }
    
    /**
     * Récupère toutes les entrées Spip_mots_liens avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_mots_liens" => "spip_mots_liens") );
                    
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
     * Recherche une entrée Spip_mots_liens avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_mot
     *
     * @return array
     */
    public function findById_mot($id_mot)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots_liens") )                           
                    ->where( "s.id_mot = ?", $id_mot );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_mots_liens avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_objet
     *
     * @return array
     */
    public function findById_objet($id_objet)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots_liens") )                           
                    ->where( "s.id_objet = ?", $id_objet );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_mots_liens avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $objet
     *
     * @return array
     */
    public function findByObjet($objet)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots_liens") )                           
                    ->where( "s.objet = ?", $objet );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_mots_liens avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $vu
     *
     * @return array
     */
    public function findByVu($vu)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_mots_liens") )                           
                    ->where( "s.vu = ?", $vu );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

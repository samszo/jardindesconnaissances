<?php
/**
 * Ce fichier contient la classe Spip_auteurs_liens.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_auteurs_liens'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Spip_auteursliens extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_auteurs_liens';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = array('id_objet','id_auteur','objet');
	
    
    /**
     * Vérifie si une entrée Spip_auteurs_liens existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_objet','id_auteur','objet'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_objet; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_auteurs_liens.
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
     * Recherche une entrée Spip_auteurs_liens avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_auteurs_liens.id_auteur = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_auteurs_liens avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idObjet
     * @param integer $idAuteur
     * @param string $objet
     *
     * @return void
     */
    public function remove($idObjet,$idAuteur,$objet)
    {
    		$this->delete('spip_auteurs_liens.id_objet = '.$idObjet.' AND id_auteur ='.$idAuteur.' AND objet="'.$objet.'"' );
    }
    
    /**
     * Récupère toutes les entrées Spip_auteurs_liens avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_auteurs_liens" => "spip_auteurs_liens") );
                    
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
     * Recherche une entrée Spip_auteurs_liens avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_auteur
     *
     * @return array
     */
    public function findById_auteur($id_auteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_liens") )                           
                    ->where( "s.id_auteur = ?", $id_auteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs_liens avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_objet
     *
     * @return array
     */
    public function findById_objet($id_objet)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_liens") )                           
                    ->where( "s.id_objet = ?", $id_objet );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs_liens avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $objet
     *
     * @return array
     */
    public function findByObjet($objet)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_liens") )                           
                    ->where( "s.objet = ?", $objet );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_auteurs_liens avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $vu
     *
     * @return array
     */
    public function findByVu($vu)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_auteurs_liens") )                           
                    ->where( "s.vu = ?", $vu );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

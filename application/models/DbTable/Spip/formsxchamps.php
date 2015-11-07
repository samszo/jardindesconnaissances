<?php
/**
 * Ce fichier contient la classe Spip_forms_champs.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_forms_champs'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Spip_formsxchamps extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_forms_champs';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_form';
	
    
    /**
     * Vérifie si une entrée Spip_forms_champs existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_form'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_form; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_forms_champs.
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
     * Recherche une entrée Spip_forms_champs avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_forms_champs.id_form = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_forms_champs avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_forms_champs.id_form = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_forms_champs avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_forms_champs" => "spip_forms_champs") );
                    
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
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_form
     *
     * @return array
     */
    public function findById_form($id_form)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.id_form = ?", $id_form );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $champ
     *
     * @return array
     */
    public function findByChamp($champ)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.champ = ?", $champ );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $rang
     *
     * @return array
     */
    public function findByRang($rang)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.rang = ?", $rang );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $type
     *
     * @return array
     */
    public function findByType($type)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.type = ?", $type );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $taille
     *
     * @return array
     */
    public function findByTaille($taille)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.taille = ?", $taille );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $obligatoire
     *
     * @return array
     */
    public function findByObligatoire($obligatoire)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.obligatoire = ?", $obligatoire );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $extra_info
     *
     * @return array
     */
    public function findByExtra_info($extra_info)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.extra_info = ?", $extra_info );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $specifiant
     *
     * @return array
     */
    public function findBySpecifiant($specifiant)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.specifiant = ?", $specifiant );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $listable_admin
     *
     * @return array
     */
    public function findByListable_admin($listable_admin)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.listable_admin = ?", $listable_admin );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $listable
     *
     * @return array
     */
    public function findByListable($listable)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.listable = ?", $listable );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $public
     *
     * @return array
     */
    public function findByPublic($public)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.public = ?", $public );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $saisie
     *
     * @return array
     */
    public function findBySaisie($saisie)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.saisie = ?", $saisie );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $aide
     *
     * @return array
     */
    public function findByAide($aide)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.aide = ?", $aide );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms_champs avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $html_wrap
     *
     * @return array
     */
    public function findByHtml_wrap($html_wrap)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms_champs") )                           
                    ->where( "s.html_wrap = ?", $html_wrap );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

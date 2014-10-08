<?php
/**
 * Ce fichier contient la classe Spip_evenements.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_evenements'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
//ATTENTION le "s" de Models est nécessaire pour une compatibilité entre application et serveur
class Models_DbTable_Spip_evenements extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_evenements';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_evenement';
	
    
    /**
     * Vérifie si une entrée Spip_evenements existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_evenement'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_evenement; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_evenements.
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
     * Recherche une entrée Spip_evenements avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_evenements.id_evenement = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_evenements avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_evenements.id_evenement = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_evenements avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_evenements" => "spip_evenements") );
                    
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
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_evenement
     *
     * @return array
     */
    public function findById_evenement($id_evenement)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.id_evenement = ?", $id_evenement );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_article
     *
     * @return array
     */
    public function findById_article($id_article)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.id_article = ?", $id_article );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_debut
     *
     * @return array
     */
    public function findByDate_debut($date_debut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.date_debut = ?", $date_debut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_fin
     *
     * @return array
     */
    public function findByDate_fin($date_fin)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.date_fin = ?", $date_fin );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $doctorant
     *
     * @return array
     */
    public function findByDoctorant($doctorant)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.doctorant = ?", $doctorant );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $descriptif
     *
     * @return array
     */
    public function findByDescriptif($descriptif)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.descriptif = ?", $descriptif );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $lieu
     *
     * @return array
     */
    public function findByLieu($lieu)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.lieu = ?", $lieu );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('oui','non') $horaire
     *
     * @return array
     */
    public function findByHoraire($horaire)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.horaire = ?", $horaire );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_evenement_source
     *
     * @return array
     */
    public function findById_evenement_source($id_evenement_source)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.id_evenement_source = ?", $id_evenement_source );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('','1','non','oui','idx') $idx
     *
     * @return array
     */
    public function findByIdx($idx)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.idx = ?", $idx );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $adresse
     *
     * @return array
     */
    public function findByAdresse($adresse)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.adresse = ?", $adresse );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $inscription
     *
     * @return array
     */
    public function findByInscription($inscription)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.inscription = ?", $inscription );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_evenements avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $places
     *
     * @return array
     */
    public function findByPlaces($places)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_evenements") )                           
                    ->where( "s.places = ?", $places );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

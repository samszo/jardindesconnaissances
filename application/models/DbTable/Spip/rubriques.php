<?php
/**
 * Ce fichier contient la classe Spip_rubriques.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_rubriques'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Spip_rubriques extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_rubriques';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_rubrique';
	

	//ATTENTION SPIP 3 supprime 
	// Model_DbTable_Spip_auteursrubriques remplacé par spip_auteurs_liens
	// Model_DbTable_Spip_motsrubriques remplacé par spip_auteurs_liens Model_DbTable_Spip_motsliens
    protected $_dependentTables = array(
    		"Model_DbTable_Spip_auteursliens"
       ,"Model_DbTable_Spip_documentsliens"
       ,"Model_DbTable_Spip_motsliens"
       ,"Model_DbTable_Spip_articles"
       );
    
    /**
     * Vérifie si une entrée Spip_rubriques existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_rubrique'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_rubrique; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_rubriques.
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
    		if(!isset($data["date"])) $data["date"] = new Zend_Db_Expr('NOW()');
    		$id = $this->insert($data);
    	}
    	return $id;
    } 
           
    /**
     * Recherche une entrée Spip_rubriques avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_rubriques.id_rubrique = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_rubriques avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $dt = $this->getDependentTables();
        foreach($dt as $t){
        	//echo $t;
        	$dbT = new $t($this->_db);
        	if($t=="Model_DbTable_Spip_documentsliens" || $t=="Model_DbTable_Spip_auteursliens" || $t=="Model_DbTable_Spip_motsliens" )
		    $dbT->delete('objet="rubrique" AND id_objet = '.$id);
        	elseif($t=="Model_DbTable_Spip_articles")
		    $dbT->removeRub($id);
		else
			$dbT->delete('id_rubrique = '.$id);
        }            	    	
    	$this->delete('spip_rubriques.id_rubrique = ' . $id);
    	
    }

    /**
     * Recherche une entrée Spip_rubriques avec la clef primaire spécifiée
     * et supprime cette entrée et les sous-entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function removeAll($id)
    {
	    	$arr = $this->findById_parent($id);
	    	foreach ($arr as $rub) {
	    		$this->removeAll($rub["id_rubrique"]);
	    	}
	    	$this->remove($id);
    }
    
    /**
     * Récupère toutes les entrées Spip_rubriques avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_rubriques" => "spip_rubriques") );
                    
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
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_rubrique
     *
     * @return array
     */
    public function findById_rubrique($id_rubrique)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.id_rubrique = ?", $id_rubrique );

         $rs = $this->fetchAll($query)->toArray(); 
         if(count($rs))return $rs[0]; else return false;
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_parent
     *
     * @return array
     */
    public function findById_parent($id_parent)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.id_parent = ?", $id_parent );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text 		$titre
     * @param boolean 	$like
     *
     * @return array
     */
    public function findByTitre($titre, $like=FALSE)
    {
        $query = $this->select()
			->from( array("s" => "spip_rubriques") );
		if($like)
			$query->where( "s.titre LIKE '%".$titre."%'" );
		else
			$query->where( "s.titre = ?", $titre );
		
        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $descriptif
     *
     * @return array
     */
    public function findByDescriptif($descriptif)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.descriptif = ?", $descriptif );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $texte
     *
     * @return array
     */
    public function findByTexte($texte)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.texte = ?", $texte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_secteur
     *
     * @return array
     */
    public function findById_secteur($id_secteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.id_secteur = ?", $id_secteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $export
     *
     * @return array
     */
    public function findByExport($export)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.export = ?", $export );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_import
     *
     * @return array
     */
    public function findById_import($id_import)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.id_import = ?", $id_import );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $statut
     *
     * @return array
     */
    public function findByStatut($statut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.statut = ?", $statut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date
     *
     * @return array
     */
    public function findByDate($date)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.date = ?", $date );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lang
     *
     * @return array
     */
    public function findByLang($lang)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.lang = ?", $lang );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $langue_choisie
     *
     * @return array
     */
    public function findByLangue_choisie($langue_choisie)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.langue_choisie = ?", $langue_choisie );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $extra
     *
     * @return array
     */
    public function findByExtra($extra)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.extra = ?", $extra );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url_propre
     *
     * @return array
     */
    public function findByUrl_propre($url_propre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.url_propre = ?", $url_propre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $statut_tmp
     *
     * @return array
     */
    public function findByStatut_tmp($statut_tmp)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.statut_tmp = ?", $statut_tmp );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_tmp
     *
     * @return array
     */
    public function findByDate_tmp($date_tmp)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.date_tmp = ?", $date_tmp );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_rubriques avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param tinyint $agenda
     *
     * @return array
     */
    public function findByAgenda($agenda)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_rubriques") )                           
                    ->where( "s.agenda = ?", $agenda );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

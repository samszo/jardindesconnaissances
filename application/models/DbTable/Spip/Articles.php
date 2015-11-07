<?php
/**
 * Ce fichier contient la classe Spip_articles.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_articles'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Spip_articles extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_articles';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_article';

    protected $_dependentTables = array(
       "Model_DbTable_Spip_auteursliens"
       ,"Model_DbTable_Spip_documentsliens"
       ,"Model_DbTable_Spip_motsliens"
       ,"Model_DbTable_Spip_visitesarticles"
       );
    

/**
     * Recherche des entrées avec une requête fulltext
     *
     * @param string $filtre
     * @param array $cols
     *
     * @return array
     */
    public function findFullText($txt)
    {
    	$txt = str_replace("'", " ", $txt);
        $query = $this->select()
            ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("a" => "spip_articles"), array("score"=>"MATCH(a.texte) AGAINST('".$txt."')", "titre", "texte", "id_article", "id_rubrique") )                           
            ->joinInner(array('r' => 'spip_rubriques'),
                'r.id_rubrique = a.id_rubrique',array('rubTitre'=>'titre', 'id_parent'))
            ->joinLeft(array('da' => 'spip_documents_articles'),
                'da.id_article = a.id_article',array())
            ->joinLeft(array('d' => 'spip_documents'),
                'd.id_document = da.id_document',array('docTitre'=>'titre', 'fichier', 'id_type'))
            ->where("MATCH(a.texte) AGAINST('".$txt."')")
        	->order("r.id_rubrique")
			;
		
        return $this->fetchAll($query)->toArray(); 
    } 
        
    /**
     * Vérifie si une entrée Spip_articles existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_article'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_article; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Spip_articles.
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
     * Recherche une entrée Spip_articles avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_articles.id_article = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_articles avec la clef primaire spécifiée
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
		        $dbT->delete('objet="article" AND id_objet = '.$id);
        	else
		        $dbT->delete('id_article = '.$id);
        }            	    	
    	$this->delete('spip_articles.id_article = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_articles avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function removeRub($id)
    {
    	$arr = $this->findById_rubrique($id);
        foreach($arr as $a){
        	$this->remove($a["id_article"]);
        }            	    	
    }
    
    
    /**
     * Récupère toutes les entrées Spip_articles avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_articles" => "spip_articles") );
                    
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
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_article
     *
     * @return array
     */
    public function findById_article($id_article)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.id_article = ?", $id_article );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $surtitre
     *
     * @return array
     */
    public function findBySurtitre($surtitre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.surtitre = ?", $surtitre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $soustitre
     *
     * @return array
     */
    public function findBySoustitre($soustitre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.soustitre = ?", $soustitre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_rubrique
     * @param string $statut
     *
     * @return array
     */
    public function findById_rubrique($id_rubrique, $statut="publie")
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.statut = ?", $statut )
                    ->where( "s.id_rubrique = ?", $id_rubrique );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $descriptif
     *
     * @return array
     */
    public function findByDescriptif($descriptif)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.descriptif = ?", $descriptif );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $chapo
     *
     * @return array
     */
    public function findByChapo($chapo)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.chapo = ?", $chapo );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $texte
     *
     * @return array
     */
    public function findByTexte($texte)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.texte = ?", $texte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $ps
     *
     * @return array
     */
    public function findByPs($ps)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.ps = ?", $ps );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date
     *
     * @return array
     */
    public function findByDate($date)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.date = ?", $date );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $statut
     *
     * @return array
     */
    public function findByStatut($statut)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.statut = ?", $statut );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_secteur
     *
     * @return array
     */
    public function findById_secteur($id_secteur)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.id_secteur = ?", $id_secteur );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $export
     *
     * @return array
     */
    public function findByExport($export)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.export = ?", $export );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_redac
     *
     * @return array
     */
    public function findByDate_redac($date_redac)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.date_redac = ?", $date_redac );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $visites
     *
     * @return array
     */
    public function findByVisites($visites)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.visites = ?", $visites );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $referers
     *
     * @return array
     */
    public function findByReferers($referers)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.referers = ?", $referers );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param double $popularite
     *
     * @return array
     */
    public function findByPopularite($popularite)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.popularite = ?", $popularite );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param char $accepter_forum
     *
     * @return array
     */
    public function findByAccepter_forum($accepter_forum)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.accepter_forum = ?", $accepter_forum );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $date_modif
     *
     * @return array
     */
    public function findByDate_modif($date_modif)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.date_modif = ?", $date_modif );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $lang
     *
     * @return array
     */
    public function findByLang($lang)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.lang = ?", $lang );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $langue_choisie
     *
     * @return array
     */
    public function findByLangue_choisie($langue_choisie)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.langue_choisie = ?", $langue_choisie );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_trad
     *
     * @return array
     */
    public function findById_trad($id_trad)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.id_trad = ?", $id_trad );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param longtext $extra
     *
     * @return array
     */
    public function findByExtra($extra)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.extra = ?", $extra );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id_version
     *
     * @return array
     */
    public function findById_version($id_version)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.id_version = ?", $id_version );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param text $nom_site
     *
     * @return array
     */
    public function findByNom_site($nom_site)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.nom_site = ?", $nom_site );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url_site
     *
     * @return array
     */
    public function findByUrl_site($url_site)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.url_site = ?", $url_site );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url_propre
     *
     * @return array
     */
    public function findByUrl_propre($url_propre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.url_propre = ?", $url_propre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_articles avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $version_of
     *
     * @return array
     */
    public function findByVersion_of($version_of)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_articles") )                           
                    ->where( "s.version_of = ?", $version_of );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

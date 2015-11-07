<?php
/**
 * Ce fichier contient la classe Spip_forms.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_forms'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Spip_forms extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'spip_forms';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'id_form';
	
    
    /**
     * Vérifie si une entrée Spip_forms existe.
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
     * Ajoute une entrée Spip_forms.
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
     * Recherche une entrée Spip_forms avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'spip_forms.id_form = ' . $id);
    }
    
    /**
     * Recherche une entrée Spip_forms avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	$this->delete('spip_forms.id_form = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Spip_forms avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("spip_forms" => "spip_forms") );
                    
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
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $id_form
     *
     * @return array
     */
    public function findById_form($id_form)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.id_form = ?", $id_form );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $descriptif
     *
     * @return array
     */
    public function findByDescriptif($descriptif)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.descriptif = ?", $descriptif );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $type_form
     *
     * @return array
     */
    public function findByType_form($type_form)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.type_form = ?", $type_form );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $modifiable
     *
     * @return array
     */
    public function findByModifiable($modifiable)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.modifiable = ?", $modifiable );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $multiple
     *
     * @return array
     */
    public function findByMultiple($multiple)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.multiple = ?", $multiple );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $forms_obligatoires
     *
     * @return array
     */
    public function findByForms_obligatoires($forms_obligatoires)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.forms_obligatoires = ?", $forms_obligatoires );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $email
     *
     * @return array
     */
    public function findByEmail($email)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.email = ?", $email );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $champconfirm
     *
     * @return array
     */
    public function findByChampconfirm($champconfirm)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.champconfirm = ?", $champconfirm );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $texte
     *
     * @return array
     */
    public function findByTexte($texte)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.texte = ?", $texte );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $moderation
     *
     * @return array
     */
    public function findByModeration($moderation)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.moderation = ?", $moderation );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $public
     *
     * @return array
     */
    public function findByPublic($public)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.public = ?", $public );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $linkable
     *
     * @return array
     */
    public function findByLinkable($linkable)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.linkable = ?", $linkable );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $documents
     *
     * @return array
     */
    public function findByDocuments($documents)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.documents = ?", $documents );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $documents_mail
     *
     * @return array
     */
    public function findByDocuments_mail($documents_mail)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.documents_mail = ?", $documents_mail );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param enum('non','oui') $arborescent
     *
     * @return array
     */
    public function findByArborescent($arborescent)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.arborescent = ?", $arborescent );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param mediumtext $html_wrap
     *
     * @return array
     */
    public function findByHtml_wrap($html_wrap)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.html_wrap = ?", $html_wrap );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param timestamp $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param bigint $num_rubrique_export
     *
     * @return array
     */
    public function findByNum_rubrique_export($num_rubrique_export)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.num_rubrique_export = ?", $num_rubrique_export );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * Recherche une entrée Spip_forms avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $champ_titre_export
     *
     * @return array
     */
    public function findByChamp_titre_export($champ_titre_export)
    {
        $query = $this->select()
                    ->from( array("s" => "spip_forms") )                           
                    ->where( "s.champ_titre_export = ?", $champ_titre_export );

        return $this->fetchAll($query)->toArray(); 
    }
    	/**
     * renvoie les données d'un formulaire
     *
     * @param int $idForm
     *
     * @return array
     */
    public function getDonneesDetails($idForm)
    {
        $arrForm = $this->findById_form($idForm);
        
        $dbC = new Model_DbTable_Spip_formsxchamps($this->_db);
        $arrChamp = $dbC->findById_form($idForm);
        
        $dbD = new Model_DbTable_Spip_formsxdonnees($this->_db);
        $arrDonnees = $dbD->findByIdForm($idForm);
         
        
        return array("form"=>$arrForm,"champs"=>$arrChamp,"donnees"=>$arrDonnees); 
    }
    
    	/**
     * renvoie proprement les données d'un formulaire 
     *
     * @param int $idForm
     * @param string 	$statut
     *
     * @return array
     */
    public function getDonneesDetailsPropre($idForm, $auteur=true, $statut="publie")
    {
        $arrForm = $this->findById_form($idForm);
        
        $dbC = new Model_DbTable_Spip_formsxchamps($this->_db);
        $arrChamp = $dbC->findById_form($idForm);

        //création de la requète
        $expIdArticle = "SUBSTRING(fd.url,INSTR(fd.url, 'id_article=')+11)";
        $query = $this->select()
            ->from( array("fd" => "spip_forms_donnees")
            		, array("id_donnee", "date", "url", "ip", "idArticle"=>new Zend_Db_Expr($expIdArticle)) 
            		)                           
			->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
			->joinInner(array('autEval' => 'spip_auteurs')
				,"autEval.id_auteur = fd.id_auteur",array('auteurEval'=>'nom'))
			->joinInner(array('art' => 'spip_articles')
				,"art.id_article = ".$expIdArticle,array('titre'));
		if($auteur){
			$query->joinInner(array('al' => 'spip_auteurs_liens')
				,"al.id_objet = art.id_article AND al.objet = 'article'",array())	
			->joinInner(array('autArt' => 'spip_auteurs')
			,"autArt.id_auteur = al.id_auteur",array('auteurArt'=>'nom'));
		}
		$query->where( "fd.statut = ?", $statut )
            ->where( "fd.id_form = ?", $idForm );
        foreach ($arrChamp as $champ) {
			if($champ["type"]=="select"){
				$query->joinInner(array('fdc'.$champ["rang"] => 'spip_forms_donnees_champs')
					,"fdc".$champ["rang"].".id_donnee = fd.id_donnee AND fdc".$champ["rang"].".champ = '".$champ["champ"]."'",array())				
					->joinInner(array('fcc'.$champ["rang"] => 'spip_forms_champs_choix')
					,"fcc".$champ["rang"].".id_form = fd.id_form AND fcc".$champ["rang"].".champ = '".$champ["champ"]."' AND  fcc".$champ["rang"].".choix = fdc".$champ["rang"].".valeur ",array($champ["titre"]=>'titre'));				
			}else{
				$query->joinInner(array('fdc'.$champ["rang"] => 'spip_forms_donnees_champs')
					,"fdc".$champ["rang"].".id_donnee = fd.id_donnee AND fdc".$champ["rang"].".champ = '".$champ["champ"]."'",array($champ["titre"]=>'valeur'));				
			}
        }
        //exécution de la requête
        $arrDonnees = $this->fetchAll($query)->toArray();          
        
        return array("form"=>$arrForm,"donnees"=>$arrDonnees); 
    }
    
}

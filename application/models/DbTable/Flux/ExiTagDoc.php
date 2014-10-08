<?php
/**
 * Ce fichier contient la classe Flux_exitagdoc.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_exitagdoc'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_ExiTagDoc extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_exitagdoc';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'exitagdoc_id';


	public function __construct($config = array())
    {
    	parent::__construct($config);

    }
	
	
    /**
     * Vérifie si une entrée Flux_exitagdoc existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('exitagdoc_id'));
		$select->where('exi_id = ?', $data['exi_id']);
		$select->where('tag_id = ?', $data['tag_id']);
		$select->where('doc_id = ?', $data['doc_id']);
		$rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->exitagdoc_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_exitagdoc.
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
    		if(!isset($data["maj"])) $data["maj"] = new Zend_Db_Expr('NOW()');
    		$id = $this->insert($data);
    	}else{
    		//met à jour le poids
    		$data['exitagdoc_id'] = $id;
    		$this->ajoutPoids($data);    		
    	}
    	return $id;
    } 
           
    /**
     * mise à jour du poids.
     *
     * @param array $data
     *
     * @return void
     */
    public function ajoutPoids($data)
    {
    	if(!isset($data["poids"]))$data["poids"]=1;        
		$sql = 'UPDATE flux_exitagdoc SET poids = poids + '.$data["poids"].' WHERE exitagdoc_id = '.$data["exitagdoc_id"];
    	$this->_db->query($sql);    
    }
    
    /**
     * Recherche une entrée Flux_exitagdoc avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_exitagdoc.uti_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_exitagdoc avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_exitagdoc.uti_id = ' . $id);
    }

    /**
     * Recherche une entrée Flux_exitagdoc avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $idDoc
     * @param integer $idTag
     * @param integer $idUti
     *
     * @return void
     */
    public function removeDocTagUti($idDoc=false, $idTag=false, $idUti=false)
    {
    	$where = "";
    	if($idDoc) $where = 'flux_exitagdoc.doc_id = ' . $idDoc;
    	if($idTag){
    		if($where) $where.= " AND ";
    		$where .= 'flux_exitagdoc.tag_id = ' . $idTag;
    	}
    	if($idUti){
    		if($where) $where.= " AND ";
    		$where .= 'flux_exitagdoc.uti_id = ' . $idUti;
    	}
    	if($where) $this->delete($where);
    }

    /**
     * Recherche les entrées avec la clef primaire spécifiée
     * et supprime ces entrées.
     *
     * @param integer $id
     *
     * @return void
     */
    public function removeDoc($id)
    {
        $this->delete('doc_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Flux_exitagdoc avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_exitagdoc" => "flux_exitagdoc") );
                    
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
    
    /*
     * Retourne les entrées de Flux_exitagdoc
     * 
     *
     * 
     */
    public function getAllInfo()
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_exitagdoc") )                           
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = utd.tag_id',array('code'))
        	->joinInner(array('d' => 'flux_doc'),
            	'd.doc_id = utd.doc_id',array('url','titre'));
			
        return $this->fetchAll($query)->toArray(); 
    }
    
    /*
     * Recherche une entrée Flux_exitagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id
     */
    public function findByUti_id($uti_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exitagdoc") )                           
                    ->where( "f.uti_id = ?", $uti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_exitagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id
     */
    public function findByTagId($tag_id)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_exitagdoc") )                           
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = utd.tag_id',array('code'))
        	->joinInner(array('d' => 'flux_doc'),
            	'd.doc_id = utd.doc_id',array('url','titre'))
        	->where( "utd.tag_id = ?", $tag_id );

        return $this->fetchAll($query)->toArray(); 
    }

    /*
     * Recherche une entrée Flux_exitagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag
     */
    public function findByTag($tag)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_exitagdoc") )                           
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = utd.tag_id',array('code'))
        	->joinInner(array('d' => 'flux_doc'),
            	'd.doc_id = utd.doc_id',array('url','titre'))
        	->where( "t.code LIKE '%".$tag."%'");

        return $this->fetchAll($query)->toArray(); 
    }

    /**
     * Recherche une entrée Flux_exitagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param string $tag
     * @param string $login
     * 
     * @return array
     */
    public function findByTagLogin($tag, $login)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_exitagdoc") )                           
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = utd.tag_id',array('code'))
            ->joinInner(array('u' => 'flux_uti'),
            	'u.uti_id = utd.uti_id AND u.login = "'.$login.'"',array())
            ->joinInner(array('d' => 'flux_doc'),
            	'd.doc_id = utd.doc_id',array('url','titre','pubDate'))
        	->where( "t.code LIKE '%".$tag."%'");

        return $this->fetchAll($query)->toArray(); 
    }
    
    /*
     * Recherche une entrée Flux_exitagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $url
     */
    public function findByUrl($url)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_exitagdoc") )                           
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = utd.tag_id',array('code'))
        	->joinInner(array('d' => 'flux_doc'),
            	'd.doc_id = utd.doc_id',array('url','titre'))
        	->where( "d.url = ?", $url);

        return $this->fetchAll($query)->toArray(); 
    }
    
    /*
     * Recherche une entrée Flux_exitagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $doc_id
     */
    public function findByDoc_id($doc_id)
    {
        $query = $this->select()
			->from( array("f" => "flux_exitagdoc") )                           
            ->where( "f.doc_id = ?", $doc_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_exitagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $maj
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exitagdoc") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
    /*
     * Recherche les documents pour des utilisateurs et des tags
     * et retourne ces entrées.
     *
     * @param string $users
     * @param string $tags
     */
    public function findDocsByUsersTags($users, $tags)
    {
	    //défiition de la requête
		$sql = "select d.doc_id, d.url, d.titre, d.branche_lft, d.branche_rgt, d.tronc, d.poids, d.maj, d.pubDate, d.note 
		from flux_exitagdoc utd
			inner join flux_doc d on d.doc_id = utd.doc_id
		where utd.tag_id IN (".$tags.") AND utd.uti_id IN (".$users.")  
		group by d.doc_id";
        $db = Zend_Db_Table::getDefaultAdapter();
    	$stmt = $db->query($sql);
    	return $stmt->fetchAll();
    }

    /*
     * Recherche les statistiques pour des tags
     * et retourne ces entrées.
     *
     * @param string $tags
     */
    public function findStatByTags($tags, $order ="d.doc_id, u.uti_id")
    {
	    //défiition de la requête
		$sql = "SELECT `d`.*, `u`.*, `t`.*, utd.* 
			FROM `flux_exitagdoc` AS `utd` 
		 		INNER JOIN `flux_doc` AS `d` ON d.doc_id=utd.doc_id
		 		INNER JOIN `flux_uti` AS `u` ON u.uti_id = utd.uti_id
		 		INNER JOIN `flux_tag` AS `t` ON t.tag_id = utd.tag_id 
		 	WHERE t.tag_id IN (".$tags.")
		 	ORDER BY ".$order;
        $db = Zend_Db_Table::getDefaultAdapter();
    	$stmt = $db->query($sql);
    	return $stmt->fetchAll();
    }

	/**
     * Récupère les tags associés à un document et leur poids
     *
     * @param integer $idUti
     * @param integer $idDoc
     * @param string $where
     * 
     * @return array
     */
	function GetExiTagDoc($idUti=false, $idDoc=false, $where="") {

		//définition de la requête
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_exitagdoc"), array("tag_id","doc_id","uti_id"))                           
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = utd.tag_id',array('code'))
        	->joinInner(array('td' => 'flux_tagdoc'),
            	'td.doc_id = utd.doc_id AND td.tag_id = utd.tag_id',array('value'=>'SUM(td.poids)'))
        	->group("utd.tag_id")
        	->order("value DESC");
        	if($idUti)$query->where( "utd.uti_id = ?", $idUti);
        	if($idDoc)$query->where( "utd.doc_id = ?", $idDoc);
        	if($where)$query->where($where);
        	
        return $this->fetchAll($query)->toArray(); 		
	}

	/**
     * Récupère les tags associés à un utilisateur
     *
     * @param integer $idUti
     * @param string $w
     * @param string $h
     * 
     * @return array
     */
	function GetUtiTags($idUti, $w="", $h="", $order=null, $limit=null, $from=null) {

		//définition de la requête
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_exitagdoc"), array("tag_id"))                           
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = utd.tag_id',array('code'))
        	->joinInner(array('td' => 'flux_tagdoc'),
            	'td.doc_id = utd.doc_id AND td.tag_id = utd.tag_id',array('value'=>'SUM(td.poids)'))
        	->where( "utd.uti_id = ?", $idUti)
        	->group("utd.tag_id");
		if($w!="")$query->where($w);
		if($h!="")$query->having($h);
		
        if($order != null)
        {
            $query->order($order);
        }else{
        	$query->order("value DESC");
        }
        if($limit != 0)
        {
            $query->limit($limit, $from);
        }
		
		return $this->fetchAll($query)->toArray(); 		
	}
	
	/**
     * Récupère les tags associés à une liste de document
     *
     * @param integer $idsDoc
     * @param string $w
     * @param string $h
     * 
     * @return array
     */
	function GetDocTags($idsDoc, $where="", $h="", $order=null, $limit=null, $from=null) {

		//définition de la requête
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_exitagdoc"), array("tag_id"))                           
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = utd.tag_id',array('code'))
        	->joinInner(array('td' => 'flux_tagdoc'),
            	'td.doc_id = utd.doc_id AND td.tag_id = utd.tag_id',array('value'=>'SUM(td.poids)'))
        	->joinInner(array('d' => 'flux_doc'),
            	'd.doc_id = utd.doc_id',array('doc_id', 'titre'))
        	->where("utd.doc_id IN (".$idsDoc.")")
        	->group("utd.tag_id")
        	->order("value DESC");

        if($order != null)
        {
            $query->order($order);
        }
        if($where != null)
        {
            $query->where($where);
        }

        if($limit != 0)
        {
            $query->limit($limit, $from);
        }
		
        if($h!="")$query->having($h);


		return $this->fetchAll($query)->toArray();
        
	}
	
	/**
     * Récupère la liste des utilisateurs avec le nombre de tags et le nombre de document
     * 
     * @param string $w
     * @param string $h
     * 
     * @return array
     */
	function GetUtisNbTagNbDoc($w="", $h="") {

		//définition de la requête
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_exitagdoc")
        		, array("nbTag"=>"COUNT(DISTINCT utd.tag_id)", "nbDoc"=>"COUNT(DISTINCT utd.doc_id)"))                           
            ->joinInner(array('u' => 'flux_uti'),
            	'u.uti_id = utd.uti_id',array('login','utd.uti_id'))
            /*
        	->joinInner(array('td' => 'flux_tagdoc'),
            	'td.doc_id = utd.doc_id AND td.tag_id = utd.tag_id',array('value'=>'SUM(td.poids)'))
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = utd.tag_id',array('code'))
            */
        	->group("utd.uti_id")
        	->order("login");
		if($w!="")$query->where($w);
		if($h!="")$query->having($h);
		
        return $this->fetchAll($query)->toArray(); 		
	}


   
        
}

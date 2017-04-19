<?php

/**
 * Classe ORM qui représente la table 'flux_utitagdoc'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 * @ignore
 */

class Model_DbTable_Flux_UtiTagDoc extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_utitagdoc';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'uti_id';

	var $indDeterre;

	public function __construct($config = array())
    {
    	parent::__construct($config);
		/*indice de déterriolisation
		 * 100% = le choix du tag des utilisateurs est aux antipodes de la référence
		 * 0 % = la géolocalisation est égal à la référence
		 */
		$this->indDeterre = "SUM(f.poids)*(100/COUNT(*))";
    }
	
	
    /**
     * Vérifie si une entrée Flux_utitagdoc existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('uti_id'));
		$select->where('uti_id = ?', $data['uti_id']);
		$select->where('tag_id = ?', $data['tag_id']);
		$select->where('doc_id = ?', $data['doc_id']);
		$rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->uti_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_utitagdoc.
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
    	}else{
    		//met à jour le poids
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
		$sql = 'UPDATE flux_utitag SET poids = poids + '.$data["poids"].' WHERE tag_id = '.$data["tag_id"].' AND uti_id ='.$data["uti_id"];
    		$this->_db->query($sql);    
    }
    
    /**
     * Recherche une entrée Flux_utitagdoc avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_utitagdoc.uti_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_utitagdoc avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
        $this->delete('flux_utitagdoc.uti_id = ' . $id);
    }

    /**
     * Recherche une entrée Flux_utitagdoc avec la clef primaire spécifiée
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
    	if($idDoc) $where = 'flux_utitagdoc.doc_id = ' . $idDoc;
    	if($idTag){
    		if($where) $where.= " AND ";
    		$where .= 'flux_utitagdoc.tag_id = ' . $idTag;
    	}
    	if($idUti){
    		if($where) $where.= " AND ";
    		$where .= 'flux_utitagdoc.uti_id = ' . $idUti;
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
     * Récupère toutes les entrées Flux_utitagdoc avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_utitagdoc" => "flux_utitagdoc") );
                    
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
     * Retourne les entrées de Flux_utitagdoc
     * 
     *
     * 
     */
    public function getAllInfo()
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_utitagdoc") )                           
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = utd.tag_id',array('code'))
        	->joinInner(array('d' => 'flux_doc'),
            	'd.doc_id = utd.doc_id',array('url','titre'));
			
        return $this->fetchAll($query)->toArray(); 
    }
    
    /*
     * Recherche une entrée Flux_utitagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id
     */
    public function findByUti_id($uti_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utitagdoc") )                           
                    ->where( "f.uti_id = ?", $uti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_utitagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id
     */
    public function findByTagId($tag_id)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_utitagdoc") )                           
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = utd.tag_id',array('code'))
        	->joinInner(array('d' => 'flux_doc'),
            	'd.doc_id = utd.doc_id',array('url','titre'))
        	->where( "utd.tag_id = ?", $tag_id );

        return $this->fetchAll($query)->toArray(); 
    }

    /*
     * Recherche une entrée Flux_utitagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag
     */
    public function findByTag($tag)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_utitagdoc") )                           
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = utd.tag_id',array('code'))
        	->joinInner(array('d' => 'flux_doc'),
            	'd.doc_id = utd.doc_id',array('url','titre'))
        	->where( "t.code LIKE '%".$tag."%'");

        return $this->fetchAll($query)->toArray(); 
    }

    /**
     * Recherche une entrée Flux_utitagdoc avec la valeur spécifiée
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
        	->from( array("utd" => "flux_utitagdoc") )                           
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
     * Recherche une entrée Flux_utitagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $url
     */
    public function findByUrl($url)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_utitagdoc") )                           
            ->joinInner(array('t' => 'flux_tag'),
            	't.tag_id = utd.tag_id',array('code'))
        	->joinInner(array('d' => 'flux_doc'),
            	'd.doc_id = utd.doc_id',array('url','titre'))
        	->where( "d.url = ?", $url);

        return $this->fetchAll($query)->toArray(); 
    }
    
    /*
     * Recherche une entrée Flux_utitagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $doc_id
     */
    public function findByDoc_id($doc_id)
    {
        $query = $this->select()
			->from( array("f" => "flux_utitagdoc") )                           
            ->where( "f.doc_id = ?", $doc_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_utitagdoc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $maj
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_utitagdoc") )                           
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
		from flux_utitagdoc utd
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
			FROM `flux_utitagdoc` AS `utd` 
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
	function GetUtiTagDoc($idUti=false, $idDoc=false, $where="") {

		//définition de la requête
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("utd" => "flux_utitagdoc"), array("tag_id","doc_id","uti_id"))                           
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
        	->from( array("utd" => "flux_utitagdoc"), array("tag_id"))                           
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
        	->from( array("utd" => "flux_utitagdoc"), array("tag_id"))                           
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
        	->from( array("utd" => "flux_utitagdoc")
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

    /**
     * calcul l'indice de territorialité des tags pour un ou tous utilisateur
     *
     * @param int $idUti
     *
     * @return array
     */
    public function calcIndTerreTagForUti($idUti=false)
    {
    	//récupère la somme des distances pour le document
    	$query = $this->select()
			->from(array("f" => "flux_utitagdoc"),array("uti_id","nb"=>"COUNT(*)", "somme"=>"SUM(f.poids)", "indice"=>$this->indDeterre))
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table        
			->joinInner(array('d' => 'flux_doc'), "d.doc_id = f.doc_id",array("url"))
			->group("f.uti_id")
			->order("indice")
			->where("f.poids <> 0");                           
		if($idUti)$query->where( "f.uti_id = ?", $idUti);
        $result = $this->fetchAll($query)->toArray(); 
        
        return $result;
        
    }
	
    /**
     * calcul l'indice de territorialité des tags pour un ou tous documents
     *
     * @param int $idDoc
     *
     * @return array
     */
    public function calcIndTerreTagForDoc($idDoc=false)
    {
    	//récupère la somme des distances pour le document
    	$query = $this->select()
			->from(array("f" => "flux_utitagdoc"),array("doc_id","nb"=>"COUNT(*)", "somme"=>"SUM(f.poids)", "indice"=>$this->indDeterre))
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table        
			->joinInner(array('d' => 'flux_doc'), "d.doc_id = f.doc_id",array("url"))
			->group("f.doc_id")
			->order("indice")
			->where("f.poids <> 0");                           
		if($idUti)$query->where( "f.doc_id = ?", $idDoc);
        $result = $this->fetchAll($query)->toArray(); 
        
        return $result;
        
    }

    /**
     * calcul l'indice de territorialité des tags pour un ou tous tags
     *
     * @param int $idTag
     *
     * @return array
     */
    public function calcIndTerreTagForTag($idTag=false)
    {
    	//récupère la somme des distances pour le document
    	$query = $this->select()
			->from(array("f" => "flux_utitagdoc"),array("tag_id","nb"=>"COUNT(*)", "somme"=>"SUM(f.poids)", "indice"=>$this->indDeterre))
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table        
			->joinInner(array('d' => 'flux_doc'), "d.doc_id = f.doc_id",array("url"))
			->group("f.tag_id")
			->order("indice")
			->where("f.poids <> 0");                           
		if($idTag)$query->where( "f.tag_id = ?", $idTag);
        $result = $this->fetchAll($query)->toArray(); 
        
        return $result;
        
    }    
    
    /**
     * récupère les class Dewey et les documents associés
     *
     * @param int $idUtiDewey
     *
     * @return array
     */
    public function getDeweyTagDoc($idUtiDewey)
    {
		/* ATTENTION
		 * problème de debug avec les requêtes complexes ???
		 * 
		 */    	
    	$query = $this->select()
			->from(array("t" => "flux_tag"),array("tag_id", "code", "desc", "niveau", "parent"))
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table        
			->joinInner(array('utd' => 'flux_utitagdoc'), "utd.tag_id = t.tag_id AND utd.uti_id = ".$idUtiDewey
				,array("nb"=>"COUNT(DISTINCT utd.doc_id)", "idsDoc"=>new Zend_Db_Expr("GROUP_CONCAT(DISTINCT utd.doc_id)")))
			->joinInner(array('d' => 'flux_doc'), "d.doc_id = utd.doc_id AND d.titre != ''",array())
		/*    	 problème de performance en récupérant la hiérarchie directement dans la requête
				->joinInner(array('tParent' => 'flux_tag'), "t.lft BETWEEN tParent.lft AND tParent.rgt",
				array("idsTagParent"=>"GROUP_CONCAT(DISTINCT tParent.tag_id ORDER BY tParent.lft)"))
    	*/ 
			->group("t.tag_id")
			->order("t.branche_lft")
			->where("t.desc != ''");                           
        $result = $this->fetchAll($query)->toArray(); 
        /*
		$sql = "SELECT `t`.`tag_id`, `t`.`code`, `t`.`desc`, `t`.`niveau`, `t`.`parent`, COUNT(DISTINCT utd.doc_id) AS `nb`, GROUP_CONCAT(DISTINCT utd.doc_id) AS `idsDoc` FROM `flux_tag` AS `t`
 INNER JOIN `flux_utitagdoc` AS `utd` ON utd.tag_id = t.tag_id AND utd.uti_id = 638 WHERE (t.desc != '') GROUP BY `t`.`tag_id` ORDER BY `t`.`lft` ASC";
		$stmt = $this->_db->query($sql);
    	$result = $stmt->fetchAll();
        */
        return $result;
        
    }    
        
}

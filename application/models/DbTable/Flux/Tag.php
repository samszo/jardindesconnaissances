<?php

/**
 * Classe ORM qui représente la table 'flux_tag'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Model_DbTable_Flux_Tag extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'flux_tag';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'tag_id';

    /*version 0
     protected $_dependentTables = array(
     ,'Model_DbTable_Flux_TagDoc'
     ,'Model_DbTable_Flux_UtiTagDoc'
     ,'Model_DbTable_Flux_ExiTag'
     ,'Model_DbTable_Flux_TagTag'
     );
     */
    protected $_dependentTables = array(
        'Model_DbTable_Flux_Rapport'
    );
    
    /***
     * Vérifie si une entrée flux_tag existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('tag_id'));
		if(isset($data['uri']))$select->where(' uri = ?', $data['uri']);
		if(isset($data['code']))$select->where(' code = ?', $data['code']);
		if(isset($data['parent']))$select->where(' parent = ?', $data['parent']);
		$rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->tag_id; else $id=false;
        return $id;
    } 
        
    /***
     * Ajoute une entrée flux_tag.
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
	    		$data = $this->updateHierarchie($data);
	    		$id = $this->insert($data);
	    	}
	    	    	
	    	if($rs)
	    		return $this->findByTag_id($id);
		else
		    	return $id;
    	
    } 

    /***
     * Modifie la hiérarchie d'une entrée.
     *
     * @param array $data
     *  
     * @return array
     */
    public function updateHierarchie($data){
    	
    		if(isset($data["parent"])){
	    		$arrP = $this->findByTag_id($data["parent"]);
    			//récupère les information du parent
	    		$arr = $this->findByParent($data["parent"]);
	    		//gestion des hiérarchies gauche droite
	    		//http://mikehillyer.com/articles/managing-hierarchical-data-in-mysql/
    			if(count($arr)>0){
	    			//met à jour les niveaux 
	    			$sql = 'UPDATE flux_tag SET rgt = rgt + 2 WHERE rgt >'.$arr[0]['rgt'];
	    			$stmt = $this->_db->query($sql);
	    			$sql = 'UPDATE flux_tag SET lft = lft + 2 WHERE lft >'.$arr[0]['rgt'];
	    			$stmt = $this->_db->query($sql);
	    			//
	    			$data['lft'] = $arr[0]['rgt']+1;
	    			$data['rgt'] = $arr[0]['rgt']+2;
	    		}else{
	    			//vérifie si la base n'est pas vide
	    			//met à jour les niveaux 
	    			$sql = 'UPDATE flux_tag SET rgt = rgt + 2 WHERE rgt >'.$arrP['lft'];
	    			$stmt = $this->_db->query($sql);
	    			$sql = 'UPDATE flux_tag SET lft = lft + 2 WHERE lft >'.$arrP['lft'];
	    			$stmt = $this->_db->query($sql);
	    			//
	    			$data['lft'] = $arrP['lft']+1;
	    			$data['rgt'] = $arrP['lft']+2;
	    		}    		
	    		$data['niveau'] = $arrP['niveau']+1;
    		}
    		if(!isset($data['lft']))$data['lft']=0;    		
    		if(!isset($data['rgt']))$data['rgt']=1;    		
    		if(!isset($data['niveau']))$data['niveau']=1;    		
    		    		
    		return $data;
    }

     /**
     * Recherche une entrée avec la valeur spécifiée
     * et retourne la liste de tous ses parents
     *
     * @param integer $idTag
     * @param string $order
     * 
     * @return array
     */
    public function getFullPath($idTag, $order="lft")
    {
        $query = $this->select()
                ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('node' => 'flux_tag'),array("parLib"=>"code"))
            ->joinInner(array('parent' => 'flux_tag'),
                'node.lft BETWEEN parent.lft AND parent.rgt',array('code', 'desc', 'tag_id', 'niveau'))
            ->where( "node.tag_id = ?", $idTag)
            ->order("parent.".$order);        
        $result = $this->fetchAll($query);
        return $result->toArray(); 
    }

     /**
     * Recherche une entrée avec la valeur spécifiée
     * et retourne la liste de tous ses enfants
     *
     * @param integer $idTag
     * @param string $order
     * @return array
     */
    public function getFullChild($idTag, $order="lft")
    {
        $query = $this->select()
            ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('node' => 'flux_tag'),array('libO'=>'code', 'idTag0'=>'tag_id', "lft", "rgt"))
            ->joinInner(array('enfants' => 'flux_tag'),
                'enfants.lft BETWEEN node.lft AND node.rgt',array("recid"=>"tag_id",'text'=>'code','code', 'desc', 'tag_id', 'parent', 'niveau'))
            ->where( "node.tag_id = ?", $idTag)
           	->order("enfants.".$order);        
		$result = $this->fetchAll($query);
        return $result->toArray(); 
    }
    
    
    
    /***
     * Recherche une entrée flux_tag avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_tag.tag_id = ' . $id);
    }
    
    /***
     * Recherche une entrée flux_tag avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return int
     */
    public function remove($id)
    {
        $nbSup = 0;
        foreach($this->_dependentTables as $t){
            $tEnfs = new $t($this->_db);
            $nbSup += $tEnfs->removeTag($id);
        }
        //supprime les enfants
        $arrEnf = $this->findByParent($id);
        foreach ($arrEnf as $enf) {
            $nbSup += $this->remove($enf['tag_id']);
        }
        
        $nbSup += $this->delete('flux_tag.tag_id = ' . $id);
        return $nbSup;
    }
        
    /***
     * Récupère toutes les entrées flux_tag avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_tag" => "flux_tag") );
                    
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
     * Recherche une entrée flux_tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id
     */
    public function findByTag_id($tag_id)
    {
        $query = $this->select()
			->from( array("f" => "flux_tag") )                           
        		->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->joinInner(array('t' => 'flux_tag'),
                'f.tag_id = t.tag_id',array("recid"=>"tag_id"))
			->where( "f.tag_id = ?", $tag_id );
		$arr = $this->fetchAll($query)->toArray();
        return count($arr) ? $arr[0] : false; 
    }
    /**
     * Recherche une entrée flux_tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $code
     */
    public function findByCode($code)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tag") )                           
                    ->where( "f.code = ?", $code );

		$arr = $this->fetchAll($query)->toArray();
        return $arr[0]; 
    }
    /**
     * Recherche une entrée flux_tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $code
     */
    public function findByCodeLike($code)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tag") )                           
                    ->where( "f.code like '%$code%'");

		return $this->fetchAll($query)->toArray();
    }
    
    /**
     * Recherche une entrée flux_tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param string $desc
     */
    public function findByDesc($desc)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tag") )                           
                    ->where( "f.desc = ?", $desc );

        return $this->fetchAll($query)->toArray(); 
    }
    /**
     * Recherche une entrée flux_tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param string $type
     * 
     * @return array
     */
    public function findByType($type)
    {
        $query = $this->select()
        ->from( array("f" => "flux_tag") )
        ->where( "f.type = ?", $type );
        
        return $this->fetchAll($query)->toArray();
    }
    /**
     * Recherche une entrée flux_tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $niveau
     */
    public function findByNiveau($niveau)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tag") )                           
                    ->where( "f.niveau = ?", $niveau );

        return $this->fetchAll($query)->toArray(); 
    }
    /**
     * Recherche une entrée flux_tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param string $parent
     */
    public function findByParent($parent)
    {
		$query = $this->select()
			->from( array("t" => "flux_tag") )                           
			->group("t.tag_id")
            ->where( "t.parent = ?", $parent )
            ->order("t.lft DESC");

        return $this->fetchAll($query)->toArray();         
    }

    /**
     * Compte le nombre d'enfant d'une entrée
     *
     * @param int $parent
     * 
     * @return int
     */
    public function compteEnfant($parent)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tag"),array("nb"=>"COUNT(tag_id)") )                           
                    ->where( "f.parent = ?", $parent );
        $arr = $this->fetchAll($query)->toArray();
        return $arr[0]["nb"];
    }

    /**
     * Compte tout les enfants d'une entrée
     *
     * @param int $idTag
     * 
     * @return int
     */
    public function compteAllEnfant($idTag)
    {
        $query = $this->select()
            ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('node' => 'flux_tag'),array("nb"=>"COUNT(*)"))
            ->joinInner(array('enfants' => 'flux_tag'),
                'enfants.lft BETWEEN node.lft AND node.rgt',array())
            ->where("node.tag_id = ?", $idTag);        
            
        $arr = $this->fetchAll($query)->toArray();
        return $arr[0]["nb"];
    }
    
	/**
     * Récupère les tags associés à une liste de tag 
     *
     * @param array $arrTags
     * @param int $tronc
     * 
     * @return array
     */
	function getTagAssos($arrTags, $tronc=0) {

		//vérifie si on prend les tags du document racine ou de ces éléments
		$where = " ";
		if($tronc>0){
			$where = ' AND d.tronc = '.$tronc;
		}
		//définition de la requête
		//attention la colonne value est nécessaire pour le graphique  
        $query = $this->select()
        	->from( array("t" => "flux_tag"),array())                           
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->joinInner(array('td' => 'flux_tagdoc'),
                'td.tag_id = t.tag_id',array("nbDoc"=>"COUNT(DISTINCT(td.doc_id))"))
            ->joinInner(array('td1' => 'flux_tagdoc'),
                'td1.doc_id = td.doc_id',array("value"=>"SUM(td1.poids)"))
            ->joinInner(array('t1' => 'flux_tag'),
                't1.tag_id = td1.tag_id',array("code","tag_id"))
			->joinInner(array('d' => 'flux_doc'),
                'd.doc_id = td.doc_id'.$where,array("nbDoc"=>"COUNT(DISTINCT(td.doc_id))"))
            ->group("t1.tag_id")
            ->order("t1.code");

         if($tronc > 0){
         	$query->joinLeft(array('dP' => 'flux_doc'),'dP.doc_id = d.tronc',array("tronc","nbTronc"=>"COUNT(DISTINCT(dP.doc_id))"));
         }
            
		//construction de la condition des tags
		$where = " ";
		if($arrTags){
			foreach ($arrTags as $tag) {
				$where .= " t.code LIKE '%".$tag."%' OR ";
			}
			$where = substr($where, 0, -3);
			$query->where($where);
		}
		
        return $this->fetchAll($query)->toArray(); 
		    	
	}

	/**
     * Récupère l'historique d'une liste de tag 
     *
     * @param array 	$arrTags
     * @param int 		$tronc
     * @param string 	$dateUnit unité de la date : year, month...
     * @param string 	$q requête full text
     * 
     * @return array
     */
	function getTagHisto($arrTags, $tronc=0, $dateUnit="", $q="") {

		//vérifie si on prend les tags du document racine ou de ces éléments
		$where = " AND d.maj !=0 ";
		if($tronc>0){
			$where = ' AND d.tronc = '.$tronc;
		}
		if($dateUnit){
			$dateUnit = 'DATE_FORMAT(d.maj, "%'.$dateUnit.'")';
		}else $dateUnit = "d.maj";
		
		if($q){
			$score = "MATCH (d.titre, d.note) AGAINST ('".$q."')";
		}else{
			$score = "COUNT(1)";
		}
		
		//définition de la requête
		//attention la colonne value est nécessaire pour le graphique  
		/*		
		SELECT t.code,
		         t.desc,
		         DATE_FORMAT(d.maj, "%Y") temps,
		         count(*) nb,
		         count(DISTINCT utd.uti_id) nbUti,
		         count(DISTINCT utd.doc_id) nbDoc
		    FROM flux_tag t
		         INNER JOIN flux_utitagdoc utd ON utd.tag_id = t.tag_id
		         INNER JOIN flux_doc d ON utd.doc_id = d.doc_id
		   WHERE t.code LIKE "%information%" OR t.code LIKE "%communication%"
		GROUP BY t.code, temps
		ORDER BY t.code, temps
		*/		
        $query = $this->select()
        	->from( array("t" => "flux_tag"),array("tag_id", "code", "desc", "nb"=>"COUNT(*)"))                           
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->joinInner(array('utd' => 'flux_utitagdoc'),
                'utd.tag_id = t.tag_id',array("nbUti"=>"COUNT(DISTINCT(utd.uti_id))", "nbDoc"=>"COUNT(DISTINCT(utd.doc_id))"))
			->joinInner(array('d' => 'flux_doc'),
                'd.doc_id = utd.doc_id'.$where,array("temps"=>$dateUnit,"score"=> new Zend_Db_Expr("SUM(".$score.")")))
            ->group(array("t.code","temps"))
            ->order(array("t.code",$dateUnit));
            
         if($tronc > 0){
         	$query->joinLeft(array('dP' => 'flux_doc'),'dP.doc_id = d.tronc',array("tronc","nbTronc"=>"COUNT(DISTINCT(dP.doc_id))"));
         }
            
		//construction de la condition des tags
		$where = " ";
		if($arrTags){
			foreach ($arrTags as $tag) {
				$where .= " t.code LIKE '%".$tag."%' OR ";
			}
			$where = substr($where, 0, -3);
		}
		if($q){
			if($where != " ") $where = "(".$where.") AND ".$score;
			else $where = $score;
		}
		$query->where($where);
		
		return $this->fetchAll($query)->toArray(); 
		    	
	}
	
	
	/**
	 * Récupère l'historique d'une liste de tag
	 *
	 * @param int 		$idMonade
	 * @param int 		$idUti
	 * @param int 		$idActi
	 * @param int 		$idParent
	 * @param array 	$arrTags
	 * @param string 	$dateUnit unité de la date : year, month...
	 * @param string 	$q requête full text
	 * @param array 	$dates extrémités temporelles
	 * @param string 	$lienDoc type de lien entre les rapport et les document
	 * @param string 		$req restriction de l'historique à une requête
	 *
	 * @return array
	 */
	function getTagHistoRapport($idMonade, $idUti, $idActi, $idParent="", $arrTags="",$dateUnit="%c-%y", $q="", $dates="", $lienDoc="uti", $req="") {
	
		//vérifie si on prend les tags du document racine ou de ces éléments
		$where = " ";
		
		if($idParent) $idParent = " AND d.parent =".$idParent;
		
		if($dateUnit){
			$temps = ' DATE_FORMAT(d.pubDate, "'.$dateUnit.'") ';
		}
	
		if($q){
			$score = " MATCH (d.titre, d.note) AGAINST ('".$q."') ";
		}else{
			$score = " 1 ";
		}
	
		//définition de la requête
		$query = $this->select()
		->from( array("t" => "flux_tag"),array("key"=>"tag_id", "type"=>"code", "desc"))
		->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
		->joinInner(array('r' => 'flux_rapport'),
				"r.monade_id = ".$idMonade."
					AND r.src_obj = 'rapport'
					AND r.dst_obj = 'tag'
					AND r.pre_obj = 'acti'
					AND r.pre_id = ".$idActi."
					AND t.tag_id = r.dst_id"
				,array());
		//ATTENTION la requête change suivant la monade
		if($lienDoc=="uti"){
			/*
			 SELECT
			 t.tag_id, t.code tag
			 ,COUNT(DISTINCT rd.src_id) nbDoc
			 ,  DATE_FORMAT(d.pubDate, "%c-%y") temps
			 FROM
			 flux_tag t
			 INNER JOIN
			 flux_rapport r ON r.monade_id = 2
			 AND r.src_obj = 'rapport'
			 AND r.dst_obj = 'tag'
			 AND r.pre_obj = 'acti'
			 AND r.pre_id = 2
			 AND t.tag_id = r.dst_id
			 INNER JOIN
			 flux_rapport rd ON rd.rapport_id = r.src_id
			 INNER JOIN
			 flux_doc d ON d.doc_id = rd.src_id AND d.parent = 1
			 -- WHERE
			 --    t.tag_id = 14 -- ecosystem info
			 GROUP BY t.tag_id, temps
			 */
			$query->joinInner(array('rd' => 'flux_rapport'),
					'rd.rapport_id = r.src_id AND rd.pre_id ='.$idUti,array("value"=>"COUNT(DISTINCT rd.src_id)"))
				->joinInner(array('d' => 'flux_doc'),
				'd.doc_id = rd.src_id '.$idParent,array("temps"=>$temps
						,"score"=> new Zend_Db_Expr("SUM(".$score.")")
						, "MinDate"=>new Zend_Db_Expr("MIN(UNIX_TIMESTAMP(d.pubDate))")
						, "MaxDate"=>new Zend_Db_Expr("MAX(UNIX_TIMESTAMP(d.pubDate))")
				));
		}else{
			/*
			SELECT
					t.tag_id, t.code tag
					,COUNT(DISTINCT rd.src_id) nbDoc
					,  DATE_FORMAT(d.pubDate, "%c-%y") temps
			        , rq.valeur req
			        , m.titre monade
					FROM
					flux_tag t
					INNER JOIN
					flux_rapport r ON r.monade_id = 2
					AND r.src_obj = 'rapport'
					AND r.dst_obj = 'tag'
					AND r.pre_obj = 'acti'
					AND r.pre_id = 3
					AND t.tag_id = r.dst_id
					INNER JOIN
					flux_rapport rd ON rd.rapport_id = r.src_id
					INNER JOIN
					flux_doc d ON d.doc_id = rd.dst_id AND d.parent = 1
					INNER JOIN
					flux_rapport rq ON rq.rapport_id = rd.src_id AND rq.rapport_id = 1
					INNER JOIN
					flux_monade m ON m.monade_id = rq.monade_id
			        GROUP BY m.monade_id, rq.rapport_id, t.tag_id, temps
			*/
			if($req) $w = "  AND rq.rapport_id = ".$req;
			else $w = "";
			$query->joinInner(array('rd' => 'flux_rapport'),
					'rd.rapport_id = r.src_id ',array("value"=>"COUNT(DISTINCT rd.src_id)"))
				->joinInner(array('d' => 'flux_doc'),
				'd.doc_id = rd.dst_id '.$idParent,array("temps"=>$temps
						,"score"=> new Zend_Db_Expr("SUM(".$score.")")
						, "MinDate"=>new Zend_Db_Expr("MIN(UNIX_TIMESTAMP(d.pubDate))")
						, "MaxDate"=>new Zend_Db_Expr("MAX(UNIX_TIMESTAMP(d.pubDate))")
					))
				->joinInner(array('rq' => 'flux_rapport'),
							'rq.rapport_id = rd.src_id ',array("reqId"=>"rq.rapport_id","req"=>"valeur"))
				->joinInner(array('m' => 'flux_monade'),
							'm.monade_id = rq.monade_id '.$w,array("monaId"=>"m.monade_id","monade"=>"titre"))
				->group("m.monade_id")
				->group("rq.rapport_id")
				->order(array("m.monade_id","rq.rapport_id"));				
		}
		$query->group("t.tag_id")
		->group($temps)
		->order(array("t.code",$temps));
		
		
	
		//construction de la condition des tags
		$where = "";
		if($arrTags){
			foreach ($arrTags as $tag) {
				$where .= " t.code LIKE '%".$tag."%' OR ";
			}
			$where = substr($where, 0, -3);
			$query->where($where);				
		}
		if($q){
			$query->where($score);				
		}
		if($dates){
			$minDate = new DateTime();
			$minDate->setTimestamp($dates[0]);
			$maxDate = new DateTime();
			$maxDate->setTimestamp($dates[1]);
			$where = "d.pubDate BETWEEN '".$minDate->format("Y-m-d H:i:s")."' AND '".$maxDate->format("Y-m-d H:i:s")."'";
			//echo $dates[0]." / ".$dates[1]." = ".$where;
			$query->where($where);
		}
		/* Pour afficher la requ$ete
		  $sql = $query->__toString();
		  echo "$sql\n";
		*/
		return $this->fetchAll($query)->toArray();
	
	}
	
	/**
     * Récupère les tags associés à une liste de tag par document 
     *
     * @param array $arrTags
     * @param int $tronc
     * 
     * @return array
     */
	function getTagAssosByDoc($arrTags, $tronc=-1) {


		//définition de la requête
        $query = $this->select()
        	->from( array("t" => "flux_tag"),array())                           
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->joinInner(array('td' => 'flux_tagdoc'),
                'td.tag_id = t.tag_id',array())
            ->joinInner(array('td1' => 'flux_tagdoc'),
                'td1.doc_id = td.doc_id',array("doc_id","tags"=>"GROUP_CONCAT(DISTINCT(td1.tag_id))"))
            ->group("td1.doc_id")
            ->order("td1.tag_id");
                        
		//vérifie si on prend les tags du document racine ou de ces éléments
		if($tronc>-1){
			$query->joinInner(array('d' => 'flux_doc'),'d.doc_id = td.doc_id AND d.tronc='.$tronc, array());
		}
		//construction de la condition des tag
		$where = " ";
		if($arrTags){
			foreach ($arrTags as $tag) {
				$where .= " t.code LIKE '%".$tag."%' OR ";
			}
			$where = substr($where, 0, -3);
			$query->where($where);
		}
		
        return $this->fetchAll($query)->toArray(); 		
    	
	}	
	
	/**
     * Récupère le nombre de tags, de type de tag et de document associé à plusieurs utilisateur
     *
     * @param string $idsUti
     * @param string $typeDoc
     * 
     * @return array
     */
	function getUtiTagAssosNb($idsUti, $typeDoc=77) {


		//définition de la requête
        $query = "select 
			-- * 
			  count(distinct doc_id) nbDoc
			 , count(distinct tag_id) nbTag
			 , count(distinct tag_id_dst) nbType
			FROM (
			  select 
			    t.tag_id
			    ,td.doc_id
			    ,tt.tag_id_dst
			    ,ut1.uti_id u1
			    ,ut2.uti_id u2
			    ,ut3.uti_id u3
			  
			  from flux_tag t
			  inner join flux_tagdoc td on td.tag_id = t.tag_id
			  inner join flux_doc d on d.doc_id = td.doc_id and d.type = 77
			  
			  inner join flux_tagtag tt on tt.tag_id_src = t.tag_id
			  inner join flux_tag tTy on tTy.tag_id = tt.tag_id_dst
			  
			  left join flux_utitag ut1 on ut1.tag_id = t.tag_id AND ut1.uti_id = 4
			  
			  left join flux_utitag ut2 on ut2.tag_id = t.tag_id AND ut2.uti_id = 5
			  
			  left join flux_utitag ut3 on ut3.tag_id = t.tag_id AND ut3.uti_id = 7
			) concat
			WHERE u1=4";
		
        return $this->fetchAll($query)->toArray(); 		
    	
	}	
	
	

	/**
     * Décompose les tags composés  
     *
     * @param string $car
     * @param boolean $hier
     * @param boolean $sup
     *
     */
	function decomposeTags($car, $hier=false, $sup=false) {

		//pour tout les tag
		//$arrT = $this->getAll();
		
		//uniquement pour les tag de catégorie
        $query = $this->select()
        	->from( array("t" => "flux_tag"))                           
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->joinInner(array('tt' => 'flux_tagtag'),
                'tt.tag_id_dst = t.tag_id',array())
            ->group("t.tag_id");
        $arrT = $this->fetchAll($query)->toArray(); 		
            
		foreach ($arrT as $t) {
			//décompose par ;
			$arr = explode($car, $t['code']);
			if(count($arr) > 1){
				foreach ($arr as $val) {
					//enregistre le tag sans
					$idTag = $this->ajouter(array("code"=>$val,"niveau"=>($t['niveau']+1)));
					//création des liens déjà existant
					$sql = "INSERT INTO flux_utitag (tag_id, uti_id, poids)
						SELECT ".$idTag.", uti_id, poids
						FROM flux_utitag
						WHERE tag_id=".$t['tag_id']." AND tag_id != ".$idTag;
			    	$stmt = $this->_db->query($sql);
					echo $val." ".$idTag." flux_utitag : ".$stmt->rowCount()." ligne(s) affectée(s)<br/>"; 

					$sql = "INSERT INTO flux_utitagdoc (tag_id, uti_id, doc_id, poids)
						SELECT ".$idTag.", uti_id, doc_id, poids
						FROM flux_utitagdoc
						WHERE tag_id=".$t['tag_id']." AND tag_id != ".$idTag;
			    	$stmt = $this->_db->query($sql);
					echo $val." ".$idTag." flux_utitagdoc : ".$stmt->rowCount()." ligne(s) affectée(s)<br/>"; 

					$sql = "INSERT INTO flux_tagtag (tag_id_src, tag_id_dst, poids)
						SELECT ".$idTag.", tag_id_dst, poids
						FROM flux_tagtag
						WHERE tag_id_src=".$t['tag_id']." AND tag_id_src != ".$idTag;
			    	$stmt = $this->_db->query($sql);
					echo $val." ".$idTag." flux_tagtag src : ".$stmt->rowCount()." ligne(s) affectée(s)<br/>"; 
					
					$sql = "INSERT INTO flux_tagtag (tag_id_src, tag_id_dst, poids)
						SELECT tag_id_src, ".$idTag.", poids
						FROM flux_tagtag
						WHERE tag_id_dst=".$t['tag_id']." AND tag_id_dst != ".$idTag;
			    	$stmt = $this->_db->query($sql);
					echo $val." ".$idTag." flux_tagtag dst : ".$stmt->rowCount()." ligne(s) affectée(s)<br/>"; 

					$sql = "INSERT INTO flux_tagdoc (tag_id, doc_id, poids)
						SELECT ".$idTag.", doc_id, poids
						FROM flux_tagdoc
						WHERE tag_id=".$t['tag_id']." AND tag_id != ".$idTag;
			    	$stmt = $this->_db->query($sql);
					echo $val." ".$idTag." flux_tagdoc : ".$stmt->rowCount()." ligne(s) affectée(s)<br/>"; 					
				}
				if($sup)$this->remove($t['tag_id']);
				
			}
		}
		
	}

	
}

<?php
/**
 * Ce fichier contient la classe flux_tag.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_tag'.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_Tag extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_tag';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'tag_id';

    protected $_dependentTables = array(
       "Model_DbTable_flux_tagdoc"
       ,"Model_DbTable_flux_tagtag"
       ,"Model_DbTable_flux_utitag"
       ,"Model_DbTable_Flux_UtiTagDoc"
       );    
    
    /**
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
		$select->where(' code = ?', $data['code']);
		if(isset($data['parent']))$select->where(' parent = ?', $data['parent']);
		$rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->tag_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée flux_tag.
     *
     * @param array $data
     * @param boolean $existe
     * @param boolean $rData
     *  
     * @return integer
     */
    public function ajouter($data, $existe=true, $rData=false)
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

    /**
     * Modifie la hiérarchie d'une entrée.
     *
     * @param array $data
     *  
     * @return array
     */
    public function updateHierarchie($data){
    	
    		if(isset($data["parent"])){
	    		//récupère les information du parent
	    		$arr = $this->findByTag_id($data["parent"]);
	    		//gestion des hiérarchies gauche droite
	    		//http://mikehillyer.com/articles/managing-hierarchical-data-in-mysql/
	    		//vérifie si le parent à des enfants
	    		$arrP = $this->findByParent($data["parent"]);
	    		if(count($arrP)){
	    			//met à jour les niveaux 
	    			$sql = 'UPDATE flux_tag SET rgt = rgt + 2 WHERE rgt >'.$arr['rgt'];
	    			$stmt = $this->_db->query($sql);
	    			$sql = 'UPDATE flux_tag SET lft = lft + 2 WHERE lft >'.$arr['rgt'];
	    			$stmt = $this->_db->query($sql);
	    			//
	    			$data['lft'] = $arr['rgt']+1;
	    			$data['rgt'] = $arr['rgt']+2;
	    		}else{
	    			//met à jour les niveaux 
	    			$sql = 'UPDATE flux_tag SET rgt = rgt + 2 WHERE rgt >'.$arr['lft'];
	    			$stmt = $this->_db->query($sql);
	    			$sql = 'UPDATE flux_tag SET lft = lft + 2 WHERE lft >'.$arr['lft'];
	    			$stmt = $this->_db->query($sql);
	    			//
	    			$data['lft'] = $arr['lft']+1;
	    			$data['rgt'] = $arr['lft']+2;
	    		}    		
	    		$data['niveau'] = $arr['niveau']+1;
    		}
    		if(!isset($data['lft']))$data['lft']=0;    		
    		if(!isset($data['rgt']))$data['rgt']=1;    		
    		if(!isset($data['niveau']))$data['niveau']=1;    		
    		    		
    		return $data;
    }

     /*
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

     /*
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
    
    
    
    /**
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
    
    /**
     * Recherche une entrée flux_tag avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	
		//suppression des données lieés
        $dt = $this->getDependentTables();
        foreach($dt as $t){
        	$dbT = new $t($this->_db);
        	if($t=="Model_DbTable_flux_tagtag"){
	        	$dbT->delete('tag_id_src = '.$id);
	        	$dbT->delete('tag_id_dst = '.$id);
        	}else
	        	$dbT->delete('tag_id = '.$id);
        }        
        $this->delete('flux_tag.tag_id = ' . $id);
    }
    
    /**
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
    
    /*
     * Recherche une entrée flux_tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $tag_id
     */
    public function findByTag_id($tag_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tag") )                           
                    ->where( "f.tag_id = ?", $tag_id );
		$arr = $this->fetchAll($query)->toArray();
        return count($arr) ? $arr[0] : false; 
    }
    /*
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
    /*
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
    
    /*
     * Recherche une entrée flux_tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $desc
     */
    public function findByDesc($desc)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tag") )                           
                    ->where( "f.desc = ?", $desc );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
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
    /*
     * Recherche une entrée flux_tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param char $parent
     */
    public function findByParent($parent)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_tag") )                           
                    ->where( "f.parent = ?", $parent );
        return $this->fetchAll($query)->toArray();
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

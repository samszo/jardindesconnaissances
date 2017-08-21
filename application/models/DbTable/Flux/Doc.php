<?php

/**
 * Classe ORM qui représente la table 'flux_doc'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Model_DbTable_Flux_Doc extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'flux_doc';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'doc_id';

    /*version 0
	protected $_dependentTables = array(
		'Model_DbTable_Flux_UtiDoc'
		,'Model_DbTable_Flux_TagDoc'
		,'Model_DbTable_Flux_UtiTagDoc'
		,'Model_DbTable_Flux_ExiTagDoc'
		,'Model_DbTable_Flux_Rapport'
		);
    */    
	protected $_dependentTables = array(
		'Model_DbTable_Flux_Rapport'
		);
		
    /**
     * Vérifie si une entrée flux_doc existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this);
		foreach($data as $k=>$v){
			if($k!="pubDate" && $k!="maj" && $k!="poids"  && $k!="note"  && $k!="data" )
				$select->where($k.' = ?', $v);
		}
		$rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows->toArray(); else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée flux_doc.
     *
     * @param array $data
     * @param boolean $existe
     * @param boolean $rs
     * @param boolean $update
     *  
     * @return integer
     */
    public function ajouter($data, $existe=true, $rs=false, $update=true)
    {
    	$id=false;
    	if($existe)$id = $this->existe($data);
    	if(!$id){
    		if(!isset($data["pubDate"])) $data["pubDate"] = new Zend_Db_Expr('NOW()');
    		if(!isset($data["maj"])) $data["maj"] = new Zend_Db_Expr('NOW()');
    		$dt = $this->updateHierarchie($data);
    		$id = $this->insert($dt);
    	}else{
    		/*met à jour le poids
    		if(isset($data["poids"])){
    			$dt["poids"] = $id[0]["poids"]+$data["poids"];
    			$this->edit($id[0]["doc_id"], $dt);
    		} 
    		//met à jour la note
    		if(isset($data["note"])){
    			$dt["note"] = $data["note"];
    			$this->edit($id[0]["doc_id"], $dt);
    		}
    		*/    		
    		//met à jour les data
    	    if(isset($data["data"]) && $update){
    			$dt["data"] = $data["data"];
    			$this->edit($id[0]["doc_id"], $dt);
    		}
    		$id = $id[0]["doc_id"];
    	}
    	if($rs)
    		return $this->findBydoc_id($id);
	else
	    	return $id;
    	
    }     

    /**
     * deconstrution du document pour en faire une monade
     * 
     * @param array $data
     * @param int 	$idMon
     * 
     * return array
     */
    public function monadisation($data, $idMon){
    	
    	//vérification de l'url
    	if(substr($data['url'], 0,31)=="https://groups.diigo.com/group/" 
    		&& !strrpos($data['url'], "/content/")===false){
    			$arr = $this->_db->getConfig();
	    		$diigo = new Flux_Diigo("","",$arr['dbname']);
	    		$diigo->getCompoGroupItem($data, $idMon);
    	}
    	
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
	    		$arrP = $this->findBydoc_id($data["parent"]);
    			//récupère les information du parent
	    		$arr = $this->findByParent($data["parent"]);
	    		//gestion des hiérarchies gauche droite
	    		//http://mikehillyer.com/articles/managing-hierarchical-data-in-mysql/
    			if(count($arr)>0){
	    			//met à jour les niveaux 
	    			$sql = 'UPDATE flux_doc SET rgt = rgt + 2 WHERE rgt >'.$arr[0]['rgt'];
	    			$stmt = $this->_db->query($sql);
	    			$sql = 'UPDATE flux_doc SET lft = lft + 2 WHERE lft >'.$arr[0]['rgt'];
	    			$stmt = $this->_db->query($sql);
	    			//
	    			$data['lft'] = $arr[0]['rgt']+1;
	    			$data['rgt'] = $arr[0]['rgt']+2;
	    		}else{
	    			//vérifie si la base n'est pas vide
	    			//met à jour les niveaux 
	    			$sql = 'UPDATE flux_doc SET rgt = rgt + 2 WHERE rgt >'.$arrP['lft'];
	    			$stmt = $this->_db->query($sql);
	    			$sql = 'UPDATE flux_doc SET lft = lft + 2 WHERE lft >'.$arrP['lft'];
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
     * Recherche une entrée flux_tag avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param char $parent
     */
    public function findByParent($parent)
    {
        $query = $this->select()
			->from( array("f" => "flux_doc") )                           
            ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->joinInner(array('d' => 'flux_doc'),'d.doc_id = f.doc_id',array('recid'=>'doc_id'))			
            ->where( "f.parent = ?", $parent );
        return $this->fetchAll($query)->toArray();
    }
        
    /**
     * Recherche une entrée flux_doc avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data, $url=null)
    {
        	if(!isset($data["maj"])) $data["maj"] = new Zend_Db_Expr('NOW()');
        	if($url)
    	        $this->update($data, 'flux_doc.url = "'. $url.'"');
        	else        
    	        $this->update($data, 'flux_doc.doc_id = ' . $id);
    }
    
    /**
     * Recherche une entrée flux_doc avec la clef primaire spécifiée
     * et supprime cette entrée
     * rt toutre les entrées liées
     *
     * @param integer $id
     *
     * @return integer
     */
    public function remove($id)
    {
    		$nbSup = 0;
    		foreach($this->_dependentTables as $t){
			$tEnfs = new $t($this->_db);
			$nbSup += $tEnfs->removeDoc($id);
		}
		//supprime les enfants
		$arrEnf = $this->findByParent($id);
		foreach ($arrEnf as $enf) {
			$nbSup += $this->remove($enf['doc_id']);
		}
		
		$nbSup += $this->delete('flux_doc.doc_id = ' . $id);
		return $nbSup;
    }
    
    /**
     * Récupère toutes les entrées flux_doc avec certains critères
     * de tri, intervalles
     *
     * @return array
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_doc" => "flux_doc") );
                    
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
     * Récupère toutes les entrées distinct de flux_doc pour un champs
     * de tri, intervalles
	 *
     * @param string $column
     *
     * @return array
     */
    public function getDistinct($column)
    {
        $query = $this->select()
			->from( array("flux_doc" => "flux_doc"),array("nb" => "COUNT(*)",$column))
			->group($column);

        return $this->fetchAll($query)->toArray();
    }
    
    /**
     * Recherche le doc le plus récent pour un utilisateur
     * et retourne cette entrée.
     *
     * @param integer $UtiId
     *
     * @return array
     */
    public function findLastDoc($UtiId)
    {
	    	$sql = "SELECT Max(d.maj) maj
	    		FROM flux_doc d 
	    		INNER JOIN flux_UtiDoc ud ON ud.doc_id = d.doc_id 
	    		INNER JOIN flux_Uti u ON u.uti_id = ud.uti_id AND u.uti_id  = ".$UtiId;
	    	$stmt = $this->_db->query($sql);
	    	return $stmt->fetchAll();
    }

	/**
     * Execute uen requete sur la base connecté
     *
     * @param string $sql
     *
     * @return array
     */
    public function exeQuery($sql)
    {
	    	$stmt = $this->_db->query($sql);
	    	return $stmt->fetchAll();
    }    
    /**
     * Recherche une entrée flux_doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $doc_id
     */
    public function findBydoc_id($doc_id)
    {
        $query = $this->select()
			->from( array("f" => "flux_doc") )                           
            ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->joinInner(array('d' => 'flux_doc'),'d.doc_id = f.doc_id',array('recid'=>'doc_id'))			
            ->where( "f.doc_id = ?", $doc_id );
		$arr = $this->fetchAll($query)->toArray();
        return count($arr) ? $arr[0] : false; 
    }
    /**
     * Recherche une entrée flux_doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url
     */
    public function findByUrl($url)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doc") )                           
                    ->where( "f.url = ?", $url );

		$arr = $this->fetchAll($query)->toArray();
        return $arr[0]; 
    }
    /**
     * Recherche une entrée flux_doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $note
     */
    public function findByNote($note)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doc") )                           
                    ->where( "f.note = ?", $note);

        return $this->fetchAll($query)->toArray(); 
    }
    /**
     * Recherche une entrée flux_doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $titre
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doc") )                           
                    ->where( "f.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
/**
     * Recherche une entrée flux_doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar 	$tronc
     * @param boolean 	$avecUti
     * @param array 		$rows
     * 
     * @return array
     */
    public function findByTronc($tronc, $avecUti=false, $rows=false)
    {
    		if($rows){
    			$query = $this->select()
            		->from( array("f" => "flux_doc"), $rows)                           
            		->where( "f.tronc = ?", $tronc );
    		}else{
    			$query = $this->select()
            		->from( array("f" => "flux_doc"))                           
                ->where( "f.tronc = ?", $tronc );
    		}
    			
        if($avecUti){
            $query->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            		->joinInner(array('ud' => 'flux_utidoc'),'ud.doc_id = f.doc_id',array('maj', 'poids'))
            		->joinInner(array('u' => 'flux_uti'),'ud.uti_id = u.uti_id',array('uti_id',"login"));            
        }
        
        return $this->fetchAll($query)->toArray(); 
    }
    /**
     * Recherche une entrée flux_doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $tronc
     */
    public function findLikeTronc($tronc)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doc") )                           
                    ->where( "f.tronc LIKE '%$tronc%'");

        return $this->fetchAll($query)->toArray(); 
    }

    /**
     * Recherche une entrée flux_doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param string $url
     * @param int $idParent
     * 
     * @return Array
     */
    public function findByUrlByParent($url, $idParent)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doc") )                           
                    ->where( "f.tronc =".$idParent)
                    ->where( "f.url LIKE '%$url%'");

        return $this->fetchAll($query)->toArray(); 
    }

    /**
     * Recherche une entrée flux_doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param string $url
     * 
     * @return Array
     */
    public function findLikeUrl($url)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doc") )                           
                    ->where( "f.url LIKE '%$url%'");

        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche une entrée flux_doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $poids
     */
    public function findByPoids($poids)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doc") )                           
                    ->where( "f.poids = ?", $poids );

        return $this->fetchAll($query)->toArray(); 
    }
    /**
     * Recherche une entrée flux_doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $maj
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doc") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    /**
     * Recherche une entrée flux_doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $pubDate
     */
    public function findByPubDate($pubDate)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doc") )                           
                    ->where( "f.pubDate = ?", $pubDate );

        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche une entrée flux_doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $type
     */
    public function findByType($type)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doc") )                           
                    ->where( "f.type = ?", $type);

        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche des entrée avec une liste de paramètres
     *
     * @param array $params
     *
     * @return array
     */
    public function findByParams($params)
    {
		$select = $this->select();
		$select->from($this);
		foreach($params as $k=>$v){
			$select->where($k.' = ?', $v);
		}
		return $this->fetchAll($select)->toArray();        
    } 
        
    /**
     * Recherche des entrée pour un tronc et un utilisateur
     *
     * @param integer $idUti
     * @param string $tronc
     * @param boolean $like
     *
     * @return array
     */
    public function findByUtiTronc($idUti, $tronc, $like=false)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("f" => "flux_doc") )                           
			->group("f.doc_id");
        if($idUti)
            $query->joinInner(array('utd' => 'flux_utitagdoc'),'utd.uti_id = '.$idUti.' AND utd.doc_id = f.doc_id',array('uti_id',"login"));
		else
            $query->joinInner(array('utd' => 'flux_utitagdoc'),'utd.doc_id = f.doc_id',array('uti_id', "login"));
		
		if($like)
			$query->where( "f.tronc LIKE '%".$tronc."%'");
		else
			$query->where( "f.tronc = ?", $tronc);
		
        return $this->fetchAll($query)->toArray(); 
    } 
    
    /**
     * Recherche des entrée pour un utilisateur
     *
     * @param integer $idUti
     *
     * @return array
     */
    public function findByUti($idUti)
    {
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("f" => "flux_doc") )                           
			->group("f.doc_id");
        $query->joinInner(array('utd' => 'flux_utitagdoc'),'utd.uti_id = '.$idUti.' AND utd.doc_id = f.doc_id',array('uti_id',"login"));
		
        return $this->fetchAll($query)->toArray(); 
    } 
    
    
    /**
     * Recherche des entrées avec une filtre
     *
     * @param string $filtre
     * @param array $cols
     *
     * @return array
     */
    public function findFiltre($filtre, $cols)
    {
        $query = $this->select()
        	->from( array("f" => "flux_doc"), $cols )                           
			->where($filtre)
			->order($cols);
		
        return $this->fetchAll($query)->toArray(); 
    } 
    
    
	/**
     * Récupère les intervales historique des documents 
     *
     * @param int 		$tronc
     * @param string 	$dateUnit unité de la date : year, month...
     * 
     * @return array
     */
	function getHistoInterval($tronc="", $dateUnit="") {

		//élimine les date nulles
		$where = "d.maj != 0";
		
		//vérifie si on prend les tags du document racine ou de ces éléments
		if($tronc){
			$where = ' AND d.tronc = '.$tronc;
		}
		if($dateUnit){
			$dateUnit = 'DATE_FORMAT(d.maj, "%'.$dateUnit.'")';
		}else $dateUnit = "d.maj";
		//définition de la requête
		//attention la colonne value est nécessaire pour le graphique  
		/*		
		SELECT DATE_FORMAT(d.maj, "%Y") temps, count(d.doc_id)
		    FROM flux_doc d
		   WHERE DATE_FORMAT(d.maj, "%Y") != "0000"
		GROUP BY temps
		ORDER BY temps
		*/		
        $query = $this->select()
        	->from( array("d" => "flux_doc"),array("temps"=>$dateUnit, "nb"=>"COUNT(*)"))                           
            ->group(array("temps"))
            ->order(array($dateUnit))
            ->where($where);
		
        return $this->fetchAll($query)->toArray(); 
		    	
	}    

	/**
     * Calcul l'historique de date de publication
     *
     * @param string 	$tronc
     * @param array 	$formatDate = format mysql de la date
     * 
     * @return array
     */
	function getHistoPubli($tronc="", $formatDate="%d-%c-%y") {

		//définition de la requête
		/*		
		SELECT 
		  temps,
		 count(*) nb
		    FROM flux_doc d
		   WHERE d.tronc = '".$tronc."'
		GROUP BY temps
		ORDER BY temps
		*/		
        $query = $this->select()
        	->from( array("d" => "flux_doc"),array("temps"=>"DATE_FORMAT(d.pubDate, '".$formatDate."')", "nb"=>"COUNT(*)"))                           
            ->group(array("temps"))
            ->order(array($dateUnit));
		//vérifie si on prend les tags du document racine ou de ces éléments
		if($tronc){
			$query->where('d.tronc = '.$tronc);
		}
		
        return $this->fetchAll($query)->toArray(); 
		    	
	}    

	/**
     * retourne l'historique des évaluations
     *
     * @param string 	$titre
     * @param array 	$formatDate = format mysql de la date
     * 
     * @return array
     */
	function getHistoEval($titre="axesEval", $dateUnit="%d-%c-%y") {

		if($dateUnit){
			$dateUnit = 'DATE_FORMAT(utd.maj, "'.$dateUnit.'")';
		}else $dateUnit = "utd.maj";
		
		//définition de la requête
        $query = $this->select()
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("d" => "flux_doc"),array("doc_id","tronc","data"))                           
            ->joinInner(array('utd' => 'flux_utitagdoc'),'utd.doc_id = d.doc_id'
            	,array("temps"=>$dateUnit
            		, "nbEval"=>"COUNT(DISTINCT utd.utitagdoc_id)"
            		, "temps"=>$dateUnit
            		, "idsTag"=>"GROUP_CONCAT(DISTINCT utd.tag_id)"
            		, "idsUti"=>"GROUP_CONCAT(DISTINCT utd.uti_id)"
            		, "dPoids"=>"GROUP_CONCAT(DISTINCT utd.poids)"
            		, "poids"=>"SUM(utd.poids)"
            		))
        	->group(array("d.data"))
            ->order(array($dateUnit));
		/*            
		SELECT d.doc_id
	, d.data
	, d.tronc
	, COUNT(DISTINCT utd.utitagdoc_id) nbEval
	, DATE_FORMAT(utd.maj, '%d-%c-%y %h:%i:%s') temps
	, GROUP_CONCAT(DISTINCT utd.tag_id) idsTag
	, GROUP_CONCAT(DISTINCT utd.uti_id) idsUti
	, GROUP_CONCAT(DISTINCT utd.poids) dPoids
    , SUM(utd.poids) poids
FROM flux_doc d
	INNER JOIN flux_utitagdoc utd ON utd.doc_id = d.doc_id
WHERE d.titre = 'axesEval'
 GROUP BY d.data 
 order by temps
		*/            
		//vérifie si on prend les tags du document racine ou de ces éléments
		if($titre){
			$query->where('d.titre = ?', $titre);
		}
		
        return $this->fetchAll($query)->toArray(); 
		    	
	}    

	
    /**
     * Recherche des entrées avec une requête fulltext
     *
     * @param string	$txt
     * @param string	$type
     * @param boolean	$group
     * @param string	$order
     * @param int		$idDoc
     *
     * @return array
     */
    public function findFullText($txt, $type, $group, $order = "score DESC", $idDoc=false)
    {
    	$txt = str_replace("'", " ", $txt);
        $query = $this->select()
            ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("d" => "flux_doc"), array("score"=>"MATCH(d.note) AGAINST('".$txt."')", "titre", "texte"=>"note", "doc_id", "tronc", "url") )                           
            ->where("MATCH(d.note) AGAINST('".$txt."')")
        	->order($order)
			;
		if($idDoc)
			$query->where("d.doc_id = ?", $idDoc);
		if($type)
			$query->where("d.type = ?", $type);
		if($group)
			$query->group(array("d.doc_id"));
			
			
        return $this->fetchAll($query)->toArray(); 
    } 

    
     /**
     * Recherche une entrée avec la valeur spécifiée
     * et retourne la liste de tous ses enfants
     *
     * @param integer $idDoc
     * @param string $order
     * @return array
     */
    public function getFullChild($idDoc, $order="lft")
    {
        $query = $this->select()
                ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('d' => 'flux_doc'),array('titreO'=>'titre', 'doc_id0'=>'doc_id'))
            ->joinInner(array('enf' => 'flux_doc'),
                'enf.lft BETWEEN d.lft AND d.rgt',array('titre', 'doc_id', 'url', 'niveau', 'parent'))
            ->where( "d.doc_id = ?", $idDoc)
           	->order("enf.".$order);        
        $result = $this->fetchAll($query);
        return $result->toArray(); 
    }    
    
    
     /**
     * Copie un document et tous ses enfants
     *
     * @param integer $idDoc
     * @param integer $idParent
     * 
     * @return array
     */
    public function copie($idDoc, $idParent=0)
    {
    		$arr = $this->findBydoc_id($idDoc);
    		unset($arr["doc_id"]);
    		unset($arr["pubDate"]);
    		unset($arr["maj"]);
    		unset($arr["lft"]);
    		unset($arr["rgt"]);
    		if($idParent)$arr["parent"]=$idParent;
    		$dt = $this->ajouter($arr,false,true);

        return $dt; 
    }    

    /**
     * test l'url d'un document
     *
     * @param string $url
     *
     * @return array
     * Array
			(
			    [0] => HTTP/1.1 200 OK
			    [Date] => Sat, 29 May 2004 12:28:14 GMT
			    [Server] => Apache/1.3.27 (Unix)  (Red-Hat/Linux)
			    [Last-Modified] => Wed, 08 Jan 2003 23:11:55 GMT
			    [ETag] => "3f80f-1b6-3e1cb03b"
			    [Accept-Ranges] => bytes
			    [Content-Length] => 438
			    [Connection] => close
			    [Content-Type] => text/html
			)
     * 
     */
    public function testUrl($url)
    {    	
	    return get_headers($url, 1);
    }
    
    
}

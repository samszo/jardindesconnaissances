<?php
/**
 * Ce fichier contient la classe flux_doc.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_doc'.
 *
 * @copyright  201=& Samuel Szoniecky
 * @license    "New" BSD License
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

	protected $_dependentTables = array(
		'Model_DbTable_Flux_UtiDoc'
		,'Model_DbTable_Flux_TagDoc'
		,'Model_DbTable_Flux_UtiTagDoc'
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
			if($k!="maj" && $k!="poids"  && $k!="note" )
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
     *  
     * @return integer
     */
    public function ajouter($data, $existe=true)
    {
    	$id=false;
    	if($existe)$id = $this->existe($data);
    	if(!$id){
    		if(!isset($data["pubDate"])) $data["pubDate"] = new Zend_Db_Expr('NOW()');
    	 	$id = $this->insert($data);
    	}else{
    		//met à jour le poids
    		if(isset($data["poids"])){
    			$dt["poids"] = $id[0]["poids"]+$data["poids"];
    			$dt["maj"] = $data["maj"];
    			$this->edit($id[0]["doc_id"], $dt);
    		} 
    		$id = $id[0]["doc_id"];
    	}
    	return $id;
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
    	if(!$data["maj"]) $data["maj"] = new Zend_Db_Expr('NOW()');
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
     * @return void
     */
    public function remove($id)
    {
    	foreach($this->_dependentTables as $t){
			$tEnfs = new $t();
			$tEnfs->removeDoc($id);
		}
    	$this->delete('flux_doc.doc_id = ' . $id);
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
        $db = Zend_Db_Table::getDefaultAdapter();
    	$stmt = $db->query($sql);
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
                    ->where( "f.doc_id = ?", $doc_id );

        return $this->fetchAll($query)->toArray(); 
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

        return $this->fetchAll($query)->toArray(); 
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
     * @param varchar $tronc
     */
    public function findByTronc($tronc)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_doc") )                           
                    ->where( "f.tronc = ?", $tronc );

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
            $query->joinInner(array('utd' => 'flux_utitagdoc'),'utd.uti_id = '.$idUti.' AND utd.doc_id = f.doc_id',array('uti_id'));
		else
            $query->joinInner(array('utd' => 'flux_utitagdoc'),'utd.doc_id = f.doc_id',array('uti_id'));
		
		if($like)
			$query->where( "f.tronc LIKE '%".$tronc."%'");
		else
			$query->where( "f.tronc = ?", $tronc);
		
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
    
}

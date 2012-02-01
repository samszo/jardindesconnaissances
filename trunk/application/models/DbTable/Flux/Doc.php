<?php
/**
 * Ce fichier contient la classe Flux_Doc.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_Doc'.
 *
 * @copyright  201=& Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Flux_Doc extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_Doc';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'doc_id';

	protected $_dependentTables = array(
		'Model_DbTable_Flux_UtiDoc'
		,'Model_DbTable_Flux_TagDoc'
		);
    
    
    /**
     * Vérifie si une entrée Flux_Doc existe.
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
			if($k!="maj" && $k!="poids" )
				$select->where($k.' = ?', $v);
		}
		$rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows->toArray(); else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_Doc.
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
    		if(!$data["pubDate"]) $data["pubDate"] = new Zend_Db_Expr('NOW()');
    	 	$id = $this->insert($data);
    	}else{
    		//met à jour le poids
    		if($data["poids"]){
    			$dt["poids"] = $id[0]["poids"]+$data["poids"];
    			$dt["maj"] = $data["maj"];
    			$this->edit($id[0]["doc_id"], $dt);
    		} 
    		$id = $id[0]["doc_id"];
    	}
    	return $id;
    }     
    
    /**
     * Recherche une entrée Flux_Doc avec la clef primaire spécifiée
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
	        $this->update($data, 'flux_Doc.url = "'. $url.'"');
    	else        
	        $this->update($data, 'flux_Doc.doc_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_Doc avec la clef primaire spécifiée
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
    	$this->delete('flux_Doc.doc_id = ' . $id);
    }
    
    /**
     * Récupère toutes les entrées Flux_Doc avec certains critères
     * de tri, intervalles
     *
     * @return array
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
        $query = $this->select()
                    ->from( array("flux_Doc" => "flux_Doc") );
                    
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
     * Récupère toutes les entrées distinct de Flux_Doc pour un champs
     * de tri, intervalles
	 *
     * @param string $column
     *
     * @return array
     */
    public function getDistinct($column)
    {
        $query = $this->select()
			->from( array("flux_Doc" => "flux_Doc"),array("nb" => "COUNT(*)",$column))
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
    		FROM flux_Doc d 
    		INNER JOIN flux_UtiDoc ud ON ud.doc_id = d.doc_id 
    		INNER JOIN flux_Uti u ON u.uti_id = ud.uti_id AND u.uti_id  = ".$UtiId;
        $db = Zend_Db_Table::getDefaultAdapter();
    	$stmt = $db->query($sql);
    	return $stmt->fetchAll();
    }
        
    /**
     * Recherche une entrée Flux_Doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $doc_id
     */
    public function findBydoc_id($doc_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Doc") )                           
                    ->where( "f.doc_id = ?", $doc_id );

        return $this->fetchAll($query)->toArray(); 
    }
    /**
     * Recherche une entrée Flux_Doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $url
     */
    public function findByUrl($url)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Doc") )                           
                    ->where( "f.url = ?", $url );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $titre
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Doc") )                           
                    ->where( "f.titre = ?", $titre );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $tronc
     */
    public function findByTronc($tronc)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Doc") )                           
                    ->where( "f.tronc = ?", $tronc );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $poids
     */
    public function findByPoids($poids)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Doc") )                           
                    ->where( "f.poids = ?", $poids );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $maj
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Doc") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Doc avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $pubDate
     */
    public function findByPubDate($pubDate)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_Doc") )                           
                    ->where( "f.pubDate = ?", $pubDate );

        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

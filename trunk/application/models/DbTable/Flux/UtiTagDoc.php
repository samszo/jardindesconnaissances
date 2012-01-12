<?php
/**
 * Ce fichier contient la classe Flux_utitagdoc.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_utitagdoc'.
 *
 * @copyright  2008 Gabriel Malkas
 * @copyright  2010 Samuel Szoniecky
 * @license    "New" BSD License
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
    	}
    	return $id;
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
		$sql = "select d.doc_id, d.url, d.titre, d.branche, d.tronc, d.poids, d.maj, d.pubDate, d.note 
		from flux_utitagdoc utd
			inner join flux_doc d on d.doc_id = utd.doc_id
		where utd.tag_id IN (".tags.") AND utd.uti_id IN (".$users.")  
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
    
}

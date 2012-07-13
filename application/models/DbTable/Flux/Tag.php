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

        return $this->fetchAll($query)->toArray(); 
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
	function getTagAssos($arrTags, $tronc=-1) {

		//vérifie si on prend les tags du document racine ou de ces éléments
		$where = " ";
		if($tronc>-1){
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

         if($tronc!=0){
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
}
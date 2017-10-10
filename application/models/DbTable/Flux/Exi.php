<?php

/**
 * Classe ORM qui représente la table 'flux_exi'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Model_DbTable_Flux_Exi extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_exi';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'exi_id';

    protected $_dependentTables = array(
        'Model_DbTable_Flux_Rapport'
    );
    
    /**
     * Vérifie si une entrée Flux_Uti existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('exi_id'));
		//vérifie les champs obligatoires et discriminants
		if(isset($data['nom']))$select->where('nom = ?', $data['nom']);
		if(isset($data['prenom']))$select->where('prenom = ?', $data['prenom']);
		if(isset($data['isni']))$select->where('isni = ?', $data['isni']);
		if(isset($data['url']))$select->where('url = ?', $data['url']);
		$rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->exi_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_exi.
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
	    		if(!isset($data["maj"]))$data["maj"]= new Zend_Db_Expr('NOW()');
	    	 	$id = $this->insert($data);
	    	}
	    	if($rs)
	    		return $this->findByExiId($id);
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
	    		$arr = $this->findByExiId($data["parent"]);
	    		//gestion des hiérarchies gauche droite
	    		//http://mikehillyer.com/articles/managing-hierarchical-data-in-mysql/
	    		//vérifie si le parent à des enfants
	    		$arrP = $this->findByParent($data["parent"]);
	    		if(count($arrP)){
	    			//met à jour les niveaux 
	    			$sql = 'UPDATE flux_exi SET rgt = rgt + 2 WHERE rgt >'.$arr['rgt'];
	    			$stmt = $this->_db->query($sql);
	    			$sql = 'UPDATE flux_exi SET lft = lft + 2 WHERE lft >'.$arr['rgt'];
	    			$stmt = $this->_db->query($sql);
	    			//
	    			$data['lft'] = $arr['rgt']+1;
	    			$data['rgt'] = $arr['rgt']+2;
	    		}else{
	    			//met à jour les niveaux 
	    			$sql = 'UPDATE flux_exi SET rgt = rgt + 2 WHERE rgt >'.$arr['lft'];
	    			$stmt = $this->_db->query($sql);
	    			$sql = 'UPDATE flux_exi SET lft = lft + 2 WHERE lft >'.$arr['lft'];
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
           
    /**
     * Recherche une entrée Flux_exi avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
    		if(isset($data["data"])){
    			if(is_object($data["data"]) || is_array($data["data"]))$data["data"] = json_eoncode($data["data"]);
    		}
        $this->update($data, 'flux_exi.exi_id = ' . $id);
        return $this->findByExiId($id);
    }
    
    /**
     * Recherche une entrée Flux_exi avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return int
     */
    public function remove($id)
    {
		//suppression des données lieés
        $dt = $this->getDependentTables();
        $nbSup = 0;
        foreach($this->_dependentTables as $t){
            $tEnfs = new $t($this->_db);
            $nbSup += $tEnfs->removeExi($id);
        }
        
        $nbSup += $this->delete('flux_exi.exi_id = '.$id);
        return $nbSup;
    }
    
    /**
     * Récupère toutes les entrées Flux_exi avec certains critères
     * de tri, intervalles
     */
    public function getAll($cols=false, $order=null, $limit=0, $from=0)
    {
	    	if($cols)
	        	$query = $this->select()->from( array("flux_exi" => "flux_exi"), $cols);
	    	else 
	    		$query = $this->select()->from( array("flux_exi" => "flux_exi") );
                    
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
     * Recherche une entrée Flux_exi avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $exi_id
     */
    public function findByExiId($exi_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exi"),array('nom', 'prenom', 'exi_id', 'recid'=>'exi_id','data','nait','mort','isni','data'))                           
                    ->where( "f.exi_id = ?", $exi_id );
		$rs = $this->fetchAll($query)->toArray();
        return  count($rs) ? $rs[0] : false;
    }
    
    /**
     * Recherche une entrée Flux_exi avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id
     */
    public function findByParent($id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exi") )                           
                    ->where( "f.parent = ?", $id );
        return $this->fetchAll($query)->toArray(); 
    }
    
    
    /*
     * Recherche des entrées Flux_exi avec la valeur spécifiée
     * et retourne ces entrées.
     *
     * @param string $ids
     * @param string $champ
     * 
     */
    public function findIn($ids, $champ)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exi") )                           
                    ->where( "f.".$champ." IN (".$ids.")" );

        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * Recherche une entrée Flux_exi avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $nom
     */
    public function findByNom($nom)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exi") )                           
                    ->where( "f.nom = ?", $nom);
		$arr = $this->fetchAll($query)->toArray();
        return $arr[0]; 
    }
    /**
     * Recherche une entrée Flux_exi avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $idUti
     */
    public function findByUtiId($idUti)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exi") )                           
                    ->where( "f.uti_id = ?", $idUti);
		$arr = $this->fetchAll($query)->toArray();
        return $arr[0]; 
    }
    
    /**
     * Recherche une entrée Flux_exi avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $maj
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_exi") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }

     /**
     * Recherche une entrée avec la valeur spécifiée
     * et retourne la liste de tous ses enfants
     *
     * @param integer $idExi
     * @param string $order
     * @return array
     */
    public function getFullChild($idExi, $order="lft")
    {
        $query = $this->select()
                ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('par' => 'flux_exi'),array('nomO'=>'nom', 'id0'=>'exi_id'))
            ->joinInner(array('enf' => 'flux_exi'),
                'enf.lft BETWEEN par.lft AND par.rgt',array('nom', 'exi_id', 'niveau'))
            ->where( "par.exi_id = ?", $idExi)
           	->order("enf.".$order);        
        $result = $this->fetchAll($query);
        return $result->toArray(); 
    }      
    
     /**
     * Recherche les entrées avec la valeur spécifiée
     * et retourne ces entrées
     *
     * @param string $tag
     * 
     * @return array
     */
    public function getExiByTag($tag)
    {
        $query = $this->select()
                ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
            ->from(array('e' => 'flux_exi'),array('nom', 'prenom', 'exi_id', 'recid'=>'exi_id','data','nait','mort','isni','data'))
            ->joinInner(array('etd' => 'flux_exitagdoc'),
                'etd.exi_id = e.exi_id',array())
            ->joinInner(array('t' => 'flux_tag'),
                't.tag_id = etd.tag_id',array('tag_id', 'code'))
            ->where( "t.code = ?", $tag)
            ->order("e.nom");        
        $result = $this->fetchAll($query);
        return $result->toArray(); 
    }      
    
}

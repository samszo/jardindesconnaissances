<?php
/**
 * Ce fichier contient la classe Flux_Exi.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'flux_exi'.
 *
 * @copyright  2014 Samuel Szoniecky
 * @license    "New" BSD License
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
       "Model_DbTable_Flux_ExiTagDoc"
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
		//seul le nom est obligatoire et discriminant
		$select->where('nom = ?', $data['nom']);
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
	    		$arrP = $this->findByExiId($data["parent"]);
	    		//gestion des hiérarchies gauche droite
	    		//http://mikehillyer.com/articles/managing-hierarchical-data-in-mysql/
	    		//vérifie si le parent à des enfants
	    		$arr = $this->findByParent($data["parent"]);
	    		if(count($arr)>0){
	    			//met à jour les niveaux 
	    			$sql = 'UPDATE flux_exi SET rgt = rgt + 2 WHERE rgt >'.$arr[0]['rgt'];
	    			$stmt = $this->_db->query($sql);
	    			$sql = 'UPDATE flux_exi SET lft = lft + 2 WHERE lft >'.$arr[0]['rgt'];
	    			$stmt = $this->_db->query($sql);
	    			//
	    			$data['lft'] = $arr[0]['rgt']+1;
	    			$data['rgt'] = $arr[0]['rgt']+2;
	    		}else{
	    			//met à jour les niveaux 
	    			$sql = 'UPDATE flux_exi SET rgt = rgt + 2 WHERE rgt >'.$arrP[0]['lft'];
	    			$stmt = $this->_db->query($sql);
	    			$sql = 'UPDATE flux_exi SET lft = lft + 2 WHERE lft >'.$arrP[0]['lft'];
	    			$stmt = $this->_db->query($sql);
	    			//
	    			$data['lft'] = $arr[0]['lft']+1;
	    			$data['rgt'] = $arr[0]['lft']+2;
	    		}    		
	    		$data['niveau'] = $arrP[0]['niveau']+1;
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
        $this->update($data, 'flux_exi.exi_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_exi avec la clef primaire spécifiée
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
	        $dbT->delete('uti_id = '.$id);
        }            	
    	$this->delete('flux_exi.exi_id = '.$id);
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
                    ->from( array("f" => "flux_exi") )                           
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
        $result = $this->fetchAll($query);
        
        if($result)
	        return $result->toArray(); 
	    else 
	    	return false;                    
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
    
    /*
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
    
}

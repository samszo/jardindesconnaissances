<?php


/**
 * Classe ORM qui représente la table 'flux_Uti'.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Model_DbTable_Flux_Uti extends Zend_Db_Table_Abstract
{
    
    /*
     * Nom de la table.
     */
    protected $_name = 'flux_uti';
    
    /*
     * Clef primaire de la table.
     */
    protected $_primary = 'uti_id';

    protected $_dependentTables = array(
       "Model_DbTable_flux_actiuti"
       ,"Model_DbTable_flux_utidoc"
       ,"Model_DbTable_Flux_UtiGeoDoc"
       ,"Model_DbTable_flux_utiieml"
       ,"Model_DbTable_Flux_UtiTagDoc"
       ,"Model_DbTable_flux_utitag"
       ,"Model_DbTable_flux_utitagrelated"
       ,"Model_DbTable_Flux_UtiUti"
       ,"Model_DbTable_Flux_UtiExi"
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
		$select->from($this, array('uti_id'));
		//seul le login est obligatoire et discriminant
		$select->where('login = ?', $data['login']);
		if(isset($data['flux']))$select->where('flux = ?', $data['flux']);
		$rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->uti_id; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée Flux_Uti.
     *
     * @param array $data
     * @param boolean $existe
     *  
     * @return integer
     */
    public function ajouter($data, $existe=true, $rs=false)
    {
    	$id=false;
    	if($existe)$id = $this->existe($data);    			
    	if(!$id){
    		if(!isset($data["date_inscription"]))$data["date_inscription"]= new Zend_Db_Expr('NOW()');
    		if(!isset($data["maj"]))$data["maj"]= new Zend_Db_Expr('NOW()');
    		if(!isset($data["ip_inscription"]) && isset($_SERVER['REMOTE_ADDR']))$data["ip_inscription"]= $_SERVER['REMOTE_ADDR'];
    		$id = $this->insert($data);
    	 	$err = "";
    	}else{
    		$err = "le login existe déjà";
    	}
    	if($rs){
    		$rs = $this->findByuti_id($id);	
    		$rs["erreur"]=$err;
    		return $rs;
    	}
    	return $id;
    } 
           
    /**
     * Recherche une entrée Flux_Uti avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
        $this->update($data, 'flux_uti.uti_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_Uti avec la clef primaire spécifiée
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
        	if($t!="Model_DbTable_Flux_UtiUti")
	        	$dbT->delete('uti_id = '.$id);
	        else
	        	$dbT->delete('uti_id_src = '.$id.' OR uti_id_dst = '.$id);
        }            	
    	$this->delete('flux_uti.uti_id = '.$id);
    }
    
    /**
     * Récupère toutes les entrées Flux_Uti avec certains critères
     * de tri, intervalles
     */
    public function getAll($cols=false, $order=null, $limit=0, $from=0)
    {
    	if($cols)
        	$query = $this->select()->from( array("flux_uti" => "flux_uti"), $cols);
    	else 
    		$query = $this->select()->from( array("flux_uti" => "flux_uti") );
                    
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
     * Recherche une entrée Flux_Uti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $uti_id
     */
    public function findByuti_id($uti_id)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_uti") )                           
                    ->where( "f.uti_id = ?", $uti_id );

        return $this->fetchAll($query)->toArray(); 
    }
    
    /*
     * Recherche des entrées Flux_Uti avec la valeur spécifiée
     * et retourne ces entrées.
     *
     * @param string $ids
     * @param string $champ
     * 
     */
    public function findIn($ids, $champ)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_uti") )                           
                    ->where( "f.".$champ." IN (".$ids.")" );

        return $this->fetchAll($query)->toArray(); 
    }
    
    /*
     * Recherche une entrée Flux_Uti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $login
     */
    public function findByLogin($login)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_uti") )                           
                    ->where( "f.login = ?", $login );
		$arr = $this->fetchAll($query)->toArray();
        return $arr[0]; 
    }
    /*
     * Recherche une entrée Flux_Uti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $maj
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_uti") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Uti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $flux
     */
    public function findByFlux($flux)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_uti") )                           
                    ->where( "f.flux = ?", $flux );

        return $this->fetchAll($query)->toArray(); 
    }
    /*
     * Recherche une entrée Flux_Uti avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $role
     * @param boolean $img 
     * @param boolean $login 
     * 
     * @return array
     */
    public function findByRole($role, $img=false, $login=false)
    {
        $query = $this->select()
			->from( array("u" => "flux_uti"),array("login","uti_id") )                           
            ->where( "u.role = ?", $role );
		if($img){
			if($login)
		        	$query->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
					->joinInner(array('d' => 'flux_doc'),"d.titre = u.login",array('doc_id','url'));
			else{
		        	$query->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
		        		->joinInner(array('ud' => 'flux_utidoc'),'ud.uti_id = u.uti_id',array())
						->joinInner(array('d' => 'flux_doc'),"d.doc_id = ud.doc_id AND type = 'foaf:img'",array('doc_id','url'));
			}
		}
            
        return $this->fetchAll($query)->toArray(); 
    }
    
    /**
     * renoie les valeur distinct d'une colone
     *
     * @param string $col
     *
     * @return array
     */
    public function getDistinct($col)
    {
        $query = $this->select()
        	->distinct()
   	     	->from( array("f" => "flux_uti"),$col)
   	     	->where($col." !=''");
    	return $this->fetchAll($query)->toArray();        
    } 

    /**
     * renvoie les rôles et les utilisateurs associés
     *
     * @return array
     */
    public function getRolesUtis()
    {
    	$arrRoles = $this->getDistinct("role");
    	$nbRole = count($arrRoles);
    	for ($i = 0; $i < $nbRole; $i++) {
    		//pour la base flux_tweetpalette
    		//$arrUti = $this->findByRole($arrRoles[$i]["role"],true);
    		//pour la base flux_etu
    		$arrUti = $this->findByRole($arrRoles[$i]["role"],true, true);
    		$arrRoles[$i]['utis'] = $arrUti;
    	}
    	return $arrRoles;        
    } 
    
}

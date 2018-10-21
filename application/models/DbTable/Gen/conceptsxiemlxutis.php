<?php
/**
 * Ce fichier contient la classe Gen_concepts_ieml_utis.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
*/
class Model_DbTable_Gen_conceptsxiemlxutis extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_concepts_ieml_utis';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_ciu';
    
    /**
     * Vérifie si une entrée existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_ciu'));
		$select->where('id_concept = ?', $data['id_concept']);
		$select->where('ieml_id = ?', $data['ieml_uti']);
    		$select->where('uti_id = ?', $data['uti_id']);
    		if(isset($data['id_gen'])) $select->where('id_gen = ?', $data['id_gen']);
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_ciu; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée 
     *
     * @param array $data
     * @param boolean $existe
     *  
     * @return integer
     */
    public function ajouter($data, $existe=true)
    {
    	
	    	$id=false;
	    	//vérifie si l'ieml_id est passé
	    	if(!isset($data["ieml_id"])){
	    		//récupère l'ieml_id
	    		$dbIeml = new Model_DbTable_Flux_Ieml();
	    		$data["ieml_id"] = $dbIeml->ajouter(array("descripteur"=>$data["descripteur"], "code"=>$data["code"]));
	    		unset($data["descripteur"]);
	    		unset($data["code"]);
	    	}
	    	if($existe)$id = $this->existe($data);
	    	if(!$id){
	    		if(!isset($data["maj"])) $data["maj"] = new Zend_Db_Expr('NOW()');
	    	 	$id = $this->insert($data);
	    	}
	    	    	 
	    	return $id;
    } 
           
    /**
     * Recherche une entrée avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'gen_concepts_ieml_utis.id_ciu = ' . $id);
    }
    
    /**
     * Recherche une entrée  avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param int $id
     *
     * @return void
     */
    public function remove($id)
    {    	
	    	//suprime le lien entre l'oeuvre et le dictionnaire
	    	$this->delete('id_ciu='.$id);
    }
    
    /**
     * trouve dans les adresses sémantiques pour un concept
     *
     * @param int 		$idCpt
     *
     * @return Array
     */
    public function findAllByConcept($idCpt)
    {
    	$sql ="SELECT ciu.id_ciu, ciu.maj
    			, c.id_concept, c.lib, c.type
    			, u.uti_id, u.login
    			, i.ieml_id, i.code, i.descripteur
    			, g.id_gen, g.valeur
    			FROM gen_concepts_ieml_utis ciu
				INNER JOIN gen_concepts c ON c.id_concept = ciu.id_concept
				INNER JOIN flux_uti u ON u.uti_id = ciu.uti_id
				INNER JOIN flux_ieml i ON i.ieml_id = ciu.ieml_id
				LEFT JOIN gen_generateurs g ON g.id_gen = ciu.id_gen
			WHERE ciu.id_concept = ".$idCpt;
		$smtp = $this->_db->query($sql);
        return $smtp->fetchAll();
    }
    
}

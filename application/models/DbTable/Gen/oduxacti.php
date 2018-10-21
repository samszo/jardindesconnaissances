<?php
/**
 * Ce fichier contient la classe gen_odu_acti.
 *
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
*/
class Model_DbTable_Gen_oduxacti extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'gen_odu_acti';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = array('id_odu','acti_id');

    
    /**
     * Vérifie si une entrée gen_odu_acti existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('id_odu','acti_id'));
		$select->where('id_odu = ?', $data['id_odu']);
		$select->where('acti_id = ?', $data['acti_id']);
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->id_odu; else $id=false;
        return $id;
    } 
        
    /**
     * Ajoute une entrée gen_odu_acti.
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
     * Recherche une entrée gen_odu_acti avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param int $idOeu
     * @param integer $idDico
     * @param int $idUti
     *
     * @return void
     */
    public function remove($idOeu, $idDico, $idUti)
    {
    	//suprime les actions liés aux dictionnaire de l'oeuvre et à l'utilisateur
    	
    	//suprime le lien entre l'oeuvre et le dictionnaire
    	$this->delete('id_oeu = ' . $idOeu.' AND id_dico = ' . $idDico.' AND uti_id = ' . $idUti);
    }
    
    /**
     * Recherche une entrée avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $idOdu
     *
     * @return array
     */
    public function findByIdOdu($idOdu)
    {
        $query = $this->select()
        	->from( array("oa" => "gen_odu_acti") )                           
        ->where( "oa.id_odu = ?", $idOdu);
        
        return $this->fetchAll($query)->toArray(); 
    }
    
    
}

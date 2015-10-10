<?php
/**
 * Ce fichier contient la classe Spip_Articles.
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
*/


/**
 * Classe ORM qui représente la table 'spip_articles'.
 *
 * @copyright  201=& Samuel Szoniecky
 * @license    "New" BSD License
 */
class Model_DbTable_Spip_Articles extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'spip_articles';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'id_article'; 
    
    /**
     * Recherche des entrées avec une requête fulltext
     *
     * @param string $filtre
     * @param array $cols
     *
     * @return array
     */
    public function findFullText($txt)
    {
    	$txt = str_replace("'", " ", $txt);
        $query = $this->select()
            ->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table
        	->from( array("a" => "spip_articles"), array("score"=>"MATCH(fulltxt) AGAINST('".$txt."')", "titre", "texte", "id_article", "id_rubrique") )                           
            ->joinInner(array('r' => 'spip_rubriques'),
                'r.id_rubrique = a.id_rubrique',array('rubTitre'=>'titre', 'id_parent'))
            ->joinLeft(array('da' => 'spip_documents_articles'),
                'da.id_article = a.id_article',array())
            ->joinLeft(array('d' => 'spip_documents'),
                'd.id_document = da.id_document',array('docTitre'=>'titre', 'fichier', 'id_type'))
            ->where("MATCH(fulltxt) AGAINST('".$txt."')")
        	->order("r.id_rubrique")
			;
		
        return $this->fetchAll($query)->toArray(); 
    } 
    
}

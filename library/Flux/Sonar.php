<?php
/**
 * Flux_Sonar
 * Classe qui gère les flux de l'application Sonar
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Sonar extends Flux_Site{

	
    /**
     * Constructeur de la classe
     *
     * @param  string   $idBase
     * @param  boolean  $bTrace
     * @param  boolean  $bCache
     * 
     */
	public function __construct($idBase=false, $bTrace=false, $bCache=true)
    {
    		parent::__construct($idBase, $bTrace, $bCache);    	

    		//on récupère la racine des documents
    		$this->initDbTables();
    		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
    		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
    		$this->idTagRoot = $this->dbT->ajouter(array("code"=>__CLASS__));
    		$this->idTagTypeFlux = $this->dbT->ajouter(array("code"=>'type flux','parent'=>$this->idTagRoot));
            $this->idTagRep = $this->dbT->ajouter(array('code'=>'réponse', 'parent'=>$this->idTagRoot));
            $this->idTagChoixRep = $this->dbT->ajouter(array('code'=>'choix', 'parent'=>$this->idTagRoot));
            $this->idTagNonChoixRep = $this->dbT->ajouter(array('code'=>'non choix', 'parent'=>$this->idTagRoot));
            $this->idTagPosChoixRep = $this->dbT->ajouter(array('code'=>'possibilité de choix', 'parent'=>$this->idTagRoot));
            $this->idTagProc = $this->dbT->ajouter(array('code'=>'processus', 'parent'=>$this->idTagRoot));
            
    }
        
    /**
	 * récupère la liste des formulaires
     * 
     * @return array
	 */
	function getListeFlux(){
        $sql = "SELECT 
                d.doc_id,
                d.url,
                d.type,
                GROUP_CONCAT(dp.titre) dpTitres,
                GROUP_CONCAT(dp.url) dpUrls,
                GROUP_CONCAT(dp.doc_id) dpIds
            FROM
                flux_doc d
                    INNER JOIN
                flux_doc dp ON d.lft BETWEEN dp.lft AND dp.rgt
            WHERE
                d.tronc = 'flux'
                    AND dp.niveau BETWEEN 2 AND d.niveau - 1
            GROUP BY d.doc_id";
        $rs = $this->dbD->exeQuery($sql);
        foreach ($rs as $id => $d) {
            $t = explode(',',$d['dpTitres']);
            $u = explode(',',$d['dpUrls']);
            $i = explode(',',$d['dpIds']);
            unset($rs[$id]['dpTitres']);
            unset($rs[$id]['dpUrls']);
            unset($rs[$id]['dpIds']);
            $rs[$id]['parents'] = [];
            foreach ($t as $k => $v) {
                $rs[$id]['parents'][] = array('titre'=>$v,'url'=>$u[$k],'id'=>$i[$k]);
            }
        }
        return $rs;
    }

}
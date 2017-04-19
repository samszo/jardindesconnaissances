<?php
/**
 * Ce fichier contient la classe Flux_monade.
 *
 * @author Samuel Szoniecky
 * @category   Zend
 * @package Zend\DbTable\Flux
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Model_DbTable_Flux_Monade extends Zend_Db_Table_Abstract
{
    
    /**
     * Nom de la table.
     */
    protected $_name = 'flux_monade';
    
    /**
     * Clef primaire de la table.
     */
    protected $_primary = 'monade_id';

    var $idExiVide;
    var $idTagVide;
    var $idDocVide;
    var $idExiTagDocVide;
    var $dbSvg;
    

    /**
     * récupère les références vide
     *
     */
    public function getVides()
    {
		$dbD = new Model_DbTable_Flux_Doc($this->_db);
		$this->idDocVide = $dbD->ajouter(array("titre"=>"vide"));
		$dbT = new Model_DbTable_Flux_Tag($this->_db);
		$this->idTagVide = $dbT->ajouter(array("code"=>"vide"));
		$dbE = new Model_DbTable_Flux_Exi($this->_db);
		$this->idExiVide = $dbE->ajouter(array("nom"=>"vide"));
		$dbETD = new Model_DbTable_Flux_ExiTagDoc($this->_db);
		$this->idExiTagDocVide = $dbETD->ajouter(array("exi_id"=>$this->idExiVide,"doc_id"=>$this->idDocVide,"tag_id"=>$this->idTagVide));
    } 
    
    /**
     * Vérifie si une entrée Flux_monade existe.
     *
     * @param array $data
     *
     * @return integer
     */
    public function existe($data)
    {
		$select = $this->select();
		$select->from($this, array('monade_id'));
		foreach($data as $k=>$v){
			$select->where($k.' = ?', $v);
		}
	    $rows = $this->fetchAll($select);        
	    if($rows->count()>0)$id=$rows[0]->monade_id; else $id=false;
        return $id;
    } 

    /**
     * Création d'une monade.
     *
     * @param array $data
     * @param boolean $existe
     *  
     * @return array
     */
    public function creer($data, $existe=true)
    {
    		$r = $this->ajouter(array("titre"=>$data['titre']));
		//on la lie à l'utilisateur
		$dbUM = new Model_DbTable_flux_UtiMonade($this->_db);
		$dbUM->ajouter(array("uti_id"=>$data['uti_id'], "monade_id"=>$r["monade_id"]));

		//on récupère les références vides 
		if(!$this->idDocVide)$this->getVides();
		
		//on les associe à un svg
		if(!$this->dbSvg)$this->dbSvg = new Model_DbTable_Flux_Svg($this->_db);
		$this->dbSvg->ajouter(array("monade_id"=>$r["monade_id"], "obj_id"=>$this->idDocVide, "obj_type"=>"Model_DbTable_Flux_Doc"));
		$this->dbSvg->ajouter(array("monade_id"=>$r["monade_id"], "obj_id"=>$this->idTagVide, "obj_type"=>"Model_DbTable_Flux_Tag"));
		$this->dbSvg->ajouter(array("monade_id"=>$r["monade_id"], "obj_id"=>$this->idExiVide, "obj_type"=>"Model_DbTable_Flux_Exi"));
		$this->dbSvg->ajouter(array("monade_id"=>$r["monade_id"], "obj_id"=>$this->idExiTagDocVide, "obj_type"=>"Model_DbTable_Flux_ExiTagDoc"));
		
		//on ajoute un rapport vide à la monade
		$dbR = new Model_DbTable_Flux_Rapport($this->_db);
		$dbR->ajouter(array("monade_id"=>$r["monade_id"], "exitagdoc_id"=>$this->idExiTagDocVide,"niveau"=>1));
				
		return $r;
    } 

    /**
     * ajout d'un rapport à une monade.
     *
     * @param int 		$idMon
     * @param int 		$idExi
     * @param int 		$idTag
     * @param int 		$idDoc
     * @param int		$niveau
     *  
     * @return array
     */
    public function ajoutRapport($idMon, $idExi, $idTag, $idDoc, $niveau)
    {
				
		//on ajoute un rapport
		$dbETD = new Model_DbTable_Flux_ExiTagDoc($this->_db);
		$idETD = $dbETD->ajouter(array("exi_id"=>$idExi,"doc_id"=>$idDoc,"tag_id"=>$idTag));

		// on ajoute une définition svg
		if(!$this->dbSvg)$this->dbSvg = new Model_DbTable_Flux_Svg($this->_db);
		//pour le rapport
		$idSvg = $this->dbSvg->ajouter(array("obj_id"=>$idETD, "obj_type"=>"Model_DbTable_Flux_ExiTagDoc", "monade_id"=>$idMon));
		
		//on ajoute le rapport à la monade
		$dbR = new Model_DbTable_Flux_Rapport($this->_db);
		$r = $dbR->ajouter(array("monade_id"=>$idMon, "exitagdoc_id"=>$idETD,"niveau"=>$niveau));
		
		return $r;
    } 
    
    /**
     * ajout d'un document à une monade.
     *
     * @param int 		$idMon
     * @param array 	$data
     * @param int 		$idExi
     * @param int 		$idTag
     *  
     * @return array
     */
    public function ajoutDoc($idMon, $data, $idExi=0, $idTag=0)
    {
    	//on ajoute ou on récupère le document
		$dbD = new Model_DbTable_Flux_Doc($this->_db);
		$data = $dbD->ajouter($data, true, true);
		
		// on ajoute une définition svg
		if(!$this->dbSvg)$this->dbSvg = new Model_DbTable_Flux_Svg($this->_db);
		//pour le rapport
		$idSvg = $this->dbSvg->ajouter(array("obj_id"=>$data["doc_id"], "obj_type"=>"Model_DbTable_Flux_Doc", "monade_id"=>$idMon));
		
		//on récupère les références vides 
		if(!$idExi || !$idTag) $this->getVides();
		if(!$idExi) $idExi = $this->idExiVide;
		if(!$idTag) $idTag = $this->idTagVide;
		
		//on ajoute un rapport
		$r = $this->ajoutRapport($idMon, $idExi, $idTag, $data["doc_id"], $data["niveau"]);
		
		return $data["doc_id"];
    } 
    
    /**
     * ajout d'un existence à une monade.
     *
     * @param int 		$idMon
     * @param array 	$data
     * @param int 		$idDoc
     * @param int 		$idTag
     *  
     * @return array
     */
    public function ajoutExi($idMon, $data, $idTag=0, $idDoc=0)
    {
    	//on ajoute ou on récupère l'existence
		$dbE = new Model_DbTable_Flux_Exi($this->_db);
		$data = $dbE->ajouter($data,true,true);
		    			
		// on ajoute une définition svg
		if(!$this->dbSvg)$this->dbSvg = new Model_DbTable_Flux_Svg($this->_db);
		//pour le rapport
		$idSvg = $this->dbSvg->ajouter(array("obj_id"=>$data["exi_id"], "obj_type"=>"Model_DbTable_Flux_Exi", "monade_id"=>$idMon));
		
		//on récupère les références vides 
		if(!$idDoc || !$idTag) $this->getVides();
		if(!$idDoc) $idDoc = $this->idDocVide;
		if(!$idTag) $idTag = $this->idTagVide;
		
		//on ajoute un rapport
		$r = $this->ajoutRapport($idMon, $data["exi_id"], $idTag, $idDoc, $data["niveau"]);
						
		return $data["exi_id"];
    } 
    
    /**
     * ajout d'un tag à une monade.
     *
     * @param int 		$idMon
     * @param array 	$data
     * @param int 		$idExi
     * @param int 		$idDoc
     *  
     * @return array
     */
    public function ajoutTag($idMon, $data, $idExi=0, $idDoc=0)
    {
    	//on ajoute ou on récupère le tag
		$dbT = new Model_DbTable_Flux_Tag($this->_db);
		$data = $dbT->ajouter($data, true, true);
		    			
		// on ajoute une définition svg
		if(!$this->dbSvg)$this->dbSvg = new Model_DbTable_Flux_Svg($this->_db);
		//pour le rapport
		$idSvg = $this->dbSvg->ajouter(array("obj_id"=>$data["tag_id"], "obj_type"=>"Model_DbTable_Flux_Tag", "monade_id"=>$idMon));
		
		//on récupère les références vides 
		if(!$idDoc || !$idExi) $this->getVides();
		if(!$idDoc) $idDoc = $this->idDocVide;
		if(!$idExi) $idExi = $this->idExiVide;
		
		//on ajoute un rapport
		$r = $this->ajoutRapport($idMon, $idExi, $data["tag_id"], $idDoc, $data["niveau"]);
						
		return $r;
    } 
    
    
    /**
     * Ajoute une entrée Flux_monade.
     *
     * @param array $data
     * @param boolean $existe
     * @param boolean $rs
     *  
     * @return integer
     */
    public function ajouter($data, $existe=true, $rs=true)
    {    	
	    	$id=false;
	    	if($existe)$id = $this->existe($data);
	    	if(!$id){
	    		if(!isset($data["maj"])) $data["maj"] = new Zend_Db_Expr('NOW()');
	    		$id = $this->insert($data);
	    	}
	    	if($rs)
	    		return $this->findById($id);
	    	else 
	    		return $id;
    } 
           
    /**
     * Recherche une entrée Flux_monade avec la clef primaire spécifiée
     * et modifie cette entrée avec les nouvelles données.
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public function edit($id, $data)
    {        
   	
    	$this->update($data, 'flux_monade.monade_id = ' . $id);
    }
    
    /**
     * Recherche une entrée Flux_monade avec la clef primaire spécifiée
     * et supprime cette entrée.
     *
     * @param integer $id
     *
     * @return void
     */
    public function remove($id)
    {
    	//supprime les rapports
    	$dbR = new Model_DbTable_Flux_Rapport($this->_db);
    	$dbR->removeMonade($id);
    	//supprime les rapport avec le svg
		$sql = 'DELETE flux_monadesvg WHERE monade_id ='.$id;
	    $stmt = $this->_db->query($sql);
	    			    	
    	$this->delete('flux_monade.monade_id = ' . $id);
    	
    }
    
    /**
     * Récupère toutes les entrées Flux_monade avec certains critères
     * de tri, intervalles
     */
    public function getAll($order=null, $limit=0, $from=0)
    {
   	
    	$query = $this->select()
                    ->from( array("flux_monade" => "flux_monade") );
                    
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
     * Recherche une entrée Flux_monade avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param int $id
     *
     * @return array
     */
    public function findById($id)
    {
        $query = $this->select()
        	->from( array("f" => "flux_monade") )                           
            ->where( "f.monade_id = ?", $id );

		$arr = $this->fetchAll($query)->toArray();
        return $arr[0];
    }
    	/**
     * Recherche une entrée Flux_monade avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param varchar $titre
     *
     * @return array
     */
    public function findByTitre($titre)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_monade") )                           
                    ->where( "f.titre = ?", $titre );
		return $this->fetchAll($query)->toArray();
    }
    /**
     * Recherche une entrée Flux_monade avec la valeur spécifiée
     * et retourne cette entrée.
     *
     * @param datetime $maj
     *
     * @return array
     */
    public function findByMaj($maj)
    {
        $query = $this->select()
                    ->from( array("f" => "flux_monade") )                           
                    ->where( "f.maj = ?", $maj );

        return $this->fetchAll($query)->toArray(); 
    }
    
   /**
     * renvoie les informations liées à une une monade
     *
     * @param int $idMon
     * @param int $niveau
     *
     * @return array
     */
    public function getRapports($IdMon, $niveau=false)
    {
        $query = $this->select()
			->from( array("m" => "flux_monade"),array("m.monade_id"))                           
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table        
				->joinInner(array('r' => 'flux_rapport'), "r.monade_id = m.monade_id",array("rapport_id", "niveau"))
				->joinInner(array('etd' => 'flux_exitagdoc'), "etd.exitagdoc_id = r.exitagdoc_id",array('exitagdoc_id'))
				->joinInner(array('svgETD' => 'flux_svg'), "svgETD.monade_id = m.monade_id AND etd.exitagdoc_id = svgETD.obj_id AND svgETD.obj_type = 'Model_DbTable_Flux_ExiTagDoc'"
					,array('svgETDid'=>'svg_id','svgETD'=>'data'))
				->joinInner(array('d' => 'flux_doc'), "d.doc_id = etd.doc_id",array('doc_id','dTitre'=>'d.titre','url','poids','dParent'=>'parent'))
				->joinInner(array('svgD' => 'flux_svg'), "svgD.monade_id = m.monade_id AND d.doc_id = svgD.obj_id AND svgD.obj_type = 'Model_DbTable_Flux_Doc'"
					,array('svgDid'=>'svg_id','svgD'=>'data'))
				->joinInner(array('t' => 'flux_tag'), "t.tag_id = etd.tag_id",array('tag_id','code'))
				->joinInner(array('svgT' => 'flux_svg'), "svgT.monade_id = m.monade_id AND t.tag_id = svgT.obj_id AND svgT.obj_type = 'Model_DbTable_Flux_Tag'"
					,array('svgTid'=>'svg_id','svgT'=>'data'))
				->joinInner(array('e' => 'flux_exi'), "e.exi_id = etd.exi_id",array('exi_id','nom'))
				->joinInner(array('svgE' => 'flux_svg'), "svgE.monade_id = m.monade_id AND e.exi_id = svgE.obj_id AND svgE.obj_type = 'Model_DbTable_Flux_Exi'"
					,array('svgEid'=>'svg_id','svgE'=>'data'))
				
				->where( "m.monade_id = ?", $IdMon )
				->group("r.rapport_id");
		if($niveau)$query->where( "r.niveau = ?", $niveau);
		/*
		select
		m.titre mTitre
		, r.rapport_id
		, d.doc_id, d.titre dTitre, d.url
		, t.tag_id, t.code
		, u.uti_id, u.login
		from flux_monade m
		inner join flux_rapport r on r.monade_id = m.monade_id
		inner join flux_exitagdoc etd on etd.exitagdoc_id = r.exitagdoc_id
		inner join flux_doc d on d.doc_id = etd.doc_id
		inner join flux_tag t on t.tag_id = etd.tag_id
		inner join flux_uti u on u.uti_id = etd.uti_id
		where m.monade_id = 2                    
		*/
        return $this->fetchAll($query)->toArray(); 
    }

	/**
     * calcule l'Indice de Complexité Existenciel pour une monade
     *
     * @param int $idMon
     *
     * @return array
     */
    public function getICE($IdMon)
    {
        //récupère les données par niveau
		/*
		SELECT r.niveau,
		         count(DISTINCT etd.doc_id) nbDoc,
		         count(DISTINCT etd.uti_id) nbUti,
		         count(DISTINCT etd.tag_id) nbTag,
		         SUM(utd.poids) poids,
		         COUNT(DISTINCT etd.exitagdoc_id) nbLien
		    FROM flux_rapport r
		         INNER JOIN flux_exitagdoc etd ON etd.exitagdoc_id = r.exitagdoc_id
		WHERE r.monade_id = 2
		GROUP BY r.niveau
		*/
    		$query = $this->select()
			->from( array("r" => "flux_rapport"),array("niveau"))                           
        	->setIntegrityCheck(false) //pour pouvoir sélectionner des colonnes dans une autre table        
				->joinInner(array('etd' => 'flux_exitagdoc'), "etd.exitagdoc_id = r.exitagdoc_id",array('nbDoc'=>"count(DISTINCT etd.doc_id)"
					 ,'nbUti'=>"count(DISTINCT etd.exi_id)", 'nbTag'=>'COUNT(DISTINCT etd.tag_id)', 'nbLien'=>'COUNT(DISTINCT etd.exitagdoc_id)'
					  , 'poids'=>'SUM(etd.poids)'))
				->where( "r.monade_id = ?", $IdMon )
				->group("niveau")
				->order("niveau DESC");
		$rs = $this->fetchAll($query)->toArray(); 
		//calcule la somme pour chaque niveau
		$nbNiv = count($rs);
		$result = array();
		$tags = array();
		$docs = array();
		$exis = array();
		$rapports = array();
		for ($i = 0; $i < $nbNiv; $i++) {
			//calcule la matrice du niveau
			$nbDoc = $rs[$i]['nbDoc']+0;
			$nbTag = $rs[$i]['nbTag']+0;
			$nbUti = $rs[$i]['nbUti']+0;
			$nbLien = $rs[$i]['nbLien']+0;
			$matrice = $nbDoc*$nbLien*$nbLien*$nbTag;
			//ajoute les matrices des niveaux précédent
			foreach ($result as $value) {
				$matrice += $value['matrice']*$value['niveau'];
				$nbDoc = $value['nbDoc'];
				$nbTag = $value['nbTag'];
				$nbUti = $value['nbUti'];
				$nbLien = $value['nbLien'];
			}
			//calcul l'indice
			$niv = $rs[$i]['niveau']+0;
			$indice = 1/($matrice*$niv);
			//ajoute les ICE enfant
			if($i>0) $iceEnf = $result[$i-1];
			else $iceEnf = array();
			//ajoute les rapports
			$rsR = $this->getRapports($IdMon, $niv);
			//compile la définition des objets liées
			$docs=array();
			foreach ($rsR as $r) {
				if($docParent[$r['doc_id']]) $docChild = $docParent[$r['doc_id']];
				else $docChild = array();
				$oDoc = array("niveau"=>$niv, 'doc_id'=>$r['doc_id'], 'parent'=>$r['dParent'],'titre'=>$r['dTitre'],'url'=>$r['url'],'poids'=>$r['poids'],'svgId'=>$r['svgDid'],'svgData'=>$r['svgD'], "children"=>$docChild);
				$docs[] = $oDoc; 
				$docParent[$r['dParent']][]=$oDoc;
				$tags[] = array("niveau"=>$niv, 'tag_id'=>$r['tag_id'],'code'=>$r['code'],'svgId'=>$r['svgTid'],'svgData'=>$r['svgT']);
				$exis[] = array("niveau"=>$niv, 'exi_id'=>$r['exi_id'],'nom'=>$r['nom'],'svgId'=>$r['svgEid'],'svgData'=>$r['svgE']);
				$rapports[] = array("niveau"=>$niv, 'tag_id'=>$r['tag_id'],'doc_id'=>$r['doc_id'],'exi_id'=>$r['exi_id'],'monade_id'=>$r['monade_id'], 'exitagdoc_id'=>$r['exitagdoc_id'],'svgId'=>$r['svgETDid'],'svgData'=>$r['svgETD'] );
			}						
			$result[$niv] = array("tags"=>$tags, "exis"=>$exis
			, "docs"=>array("doc_id"=>-1,"titre"=>"documents","poids"=>count($docs), "children"=>$docs)
			, "rapports"=>$rapports
			, "children"=>$iceEnf, "niveau"=>$niv, "nbUti"=>$nbUti, "nbLien"=>$nbLien, "nbTag"=>$nbTag, "nbDoc"=>$nbDoc, "matrice"=>$matrice, "indice"=>$indice);
			
		}
		
        return $result; 
    }   
    
}

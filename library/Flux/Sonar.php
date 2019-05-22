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
    		$this->idDocStruc = $this->dbD->ajouter(array("titre"=>'structures','parent'=>$this->idDocRoot));
    		$this->idDocCol = $this->dbD->ajouter(array("titre"=>'Collection IIIF','parent'=>$this->idDocRoot));
    		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
    		$this->idTagRoot = $this->dbT->ajouter(array("code"=>__CLASS__));
    		$this->idTagTypeFlux = $this->dbT->ajouter(array("code"=>'type flux','parent'=>$this->idTagRoot));
            $this->idTagRep = $this->dbT->ajouter(array('code'=>'réponse', 'parent'=>$this->idTagRoot));
            $this->idTagChoixRep = $this->dbT->ajouter(array('code'=>'choix', 'parent'=>$this->idTagRoot));
            $this->idTagNonChoixRep = $this->dbT->ajouter(array('code'=>'non choix', 'parent'=>$this->idTagRoot));
            $this->idTagPosChoixRep = $this->dbT->ajouter(array('code'=>'possibilité de choix', 'parent'=>$this->idTagRoot));
            $this->idTagProc = $this->dbT->ajouter(array('code'=>'processus', 'parent'=>$this->idTagRoot));
            $this->idTagStruc = $this->dbT->ajouter(array('code'=>'structure', 'parent'=>$this->idTagRoot));
                        
    }

    /**
	 * initialise les collections IIIF
     * 
     * @return void
	 */
    function initCollectionIIIF(){
		$this->dbD->ajouter(array('titre'=>'Visages SMEL','url'=>'https://jardindesconnaissances.univ-paris8.fr/smel/omk/iiif/collection/1496','parent'=>$this->idDocCol));
		$this->dbD->ajouter(array('titre'=>'Tout SMEL','url'=>'https://jardindesconnaissances.univ-paris8.fr/smel/omk/iiif/collection/1','parent'=>$this->idDocCol));
		$this->dbD->ajouter(array('titre'=>'Affiche de mai 68','url'=>'https://octaviana.fr/iiif/collection/346','parent'=>$this->idDocCol));
		$this->dbD->ajouter(array('titre'=>'Illustrations de thèse','url'=>'https://jardindesconnaissances.univ-paris8.fr/ADACEMU/omk/iiif/collection/63','parent'=>$this->idDocCol));
		$this->dbD->ajouter(array('titre'=>'Reportages photographiques des présidences de la république française','url'=>'https://jardindesconnaissances.univ-paris8.fr/ValArNum/omks/iiif/collection/151682','parent'=>$this->idDocCol));
        $this->dbD->ajouter(array('titre'=>'Tests locaux','url'=>'../../data/SMEL/iiif-smel1.json','parent'=>$this->idDocCol));

    }

    /**
	 * recupère les collections IIIF
     * 
     * @return array
	 */
    function getCollectionIIIF(){
		return $this->dbD->findByParent($this->idDocCol);
    }

    /**
	 * initialise les structures
     * 
     * @return void
	 */
    function initStructure(){
        $this->dbD->ajouter(array('titre'=>'bon et mauvais','data'=>'bon,mauvais','parent'=>$this->idDocStruc));
        $this->dbD->ajouter(array('titre'=>'IEML : élémentaire','data'=>'vide,actuel,virtuel,signe,être,chose','parent'=>$this->idDocStruc));
        $this->dbD->ajouter(array('titre'=>'Yi Jing : élémentaires','data'=>'Ciel,Tonnerre,Eau,Montagne,Terre,Vent,Feu,Brume','parent'=>$this->idDocStruc));
        $this->dbD->ajouter(array('titre'=>'Yi Jing : hexagrammes','data'=>"le Créatif - le Ciel,le Réceptif,la Difficulté initiale,la Folie juvénile,l'Attente,le Conflit,l'Armée,la Solidarité-  l'Union,le Pouvoir d'apprivoisement du petit,la Marche,la Paix,la Stagnation,la Communauté avec les hommes,le Grand Avoir,l'Humilité,l'Enthousiasme,La Suite,le Travail sur ce qui est corrompu,l'Approche,la Contemplation,Mordre au travers,la Grâce,l'Éclatement,le Retour,l'Innocence,le Pouvoir d'apprivoisement du grand,les Commissures des lèvres,la Prépondérance du grand,l'Insondable,le Feu,l'Influence,la Durée,la Retraite,la Puissance du grand,le Progrès,l'Obscurcissement de la lumière,la Famille,l'Opposition,l'Obstacle,la Libération,la Diminution,l'Augmentation,la Percée,Venir à la rencontre,le Rassemblement,la Poussée vers le haut,l'Accablement,le Puits,la Révolution,le Chaudron,l'Ébranlement,la Montagne,le Développement,l'Épousée,Abondance,le Voyageur,le Doux,le Lac,La Dispersion,la Limitation,la Vérité intérieure,la Prépondérance du Petit,Après l'accomplissement,Avant l'accomplissement",'parent'=>$this->idDocStruc));
        $this->dbD->ajouter(array('titre'=>'易静：小学','data'=>"乾,坤,震,坎,艮,巽,離,兌",'parent'=>$this->idDocStruc));
    }

    /**
	 * recupère les structure
     * 
     * @return array
	 */
    function getStructure(){
		return $this->dbD->findByParent($this->idDocStruc);
    }
    
    /**
	 * récupère la liste des flux
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


    /**
	 * enregistre la position
     * 
     * @param array     $data
     * @param string    $type
     * @param array     $geo
     * @param array     $uti
     * 
     * @return array
	 */
	function savePosi($data, $type, $geo, $uti){

        //enregistre la geo
        if($geo){
            $this->idGeo = $this->dbG->ajouter(array(
                'lat'=>$geo['coords']['latitude'],
                'lng'=>$geo['coords']['longitude'],
                'alt'=>$geo['coords']['altitude'] ? $geo['coords']['altitude'] : 0,
                'pre'=>$geo['coords']['accuracy']
            ));
        }
        
        //enregistrement de la structure
        $titreStruc = "";
        foreach ($data['structure'] as $s) {
            $titreStruc .= $s['t']." - ";
        }
        $titreStruc = substr($titreStruc,0,-3);
        $idStruc = $this->dbD->ajouter(array('titre'=>$titreStruc
            ,'tronc'=>'structure'
            ,'parent'=>$this->idDocStruc));

        //enregistre le document
        switch ($type) {
            case 'IIIF':
                $idDoc = $this->dbD->ajouter(array('titre'=>'image IIIF'
                ,'tronc'=>$type
                ,'parent'=>$this->idDocRoot
                ,'url'=>$data['id']));
                break;
        }

        //enregistre l'évaluation de la structure
        // src = le document
        // dst = la structure
        // pre = l'auteur de l'évaluation
        $idRap = $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
            ,"src_id"=>$idDoc,"src_obj"=>"doc"
            ,"dst_id"=>$idStruc,"dst_obj"=>"doc"
            ,"pre_id"=>$uti['id_uti'],"pre_obj"=>"uti"
            ,"valeur"=>json_encode(array("x"=>$data['x'],"y"=>$data['y'],"numX"=>$data['numX'],"numY"=>$data['numY']
                ,"degrad"=>$data['degrad']
                ,"distance"=>$data['distance']
                ))
        ));

        //enregistre l'évaluation de la structure
        // src = le document
        // dst = le tag de la structure
        // pre = le rapport avec la structure
        foreach ($data['structure'] as $s) {
            //récupère le tag
            $idTag = $this->dbT->ajouter(array('code'=>$s['t'],'parent'=>$this->idTagStruc));

            $this->dbR->ajouter(array("monade_id"=>$this->idMonade,"geo_id"=>$this->idGeo
                ,"src_id"=>$idDoc,"src_obj"=>"doc"
                ,"dst_id"=>$idTag,"dst_obj"=>"tag"
                ,"pre_id"=>$idRap,"pre_obj"=>"rapport"
                ,"niveau"=>floatval($s['p'])
            ));
        }



    }    


    /**
	 * récupère les évaluations
     * 
     * @param string    $type
     * @param string    $id
     * 
     * @return array
	 */
	function getEvals($type, $id){


        //construction de la requête
        switch ($type) {
            case 'IIIF':
                $sql = "SELECT 
                d.doc_id, d.url
                , r.valeur, r.maj
                , u.uti_id, u.login
                ,ds.doc_id idStruc , ds.data struc
                ,g.lat, g.lng, g.alt
            FROM
                flux_doc d
                    INNER JOIN
                flux_rapport r ON r.src_id = d.doc_id
                    AND r.src_obj = 'doc' AND r.pre_obj = 'uti'
                    INNER JOIN
                flux_uti u ON u.uti_id = r.pre_id
                    INNER JOIN
                flux_doc ds ON ds.doc_id = r.dst_id
                    INNER JOIN
                flux_geo g ON g.geo_id = r.geo_id
            WHERE
                d.url = '".$id."'";
                break;
        }

        $rs = $this->dbD->exeQuery($sql);
        return $rs;

    }        

}
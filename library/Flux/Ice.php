<?php
/**
 * FluxIce
 * Classe qui gère calcule pour les Indice d'Existence Informationnelle
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Flux_Ice extends Flux_Site{

	
    /**
     * Constructeur de la classe
     *
     * @param  string   $idBase
     * @param  boolean  $bTrace
     * @param  boolean  $bCache
     * 
     */
	public function __construct($idBase=false, $bTrace=true, $bCache=true)
    {
    		parent::__construct($idBase, $bTrace, $bCache);    	
	    	
    }

     	/**
	 * renvoit les évaluation d'une monade
	 * @param  int $idMonade
	 *
	 */
	function getEvalsMonade($idMonade){
	    
	    $sql = "SELECT 
    r.rapport_id,
    r.maj,
    r.niveau,
    r.valeur,
    u.uti_id,
    u.login,
    t.tag_id,
    t.code,
    d.doc_id,
    d.url,
    d.note,
    d.tronc,
    dp.doc_id,
    dp.note pNote,
    dp.url,
    dgp.doc_id,
    dgp.titre
FROM
    flux_rapport r
        INNER JOIN
    flux_doc d ON d.doc_id = r.src_id
        INNER JOIN
    flux_doc dp ON dp.doc_id = d.parent
        INNER JOIN
    flux_doc dgp ON dgp.doc_id = dp.parent
        INNER JOIN
    flux_uti u ON u.uti_id = r.pre_id
        INNER JOIN
    flux_tag t ON t.tag_id = r.dst_id
WHERE
    r.monade_id = ".$idMonade."
ORDER BY d.tronc
";
	    
	    return $this->dbD->exeQuery($sql);
	    
    }
    
	/**
	 * renvoit les évaluations temporelle d'une monade par Tag
	 * @param  int      $idMonade
     * @param  string   $dateUnit
     * @param  string   $dateType
	 *
	 */
	function getEvalsMonadeHistoByTag($idMonade, $dateUnit, $dateType="dateChoix"){
	    
	    if($dateType=="dateChoix")$colTemps = "r.maj";
	    if($dateType=="dateDoc")$colTemps = "r.maj";
	    
	    
	    $sql = "select
        	SUM(r.niveau) value
        	, GROUP_CONCAT(u.uti_id) utis
        	, t.tag_id 'key', t.code 'type', t.desc , t.type color
        	, GROUP_CONCAT(DISTINCT d.parent) docsP
        	, GROUP_CONCAT(DISTINCT d.doc_id) docs
        	,	DATE_FORMAT(".$colTemps.", '".$dateUnit."') temps
        	,	MIN(UNIX_TIMESTAMP(".$colTemps.")) MinDate
        	,	MAX(UNIX_TIMESTAMP(".$colTemps.")) MaxDate
        	
        	from flux_rapport r
        	inner join flux_doc d on d.doc_id = r.src_id
        	inner join flux_uti u on u.uti_id = r.pre_id
        	inner join flux_tag t on t.tag_id = r.dst_id
        	where r.monade_id = ".$idMonade."
        	GROUP BY t.tag_id, temps
        	ORDER BY temps ";
	    $this->trace($sql);
	    return $this->dbD->exeQuery($sql);
	    
	}

	/**
	 * renvoit les évaluations temporelle d'une monade par Utilisateur
	 * @param  int      $idMonade
	 * @param  string   $dateUnit
	 * @param  string   $dateType
	 * @param  int      $idTag
	 *
	 */
	function getEvalsMonadeHistoByUti($idMonade, $dateUnit, $dateType="dateChoix", $idTag=false){
	    
	    if($dateType=="dateChoix")$colTemps = "r.maj";
	    if($dateType=="dateDoc")$colTemps = "r.maj";
	    
	    
	    $sql = "SELECT
            	SUM(r.niveau) value,
            	GROUP_CONCAT(t.tag_id) tags,
            	u.uti_id 'key',
            	u.login 'type',
            	GROUP_CONCAT(d.doc_id) docs
        	,	DATE_FORMAT(".$colTemps.", '".$dateUnit."') temps
        	,	MIN(UNIX_TIMESTAMP(".$colTemps.")) MinDate
        	,	MAX(UNIX_TIMESTAMP(".$colTemps.")) MaxDate
            	FROM
            	flux_rapport r
            	INNER JOIN
            	flux_doc d ON d.doc_id = r.src_id
            	INNER JOIN
            	flux_uti u ON u.uti_id = r.pre_id
            	INNER JOIN
            	flux_tag t ON t.tag_id = r.dst_id
            	WHERE
            	r.monade_id = ".$idMonade;
	    if($idTag) $sql .= "	AND t.tag_id = ".$idTag;         
         $sql .= "
            	GROUP BY u.uti_id , temps
            	ORDER BY temps";
	    
        $this->trace($sql);
        return $this->dbD->exeQuery($sql);
        
    }

    /**
     * renvoit les évaluations temporelle d'une monade par Document
     * @param  int      $idMonade
     * @param  string   $dateUnit
     * @param  string   $dateType
     *
     */
    function getEvalsMonadeHistoByDoc($idMonade, $dateUnit, $dateType="dateChoix"){
        
        if($dateType=="dateChoix")$colTemps = "r.maj";
        if($dateType=="dateDoc")$colTemps = "r.maj";
        
        
        $sql = "SELECT 
        SUM(r.niveau) value,
        GROUP_CONCAT(t.tag_id) tags,
        CONCAT(dgp.doc_id,'_',dp.doc_id) 'key',
        CONCAT(dgp.titre, ' : ',dp.titre) 'type',
        dp.note,
        GROUP_CONCAT(d.note) evals,
        GROUP_CONCAT(u.uti_id) utis
            ,	DATE_FORMAT(".$colTemps.", '".$dateUnit."') temps
            ,	MIN(UNIX_TIMESTAMP(".$colTemps.")) MinDate
            ,	MAX(UNIX_TIMESTAMP(".$colTemps.")) MaxDate
    FROM
        flux_rapport r
            INNER JOIN
        flux_doc d ON d.doc_id = r.src_id
            INNER JOIN
        flux_doc dp ON dp.doc_id = d.parent
            INNER JOIN
        flux_doc dgp ON dgp.doc_id = dp.parent
            INNER JOIN
        flux_uti u ON u.uti_id = r.pre_id
            INNER JOIN
        flux_tag t ON t.tag_id = r.dst_id
    WHERE
        r.monade_id = ".$idMonade."
    GROUP BY dp.doc_id, temps
    ORDER BY temps";
        
        $this->trace($sql);
        return $this->dbD->exeQuery($sql);
        
    }
        	
    /**
     * calcule la complexité de l'écosystème
     *
     * @param  int         $idDoc
     * @param  int         $idTag
     * @param  int         $idExi
     * @param  int         $idGeo
     * @param  int         $idMonade
     * @param  int         $idRapport
     * @param  boolean     $cache
     *
     * @return array
     *
     */
    function getComplexEcosystem($idDoc=0, $idTag=0, $idExi=0, $idGeo=0, $idMonade=0, $idRapport=0, $cache=true){
        
        $result = false;
        $c = str_replace("::", "_", __METHOD__)."_"
            .$this->idBase.'_'.$idDoc.'_'.$idTag.'_'.$idExi.'_'.$idGeo.'_'.$idMonade.'_'.$idRapport;
        if($cache){
            $result = $this->cache->load($c);
        }
        if(!$result){        	    
            $result = array("idBase"=>$this->idBase,"sumNiv"=>0,"sumEle"=>0,"sumComplex"=>0,"details"=>array());
            //récupère la définition des niches si besoin
            $niches = false;
            if($idDoc){
                $niches = $this->getNicheDoc($idDoc);
                $d = $this->getComplexDoc($idDoc);
                $t = $this->getComplexTag(implode(',',$niches['tag']));
                $p = $this->getComplexActeurPersonne(implode(',',$niches['exi']));
                $g = $this->getComplexActeurGeo(implode(',',$niches['geo']));
                $m = $this->getComplexActeurAlgo(implode(',',$niches['monade']));
                $r = $this->getComplexRapport(implode(',',$niches['rapport']));
            } elseif ($idTag){
                $niches = $this->getNicheTag($idTag);
                $d = $this->getComplexDoc(implode(',',$niches['doc']));
                $t = $this->getComplexTag($idTag);
                $p = $this->getComplexActeurPersonne(implode(',',$niches['exi']));
                $g = $this->getComplexActeurGeo(implode(',',$niches['geo']));
                $m = $this->getComplexActeurAlgo(implode(',',$niches['monade']));
                $r = $this->getComplexRapport(implode(',',$niches['rapport']));        	        
            } elseif ($idExi){
                $niches = $this->getNicheExi($idExi);
                $d = $this->getComplexDoc(implode(',',$niches['doc']));
                $t = $this->getComplexTag(implode(',',$niches['tag']));
                $p = $this->getComplexActeurPersonne($idExi);
                $g = $this->getComplexActeurGeo(implode(',',$niches['geo']));
                $m = $this->getComplexActeurAlgo(implode(',',$niches['monade']));
                $r = $this->getComplexRapport(implode(',',$niches['rapport']));
            } elseif ($idGeo){
                $niches = $this->getNicheGeo($idGeo);
                $d = $this->getComplexDoc(implode(',',$niches['doc']));
                $t = $this->getComplexTag(implode(',',$niches['tag']));
                $p = $this->getComplexActeurPersonne(implode(',',$niches['exi']));
                $g = $this->getComplexActeurGeo($idGeo);
                $m = $this->getComplexActeurAlgo(implode(',',$niches['monade']));
                $r = $this->getComplexRapport(implode(',',$niches['rapport']));        	        
            } elseif ($idMonade){
                $niches = $this->getNicheMonade($idMonade);
                $d = $this->getComplexDoc(implode(',',$niches['doc']));
                $t = $this->getComplexTag(implode(',',$niches['tag']));
                $p = $this->getComplexActeurPersonne(implode(',',$niches['exi']));
                $g = $this->getComplexActeurGeo(implode(',',$niches['geo']));
                $m = $this->getComplexActeurAlgo($idMonade);
                $r = $this->getComplexRapport(implode(',',$niches['rapport']));
            } elseif ($idRapport){
                $niches = $this->getNicheRapport($idRapport);
                $d = $this->getComplexDoc(implode(',',$niches['doc']));
                $t = $this->getComplexTag(implode(',',$niches['tag']));
                $p = $this->getComplexActeurPersonne(implode(',',$niches['exi']));
                $g = $this->getComplexActeurGeo(implode(',',$niches['geo']));
                $m = $this->getComplexActeurAlgo(implode(',',$niches['monade']));
                $r = $this->getComplexRapport($idRapport);
            }else{
                $d = $this->getComplexDoc();
                $t = $this->getComplexTag();
                $p = $this->getComplexActeurPersonne();
                $g = $this->getComplexActeurGeo();
                $m = $this->getComplexActeurAlgo();
                $r = $this->getComplexRapport();        	        
            }
            
            $result["sumNiv"]=$d["numNiv"]+$t["numNiv"]+$p["numNiv"]+$g["numNiv"]+$m["numNiv"]+$r["numNiv"];
            $result["sumEle"]=$d["sumNb"]+$t["sumNb"]+$p["sumNb"]+$g["sumNb"]+$m["sumNb"]+$r["sumNb"];
            $result["sumComplex"]=$d["sumComplex"]+$t["sumComplex"]+$p["sumComplex"]+$g["sumComplex"]+$m["sumComplex"]+$r["sumComplex"];
            $result["details"]=array($d,$t,$p,$g,$m,$r);
            
            $this->cache->save($result, $c);
            
        }
        
        return $result;
        
    }

    /**
     * calcule la niche
     *
     * @param  int         $idDoc
     * @param  string      $type
     * @param  array       $arr
     * @param  array       $result
     *
     * @return array
     *
     */
    function getNiche($id, $type, $arr){
        
        //calcul les regroupements
        $result = array("doc"=>array(),"tag"=>array(),"exi"=>array(),"geo"=>array(),"monade"=>array(),"rapport"=>array());
        //récupère les identifiants uniques pour chaque objet
        foreach ($arr as $r) {
            $result[$r["sdo"]][$r["sdid"]] = 1;
            $result[$r["spo"]][$r["spid"]] = 1;
            $result[$r["dso"]][$r["dsid"]] = 1;
            $result[$r["dpo"]][$r["dpid"]] = 1;
            $result[$r["pso"]][$r["psid"]] = 1;
            $result[$r["pdo"]][$r["pdid"]] = 1;
            $result["rapport"][$r["sid"]] = 1;
            $result["rapport"][$r["did"]] = 1;
            $result["rapport"][$r["pid"]] = 1;
            $result["monade"][$r["sm"]] = 1;
            $result["monade"][$r["dm"]] = 1;
            $result["monade"][$r["pm"]] = 1;
        }
        //construction du tableau des objets
        $rs = array("type"=>$type,"id"=>$id
            ,"doc"=>array(),"tag"=>array(),"exi"=>array(),"geo"=>array(),"monade"=>array(),"rapport"=>array()
            ,"details"=>$arr);
        foreach ($result as $k => $vs){
            if($k){
                if(!count($vs))$rs[$k][]=-1;
                foreach ($vs as $v=>$n) {
                    if($v) $rs[$k][]=$v;
                }
            }
        }
        
        return $rs;
        
    }
        
    /**
     * calcule la niche pour 1 document
     *
     * @param  int         $idDoc
     *
     * @return array
     *
     */
    function getNicheDoc($idDoc){
        
        $sql = "	SELECT
                de.doc_id idE,
            de.titre titreE,
            de.niveau-d.niveau+1 niveauE,
            rS.rapport_id sid,
            rS.monade_id sm,
            rS.dst_id sdid,
            rS.dst_obj sdo,
            rS.pre_id spid,
            rS.pre_obj spo,
            rD.rapport_id did,
            rD.monade_id dm,
            rD.src_id dsid,
            rD.src_obj dso,
            rD.pre_id dpid,
            rD.pre_obj dpo,
            rP.rapport_id pid,
            rP.monade_id pm,
            rP.dst_id pdid,
            rP.dst_obj pdo,
            rP.src_id psid,
            rP.src_obj pso
            FROM flux_doc d
            INNER JOIN flux_doc de ON de.lft BETWEEN d.lft AND d.rgt
            LEFT JOIN
                flux_rapport rS ON rS.src_id = de.doc_id
                AND rS.src_obj = 'doc'
            LEFT JOIN
                flux_rapport rD ON rD.dst_id = de.doc_id
                AND rD.dst_obj = 'doc'
            LEFT JOIN
                flux_rapport rP ON rP.pre_id = de.doc_id
                AND rP.pre_obj = 'doc'
            WHERE
            d.doc_id = ".$idDoc."
            ORDER BY de.lft";
        $this->trace($sql);
        $arr = $this->dbD->exeQuery($sql);
        
        return $this->getNiche($idDoc, "document", $arr);
        
    }
    
    /**
     * calcule la niche pour 1 tag
     *
     * @param  int         $idTag
     *
     * @return array
     *
     */
    function getNicheTag($idTag){
        
        $sql = "	SELECT 
            te.tag_id idE,
            te.code titreE,
            te.niveau-t.niveau+1 niveauE,
            rS.rapport_id sid,
            rS.monade_id sm,
            rS.dst_id sdid,
            rS.dst_obj sdo,
            rS.pre_id spid,
            rS.pre_obj spo,
            rD.rapport_id did,
            rD.monade_id dm,
            rD.src_id dsid,
            rD.src_obj dso,
            rD.pre_id dpid,
            rD.pre_obj dpo,
            rP.rapport_id pid,
            rP.monade_id pm,
            rP.dst_id pdid,
            rP.dst_obj pdo,
            rP.src_id psid,
            rP.src_obj pso    
        FROM
            flux_tag t
                INNER JOIN
            flux_tag te ON te.lft BETWEEN t.lft AND t.rgt
                LEFT JOIN
            flux_rapport rS ON rS.src_id = te.tag_id
                AND rS.src_obj = 'tag'
                LEFT JOIN
            flux_rapport rD ON rD.dst_id = te.tag_id
                AND rD.dst_obj = 'tag'
                LEFT JOIN
            flux_rapport rP ON rP.pre_id = te.tag_id
                AND rP.pre_obj = 'tag'
        WHERE
            t.tag_id = ".$idTag."
        ORDER BY te.lft";
        $this->trace($sql);
        $arr = $this->dbD->exeQuery($sql);
        
        return $this->getNiche($idTag, "tag", $arr);

    }
    
    /**
     * calcule la niche pour 1 exi
     *
     * @param  int         $idExi
     *
     * @return array
     *
     */
    function getNicheExi($idExi){
        
        $sql = "SELECT 
            ee.exi_id idE,
            CONCAT(ee.prenom, ' ', ee.nom) titreE,
            ee.niveau - e.niveau + 1 niveauE,
            rS.rapport_id sid,
            rS.monade_id sm,
            rS.dst_id sdid,
            rS.dst_obj sdo,
            rS.pre_id spid,
            rS.pre_obj spo,
            rD.rapport_id did,
            rD.monade_id dm,
            rD.src_id dsid,
            rD.src_obj dso,
            rD.pre_id dpid,
            rD.pre_obj dpo,
            rP.rapport_id pid,
            rP.monade_id pm,
            rP.dst_id pdid,
            rP.dst_obj pdo,
            rP.src_id psid,
            rP.src_obj pso
        FROM
            flux_exi e
                INNER JOIN
            flux_exi ee ON ee.lft BETWEEN e.lft AND e.rgt
                LEFT JOIN
            flux_rapport rS ON rS.src_id = ee.exi_id
                AND rS.src_obj = 'exi'
                LEFT JOIN
            flux_rapport rD ON rD.dst_id = ee.exi_id
                AND rD.dst_obj = 'exi'
                LEFT JOIN
            flux_rapport rP ON rP.pre_id = ee.exi_id
                AND rP.pre_obj = 'exi'
        WHERE
            e.exi_id = ".$idExi."
        ORDER BY ee.lft";
        $this->trace($sql);
        $arr = $this->dbD->exeQuery($sql);
        
        return $this->getNiche($idExi, "acteur-personne", $arr);
        
    }
                
    /**
     * calcule la niche pour 1 geo
     *
     * @param  int         $idGeo
     *
     * @return array
     *
     */
    function getNicheGeo($idGeo){
        
        $sql = "SELECT 
            g.geo_id idE,
            g.adresse titreE,
            1 niveauE,
            rS.rapport_id sid,
            rS.monade_id sm,
            rS.dst_id sdid,
            rS.dst_obj sdo,
            rS.pre_id spid,
            rS.pre_obj spo,
            rD.rapport_id did,
            rD.monade_id dm,
            rD.src_id dsid,
            rD.src_obj dso,
            rD.pre_id dpid,
            rD.pre_obj dpo,
            rP.rapport_id pid,
            rP.monade_id pm,
            rP.dst_id pdid,
            rP.dst_obj pdo,
            rP.src_id psid,
            rP.src_obj pso
        FROM
            flux_geo g
                LEFT JOIN
            flux_rapport rS ON rS.src_id = g.geo_id
                AND rS.src_obj = 'geo'
                LEFT JOIN
            flux_rapport rD ON rD.dst_id = g.geo_id
                AND rD.dst_obj = 'geo'
                LEFT JOIN
            flux_rapport rP ON rP.pre_id = g.geo_id
                AND rP.pre_obj = 'geo'
        WHERE
            g.geo_id = ".$idGeo."
        ORDER BY g.geo_id";
        $this->trace($sql);
        $arr = $this->dbD->exeQuery($sql);
        
        return $this->getNiche($idGeo, "acteur-geo", $arr);
        
    }
    
    /**
     * calcule la niche pour 1 monade
     *
     * @param  int         $idMonade
     *
     * @return array
     *
     */
    function getNicheMonade($idMonade){
        
        $sql = "SELECT 
            m.monade_id idE,
            m.titre titreE,
            1 niveauE,
            rS.rapport_id sid,
            rS.monade_id sm,
            rS.dst_id sdid,
            rS.dst_obj sdo,
            rS.pre_id spid,
            rS.pre_obj spo,
            rD.rapport_id did,
            rD.monade_id dm,
            rD.src_id dsid,
            rD.src_obj dso,
            rD.pre_id dpid,
            rD.pre_obj dpo,
            rP.rapport_id pid,
            rP.monade_id pm,
            rP.dst_id pdid,
            rP.dst_obj pdo,
            rP.src_id psid,
            rP.src_obj pso
        FROM
            flux_monade m
                LEFT JOIN
            flux_rapport rS ON (rS.src_id = m.monade_id
                AND rS.src_obj = 'monade') OR rS.monade_id = m.monade_id
                LEFT JOIN
            flux_rapport rD ON (rD.dst_id = m.monade_id
                AND rD.dst_obj = 'monade') OR rS.monade_id = m.monade_id
                LEFT JOIN
            flux_rapport rP ON (rP.pre_id = m.monade_id
                AND rP.pre_obj = 'monade') OR rS.monade_id = m.monade_id
        WHERE
            m.monade_id = ".$idMonade."
        ORDER BY m.monade_id";
        $this->trace($sql);
        $arr = $this->dbD->exeQuery($sql);
        
        return $this->getNiche($idMonade, "acteur-algo", $arr);
        
    }

    /**
     * calcule la niche pour 1 rapport
     *
     * @param  int         $idGeo
     *
     * @return array
     *
     */
    function getNicheRapport($idRapport){
        
        $sql = "SELECT 
            r.rapport_id idE,
            CONCAT(r.src_obj,
                    '-',
                    r.dst_obj,
                    '-',
                    r.pre_obj) titreE,
            1 niveauE,
            rS.rapport_id sid,
            rS.monade_id sm,
            rS.dst_id sdid,
            rS.dst_obj sdo,
            rS.pre_id spid,
            rS.pre_obj spo,
            rD.rapport_id did,
            rD.monade_id dm,
            rD.src_id dsid,
            rD.src_obj dso,
            rD.pre_id dpid,
            rD.pre_obj dpo,
            rP.rapport_id pid,
            rP.monade_id pm,
            rP.dst_id pdid,
            rP.dst_obj pdo,
            rP.src_id psid,
            rP.src_obj pso
        FROM
            flux_rapport r
                LEFT JOIN
            flux_rapport rS ON (rS.src_id = r.rapport_id AND rS.src_obj = 'rapport') OR rS.rapport_id = r.rapport_id
                LEFT JOIN
            flux_rapport rD ON (rD.dst_id = r.rapport_id AND rD.dst_obj = 'rapport') OR rD.rapport_id = r.rapport_id
                LEFT JOIN
            flux_rapport rP ON (rP.pre_id = r.rapport_id AND rP.pre_obj = 'rapport') OR rP.rapport_id = r.rapport_id
        WHERE
            r.rapport_id  = ".$idRapport."
        ORDER BY r.rapport_id";
        $this->trace($sql);
        $arr = $this->dbD->exeQuery($sql);
        
        return $this->getNiche($idRapport, "rapport", $arr);
        
    }
    
    /**
     * calcule la complexité des documents
     *
     * @param  string          $ids
     *
     * @return array
     *
     */
    function getComplexDoc($ids=""){
        
        $result = array("idBase"=>$this->idBase,"type"=>"document","ids"=>$ids,"sumNb"=>0,"numNiv"=>0,"sumNiv"=>0,"sumComplex"=>0,"details"=>array());
        if($ids == "-1") return $result;
        
        if ($ids) $w = " WHERE d.doc_id IN (".$ids.") ";
        else $w = "";
        
        $sql = "SELECT
            COUNT(DISTINCT de.doc_id) nb,
                de.niveau + 1 - d.niveau niv,
                COUNT(DISTINCT de.doc_id) * (de.niveau + 1 - d.niveau) complexite
        FROM
            flux_doc d
        INNER JOIN flux_doc de ON de.lft BETWEEN d.lft AND d.rgt
        ".$w."
        GROUP BY de.niveau + 1 - d.niveau";
        $this->trace($sql);
        $arr = $this->dbD->exeQuery($sql);
        
        //calcul les sommes
        foreach ($arr as $r) {
            $result["sumNb"] += $r["nb"];
            $result["sumNiv"] += $r["niv"];
            $result["sumComplex"] += $r["complexite"];
            if($result["numNiv"] < $r["niv"]) $result["numNiv"]=$r["niv"];
            $result["details"][] = $r;
        }
        
        return $result;
    }
    
    /**
     * calcule la complexité des tags
     *
     * @param  string          $ids
     *
     * @return array
     *
     */
    function getComplexTag($ids=""){        	    
        
        $result = array("idBase"=>$this->idBase,"type"=>"concept","ids"=>$ids,"sumNb"=>0,"numNiv"=>0,"sumNiv"=>0,"sumComplex"=>0,"details"=>array());
        if($ids == "-1") return $result;
        
        if ($ids) $w = " WHERE t.tag_id IN (".$ids.") ";        	    
        else $w = "";
        
        $sql = "SELECT 
            count(distinct te.tag_id) nb,
            te.niveau + 1 - t.niveau niv,
            count(distinct te.tag_id)*(te.niveau + 1 - t.niveau) complexite
        
        FROM
            flux_tag t
                INNER JOIN
            flux_tag te ON te.lft between t.lft and t.rgt
        ".$w."
        group by te.niveau + 1 - t.niveau";
        $this->trace($sql);
        $arr = $this->dbD->exeQuery($sql);
        
        //calcul les sommes
        foreach ($arr as $r) {
            $result["sumNb"] += $r["nb"];
            $result["sumNiv"] += $r["niv"];
            $result["sumComplex"] += $r["complexite"];
            if($result["numNiv"] < $r["niv"]) $result["numNiv"]=$r["niv"];
            $result["details"][] = $r;
        }
        
        return $result;
    }
    
    /**
     * calcule la complexité des acteurs - personne = exi
     *
     * @param  string          $ids
     *
     * @return array
     *
     */
    function getComplexActeurPersonne($ids=""){        	    
        
        $result = array("idBase"=>$this->idBase,"type"=>"acteur-personne","ids"=>$ids,"sumNb"=>0,"numNiv"=>0,"sumNiv"=>0,"sumComplex"=>0,"details"=>array());
        if($ids == "-1") return $result;
        
        if ($ids) $w = " WHERE e.exi_id IN (".$ids.") ";
        else $w = "";
        //ATTENTION les exi anciens sont mal géré au niveau lft rgt
        $sql = "SELECT 
            count(distinct ee.exi_id) nb,
            ee.niveau + 1 - e.niveau niv,
            count(distinct ee.exi_id)*(ee.niveau + 1 - e.niveau) complexite
        
        FROM
            flux_exi e
                INNER JOIN
            -- flux_exi ee ON ee.lft BETWEEN e.lft AND e.rgt
            flux_exi ee ON ee.exi_id = e.exi_id
        ".$w."
            group by ee.niveau + 1 - e.niveau";
        $this->trace($sql);
        $arr = $this->dbD->exeQuery($sql);
        
        //calcul les sommes
        foreach ($arr as $r) {
            $result["sumNb"] += $r["nb"];
            $result["sumNiv"] += $r["niv"];
            $result["sumComplex"] += $r["complexite"];
            if($result["numNiv"] < $r["niv"]) $result["numNiv"]=$r["niv"];
            $result["details"][] = $r;
        }
        
        return $result;
    }
    
    /**
     * calcule la complexité des acteurs - geo
     *
     * @param  string          $ids
     *
     * @return array
     *
     */
    function getComplexActeurGeo($ids=""){        	    
        
        $result = array("idBase"=>$this->idBase,"type"=>"acteur-geo","ids"=>$ids,"sumNb"=>0,"numNiv"=>0,"sumNiv"=>0,"sumComplex"=>0,"details"=>array());
        if($ids == "-1") return $result;
        
        if ($ids) $w = " WHERE g.geo_id IN (".$ids.") ";
        else $w = "";
        
        $sql = "SELECT 
            count(distinct g.geo_id) nb,
                1 niv,
            count(distinct g.geo_id)*1 complexite
        
        FROM
            flux_geo g
        ".$w." ";
        $this->trace($sql);
        $arr = $this->dbD->exeQuery($sql);
        
        //calcul les sommes
        foreach ($arr as $r) {
            $result["sumNb"] += $r["nb"];
            $result["sumNiv"] += $r["niv"];
            $result["sumComplex"] += $r["complexite"];
            if($result["numNiv"] < $r["niv"]) $result["numNiv"]=$r["niv"];
            $result["details"][] = $r;
        }
        
        return $result;
    }
    
    /**
     * calcule la complexité des acteurs - algo
     *
     * @param  string          $ids
     *
     * @return array
     *
     */
    function getComplexActeurAlgo($ids=""){        	    
        
        $result = array("idBase"=>$this->idBase,"type"=>"acteur-algo","ids"=>$ids,"sumNb"=>0,"numNiv"=>0,"sumNiv"=>0,"sumComplex"=>0,"details"=>array()
            ,"sumNbRapport"=>0,"sumSrc"=>0,"sumPre"=>0,"sumDst"=>0,"sumComplexRapport"=>0
        );
        if($ids == "-1") return $result;
        
        if ($ids) $w = " WHERE m.monade_id IN (".$ids.") ";
        else $w = "";
        
        $sql = "SELECT 
            COUNT(DISTINCT m.monade_id) nb,
            1 niv,
            COUNT(DISTINCT m.monade_id) * 1 complexite,
            CONCAT(r.src_obj,
                    '-',
                    r.dst_obj,
                    '-',
                    r.pre_obj) obj,
            COUNT(DISTINCT r.rapport_id) nbRapport,
            COUNT(DISTINCT r.src_id) nbSrc,
            COUNT(DISTINCT r.dst_id) nbDst,
            COUNT(DISTINCT r.pre_id) nbPre,
            COUNT(DISTINCT r.rapport_id) + COUNT(DISTINCT r.src_id) + COUNT(DISTINCT r.dst_id) + COUNT(DISTINCT r.pre_id) complexiteRapport
        FROM
            flux_monade m
                INNER JOIN
            flux_rapport r ON r.monade_id = m.monade_id
        ".$w." 
            GROUP BY m.monade_id , CONCAT(r.src_obj,
                '-',
                r.dst_obj,
                '-',
                r.pre_obj)";
        $this->trace($sql);
        $arr = $this->dbD->exeQuery($sql);
        
        //calcul les sommes
        foreach ($arr as $r) {
            $result["sumNb"] += $r["nb"];
            $result["sumNiv"] += $r["niv"];
            $result["sumComplex"] += $r["complexite"];
            if($result["numNiv"] < $r["niv"]) $result["numNiv"]=$r["niv"];        	        
            $result["sumNbRapport"] += $r["nbRapport"];
            $result["sumSrc"] += $r["nbSrc"];
            $result["sumDst"] += $r["nbDst"];
            $result["sumPre"] += $r["nbPre"];
            $result["sumComplexRapport"] += $r["complexiteRapport"];
            $obj = explode("-", $r["obj"]);
            $r["typeSrc"]=$obj[0];
            $r["typeDst"]=$obj[1];
            $r["typePre"]=$obj[2];        	        
            $result["details"][] = $r;
        }
        
        return $result;
    }
    /**
     * calcule la complexité des rapport
     *
     * @param  string          $ids
     *
     * @return array
     *
     */
    function getComplexRapport($ids=""){        	    
        
        $result = array("idBase"=>$this->idBase,"type"=>"rapport","ids"=>$ids,"sumNb"=>0,"sumSrc"=>0,"sumPre"=>0,"sumDst"=>0,"sumComplex"=>0,"details"=>array());
        if($ids == "-1") return $result;
        
        if ($ids) $w = " WHERE r.rapport_id IN (".$ids.") ";
        else $w = "";
        
        $sql = "SELECT 
            CONCAT(r.src_obj,
                    '-',
                    r.dst_obj,
                    '-',
                    r.pre_obj) obj,
            COUNT(DISTINCT r.rapport_id) nbRapport,
            COUNT(DISTINCT r.src_id) nbSrc,
            COUNT(DISTINCT r.dst_id) nbDst,
            COUNT(DISTINCT r.pre_id) nbPre,
            COUNT(DISTINCT r.rapport_id)+COUNT(DISTINCT r.src_id)+COUNT(DISTINCT r.dst_id)+COUNT(DISTINCT r.pre_id) complexite
        FROM
            flux_rapport r
        ".$w."
        GROUP BY CONCAT(r.src_obj,
                '-',
                r.dst_obj,
                '-',
                r.pre_obj)";
        $this->trace($sql);
        $arr = $this->dbD->exeQuery($sql);
        
        //calcul les sommes
        foreach ($arr as $r) {
            $result["sumNb"] += $r["nbRapport"];
            $result["sumSrc"] += $r["nbSrc"];
            $result["sumDst"] += $r["nbDst"];
            $result["sumPre"] += $r["nbPre"];
            $result["sumComplex"] += $r["complexite"];
            $obj = explode("-", $r["obj"]);
            $r["typeSrc"]=$obj[0];
            $r["typeDst"]=$obj[1];
            $r["typePre"]=$obj[2];
            
            $result["details"][] = $r;        	        
        }
        $result["numNiv"]=count($arr);
        
        return $result;
    }
}
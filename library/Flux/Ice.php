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
	public function __construct($idBase=false, $bTrace=false, $bCache=true)
    {
    		parent::__construct($idBase, $bTrace, $bCache);    	

    		//on récupère la racine des documents
    		$this->initDbTables();
    		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
    		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
    		$this->idTagRoot = $this->dbT->ajouter(array("code"=>__CLASS__));
    		$this->idTagRela = $this->dbT->ajouter(array("code"=>'type relation','parent'=>$this->idTagRoot));
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
	function getListeForm(){
        return $this->dbD->findByTronc('formSem');
    }

    /**
	 * récupère les données d'un formulaire
     * 
	 * @param  array    $idForm
	 * @param  boolean  $reponse
     * 
     * @return array
	 */
	function getForm($idForm, $reponse=false){
        $arrF = $this->dbD->findBydoc_id($idForm);
        $f= json_decode($arrF['note']);
        $f->questions = array();        
        $rs = array('forms'=>[]);
        $rs['forms'][0]=$f;
        $arrQ = $this->dbD->findByParent($idForm);
        foreach ($arrQ as $q) {
            $objQ = json_decode($q['note']);
            $objQ->propositions = $this->getQuestProps($q['doc_id']); 
            $rs['forms'][0]->questions[] = $objQ;
        }
        if($reponse){
            $rs['reponses']=[];
            $arrR = $this->getFormReponse($idForm);
            foreach ($arrR as $r) {
                $rs['reponses'][] = json_decode($r['valeur']);
            }            
        }

        return $rs;
    }

    /**
	 * récupère les données d'une question
     * 
	 * @param  array    $idQ
     * 
     * @return array
	 */
	function getQuest($idQ){
        $arr = $this->dbD->findBydoc_id($idQ);
        $q= json_decode($arr['note']);
        $q->propositions = $this->getQuestProps($idQ);        
        return $q;
    }

    /**
	 * récupère les propositions d'une question
     * 
	 * @param  array    $idQ
     * 
     * @return array
	 */
	function getQuestProps($idQ){
        $sql = "SELECT 
        p.doc_id, p.note, l.valeur
    FROM
        flux_doc q
            INNER JOIN
        flux_doc p ON p.parent = q.doc_id
            LEFT JOIN
        flux_rapport l ON l.src_id = p.doc_id
            AND l.src_obj = 'doc'
    WHERE
        q.doc_id = ".$idQ;
        $arr = $this->dbD->exeQuery($sql);
        $oIdP = 0;
        $rs = array();
        foreach ($arr as $p) {
            if($p['doc_id']!=$oIdP){
                if($oIdP>0)$rs[]=$pq;
                $pq = json_decode($p['note']);
                $pq->liens = array();    
                $oIdP=$p['doc_id'];    
            }
            $pq->liens[]=json_decode($p['valeur']);      
        }
        if($pq)$rs[]=$pq;
        return $rs;
    }

    /**
	 * récupère les données d'une proposition
     * 
	 * @param  array    $idP
     * 
     * @return array
	 */
	function getProp($idP){
        $arr = $this->dbD->findBydoc_id($idP);
        $p= json_decode($arr['note']);
        $p->liens = array();        
        $arrL = $this->dbR->findBySource($idQ, 'doc');
        foreach ($arrl as $l) {           
            $p->liens[] = json_decode($l['valeur']);
        }
        return $p;
    }

    /**
	 * récupère les réponse pour un formulaire
     * 
	 * @param  array    $idForm
     * 
     * @return array
	 */
	function getFormReponse($idForm){
        $sql = "SELECT 
                rf.*
            FROM
                flux_doc df
                    INNER JOIN
                flux_rapport rf ON rf.src_id = df.doc_id
                    AND rf.src_obj = 'doc'
                    AND rf.dst_obj = 'tag'
                    AND rf.dst_id = 3
                    AND rf.pre_obj = 'uti'
            WHERE
                df.doc_id =".$idForm;
        return $this->dbD->exeQuery($sql);
    }

    /**
	 * supprime les informations d'un formulaire sémantique
	 * @param  int $idForm
     * 
     * @return array
	 */
	function deleteFormSem($idForm){

        //supprime toute les questions
        $nbSup = $this->dbD->remove($idForm);
        
        return $nbSup;
    }

    /**
	 * supprime les informations d'une question
	 * @param  int $idQuest
     * 
     * @return array
	 */
	function deleteQuestSem($idQuest){

        //supprime toute les questions
        $nbSup = $this->dbD->remove($idQuest);
        
        return $nbSup;
    }

    /**
	 * supprime les informations d'une proposition
	 * @param  int $idProp
     * 
     * @return array
	 */
	function deletePropSem($idProp){

        //supprime toute les questions
        $nbSup = $this->dbD->remove($idProp);
        
        return $nbSup;
    }

    /**
	 * enregistre les informations d'un formulaire sémantique
	 * @param  array    $params
	 * @param  boolean  $update
     * 
     * @return array
	 */
	function sauveFormSem($params, $update=false){
        if($params['idForm']){
            $idForm = $params['idForm'];
        }elseif ($update) {
            foreach ($params as $p) {
                $rsForm = $this->dbD->findBydoc_id($p['recid']);
                if($rsForm){
                    $note = json_decode($rsForm['note'],JSON_OBJECT_AS_ARRAY);
                    foreach ($p as $k => $v) {
                        $note[$k]=$v;
                    }
                    if(!$p['txtForm'])$note['txtForm']=$rsForm['titre'];
                    $this->dbD->edit($p['recid'],array('titre'=>$note['txtForm'],'note'=>json_encode($note)));    
                }
            }
            return;
        }else{
            //enregistre le formulaire
            $idForm = $this->dbD->ajouter(array('titre'=>$params['txtForm'],'parent'=>$this->idDocRoot
                ,'tronc'=>'formSem'),false);                
            //enregistre le json avec l'identifiant;
            $params['idForm']=$idForm;
            $params['recid']=$idForm;
        }
        $this->dbD->edit($idForm,array('titre'=>$params['txtForm'],'note'=>json_encode($params)));

        return $idForm;
    }

    /**
	 * enregistre les informations d'une question d'un formulaire sémantique
	 * @param  array $q
	 * @param  boolean  $update
     * 
     * @return array
	 */
	function sauveQuestionFormSem($q, $update=false){
        if($q['idQ']){
            $idQ = $q['idQ'];
        }elseif ($update) {
            foreach ($q as $p) {
                $rsQuest = $this->dbD->findBydoc_id($p['recid']);
                if($rsQuest){
                    $note = json_decode($rsQuest['note'],JSON_OBJECT_AS_ARRAY);
                    foreach ($p as $k => $v) {
                        $note[$k]=$v;
                    }
                    if(!$p['txtQ'])$note['txtQ']=$rsQuest['titre'];
                    $this->dbD->edit($p['recid'],array('titre'=>$note['txtQ'],'note'=>json_encode($note)));    
                }
            }
            return;
        }else{
            //enregistre la question
            $idQ = $this->dbD->ajouter(array('titre'=>$q['txtQ'],'parent'=>$q['idForm']
            ,'tronc'=>'formSemQuest'),false);
            //enregistre le json avec l'identifiant;
            $q['idQ']=$idQ;
            $q['recid']=$idQ;
        }
        $this->dbD->edit($idQ,array('titre'=>$q['txtQ'],'note'=>json_encode($q)));
        return $idQ;
    }

    /**
	 * enregistre les liens entre réponses d'une question d'un formulaire sémantique
	 * @param  array $l
     * 
     * @return array
	 */
	function sauveLiensFormSem($l){
        if($l['idL']){
            $idRapport = $l['idL'];
        }else{            
            //récupèretation du mot clef
            $idTag = $this->dbT->ajouter(array('code'=>$l['reltype'], 'parent'=>$this->idTagRela));
            //création du rapport
            $idRapport = $this->dbR->ajouter(array('monade_id'=>$this->idMonade,'geo_id'=>$this->idGeo
                ,"src_id"=>$l['idPsource'],"src_obj"=>"doc"
                ,"dst_id"=>$l['idPtarget'],"dst_obj"=>"doc"
                ,"pre_id"=>$idTag,"pre_obj"=>"tag"
                ));
            $l['idL']=$idRapport;
        }
        $this->dbR->edit($idRapport,array("valeur"=>json_encode($l)));
        return $idRapport;
    }

    /**
	 * enregistre les informations d'une proposition à une question d'un formulaire sémantique
	 * @param  array $r
	 * @param  boolean $rs
	 * @param  boolean  $update
     * 
     * @return array
	 */
	function sauvePropositionFormSem($r,$rs=false,$update=false){
        if($r['idP']){
            $idDoc = $r['idP'];
            $r['recid']=$idDoc;
        }elseif ($update) {
            foreach ($r as $p) {
                $rsProp = $this->dbD->findBydoc_id($p['recid']);
                if($rsProp){
                    $note = json_decode($rsProp['note'],JSON_OBJECT_AS_ARRAY);
                    foreach ($p as $k => $v) {
                        $note[$k]=$v;
                    }
                    if(!$p['iemlR'])$note['iemlR']=$rsProp['titre'];
                    $this->dbD->edit($p['recid'],array('titre'=>$note['iemlR'],'note'=>json_encode($note)));    
                }
            }
            return;
        }else{
            //création du document
            $idDoc = $this->dbD->ajouter(array('titre'=>$r['iemlR'],'parent'=>$r['idQ']
                ,'tronc'=>'formSemProp_'.$r['iemlRelaType'],'note'=>json_encode($r)));
            //mise à jour des références
            $r['idP']=$idDoc;
            $r['recid']=$idDoc;
        }
        $r['idParent']=$r['idQ'];
        $this->dbD->edit($idDoc,array('note'=>json_encode($r)));

        if($rs)
            return $r;
        else
            return $idDoc;
    }

    /**
	 * enregistre les informations d'une réponse à une question d'un formulaire sémantique
	 * @param  array $r
     * 
     * @return array
	 */
	function sauveReponseFormSem($r){
        if($r['idR']){
            $idRapport = $r['idR'];
        }else{
            //enregistre la reponse
            if($r['lat']){
                $arr = array('lat'=>$r['lat'],'lng'=>$r['lng'],'pre'=>$r['pre'],'maj'=>$r['t']);
                $this->idGeo = $this->dbG->ajouter($arr);    
            }
            //création du rapport
            $idRapport = $this->dbR->ajouter(array('monade_id'=>$this->idMonade,'geo_id'=>$this->idGeo
                ,"src_id"=>$r['idForm'],"src_obj"=>"doc"
                ,"dst_id"=>$this->idTagRep,"dst_obj"=>"tag"
                ,"pre_id"=>$r['idUti'],"pre_obj"=>"uti"
                ));
            $r['idR']=$idRapport;
        }
        $this->dbR->edit($idRapport,array("valeur"=>json_encode($r)));
    
        return $idRapport;
    }

    /**
	 * enregistre les informations d'une réponse à une question d'un formulaire sémantique
	 * @param  array $c
	 * @param  boolean $pc
     * 
     * @return array
	 */
	function sauveChoixReponseFormSem($c, $pc=false){
        if($c['idC']){
            $idRapport = $c['idC'];
        }else{
            $idTag = $pc ? $this->idTagPosChoixRep : $this->idTagChoixRep;
            //création du rapport
            $idRapport = $this->dbR->ajouter(array('monade_id'=>$this->idMonade,'geo_id'=>$this->idGeo
                ,"src_id"=>$c['idR'],"src_obj"=>"rapport"
                ,"dst_id"=>$c['idP'],"dst_obj"=>"doc"
                ,"pre_id"=>$idTag,"pre_obj"=>"tag"
                ));
                $c['idC']=$idRapport;
            }
        $this->dbR->edit($idRapport,array("valeur"=>json_encode($c)));

        return $idRapport;
    }

    /**
	 * enregistre les informations d'un processus de réponse à une question d'un formulaire sémantique
	 * @param  array $p
     * 
     * @return array
	 */
	function sauveProcessusReponseFormSem($p){
        if($p['idPR']){
            $idRapport = $p['idPR'];
        }else{
            //création du rapport
            $idRapport = $this->dbR->ajouter(array('monade_id'=>$this->idMonade,'geo_id'=>$this->idGeo
                ,"src_id"=>$p['idR'],"src_obj"=>"rapport"
                ,"dst_id"=>$p['idP'],"dst_obj"=>"doc"
                ,"pre_id"=>$this->idTagProc,"pre_obj"=>"tag"
                ,"maj"=>$p['t']
                ));
            $p['idPR']=$idRapport;
        }
        $this->dbR->edit($idRapport,array("valeur"=>json_encode($p)));

        return $idRapport;
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
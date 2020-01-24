<?php
/**
 * Flux_Isidore
 * Classe qui gère les flux de l'API Isidore
 * https://www.rechercheisidore.fr/api 
 * 
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\API
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Flux_Isidore extends Flux_Site{

	//var $searchUrl = 'https://api.rechercheisidore.fr/resource/search?';
	var $searchUrl = 'https://api.isidore.science/resource/search?';
	var $output = "json";
	var $rs;
	var $NbResult=1000;
	var $sparqlUrl = 'https://api.isidore.science/sparql?';

	
    /**
     * Constructeur de la classe
     *
     * @param  string $idBase
     * 
     */
	public function __construct($idBase=false, $bTrace=false)
    {
    		parent::__construct($idBase, $bTrace);    	
    		
    		//on récupère la racine des documents
    		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
    		if(!$this->dbM)$this->dbM = new Model_DbTable_Flux_Monade($this->db);
    		$this->idDocRoot = $this->dbD->ajouter(array("titre"=>__CLASS__));
    		$this->idMonade = $this->dbM->ajouter(array("titre"=>__CLASS__),true,false);
    		    		
    }

    /**
     * Execute une requète sur le moteur
     *
     * @param  string 	$query
     * @param  boolean 	$sparql
     *
     * @return string
     */
    public function query($query, $sparql=false)
    {
			$this->trace("DEBUT ".__METHOD__);

			if($sparql)
				$url = $this->sparqlUrl.'query='.urlencode($query)
					.'&format='.$this->output;
			else
				$url = $this->searchUrl."output=".$this->output.$query;
			$this->trace($url);
			$body = $this->getUrlBodyContent($url);
			$this->trace("FIN ".__METHOD__);
			return $body;
	
	}
	
    /**
     * Récupère le nombre de document total pour toutes les disciplines 
     * @param boolean $bArr
     * 
     * @return array
     */
    public function getNbDocByDicipline($bArr=true)
    {
		$this->trace(__METHOD__." DEBUT ");
		
		$req = 'SELECT ?topic ?label (count(?documents) as ?count) WHERE {
			?documents sioc:topic ?topic.
			?topic skos:prefLabel ?label.
			FILTER(lang(?label)="fr")
			}
			GROUP BY ?topic ?label
			ORDER BY ?label';
		$json = $this->query($req,true);
		if($bArr)
			return json_decode($json,true);
		else
			return $json;

	}

    /**
     * Récupère le nombre de document par an pour toutes une discipline 
     * 
	 * @param string 	$refDis
     * @param boolean 	$bArr
     * 
     * @return array
     */
    public function getNbDocByAnForDicipline($refDis, $bArr=true)
    {
		$this->trace(__METHOD__." DEBUT ");
		
		$req = 'SELECT (count(?documents) as ?count)   ?an
		WHERE{
		?documents sioc:topic <'.$refDis.'>.
		?documents dcterms:date ?date
		FILTER (?date >= "0001-01-01"^^xsd:date && ?date < "3000-01-01"^^xsd:date)
		}
		GROUP BY (YEAR(?date) as ?an)
		ORDER BY ?an
		LIMIT 3000';
		$json = $this->query($req,true);
		if($bArr)
			return json_decode($json,true);
		else
			return $json;

	}


    /**
     * Construction du tableau des disciplines par date
     *
     * @param  	string	$req
     * @param  	string	$params
     *
     * @return array
     */
    public function getDoc($req, $params=[])
    {
		$this->trace(__METHOD__." DEBUT ".$req);
		
		//récupère la liste des documents pour une requete
		//pour une requête donnée
		$req = "&q=".urlencode($req);
		foreach ($params as $k => $v) {
			$req .= "&".$k."=".urlencode($v);
		}
		$json = $this->query($req);
		return json_decode($json);

	}
	
    /**
     * Construction du tableau des disciplines par date
     *
     * @param  	string	$req
     * @param	string	$for
     *
     * @return array
     */
    public function getHistoDiscipline($req, $for="")
    {
    		$this->trace(__METHOD__." DEBUT ".$req);
    		set_time_limit(0);
    		
    		//récupère la liste totale des disciplines et des dates
    		//pour une requête donnée
    	 	$req = "&facet=discipline,replies=100&facet=date,replies=100&q=".urlencode($req);
	    	$json = $this->query($req);
	    	$arrQ = json_decode($json);
	    		    	
	    	$this->rs['total']=array();
	    	foreach ($arrQ->response->replies->facets->facet as $face) {
	    		if($face->{'@id'}=="discipline"){
	    			//enregistre le total des disciplines
	    			foreach ($face->node as $n) {
	    				$this->trace(" total ".$n->label->{'$'});    				 
	    				$this->rs['total'][]=$this->formatData($n, date('Y'));
	    			}	    			
	    		}
	    		if($face->{'@id'}=="date"){
	    			//récupère le nombre d'items par discipline pour chaque année
	    			foreach ($face->node as $n1) {
//	    				foreach ($n1->node as $n2) {
//	    					foreach ($n2->node as $n3) {
	    						$json = $this->query($req."&date=".$n1->{'@key'});
	    						$arrAn = json_decode($json);
	    						$this->trace($req."&date=".$n1->{'@key'});
	    						if($n1->{'@key'}=="1900/1960/1960"){
	    							$t = 1;
	    						}
	    						if(is_array($arrAn->response->replies->facets->facet)){
		    						$n4 = $arrAn->response->replies->facets->facet[1]->node;
	    							if(is_array($n4)){
		    							foreach ($n4 as $n5) {
		    								$this->rs["an"][]=$this->formatData($n5, $n1->{'@key'});
		    							}
	    							}else{
	    								$this->rs["an"][]=$this->formatData($n4, $n1->{'@key'});	    									
	    							}
	    						}
	    					//}	
	    				//}
	    			}	    			
	    		}
	    	}
	    	
	    	if($for=="stream"){
	    	
	    		//trie le tableau par label
	    		$data = $this->array_orderby($this->rs["an"], 'type', SORT_ASC, 'temps', SORT_ASC);
	    		
	    		$stat = new Flux_Stats();
	    		$nData = $stat->getDataForStream($data, '%Y');
	    		 
	    		//ordonne le tableau
	    		$data = $nData;
	    	}else
	    		$data = $this->rs;
	    	$this->trace(__METHOD__." FIN ");
	    	return $data;
	}

    /**
     * Construction du tableau des nombre de document par disciplines par date
     *
     * @param	string	$for
     *
     * @return array
     */
    public function getRefHistoDiscipline($for="")
    {
		$this->trace(__METHOD__." DEBUT ".$for);


		$c = "getRefHistoDiscipline"; 
		//$this->cache->remove($c);
		if($this->bCache){
		   	$data = $this->cache->load($c);
		}
        if(!$data){

			set_time_limit(0);
			
			//récupère le nombre de document total par disciplines
			$arrD = $this->getNbDocByDicipline();
						
			$this->rs['total']=array();
			$date = date('Y');
			foreach ($arrD['results']['bindings'] as $dis) {
				//enregistre le total pour la disciplines
				$nD = array('key'=>$dis['topic']['value'],'type'=>$dis['label']['value'],'desc'=>$dis['label']['value']
					,'temps'=>$date,'score'=>$dis['count']['value'],'value'=>$dis['count']['value']
					,'MinDate'=>date('0000-01-01'),'MaxDate'=>date($date.'-01-01')
				);
				$this->rs['total'][]=$nD;
				//récupère le nombre de document pour chaque année
				$arrAn = $this->getNbDocByAnForDicipline($dis['topic']['value']);
				foreach ($arrAn['results']['bindings'] as $an) {
					$annee = str_pad($an['an']['value'], 4, "0", STR_PAD_LEFT);
					//on ne prend pas les dates supérieures à l'année en cours
					if(intval($annee) <= intval($date)){
						$nD = array('key'=>$dis['topic']['value'],'type'=>$dis['label']['value'],'desc'=>$dis['label']['value']
							,'temps'=>$annee,'score'=>$an['count']['value'],'value'=>$an['count']['value']
							,'MinDate'=>date($annee.'-01-01'),'MaxDate'=>date($annee.'-01-01')
						);
						$this->rs["an"][]=$nD;	    									
					}
				}
			}
			
			if($for=="stream"){
				//trie le tableau par label
				$data = $this->array_orderby($this->rs["an"], 'key', SORT_ASC, 'MinDate', SORT_ASC);
				
				$stat = new Flux_Stats();
				//$stat->bTrace = true;
				//$stat->bTraceFlush = true;
				$nData = $stat->getDataForStream($data, '%Y');
					
				//ordonne le tableau
				$data = $nData;
			}else
				$data = $this->rs;

			$this->cache->save($data, $c);
		}
		$this->trace(__METHOD__." FIN ");
		return $data;
	}
	
	
	/**
	 * formatage des data pour la visualisation
	 *
	 * @param  	objet	$dis
	 * @param  	string	$date
	 *
	 * @return array
	 */
	function formatData($dis, $date){
		$url = explode("/",$dis->label->{'@$'});
		$nD = array('key'=>$dis->{'@key'},'type'=>$dis->label->{'$'},'desc'=>$dis->label->{'$'}
				,'temps'=>$date,'score'=>$dis->{'@items'},'value'=>$dis->{'@items'}
				,'MinDate'=>date($date.'-01-01'),'MaxDate'=>date($date.'-01-01')
		);
		
		return $nD;
	}
	
    
}
/*NOMBRE de document par discipline
PREFIX sioc: <http://rdfs.org/sioc/ns#>
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX ore: <http://www.openarchives.org/ore/terms/>
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
SELECT ?topic ?label (count(?documents) as ?count) WHERE {
?documents sioc:topic ?topic.
?topic skos:prefLabel ?label.
FILTER(lang(?label)="fr")
}
GROUP BY ?topic ?label
ORDER BY DESC(?count)
*/

/*Nombre de document par année pour une discipline
PREFIX sioc:<http://rdfs.org/sioc/ns#>
PREFIX dcterms:<http://purl.org/dc/terms/>
PREFIX foaf:<http://xmlns.com/foaf/0.1/>
PREFIX ore:<http://www.openarchives.org/ore/terms/>
PREFIX skos:<http://www.w3.org/2004/02/skos/core#>
SELECT (count(?documents) as ?count)   ?an
WHERE{
?documents sioc:topic <http://aurehal.archives-ouvertes.fr/subject/shs.hist>.
?documents dcterms:date ?date
FILTER (?date >= '0001-01-01'^^xsd:date && ?date < '3000-01-01'^^xsd:date)
}
GROUP BY (YEAR(?date) as ?an)
ORDER BY ?an
LIMIT 3000
*/
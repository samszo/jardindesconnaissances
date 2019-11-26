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
     * @param  string $query
     *
     * @return string
     */
    public function query($query)
    {
			$url = $this->searchUrl."output=".$this->output.$query;
			$this->trace($url);
			return $this->getUrlBodyContent($url,$this->bCache);
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
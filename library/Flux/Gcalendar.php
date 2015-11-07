<?php

/**
 * Classe qui gère les flux Google calendar
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Gcalendar extends Flux_Site{

	var $service;		
	var $idsEvent;		
	var $events;		
	var $token;		
	var $client_id = KEY_GOOGLE_CLIENT_ID;
 	var $client_secret = KEY_GOOGLE_CLIENT_SECRET;
	var $redirect_uri = 'http://localhost/exemples/php/google-api/examples/user-example.php';
	 	
	public function __construct($token)
    {
    	parent::__construct($idBase);
    	
		$client = new Google_Client();				
		$client->setAccessToken($token);
		$this->service = new Google_Service_Calendar($client);
    		$this->token = $token;	    
    }
	
    public function getListeCalendar()
    {
    		$this->trace("DEBUT ".__METHOD__);
		$c = str_replace("::", "_", __METHOD__)."_".md5($this->token); 
		$calendars = $this->cache->load($c);
    	
	    	if(!$calendars){
				$calendarList = $this->service->calendarList->listCalendarList();    	
			    while(true) {
				  foreach ($calendarList->getItems() as $calendarListEntry) {
				    $calendars[] = $this->getCalendarInfo($calendarListEntry);
				  }
				  $pageToken = $calendarList->getNextPageToken();
				  if ($pageToken) {
				    $optParams = array('pageToken' => $pageToken);
				    $calendarList = $this->service->calendarList->listCalendarList($optParams);
				  } else {
				    break;
				  }
				}		    		
	        	$this->cache->save($calendars, $c);
	    	}
	    	$this->trace("FIN ".__METHOD__);
		return $calendars;
    }

	public function getListeAcl($idCal)
	{
    	$acls ="";
		$acl = $this->service->acl->listAcl($idCal);		
		foreach ($acl->getItems() as $rule) {
		  $acls[]=$this->getAclInfo($rule);
		}
		return $acls;		
	}    
    
    
    public function getListeEvents($idCal, $optParams)
    {
		$c = str_replace("::", "_", __METHOD__)."_".md5($idCal)."_".$this->getParamString($optParams,true); 
		$this->events = $this->cache->load($c);
    	
		if(!$this->events){
	    	$events = $this->service->events->listEvents($idCal, $optParams);
			while(true) {
	    		foreach ($events->getItems() as $event) {
				    $arrS = $event->getRecurrence();
				   	if($arrS) {
					    $this->getInstances($idCal, $event->getId());
				    }else{
					  	//ajoute l'événement s'il n'est pas en doublons
					  	if(!isset($this->idsEvent[$event->getId()]))				    	
					    	$this->events[]=$this->getEventInfo($event);
				    }
			  	}
			  $pageToken = $events->getNextPageToken();
			  if ($pageToken) {
			    $optParams['pageToken'] = $pageToken;
			    $events = $this->service->events->listEvents($idCal, $idEvent, $optParams);
			  } else {
			    break;
			  }
			}
			
			// Obtient une liste de colonnes
			foreach ($this->events as $key => $row) {
			    $startTS[$key] = $row['startTS'];
				/*
				$summary[$key]  = $row['summary'];
			    $id[$key] = $row['id'];
			    $start[$key] = $row['start'];
			    $end[$key] = $row['end'];
			    $endTS[$key] = $row['endTS'];
			    $duree[$key] = $row['duree'];
			    $description[$key] = $row['description'];
			    $location[$key] = $row['location'];
			    */
			}

			// Trie les données par startTS décroissant
			// Ajoute $data en tant que dernier paramètre, pour trier par la clé commune
			//array_multisort($startTS, SORT_ASC, $start, SORT_ASC, $end, SORT_ASC, $endTS, SORT_ASC, $duree, SORT_ASC, $summary, SORT_ASC
			//	, $description, SORT_ASC, $location, SORT_ASC, $id, SORT_ASC, $this->events);
			array_multisort($startTS, SORT_ASC, $this->events);
				
			
        	$this->cache->save($this->events, $c);			
		}
		return $this->events;
	}
    
    public function getInstances($idCal, $idEvent)
    {
	    $events = $this->service->events->instances($idCal, $idEvent);
		while(true) {
		  foreach ($events->getItems() as $event) {
		  	//ajoute l'événement s'il n'est pas en doublons
		  	if(!isset($this->idsEvent[$event->getId()]))
				$this->events[]=$this->getEventInfo($event);
		  }
		  $pageToken = $events->getNextPageToken();
		  if ($pageToken) {
		    $optParams = array('pageToken' => $pageToken);
		    $events = $this->service->events->instances($idCal, $idEvent, $optParams);
		  } else {
		    break;
		  }
		}    	
    }

    public function showEventInfo($event)
    {
	    echo "<br/>".$event->getSummary();
	    $s = $event->getStart();
	    $e = $event->getEnd();
	    echo $s['dateTime'].", ".$e['dateTime'];
	    echo $event->getDescription().", ";
	    echo $event->getLocation().", ";
	    $arrA = $event->getAttendees().", ";
	    foreach ($arrA as $key => $value) {
		    echo $key." ".$value."<br/>";
	    }
		echo "<br/>";    	
    }

    public function getEventInfo($event)
    {
	    $s = $event->getStart();
	    $e = $event->getEnd();
		//récupère le timestamp
		$dS = new Zend_Date($s['dateTime'],Zend_Date::ISO_8601);
		$dE = new Zend_Date($e['dateTime'],Zend_Date::ISO_8601);
		$tsS = $dS->getTimestamp();
		$tsE = $dE->getTimestamp();
		//calcul la durée de l'événements
		$duree = $dE->sub($dS)->toValue();
	    //enregistre l'id pour la vérification de doublons
		$this->idsEvent[$event->getId()]=true;
	    
    	$r = array("summary"=>$event->getSummary()
	    	,"id"=>$event->getId()
    		,"start"=>$s['dateTime'],"end"=>$e['dateTime']
    		,"startTS"=>$tsS,"endTS"=>$tsE
    		,"duree"=>$duree
    		,"description"=>$event->getDescription()
	    	,"location"=>$event->getLocation()
	    	);
	    $arrA = $event->getAttendees().", ";
	    foreach ($arrA as $key => $value) {
		    $r['attendees'][] = $value;
	    }
		return $r;    	
    }

    public function getCalendarInfo($cal)
    {
    	$this->trace("DEBUT ".__METHOD__);
    	
    	$r = array("summary"=>$cal->getSummary()
	    	,"id"=>$cal->getId()
    		,"access"=>$cal->getAccessRole()
	    	,"description"=>$cal->getDescription()
	    	,"location"=>$cal->getLocation()
	    	);

	    $this->trace($r["summary"]." ".$r["access"]." : ".$r["id"]);	    	
	    	
	    //récupère les roles
	    if($r["access"]!="writer" && $r["access"]!="reader"){
		    $roles = $this->getListeAcl($r["id"]);
		    $r["roles"]=$roles;
	    }    	
	    $this->trace("FIN ".__METHOD__);
	    
	    return $r;    	
    }

    public function getAclInfo($acl)
    {
    	$this->trace("DEBUT ".__METHOD__);
    	$r = array("id"=>$acl->getId()
    		,"role"=>$acl->getRole()
	    	);
	    $this->trace($r["id"]." : ".$r["role"]);
	    $this->trace("FIN ".__METHOD__);
	    return $r;    	
    }
    
}
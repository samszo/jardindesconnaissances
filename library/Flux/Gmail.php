<?php

require_once 'GMailReader.php';

/**
 * Classe qui gère les flux Gmail
 *
 * @copyright  2011 Samuel Szoniecky
 * @license    "New" BSD License
 * 
 */
class Flux_Gmail extends Flux_Site{

	var $login;
	var $mail;
	var $pwd;
	var $ConsumerKey = "www.jardindesconnaissances.com";
	var $ConsumerSecret = "JRkkefUHllHyquos1tzQvNM7";	
	public function __construct($login=null, $pwd=null, $idBase=false)
    {
    	parent::__construct($idBase);
    	
    	$this->login = $login;
    	$this->mail = $this->login."@gmail.com";
    	$this->pwd = $pwd;


$gmail = new GmailReader();
$gmail->openMailBox($this->mail, $this->pwd);
//$results = $gmail->getMessagesSince('Fri, 22 Oct 2010 9:00:00');
$nbMails = $gmail->getNumUnseenMessages();
$folders = $gmail->getMailBoxDirectory();    	
echo $nbMails;
print_r($folders); 
    	
    	
    }
	
	function getSpreadsheets(){
	}


	
}
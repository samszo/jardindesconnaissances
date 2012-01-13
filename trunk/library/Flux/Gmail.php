<?php

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
	var $imap;

	public function __construct($login=null, $pwd=null, $idBase=false)
    {
    	parent::__construct($idBase);
    	
    	$this->login = $login;
    	$this->mail = $this->login."@gmail.com";
    	$this->pwd = $pwd;

    	
    	$this->imap = new Zend_Mail_Storage_Imap(array('host'=> 'imap.gmail.com','user'=> $this->mail,'password' => $this->pwd, 'port' => 993,'ssl' => true));
		$message = $imap->getMessage(100);    	
    	
    }
	
	function getSpreadsheets(){
	}


	
}
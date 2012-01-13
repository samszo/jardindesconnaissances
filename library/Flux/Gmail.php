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
    	
    }
	
	function getFolders(){
		$folders = new RecursiveIteratorIterator(
                        $this->imap->getFolders(),
                        RecursiveIteratorIterator::SELF_FIRST
                    );
	    foreach ($folders as $localName => $folder) {
	        $localName = str_pad('', $folders->getDepth(), '-', STR_PAD_LEFT)
	                   . $localName;
	        echo ' - ';
	        if (!$folder->isSelectable()) {
	            echo ' disabled';
	        }
	        echo ' : ' . htmlspecialchars($folder) . ' - '
	            . htmlspecialchars($localName) . '<br/>';
	    }
	    echo '';		
	}

	function getMessagesByFolderName($name){
		$folder = $this->imap->getFolders($name);
		$this->imap->selectFolder($name);
		$nbMessage = $this->imap->countMessages();

		$message = $this->imap->getMessage(10);
		
		if($message->isMultipart()){
			$part = $message->getPart(1);
			echo $part->getContent();			
		}

		echo '<pre>';
		echo $message->getContent();
		echo '</pre>';
		
	}

	
}
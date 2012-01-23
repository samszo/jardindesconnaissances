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

    /**
     * Construction du gestionnaire de flux.
     *
     * @param string $login
     * @param string $pwd
     * @param string $idBase
     *
     * 
     */
	public function __construct($login=null, $pwd=null, $idBase=false)
    {
    	parent::__construct($idBase);
    	
    	$this->login = $login;
    	$this->mail = $this->login."@gmail.com";
    	$this->pwd = $pwd;
    	
    	$this->imap = new Zend_Mail_Storage_Imap(array('host'=> 'imap.gmail.com','user'=> $this->mail,'password' => $this->pwd, 'port' => 993,'ssl' => true));
    	
    }
	
    /**
     * Affiche la lsite des dossiers pour un compte mail.
     *
     *
     * 
     */
    function showFolders(){
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

    /**
     * Enregistre les messages d'un dossier de mail.
     *
     * @param string $name
     * @param string $type
     *
     * 
     */
	 function saveFolderMessages($name, $type=""){

		//sélection du dossier  
		$this->imap->selectFolder($name);
		$nbMessage = $this->imap->countMessages();
		//parcourt tout les messages du dossier
		for ($i = 1; $i <= $nbMessage; $i++) {
			//récupération du message
			$message = $this->imap->getMessage($i);
			//récupération des en tête
			$headers = $message->getHeaders();
			$t = $this->getSubjectType($headers["subject"]);
			//vérifie si on traite ce type
			if($type=="" || $type==$t){
				//récupération des flags
				$flags = $message->getFlags();
				//récupération du contenu
				$c = $message->getContent();
				//traitement du mail suivant le type
				switch ($type) {
					case "google_alerte":
						$this->saveMessageGoogleAlertContent($c);
						break;
					default:
						$this->saveMessageContent($c);
						break;
				}							
			}
		}
		
	}
	
	function getSubjectType($subject){
		
		if(substr($subject, 0,15)=="Alerte Google -")return "google_alerte";
		
		return "";
	}
	
	function saveMessageGoogleAlertContent($content){
		//charge le contenu dans un dom
		$dom = new Zend_Dom_Query($content);	       	
	   	//récupère les éléments de l'alerte
		$results = $dom->query('//td');
		
	}
	
	function saveMessageContent($content){
		//récupération des mots clefs
		$kw = $this->getMotClef($content);		
	}
		
}
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
	var $dossier;
	var $message;
	var $headers;
	var $flags;
	var $content;
	
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

    	$this->getUser(array("login"=>$this->login,"flux"=>"gmail"));
		//initialise les gestionnaires de base de données
    	if(!$this->dbT)$this->dbT = new Model_DbTable_Flux_Tag($this->db);
		if(!$this->dbD)$this->dbD = new Model_DbTable_Flux_Doc($this->db);
		if(!$this->dbUD)$this->dbUD = new Model_DbTable_Flux_UtiDoc($this->db);
		if(!$this->dbTD)$this->dbTD = new Model_DbTable_Flux_TagDoc($this->db);
		if(!$this->dbUTD)$this->dbUTD = new Model_DbTable_Flux_UtiTagDoc($this->db);
		if(!$this->dbUU)$this->dbUU = new Model_DbTable_Flux_UtiUti($this->db);
		
	 	//sélection du dossier
		$this->dossier = $name; 
		$this->imap->selectFolder($this->dossier);
		$nbMessage = $this->imap->countMessages();
		//parcourt tout les messages du dossier
		for ($i = 1; $i <= $nbMessage; $i++) {
			//récupération du message
			$this->message = $this->imap->getMessage($i);
			//récupération des en tête
			$this->headers = $this->message->getHeaders();
			//récupération des flags
			$this->flags = $this->message->getFlags();
			//récupération du contenu
			$this->content = $this->message->getContent();
			//traitement du mail suivant le type
			switch ($type) {
				case "google_alerte":
					$t = $this->getSubjectType($this->headers["subject"]);
					$this->saveMessageGoogleAlertContent($this->content);
					break;
				case "image":
					$idD = $this->saveMail();
					$this->saveImageContent($idD);
					break;
				case "rhizome":
					if($this->headers["return-path"]=="<discuss@rhizome.org>" || $this->headers["return-path"]=="<announce@rhizome.org>"){
						//enregistre le document
						
						$this->saveRhizomeContent($this->content);						
					}
					break;
				default:
					$this->saveMessageContent($this->content);
					break;
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

	function saveImageContent($idD){
		//charge le contenu dans un dom
		$dom = new Zend_Dom_Query($this->content);	       	
	   	//récupère les éléments de l'alerte
		$results = $dom->queryXpath('//img');
		$i = 0;
		$url = "";
		foreach ($results as $img) {
			if($url != $img->getAttribute("src")){
				$url = $img->getAttribute("src");
				$alt = $img->getAttribute("alt");
				//vérifie si l'image est déjà enregistrée
				$arrD = $this->dbD->findByNote($url);
				if(count($arrD)>0){
					if($arrD[0]["tronc"]!=$idD){
						//duplique le document dans la base
						$arrD[0]["tronc"]=$idD;
						unset($arrD[0]["doc_id"]);
						$idDocImg = $this->dbD->ajouter($arrD[0]);
						echo 'Image dupliquée : <a href="'.$arrD[0]["url"].'">'.$idDocImg.' : '.$alt.'<br/>';
					}else{
						echo 'Image déjà enregistrée : <a href="'.$arrD[0]["url"].'">'.$arrD[0]["doc_id"].' : '.$alt.'<br/>';
					}
				}else{
					//enregistre l'image sur le disque
					if(!$img = file_get_contents($url)) { 
					  echo 'pas de fichier : '.$url."<br/>";
					}else{				
						$extention = pathinfo($url, PATHINFO_EXTENSION);
						$chemin = ROOT_PATH.'/data/gmail/'.$this->dossier.'/img/'.$idD.'_'.$i.'.'.$extention;
						$urlLocal = str_replace(ROOT_PATH, WEB_ROOT, $chemin);  
						if(!$f = fopen($chemin, 'w')) { 
						  echo 'Ouverture du fichier impossible '.$chemin."<br/>";
						}elseif (fwrite($f, $img) === FALSE) { 
						  echo 'Ecriture impossible '.$chemin."<br/>";
						}else{
							//enregistre le document dans la base
							$idDocImg = $this->dbD->ajouter(array("url"=>$urlLocal,"titre"=>$alt,"tronc"=>$idD, "note"=>$url));
							echo 'Image enregistrée : <a href="'.$urlLocal.'">'.$idDocImg.' : '.$alt.'<br/>';
						} 
					}
				}				
				fclose($f);  
				$i++;				
			}
		}		
	}

	function saveRhizomeContent($content){
		//charge le contenu dans un dom
		
	}
	
	function saveMessageContent($content){
		//récupération des mots clefs
		$kw = $this->getKW($content);		
	}

	
    function saveMail(){
				    			
		//ajouter un document correspondant au lien
	   	$d = new Zend_Date($this->headers["date"]);
		$idD = $this->dbD->ajouter(array("url"=>"","titre"=>$this->headers["subject"],"tronc"=>0,"pubDate"=>$d->get("c"), "note"=>$this->content, "type"=>77));
		
		//ajoute un lien entre l'utilisateur qui a envoyé le mail et le document avec un poids
		$this->getUser(array("login"=>$this->headers["from"]));
		$this->dbUD->ajouter(array("uti_id"=>$this->user, "doc_id"=>$idD, "poids"=>1));										    
		
		//ajoute les utilisateurs destinataire du mail
		foreach ($this->headers["delivered-to"] as $uti) {
			$idUdst = $this->getUser(array("login"=>$uti));
			$this->dbUU->ajouter(array("uti_id_src"=>$this->user,"uti_id_dst"=>$idUdst));
		}
		
		/*récupère les mot clefs
	   	$arrKW = $this->getKW(strip_tags($this->content));
	   	//enregistre les mots clefs
	   	$i=0; 
	   	$d = new Zend_Date();
	   	foreach ($arrKW as $kw=>$nb){
			$this->saveTag($kw, $idD, $nb, $d->get("c"));
			$i++;	    			
	   	}
	   	*/
		
	   	return $idD;
	}	
}
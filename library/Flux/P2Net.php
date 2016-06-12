<?php
/**
 * Classe qui gère les flux venant des flux P2NET 
 * http://patent2netv2.vlab4u.info/
 * @copyright  2016 Samuel Szoniecky
 * @license    CC by-sa
 * 
 */
class Flux_P2Net extends Flux_Site{
		
	var $pathData;
	var $pathDonnees;
	var $subDonnees = array("Abstract","Claims","Description","FamiliesAbstract","FamiliesClaims","FamiliesDescription");
	
	/**
     * Constructeur de la classe
     *
     * @param  string $idBase
     * @param  boolean $bTrace
     * 
     */
 	public function __construct($idBase=false, $bTrace=false)
    {
    		parent::__construct($idBase, $bTrace);    	
    		
    		$this->pathData = ROOT_PATH."/data/p2net";	
    		$this->pathDonnees = ROOT_PATH."/data/p2net/DONNEES";	
    		
    		
    		
    }

    
    	/**
     * Rassemble les données produite par P2NET 
     * par langue
     * pour un traitement par TXM
     *
     * @param  string $query
     * 
     */
 	public function rassembleData($query)
    {
    		//parcourt les lettres
		foreach(range('A','Z') as $letter){
	    		//parcourt les sous répertoires    		
	    		foreach ($this->subDonnees as $sub) {	    			    			
	    			//on renome la source et on crée la destination
	   			$cheminSrc = $this->pathDonnees."/".$query.$letter."/PatentContents/".$sub;
	   			if(is_dir($cheminSrc)){
		    			$cheminDst = $this->pathData."/".$query."/PatentContents/".$sub;
		    			if(!is_dir($cheminDst)) mkdir($cheminDst, 0777);	    			
			    		$this->copieFicParLangue($cheminSrc, $cheminDst);			    			
	   			}
	    		}
		}
    	
    			    
    }
    

    /**
     * copie les fichiers produit par P2NET 
     * par langue
     * pour un traitement par TXM
     *
     * @param  string $src
     * @param  string $dst
     * 
     */
 	public function copieFicParLangue($src, $dst)
    {   
		$lstat    = lstat($src);
	    $mtime    = date('d/m/Y H:i:s', $lstat['mtime']);
	    $filetype = filetype($src);
	     
	    // Affichage des infos sur le fichier $chemin
	    $this->trace($src. "type: ".$filetype." size: ".$lstat['size']." mtime: ".$mtime);
	     
	    // Si $chemin est un dossier => on appelle la fonction explorer() pour chaque élément (fichier ou dossier) du dossier$chemin
	    if( is_dir($src) ){
	        $me = opendir($src);
	        while( $child = readdir($me) ){
	            if( $child != '.' && $child != '..' ){
	                $this->copieFicParLangue($src.DIRECTORY_SEPARATOR.$child, $dst);
	            }
	        }
	    }else{
	    		$ficName = basename($src);
	    		//récupère la langue 
	    		$langue = substr($ficName, 0, 2);
	    		//vérifier si le dossier existe
	    		if(!is_dir($dst."/".$langue))mkdir($dst."/".$langue, 0777);
	    		//copie dans le fichier dans le bon répertoire
			
			if (!copy($src, $dst."/".$langue."/".$ficName)) {
			    $this->trace("La copie de $ficName du fichier a échoué...");
			}else{
			    $this->trace("La copie de $ficName du fichier OK");				
			}	    		
	    }      	
    } 
	
}
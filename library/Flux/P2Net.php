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
	var $jsons = array(
		array("dt"=>array("data"=>array()),"fic"=>"*.json")
		,array("dt"=>array("data"=>array()),"fic"=>"*CountryMap.json")
		,array("dt"=>array("data"=>array()),"fic"=>"*MapApplicant.json")
		,array("dt"=>array("data"=>array()),"fic"=>"*MapInventor.json")
		,array("dt"=>array(),"fic"=>"*Pivot.json")
		,array("dt"=>array("data"=>array()),"fic"=>"Families*.json")
		,array("dt"=>array(),"fic"=>"Families*Pivot.json")
		);
	
	
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
     * Rassemble les fichiers 
     * pour un traitement par IRAMUTEQ
     *
     * @param  string $query
     * 
     */
 	public function mergeContent($query)
    {
    	
    		//parcours les sous répertoires    		
    		foreach ($this->subDonnees as $sub) {	    			    			
    			//on renome la source et on crée la destination
   			$src = $this->pathData.DIRECTORY_SEPARATOR.$query.DIRECTORY_SEPARATOR."PatentContents".DIRECTORY_SEPARATOR.$sub;
   			if(is_dir($src)){
		        $me = opendir($src);
		        while( $child = readdir($me) ){
		            if( $child != '.' && $child != '..' && $child != '.DS_Store'){
				        $srcLng = $src.DIRECTORY_SEPARATOR.$child;
				        $dstLng = $src.DIRECTORY_SEPARATOR.$child."_".$query."_".$sub."_recap.txt";
		            		$meLng = opendir($srcLng);
		            		if (file_exists($dstLng)) unlink($dstLng);	    				            		
				        while( $childLng = readdir($meLng) ){
				            if( $childLng != '.' && $childLng != '..' && $childLng != '.DS_Store'){		            	
				                $this->mergeFile($srcLng.DIRECTORY_SEPARATOR.$childLng, $dstLng);
				            }
		            		}
		        		}
   				}
    			}
    		}
    }
	/**
     * rassemble les fichiers produit par P2NET 
     * pour un traitement par TXM
     *
     * @param  string $src
     * @param  string $dst
     * 
     */
 	public function mergeFile($src, $dst)
    {   
		$lstat    = lstat($src);
	    $mtime    = date('d/m/Y H:i:s', $lstat['mtime']);
	    $filetype = filetype($src);
	     
	    // Affichage des infos sur le fichier $chemin
	    $this->trace($src. " type: ".$filetype." size: ".$lstat['size']." mtime: ".$mtime);
	     
	    // Si $chemin est un dossier => on appelle la fonction explorer() pour chaque élément (fichier ou dossier) du dossier$chemin
	    if( file_exists($src) ){
		    	$txt = file_get_contents($src);
			$fic = fopen($dst, 'a');
			fputs($fic, "\n");
			fputs($fic, $txt);
			fclose($fic);
	    }    			    
    }    
    
    	/**
     * Rassemble les données produite par P2NET 
     * par langue
     * pour un traitement par TXM
     *
     * @param  string $query
     * 
     */
 	public function mergeData($query)
    {
    	
    		//parcourt les lettres
		foreach(range('A','H') as $letter){
	    		//rassemble les sous répertoires    		
	    		foreach ($this->subDonnees as $sub) {	    			    			
	    			//on renome la source et on crée la destination
	   			$cheminSrc = $this->pathDonnees."/".$query.$letter."/PatentContents/".$sub;
	   			if(is_dir($cheminSrc)){
		    			$cheminDst = $this->pathData."/".$query;
		    			if(!is_dir($cheminDst)) mkdir($cheminDst, 0777);	    			
	   				$cheminDst = $cheminDst."/PatentContents";
		    			if(!is_dir($cheminDst)) mkdir($cheminDst, 0777);	    			
		    			$cheminDst = $cheminDst."/".$sub;
		    			if(!is_dir($cheminDst)) mkdir($cheminDst, 0777);	    			
		    			$this->copieFicParLangue($cheminSrc, $cheminDst);			    			
	   			}
	    		}
	    		//
	    		//rassemble les jsons
	    		for ($i = 0; $i < count($this->jsons); $i++) {
	    			$js = $this->jsons[$i];
	    			$cheminSrc = $this->pathDonnees."/".$query.$letter."/".str_replace("*", $query.$letter, $js["fic"]);
	    			if (file_exists($cheminSrc)){
			    		$this->jsons[$i]["dst"] = $this->pathData."/".$query."/".str_replace("*", $query, $js["fic"]);
					$json = json_decode(file_get_contents($cheminSrc));
					if(isset($js["dt"]["data"])){
						foreach ($json->data as $k=>$j) {
							$this->jsons[$i]["dt"]["data"][]=$j;
						}
					}else{
						foreach ($json as $k=>$j) {
							$this->jsons[$i]["dt"][]=$j;
						}
					}
	    			}
			}	 			
		}
		
		//enregistre les nouveaux fichiers  
		foreach ($this->jsons as $js) {
			if($js["dst"]){
				$newfic = fopen($js["dst"], 'a');
				fputs($newfic, json_encode($js["dt"]));
				fclose($newfic);
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
	    $this->trace($src. " type: ".$filetype." size: ".$lstat['size']." mtime: ".$mtime);
	     
	    // Si $chemin est un dossier => on appelle la fonction explorer() pour chaque élément (fichier ou dossier) du dossier$chemin
	    if( is_dir($src) ){
	        $me = opendir($src);
	        while( $child = readdir($me) ){
	            if( $child != '.' && $child != '..' && $child != '.DS_Store'){
	                $this->copieFicParLangue($src.DIRECTORY_SEPARATOR.$child, $dst);
	            }
	        }
	    }else{
	    		$ficName = basename($src);
	    		//récupère la langue 
	    		$langue = substr($ficName, 0, 2);
	    		$ficName = $dst."/".$langue."/".$ficName;
	    		//vérifier si le dossier existe
	    		if(!is_dir($dst."/".$langue))mkdir($dst."/".$langue, 0777);
	    		//supprime le fichier s'il existe
			if (file_exists($ficName)) unlink($ficName);	    		
	    		//copie dans le fichier dans le bon répertoire			
			if (!copy($src, $ficName)) {
			    $this->trace("ECHEC copie $ficName");
			}else{
			    $this->trace("OK copie $ficName");				
			}	    		
	    }      	
    } 
	
}
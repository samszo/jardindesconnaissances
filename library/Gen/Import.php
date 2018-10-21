<?php

/**
 * Concrete class for import file
 *
 * @category   Generateur
 * @package    Import
 * @copyright  2013 Samuel Szoniecky
 * @license    "New" BSD License
 */

class Gen_Import {
    	
	public function addDoc($data){

		try {
			//print_r($data);					
			
			$prefix = "";
			if($data['objName']=="Model_DbTable_Gen_dicos"){
				$rep = '/data/dicos';
			}
		
			//print_r($_FILES);
			if (is_uploaded_file($_FILES['file']['tmp_name'])) {
			   //echo "File ". $_FILES['file']['name'] ." téléchargé avec succès.\n";
		        $info = $_FILES["file"];
				$tmp_name = $_FILES["file"]["tmp_name"];
		        $name = $_FILES["file"]["name"];
	        		
				//renomme le fichier pour éviter les doublons
				$tabDecomp = explode('.', $name);
				$extention = ".".strtolower($tabDecomp[sizeof($tabDecomp)-1]);
				$data['new_name'] = uniqid().$extention;
		        $data['rep'] = ROOT_PATH.$rep."/"; 
				$path = $data['rep'].$data['new_name'];					
		        $url = WEB_ROOT.$rep."/".$data['new_name'];
	        		move_uploaded_file($tmp_name, $path);

				//$finfo = new finfo(FILEINFO_MIME);
				$type = "text/plain";//$finfo->file($path);	     
   		
		        $dataDoc = array("url"=>$url,"titre"=>$name,"tronc"=>$data['objName'],"content_type"=>$type);

		    		$data['path_source'] = $path;			    		
			    	//rename(ROOT_PATH.$rep.'/'.$name,$path);
				$info["resultImport"] = $this->saveDoc($data, $dataDoc);
			} else {
			   echo "Attaque possible par téléchargement de fichier : ";
			   echo "Nom du fichier : '". $_FILES['file']['tmp_name'] . "'.";
			}
	        
			/*
			$adapter = new Zend_File_Transfer_Adapter_Http();				
			$adapter->setDestination(ROOT_PATH.$rep);
			
			if (!$adapter->receive()) {
				$messages = $adapter->getMessages();
				echo implode("Mauvaise réception\n", $messages);
		      }else{
				// Retourne toutes les informations connues sur le fichier
				$files = $adapter->getFileInfo();
				foreach ($files as $file => $info) {
					// Les validateurs sont-ils OK ?
					if (!$adapter->isValid($file)) {
						print "Désolé mais $file ne correspond pas à ce que nous attendons";
						continue;
					}
					//renomme le fichier pour éviter les doublons
					$tabDecomp = explode('.', $info["name"]);
					$extention = ".".strtolower($tabDecomp[sizeof($tabDecomp)-1]);
					$data['new_name'] = $prefix.uniqid().$extention;
			        $data['rep'] = ROOT_PATH.$rep."/"; 
					$path = $data['rep'].$data['new_name'];					
			        $url = WEB_ROOT.$rep."/".$data['new_name'];

			        $dataDoc = array(
			    		"url"=>$url,"titre"=>$info["name"],"content_type"=>$adapter->getMimeType()
			    		,"tronc"=>$data['objName']);

			    		$data['path_source'] = $path;			    		
				    	rename(ROOT_PATH.$rep.'/'.$info["name"],$path);
			    		
			    		
					$info["resultImport"] = $this->saveDoc($data, $dataDoc);
					
					//print_r($info);					
				}
		      }
		      */
		}catch (Zend_Exception $e) {
			// Appeler Zend_Loader::loadClass() sur une classe non-existante
          	//entrainera la levée d'une exception dans Zend_Loader
          	echo "Récupère exception: " . get_class($e) . "\n";
          	echo "Message: " . $e->getMessage() . "\n";
          	echo "Rep:".$data['rep'] . "\n";
          	echo "Path:".$path . "\n";
          	echo "url:".$url . "\n";
          	// puis tout le code nécessaire pour récupérer l'erreur
		}
     	//echo json_encode(array("error"=>$info["error"]));   
     	echo json_encode($info);   
	}
    
    public function saveDoc($data, $dataDoc){
    	
    	if($dataDoc["url"]=="url")return ;
		
    	$dbDoc = new Model_DbTable_Flux_Doc();    		    		
    	
    	$dataDico = array("url_source"=>$url,"path_source"=>$data["path_source"],"langue"=>$data["langue"],"nom"=>$data["nom"],"type"=>$data["type"],"licence"=>"by");     		
    	
    	if($data['objName']=='Model_DbTable_Gen_dicos'){
    		$dbDico = new Model_DbTable_Gen_dicos();
    		
    		//ajoute les référence du document dans la base
    		$dataDoc["data"]="{'action':'importation dictionnaire','idExi':".$data["idExi"].",'idDico':".$data["idDico"]."}";
    		$idDoc = $dbDoc->ajouter($dataDoc);
    		
			//vérifie s'il faut ajouter le lien à l'oeuvre
    		if($data["idOeu"]){
    			$dbODU = new Model_DbTable_Gen_oeuvresxdicosxutis(); 
	    		$dbODU->ajouter($data["idOeu"], $data["idExi"], array($idDicoDst));
    		}
    		
    		$oDico = new Gen_Dico();
    		if(isset($data["csv"])){
    			//importation d'un fichier csv
    			$idDico = $data["idDico"];
    			$oDico->importCSV($data["idDico"], $dataDico);	
    		}else{
	    		//ajoute le dictionnaire
	    		$url = str_replace(ROOT_PATH,WEB_ROOT,$data["path_source"]);
	    		$idDico = $dbDico->ajouter($dataDico, $data["idExi"]);
    			//importation des dicos Balpe
	    		$oDico->GetMacToXml($idDico);
	    		$oDico->SaveBdd($idDico, $data["idConj"]);    			
    		}
    		
    		$dataDico = $dbDico->findByIdDico($idDico);
    		return $dataDico;
    	}
		
		return $idDoc;
		
    }    
    
/** merci à http://j-reaux.developpez.com/tutoriel/php/fonctions-redimensionner-image/
// 	---------------------------------------------------------------
// fonction de REDIMENSIONNEMENT physique "PROPORTIONNEL" et Enregistrement
// 	---------------------------------------------------------------
// retourne : 1 (vrai) si le redimensionnement et l enregistrement ont bien eu lieu, sinon rien (false)
// 	---------------------------------------------------------------
// La FONCTION : fctredimimage ($W_max, $H_max, $rep_Dst, $img_Dst, $rep_Src, $img_Src)
// Les parametres :
// - $W_max : LARGEUR maxi finale --> ou 0
// - $H_max : HAUTEUR maxi finale --> ou 0
// - $rep_Dst : repertoire de l image de Destination (deprotégé) --> ou '' (meme repertoire)
// - $img_Dst : NOM de l image de Destination --> ou '' (meme nom que l image Source)
// - $rep_Src : repertoire de l image Source (deprotégé)
// - $img_Src : NOM de l image Source
// 	---------------------------------------------------------------
// 3 options :
// A- si $W_max != 0 et $H_max != 0 : a LARGEUR maxi ET HAUTEUR maxi fixes
// B- si $H_max != 0 et $W_max == 0 : image finale a HAUTEUR maxi fixe (largeur auto)
// C- si $W_max == 0 et $H_max != 0 : image finale a LARGEUR maxi fixe (hauteur auto)
// Si l'image Source est plus petite que les dimensions indiquees : PAS de redimensionnement.
// 	---------------------------------------------------------------
// $rep_Dst : il faut s'assurer que les droits en écriture ont été donnés au dossier (chmod)
// - si $rep_Dst = ''   : $rep_Dst = $rep_Src (meme repertoire que l image Source)
// - si $img_Dst = '' : $img_Dst = $img_Src (meme nom que l image Source)
// - si $rep_Dst='' ET $img_Dst='' : on ecrase (remplace) l image source !
// 	---------------------------------------------------------------
// NB : $img_Dst et $img_Src doivent avoir la meme extension (meme type mime) !
// Extensions acceptees (traitees ici) : .jpg , .jpeg , .png
// Pour ajouter d autres extensions : voir la bibliotheque GD ou ImageMagick
// (GD) NE fonctionne PAS avec les GIF ANIMES ou a fond transparent !
// 	---------------------------------------------------------------
// UTILISATION (exemple) :
// $redimOK = fctredimimage(120,80,'reppicto/','monpicto.jpg','repimage/','monimage.jpg');
// if ($redimOK == 1) { echo 'Redimensionnement OK !';  }
*/ 	
function fctredimimage($W_max, $H_max, $rep_Dst, $img_Dst, $rep_Src, $img_Src) {
 // ------------------------------------------------------------------
 $condition = 0;
 // Si certains parametres ont pour valeur '' :
   if ($rep_Dst == '') { $rep_Dst = $rep_Src; } // (meme repertoire)
   if ($img_Dst == '') { $img_Dst = $img_Src; } // (meme nom)
 // ------------------------------------------------------------------
 // si le fichier existe dans le répertoire, on continue...
 if (file_exists($rep_Src.$img_Src) && ($W_max!=0 || $H_max!=0)) { 
   // ----------------------------------------------------------------
   // extensions acceptees : 
   $ExtfichierOK = '" jpg jpeg png"'; // (l espace avant jpg est important)
   // extension fichier Source
   $tabimage = explode('.',$img_Src);
   $extension = $tabimage[sizeof($tabimage)-1]; // dernier element
   $extension = strtolower($extension); // on met en minuscule
   // ----------------------------------------------------------------
   // extension OK ? on continue ...
   if (strpos($ExtfichierOK,$extension) != '') {
      // -------------------------------------------------------------
      // recuperation des dimensions de l image Src
      $img_size = getimagesize($rep_Src.$img_Src);
      $W_Src = $img_size[0]; // largeur
      $H_Src = $img_size[1]; // hauteur
      // -------------------------------------------------------------
      // condition de redimensionnement et dimensions de l image finale
      // -------------------------------------------------------------
      // A- LARGEUR ET HAUTEUR maxi fixes
      if ($W_max != 0 && $H_max != 0) {
         $ratiox = $W_Src / $W_max; // ratio en largeur
         $ratioy = $H_Src / $H_max; // ratio en hauteur
         $ratio = max($ratiox,$ratioy); // le plus grand
         $W = $W_Src/$ratio;
         $H = $H_Src/$ratio;   
         $condition = ($W_Src>$W) || ($W_Src>$H); // 1 si vrai (true)
      }      // -------------------------------------------------------------
      // B- HAUTEUR maxi fixe
      if ($W_max == 0 && $H_max != 0) {
         $H = $H_max;
         $W = $H * ($W_Src / $H_Src);
         $condition = $H_Src > $H_max; // 1 si vrai (true)
      }
      // -------------------------------------------------------------
      // C- LARGEUR maxi fixe
      if ($W_max != 0 && $H_max == 0) {
         $W = $W_max;
         $H = $W * ($H_Src / $W_Src);         
         $condition = $W_Src > $W_max; // 1 si vrai (true)
      }
      // -------------------------------------------------------------
      // on REDIMENSIONNE si la condition est vraie
      // -------------------------------------------------------------
      // Par defaut : 
	  // Si l'image Source est plus petite que les dimensions indiquees :
	  // PAS de redimensionnement.
	  // Mais on peut "forcer" le redimensionnement en ajoutant ici :
	  // $condition = 1;
      if ($condition == 1) {
         // ----------------------------------------------------------
         // creation de la ressource-image "Src" en fonction de l extension
         switch($extension) {
         case 'jpg':
         case 'jpeg':
           $Ress_Src = imagecreatefromjpeg($rep_Src.$img_Src);
           break;
         case 'png':
           $Ress_Src = imagecreatefrompng($rep_Src.$img_Src);
           break;
         }
         // ----------------------------------------------------------
         // creation d une ressource-image "Dst" aux dimensions finales
         // fond noir (par defaut)
         switch($extension) {
         case 'jpg':
         case 'jpeg':
           $Ress_Dst = imagecreatetruecolor($W,$H);
           break;
         case 'png':
           $Ress_Dst = imagecreatetruecolor($W,$H);
           // fond transparent (pour les png avec transparence)
           imagesavealpha($Ress_Dst, true);
           $trans_color = imagecolorallocatealpha($Ress_Dst, 0, 0, 0, 127);
           imagefill($Ress_Dst, 0, 0, $trans_color);
           break;
         }
         // ----------------------------------------------------------
         // REDIMENSIONNEMENT (copie, redimensionne, re-echantillonne)
         imagecopyresampled($Ress_Dst, $Ress_Src, 0, 0, 0, 0, $W, $H, $W_Src, $H_Src); 
         // ----------------------------------------------------------
         // ENREGISTREMENT dans le repertoire (avec la fonction appropriee)
         switch ($extension) { 
         case 'jpg':
         case 'jpeg':
           imagejpeg ($Ress_Dst, $rep_Dst.$img_Dst);
           break;
         case 'png':
           imagepng ($Ress_Dst, $rep_Dst.$img_Dst);
           break;
         }
         // ----------------------------------------------------------
         // liberation des ressources-image
         imagedestroy ($Ress_Src);
         imagedestroy ($Ress_Dst);
      }
      // -------------------------------------------------------------
   }
 }
// 	---------------------------------------------------------------
 // si le fichier a bien ete cree
 if ($condition == 1 && file_exists($rep_Dst.$img_Dst)) { return true; }
 else { return false; }
}
// retourne : 1 (vrai) si le redimensionnement et l enregistrement ont bien eu lieu, sinon rien (false)
// 	---------------------------------------------------------------    


// 	---------------------------------------------------------------
// fonction d AJOUT DE TEXTE a une image et Enregistrement
// 	---------------------------------------------------------------
// retourne : 1 (vrai) si l ajout de texte a bien ete ajoute, sinon rien (false)
// 	---------------------------------------------------------------
// La FONCTION : fcttexteimage ($chaine, $rep_Dst, $img_Dst, $rep_Src, $img_Src, $position)
// Les parametres :
// - $chaine : TEXTE a ajouter
// - $rep_Dst : repertoire de l image de Destination (deprotégé) --> ou '' (meme repertoire)
// - $img_Dst : NOM de l image de Destination --> ou '' (meme nom que l image Source)
// - $rep_Src : repertoire de l image Source (deprotégé)
// - $img_Src : NOM de l image Source
// - $position : position du texte sur l image
// 	---------------------------------------------------------------
// ATTENTION : si le texte est TROP long, il risque d etre tronque !
// 	---------------------------------------------------------------
// Position du texte sur l image (valeurs possibles) :
// $position = 'HG' --> en Haut a Gauche (valeur par defaut)
// $position = 'HD' --> en Haut a Droite
// $position = 'HC' --> en Haut au Centre
// $position = 'BG' --> en Bas a Gauche
// $position = 'BD' --> en Bas a Droite
// $position = 'BC' --> en Bas au Centre
// 	---------------------------------------------------------------
// $rep_Dst : il faut s'assurer que les droits en écriture ont été donnés au dossier (chmod)
// - si $rep_Dst = ''   : $rep_Dst = $rep_Src (meme repertoire que l image Source)
// - si $img_Dst = '' : $img_Dst = $img_Src (meme nom que l image Source)
// - si $rep_Dst='' ET $img_Dst='' : on ecrase (remplace) l image source !
// 	---------------------------------------------------------------
// NB : $img_Dst et $img_Src doivent avoir la meme extension (meme type mime) !
// Extensions acceptees (traitees ici) : .jpg , .jpeg , .png
// Pour ajouter d autres extensions : voir la bibliotheque GD ou ImageMagick
// (GD) NE fonctionne PAS avec les GIF ANIMES ou a fond transparent !
// 	---------------------------------------------------------------
// UTILISATION (exemple copyright, ou legende de l image) :
// $texteOK = fcttexteimage('copyright : MOI','reppicto/','monpicto.jpg','repimage/','monimage.jpg','BG');
// if ($texteOK == 1) { echo 'Ajout du texte OK !';  }
// 	---------------------------------------------------------------
function fcttexteimage($chaine, $rep_Dst, $img_Dst, $rep_Src, $img_Src, $position) {
 $condition = 0;
 // ------------------------------------------------------------------
   $position = strtoupper($position); // on met en majuscule (par defaut)
 // Si certains parametres ont pour valeur '' :
   if ($rep_Dst == '') { $rep_Dst = $rep_Src; } // (meme repertoire)
   if ($img_Dst == '') { $img_Dst = $img_Src; } // (meme nom)
   if ($position == '') { $position = 'BG'; } // en Bas A Gauche (valeur par defaut)
 // ------------------------------------------------------------------
 // si le fichier existe dans le répertoire, on continue...
 if (file_exists($rep_Src.$img_Src) && $chaine!='') { 
   // ----------------------------------------------------------------
   // extensions acceptees : 
   $ExtfichierOK = '" jpg jpeg png"'; // (l espace avant jpg est important)
   // extension fichier Source
   $tabimage = explode('.',$img_Src);
   $extension = $tabimage[sizeof($tabimage)-1]; // dernier element
   $extension = strtolower($extension); // on met en minuscule
   // ----------------------------------------------------------------
   // extension OK ? on continue ...
   if (strpos($ExtfichierOK,$extension) != '') {
      // -------------------------------------------------------------
      // recuperation des dimensions de l image Src
      $img_size = getimagesize($rep_Src.$img_Src);
      $W_Src = $img_size[0]; // largeur
      $H_Src = $img_size[1]; // hauteur
      // -------------------------------------------------------------
      // creation de la ressource-image "Dst" en fonction de l extension
      // (a partir de l image source)
      switch($extension) {
      case 'jpg':
      case 'jpeg':
        $Ress_Dst = imagecreatefromjpeg($rep_Src.$img_Src);
        break;
      case 'png':
        $Ress_Dst = imagecreatefrompng($rep_Src.$img_Src);
        break;
      }
      // -------------------------------------------------------------
      // creation de l image TEXTE
      // -------------------------------------------------------------
      // dimension de l image "Txt" en fonction :
      // - de la longueur du texte a afficher
      // - des dimensions des caracteres (7x15 pixels par caractere)
      // ATTENTION : si le texte est TROP long, il risque d etre tronque !
      $W = strlen($chaine) * 7;
      if ($W > $W_Src) { $W = $W_Src; }
      $H = 15; // 15 pixels de haut (par defaut)
      // -------------------------------------------------------------
      // creation de la ressource-image "Txt" (en fonction de l extension)
      switch($extension) {
      case 'jpg':
      case 'jpeg':
      case 'png':
        $Ress_Txt = imagecreatetruecolor($W,$H);
        // Couleur du Fond : blanc
        $blanc = imagecolorallocate ($Ress_Txt, 255, 255, 255);
        imagefill ($Ress_Txt, 0, 0, $blanc);
        // Couleur du Texte : noir
        $textcolor = imagecolorallocate($Ress_Txt, 0, 0, 0);
        // Ecriture du TEXTE
        imagestring($Ress_Txt, 3, 0, 0, $chaine, $textcolor);
        break;
      }
      // -------------------------------------------------------------
      // positionnement du TEXTE sur l image
      // -------------------------------------------------------------
      if ($position == 'HG') {
         $X_Dest = 0;
         $Y_Dest = 0;
      }
      if ($position == 'HD') {
         $X_Dest = $W_Src - $W;
         $Y_Dest = 0;
      }
      if ($position == 'HC') {
         $X_Dest = ($W_Src - $W)/2;
         $Y_Dest = 0;
      }
      if ($position == 'BG') {
         $X_Dest = 0;
         $Y_Dest = $H_Src - $H;
      }
      if ($position == 'BD') {
         $X_Dest = $W_Src - $W;
         $Y_Dest = $H_Src - $H;
      }
      if ($position == 'BC') {
         $X_Dest = ($W_Src - $W)/2;
         $Y_Dest = $H_Src - $H;
      }
      // -------------------------------------------------------------
      // copie par fusion de l image "Txt" sur l image "Dst"
      // (avec transparence de 50%)
      // -------------------------------------------------------------
      imagecopymerge ($Ress_Dst, $Ress_Txt, $X_Dest, $Y_Dest, 0, 0, $W, $H, 50);
      // ----------------------------------------------------------
      // ENREGISTREMENT dans le repertoire (en fonction de l extension)
      switch ($extension) { 
      case 'jpg':
      case 'jpeg':
        imagejpeg ($Ress_Dst, $rep_Dst.$img_Dst);
        $condition = 1;
        break;
      case 'png':
        imagepng ($Ress_Dst, $rep_Dst.$img_Dst);
        $condition = 1;
        break;
      }
      // -------------------------------------------------------------
      // liberation des ressources-image
      imagedestroy ($Ress_Txt);
      imagedestroy ($Ress_Dst);
      // -------------------------------------------------------------
   }
 }
// 	---------------------------------------------------------------
 // si le fichier a bien ete cree
 if ($condition == 1 && file_exists($rep_Dst.$img_Dst)) { return true; }
 else { return false; }
}
// retourne : 1 (vrai) si l ajout de texte a bien ete ajoute, sinon rien (false)
// 
}
?>
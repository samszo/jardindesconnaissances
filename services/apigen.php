<?php
require_once( "../application/configs/config.php" );
try {
	
	$application->bootstrap();
	//print_r($application);

	/*
	$_GET['oeu']=41;
	$_GET['cpt']=167638;
	$_GET['gen']= 39610;
	$_GET['nb']=1;
	$_GET['minC']=3;
	$_GET['maxC']=6;	

$_GET['frt']='json';
$_GET['oeu']='6';
$_GET['cpt']=157829;	
	*/
	
	if(isset($_GET['test'])){
		$test = true;
	}else
		$test = false;
	
	if(isset($_GET['frt']))
		$frt = $_GET['frt'];
	else
		$frt = "txt";

	if(isset($_GET['audio'])){
		$audio=$_GET['audio'];
		$frt = "html";
	}
	
	if($frt == "html" || $frt == "frg" )
		$rtn = "<br/>";
	else
		$rtn = "\n";
	
	//récupération des paramètres
	if(isset($_GET['oeu']))
		$idOeu = $_GET['oeu'];
	else
		$err = "ERREUR : aucune oeuvre n'est définie.".$rtn;
	
	if(isset($_GET['cpt']))
		$idCpt = $_GET['cpt'];
	else
		$err .= "ERREUR : aucun texte génératif n'est défini.".$rtn;
	if(isset($_GET['gen']))
		$idGen = $_GET['gen'];
	else
		$idGen=0;
				
	if(isset($_GET['btn']))
		$btn = $_GET['btn'];
	else
		$btn = false;
	if(isset($_GET['nb']))
		$nb = $_GET['nb'];
	else
		$nb = 1;
	if(isset($_GET['minC']))
		$coupures[] = $_GET['minC'];
	else
		$coupures = false;
	if(isset($_GET['maxC']))
		$coupures[] = $_GET['maxC'];
	else
		$coupures = false;
	if(isset($_GET['force']))
		$force = $_GET['force'];
	else
		$force = false;
		
	if(!$err){
		//récupère le texte génératif
		if($idGen){
			$dbGen = new Model_DbTable_Gen_generateurs();
			$arrGen = $dbGen->findById_gen($idGen);
			$txtGen = $arrGen[0]["valeur"];
		}else{
			try {
				$dbCpt = new Model_DbTable_Gen_concepts();
				$arrCpt = $dbCpt->findById_concept($idCpt);
				$txtGen = $dbCpt->getGenTexte($arrCpt[0]);
			}catch (Zend_Exception $e) {
				echo "Récupère exception: " . get_class($e) . "\n";
				echo "Message: " . $e->getMessage() . "\n";
			}
		
		}
		//
		
		
		//initialisation du moteur
		$m = new Gen_Moteur();
				
		//récupère les dictionnaires
		$m->arrDicos = $m->getDicosOeuvre($idOeu);
		$m->showErr = $test;
		$m->bTrace = false;
		$m->forceCalcul = $force;
		$m->coupures = $coupures;
		$m->finLigne = $rtn;
		$txts = "";
		for ($i = 0; $i < $nb; $i++) {
			$txt = $m->Generation($txtGen).$rtn;
			//if($rtn == "\n")$txt = str_replace("<br/>", "\n", $txt);
			$txts .= $txt;
			if($frt=="json") $detail = $m->arrClass;
			if($test){
				$txts .= $m->finLigne.$m->detail;
			}
		}
		//
	}
	//pour l'exécution du script dans une page extérieur au domaine
	header('Access-Control-Allow-Origin: *');
	header("X-XSS-Protection: 0");	
	
	//vérifie le content type à retourner
	if($frt == "html" || $frt == "frg" )
		header('Content-Type: text/html; charset=UTF-8');
	else
		header('Content-Type: text/plain; charset=UTF-8');
		
	//construction de l'audio
	if($audio){
		//à revoir
		include "http://localhost/jdc/public/flux/parole?txt=".urlencode($txt);
	}
		
		
	//construction du code pour le bouton
	$script = "";
	$scodeBtn = "";
	$codeDiv = "";
	if($btn){
		//construction de l'url de génération
		$params = str_replace("frt=iframe", "frt=frg", $_SERVER['QUERY_STRING']);
		$params = str_replace("frt=html", "frt=frg", $params);
		$urlGen = WEB_ROOT.'/services/api.php';  
		
		$script ='
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>';
		$codeBtn = '<button class="genButton" type="button" onclick="document.getElementsByTagName(\'body\')[0].style.cursor = \'wait\';$.get(\''.$urlGen.'\', {\'oeu\':'.$idOeu.', \'cpt\':'.$idCpt.', \'gen\':'.$idGen.', \'frt\':\'frg\'}, function(fragment){$(\'#gen'.$idOeu.'_'.$idCpt.'_'.$idGen.'\').html(fragment);document.getElementsByTagName(\'body\')[0].style.cursor = \'default\';});">Génère</button>';		
		$codeGen = '<script type="text/javascript">$.get(\''.$urlGen.'\', {\'oeu\':'.$idOeu.', \'cpt\':'.$idCpt.', \'gen\':'.$idGen.', \'frt\':\'frg\'}, function(fragment){$(\'#gen'.$idOeu.'_'.$idCpt.'_'.$idGen.'\').html(fragment);document.getElementsByTagName(\'body\')[0].style.cursor = \'default\';});</script>';		
		$codeDiv = "<div class='genTxt' id='gen".$idOeu."_".$idCpt."_".$idGen."' >".$txts."</div>".$codeBtn.$codeGen.$audio;		
	}
	if($audio){
		$codeDiv = $txts.$audio;
	}
		
	if($frt == "frg"){
		echo "<div>".$err.$rtn.$txts.$audio."</div>";
	}
	
	if($frt == "html"){
		echo "<html>
	<head>	
		<meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
		<link rel='stylesheet' type='text/css' href='".WEB_ROOT."/public/css/apiGen.css'>
		".$script."
	</head>
	<body>
	".$err.$rtn.$codeDiv."
	</body>
</html>";
	}

	if($frt == "txt"){
		echo $err;
		echo $txts;		
	}
	
	if($frt == "json"){
		$js = json_encode(array("err"=>$err,"txt"=>convToUtf8($txts),"detail"=>convToUtf8($detail)));
		if($js)
			echo $js;
		else 
			echo getJsonErreur(json_last_error()).$txts;
	}
	
	if($frt == "iframe"){
		//construction de l'url de génération
		$params = str_replace("frt=iframe", "frt=html", $_SERVER['QUERY_STRING']);
		$urlGen = WEB_ROOT.'/services/api.php?'.$params;  
		echo '<iframe src="'.$urlGen.'" width="760" height="500" frameborder="0" marginheight="0" marginwidth="0">Chargement en cours...</iframe>';		
	}
	
	
}catch (Zend_Exception $e) {
	echo "Récupère exception: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
}

function getJsonErreur($err){
switch ($err) {
        case JSON_ERROR_NONE:
            echo ' - Aucune erreur';
        break;
        case JSON_ERROR_DEPTH:
            echo ' - Profondeur maximale atteinte';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Inadéquation des modes ou underflow';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Erreur lors du contrôle des caractères';
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Erreur de syntaxe ; JSON malformé';
        break;
        case JSON_ERROR_UTF8:
            echo ' - Caractères UTF-8 malformés, probablement une erreur d\'encodage';
        break;
        default:
            echo ' - Erreur inconnue';
        break;
    }	
}

function convToUtf8($str)
{
	if( mb_detect_encoding($str,"UTF-8, ISO-8859-1, GBK")!="UTF-8" )
		return  iconv("gbk","utf-8",$str);	
	else
		return $str;
} 

?>   		
<?php
date_default_timezone_set('Europe/Paris');
setlocale (LC_TIME, 'fr_FR.utf8','fra'); 
ini_set("memory_limit",'1600M');
$www = "/var/www";
define ("WEB_ROOT","http://gapai.univ-paris8.fr/jdc");
define ("ROOT_PATH",$www."/jdc");
define ("WEB_ROOT_AJAX",WEB_ROOT."/public");
define ("SEP_PATH","/");



//code de s�curit� pour l'administation
define ("CODE_ADMIN","simple");

//chemin de la librairie ffmpeg
//define ("FFMEPG","C:\\ffmpeg\\bin\\ffmpeg.exe ");


// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', ROOT_PATH . '/application');

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

set_include_path(ROOT_PATH.'/library');       

/** Zend_Application*/
set_include_path(get_include_path().PATH_SEPARATOR.$www."/Zend/library");
set_include_path(get_include_path().PATH_SEPARATOR.$www."/Zend/extras/library");

require_once 'Zend/Application.php';

//chargement des librairies supplémentaires
set_include_path(get_include_path().PATH_SEPARATOR."../library/Epub");

require_once( "ext/autokeyword.php" );
//require_once( "AlchemyAPI.php" );
require_once( "ext/AlchemyAPI_CURL.php" );
require_once( "ext/ArrayMixer.php" );
require_once( "ext/opencalais.php" );
require_once( "ext/pdfParser.php" );
require_once( "ext/DocxConversion.php" );
require_once( "ext/bibtex-parser.php" );
require_once( "libRIS/RISReader.php" );
require_once( "libRIS/RISTags.php" );
require_once( "libRIS/ParseException.php" );
require_once( "oauth/OAuth.php");
require_once( "oauth/tokenStore/SessionTokenStore.php");
require_once( "oauth/backend/ScoopCurlHttpBackend.php");
require_once( "oauth/executor/ScoopExecutor.php");
require_once( "CAS/CAS.php");
require_once( "EasyRdf.php");



// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

?>
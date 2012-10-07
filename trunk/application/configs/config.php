<?php
date_default_timezone_set('Europe/Paris');
setlocale (LC_TIME, 'fr_FR.utf8','fra'); 
ini_set("memory_limit",'1600M');
//$www = "/Users/paragraphe/Documents/www";
$www = "C:/wamp/www";
define ("WEB_ROOT","http://localhost/jardindesconnaissances");
define ("ROOT_PATH",$www."/jardindesconnaissances");
define ("WEB_ROOT_AJAX",WEB_ROOT."/public");
define ("SEP_PATH","/");

//clef des API
define ("KEY_ZEMANTA","");
define ("KEY_ALCHEMY","");
define ("KEY_GOOGLE_URL","");
define ("KEY_AMAZON","");
define ("KEY_AMAZON_PWD","");
define ("AMAZON_PWD","");
define ("AMAZON_AT","");


//code de sécurité pour l'administation
define ("CODE_ADMIN","simple");

//chemin de la librairie ffmpeg
define ("FFMEPG","C:\\ffmpeg\\bin\\ffmpeg.exe ");


// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', ROOT_PATH . '/application');

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));


set_include_path(ROOT_PATH.'/library');       

/** Zend_Application 1.10.8
set_include_path(get_include_path().PATH_SEPARATOR.$www."/ZendFramework-1.10.8/library");
set_include_path(get_include_path().PATH_SEPARATOR.$www."/ZendFramework-1.10.8/extras/library");
*/
/** Zend_Application 1.12.0*/
set_include_path(get_include_path().PATH_SEPARATOR.$www."/ZendFramework-1.12.0/library");
set_include_path(get_include_path().PATH_SEPARATOR.$www."/ZendFramework-1.12.0/extras/library");

require_once 'Zend/Application.php';


// *ZAMFBROWSER IMPLEMENTATION*
set_include_path(get_include_path().PATH_SEPARATOR.$www."/ZamfBrowser/browser");
require_once( "ZendAmfServiceBrowser.php" );

//chargement des librairies supplémentaires
require_once( "autokeyword.php" );
//require_once( "AlchemyAPI.php" );
require_once( "AlchemyAPI_CURL.php" );


// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

?>
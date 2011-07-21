<?php
date_default_timezone_set('Europe/Paris');
setlocale (LC_TIME, 'fr_FR.utf8','fra'); 
ini_set("memory_limit",'1600M');

define ("WEB_ROOT","http://localhost/jardindesconnaissances");
define ("ROOT_PATH","/Users/paragraphe/Documents/www/jardindesconnaissances");
define ("WEB_ROOT_AJAX",WEB_ROOT."/public");


// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', ROOT_PATH . '/application');

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));


set_include_path(ROOT_PATH.'/library');       
set_include_path(get_include_path().PATH_SEPARATOR."/Users/paragraphe/Documents/www/ZendFramework-1.10.8/library");
set_include_path(get_include_path().PATH_SEPARATOR."/Users/paragraphe/Documents/www/ZendFramework-1.10.8/extras/library");

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

?>
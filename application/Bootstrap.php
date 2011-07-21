<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initAutoload()
	{
		$moduleLoader = new Zend_Application_Module_Autoloader(array(
			'namespace' => '',
			'basePath' => APPLICATION_PATH));

		$loader = Zend_Loader_Autoloader::getInstance();
		$loader->registerNamespace(array('Flux_'));
		$loader->registerNamespace(array('Jardin_'));
		return $moduleLoader;
	}

	protected function _initViewHelpers()
	{
		/*
	    $this->bootstrap('layout');
	    $layout = $this->getResource('layout');
	    $view = $layout->getView();
	    $view->doctype('XHTML1_STRICT');
	    $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');
	    $view->headTitle()->setSeparator(' - ');
	    $view->headTitle('Jardin des connaissances');
	    
		$view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewRenderer->setView($view);
		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
		*/	    
	}	

}


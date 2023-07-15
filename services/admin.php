<?php
require_once( "../application/configs/config.php" );
try {
	
	$application->bootstrap();
	
	$server = new Zend_Amf_Server();

	$server->addDirectory(APPLICATION_PATH);
	$server->addDirectory(ROOT_PATH.'/library');
		
	//$server->setClass("Gen_Moteur");
	$server->setProduction(false);
	
	$response = $server->handle();

}catch (Zend_Exception $e) {
  echo "<h1>Erreur d'exécution</h1>
  <h2>".$this->message."</h2>
  <h3>Exception information:</h3>
  <p><b>Message:</b>".$this->exception->getMessage()."</p>
  <h3>Stack trace:</h3>
  <pre>".$this->exception->getTraceAsString()."</pre>
  <h3>Request Parameters:</h3>
  <pre>".var_export($this->request->getParams(), true)."</pre>";
}

echo $response;
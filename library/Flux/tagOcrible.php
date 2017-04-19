<?php
/**
 * Flux_tagOcrible
 * 
 * Classe qui gère les flux de l'application mobile tagOcrible
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Flux_tagOcrible extends Flux_Site{

  
  
	/**
	* constructeur de la classe
	* 
	* @param string $idBase
	* 
	* return Flux_tagOcrible
	* 
	*/
	public function __construct($idBase=false)
    {
    	parent::__construct($idBase);
    }

	/**
	* récupère les tags pour un utilisateur dans une base
	* 
	* @param string $login
	* @param string $idBase
	* 
	* return array
	* 
	*/
    public function getTags($login, $idBase)
    {
    	$db = $this->getDb($idBase);
    	$dbUT = new Model_DbTable_flux_utitag($db);
    	return $dbUT->findTagByUti($login);
    }

	/**
	* récupère les cribles pour un utilisateur dans une base
	* 
	* @param string $idUti
	* @param string $idBase
	* 
	* return array
	* 
	*/
	public function getCribles($idUti, $idBase)
    {
    	$db = $this->getDb($idBase);
    	$dbCrible = new Models_DbTable_Flux_Crible($db);
    	
    	return $dbCrible->findByUti_id($idUti);
    	
    	parent::__construct($idBase);
    }

	/**
	* constructeur de la classe
	* 
	* @param string $idBase
	* 
	* return Flux_tagOcrible
	* 
	*/
	public function setTag($idBase=false)
    {
    	parent::__construct($idBase);
    }

	/**
	* constructeur de la classe
	* 
	* @param string $idBase
	* 
	* return Flux_tagOcrible
	* 
	*/
	public function setCrible($idBase=false)
    {
    	parent::__construct($idBase);
    }

}
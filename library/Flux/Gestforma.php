<?php
/**
 * Flux_Gestforma
 * 
 * Classe qui gère les flux du projet Gestforma
 * merci à 
 * @file csv2skosxl.php
 * http://skos.um.es/unescothes/
 * https://www.w3.org/TR/2005/WD-swbp-skos-core-spec-20051102
 * 
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Projet
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */

class Flux_Gestforma extends Flux_Site{


	public function __construct($idBase=false, $bTrace=false)
    {
    	parent::__construct($idBase,$bTrace);
       	
    }   
    


}
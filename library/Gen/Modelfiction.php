<?php
/**
 * Gen_Modelfiction
 * Classe qui implémente le modèle génratif de fiction
 * 
 * @author Philippe Bootz
 * @author Samuel Szoniecky
 * @category   Zend
 * @package library\Flux\Outils
 * @license https://creativecommons.org/licenses/by-sa/2.0/fr/ CC BY-SA 2.0 FR
 * @version  $Id:$
 */
class Gen_Modelfiction extends Flux_Site{


	
    /**
     * Constructeur de la classe
     *
     * @param  string   $idBase
     * @param  boolean  $bTrace
     * @param  boolean  $bCache
     * 
     */
	public function __construct($idBase=false, $bTrace=false, $bCache=true)
    {
        parent::__construct($idBase, $bTrace, $bCache);    	

    }

    /**
     * Les méthodes de la classe sont dévéloppées spécifiquement pour un événement particulier
     * Le nom de la méthode correspond à l'identifiant de la base omeka s + l'identifiant de l'événement
     */

    /**
     * Déplacement d’un étranger 
     * Heuristique : he1
     * Fonction : De (Io, Lf) 
     * Résultat : modifie l’état du monde
     *
     * @param  string   $idBase
     * @param  boolean  $bTrace
     * @param  boolean  $bCache
     * 
     */
	public function __construct($idBase=false, $bTrace=false, $bCache=true)
    {
        parent::__construct($idBase, $bTrace, $bCache);    	

    }


}
<?php
/**
 * Generateur Framework
 *
 * LICENSE
 *
 * This source file is subject to the Artistic/GPL license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.generateur.com/license/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@generateur.com so we can send you a copy immediately.
 *
 * @category   Generateur
 * @package    Fragment
 * @copyright  Copyright (c) 2010 J-P Balpe (http://www.balpe.com)
 * @license    http://framework.generateur.com/license/ Artistic/GPL License
 * @version    $Id: Fragment.php 0 2010-09-20 07:46:09Z samszo $
 */

/**
 * Concrete class for generating Fragment for Generateur.
 *
 * @category   Generateur
 * @package    Fragment
 * @copyright  Copyright (c) 2010 J-P Balpe (http://www.balpe.com)
 * @license    http://framework.generateur.com/license/ Artistic/GPL License
 */

class Gen_Fragment
{

    /**
     * @type tableau des types de fragment
     */
	var $types = array("verbes","adjectifs","substantif","syntagme","texte");
    /**
     * @fragments tableau des fragments associées
     */
	var $fragments = array();
    /**
     * @texte valeur textuelle du fragment
     */
	var $texte = "";
	
	/**
	 * The constructor initializes the Donnee. 
	 */
	public function __construct($frag) {
		//calcul le type de fragment suivant son préfixe
	
	}

	/**
	 * Retourne la valeur texte du fragment et de tous ces enfants
	 * @return string
	 */
	public function getTexte() {

		$texte = "";
		foreach($this->fragments as $frag){
			$texte .= $frag->getTexte();
		}
		
	    $this->texte = $texte;
	    return $this->texte;
	}
	

}
?>
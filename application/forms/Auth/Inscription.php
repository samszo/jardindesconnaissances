<?php
/**
 * 
 * Enter description here ...
 * @author samszo
 * merci à http://www.dator.fr/tutorial-creer-une-application-avec-le-zend-framework-%E2%80%93-8-le-formulaire-dinscription-de-watchmydesk/
 */
class Form_Auth_Inscription extends Zend_Form
{
	public function __construct($options = null)
	{
		parent::__construct($options);
		
		$this->setName('inscription');
		
		$login = new Zend_Form_Element_Text("login", array('size' => 25));
		//problème avec la connexion par défaut à la base
		//$loginDoesntExist = new Zend_Validate_Db_NoRecordExists('flux_uti', 'login');
		$login->setLabel('Login')
		  ->addFilter('StripTags')
		  ->addFilter('StringTrim')
		  ->setRequired(true)
		  //->addValidator($loginDoesntExist)
		  ->addValidator('StringLength', false, array(3, 20))
		  ->setDescription("Login entre 6 et 20 charactères.");
		  
		//problème avec la connexion par défaut à la base
		//$emailDoesntExist = new Zend_Validate_Db_NoRecordExists('flux_uti', 'email');
		$email = new Zend_Form_Element_Text("email", array('size' => 25));
		$email->setLabel('Adresse email')
		  ->addFilter('StripTags')
		  ->addFilter('StringTrim')
		  ->setRequired(true)
		  //->addValidator($emailDoesntExist)
		  ->addValidator('EmailAddress')
		  ->setDescription("Saisir un email valide.");		  

		$password = new Zend_Form_Element_Password("mdp", array('size' => 25));
		$password->setLabel('Mot de passe')
			->setRequired(true);
		
		$envoyer = new Zend_Form_Element_Submit('envoyer');
        $envoyer->setAttrib('id', 'boutonenvoyer')
        	->setIgnore(true);
		
		$this->addElements(array($login,$email, $password, $envoyer));
		
	}
	
}
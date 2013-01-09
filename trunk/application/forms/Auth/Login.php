<?php
class Form_Auth_Login extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
 
        $this->addElement(
            'text', 'login', array(
                'label' => 'Login:',
                'required' => true,
                'filters'    => array('StringTrim'),
            ));
 
        $this->addElement('password', 'password', array(
            'label' => 'Mot de passe:',
            'required' => true,
            ));
 
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Connexion',
            ));

        $this->addElement('submit', 'crea', array(
            'ignore'	=> true,
            'label'		=> "Inscription",
            'description'		=> "Si vous n'avez pas de login, vous pouvez vous inscrire."
        ));
        
    }
}
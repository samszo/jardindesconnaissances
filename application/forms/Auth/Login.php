<?php
class Form_Auth_Login extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
 
        $this->addElement(
            'text', 'mail', array(
                'label' => 'Mail:',
                'required' => true,
                'filters'    => array('StringTrim'),
            ));
 
        $this->addElement('password', 'password', array(
            'label' => 'Mot de passe:',
            'required' => true,
            ));
 
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Login',
            ));
 
    }
}
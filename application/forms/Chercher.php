<?php
class Form_Chercher extends Zend_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setName('chercher');        		
      	
      	$txt = new Zend_Form_Element_Text('recherche');
      	$txt->setRequired(true);
		$txt->setLabel('Saisir votre recherche');
				
		$envoyer = new Zend_Form_Element_Submit('envoyer');
        $envoyer->setAttrib('id', 'boutonenvoyer');
		$this->setAttrib('enctype', 'multipart/form-data');
        $this->addElements(array($txt, $envoyer));
    }
}

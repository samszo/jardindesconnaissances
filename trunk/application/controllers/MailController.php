<?php

class MailController extends Zend_Controller_Action
{


    public function indexAction()
    {
    	$this->view->data = "Liste des mails disponibles";
    	
    	
    }

    public function spipconnexionAction()
    {

    	$this->view->site = "CiTU";
    	$this->view->login = "samszo";
    	$this->view->mdp = "kjhup";
		$this->view->prenom = 'samuel';
		$this->view->nom = 'szoniecky';
		$this->view->arrAut = array("id_auteur"=>23,"nom"=>"sam szo");
		$this->view->arrRub = array("id_rubrique"=>23,"titre"=>"10. sam szo");
		$this->view->arrArt = array(array("id_article"=>23,"titre"=>"hoiuj"),array("id_article"=>24,"titre"=>"dfgt"));
		$this->view->urlVoir = "http://gapai.univ-paris8.fr/citu/spip.php?article";
    	$this->view->urlEcrire = "http://gapai.univ-paris8.fr/citu/ecrire/?exec=naviguer&id_rubrique=";
    	$this->view->urlAuteur = "http://gapai.univ-paris8.fr/citu/ecrire/?exec=auteur_infos&id_auteur=";
    	$this->view->mailContact="samszo@free.fr";
    	$this->view->nomContact = "Samuel Szoniecky";    	
    	
    }
    
    
}




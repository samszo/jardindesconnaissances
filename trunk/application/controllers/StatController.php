<?php

/**
 * StatController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class StatController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->view->title = "Statistiques disponibles";
	    $this->view->headTitle($this->view->title, 'PREPEND');
	    $this->view->stats = array("tags pour un utilisateur", "utilisateurs en relation");
	}

    public function tagusernetworkAction()
    {
	    $this->view->stats = "";
    	if($this->_getParam('tag', 0) && $this->_getParam('uti', 0)){
			$s = new Flux_Stats;
			$this->view->stats = $s->GetTagUserNetwork(utf8_encode($this->_getParam('tag', 0)), $this->_getParam('uti', 0));	    
	    }
    }	

    public function updateAction()
    {
	    $this->view->stats = "";
    	if($this->_getParam('code', 0)){
			$s = new Flux_Stats;
			$this->view->stats = $s->update($this->_getParam('code', 0));	    
	    }
    }	

    public function tagassosAction()
    {
	    $this->view->stats = "";
    	if($this->_getParam('tags')){
			$s = new Flux_Stats($this->_getParam('idBase', 0));
			$this->view->stats = $s->GetTagAssos($this->_getParam('tags'),$this->_getParam('racine', 0));	    
	    }
    }	

    public function audiowaveAction()
    {
	    $this->view->stats = "";
    	$audio = new Flux_Audio();
		$this->view->stats = $audio->getWave("../data/audios/01_10-02-81_9A.wav");	    
    }	

}

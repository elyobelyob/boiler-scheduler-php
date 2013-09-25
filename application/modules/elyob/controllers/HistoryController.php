<?php

class Elyob_HistoryController extends Zend_Controller_Action
{ 
    public function indexAction()
    {    	
    	$this->view->headScript()->appendFile('/js/pagerefresh.js');
    	$this->view->headScript()->appendFile('/js/clock.js');
    	$this->view->headTitle('History');
    }
}

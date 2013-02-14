<?php

class Elyob_GroupsController extends Zend_Controller_Action
{ 
    public function indexAction()
    {    	
    	$this->view->headScript()->appendFile('/js/clock.js');
    	$this->view->headScript()->appendFile('/js/checkStatus.js');
    	$this->view->headTitle('Groups');
    }
}
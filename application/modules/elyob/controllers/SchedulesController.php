<?php
/**
 * Elyob_SchedulesController
 * 
 * @category   Elyob
 * @package    Elyob_Controller
 */
class Elyob_SchedulesController extends Zend_Controller_Action
{ 
    public function indexAction()
    {    	
    	$this->view->headScript()->appendFile('/js/clock.js');
    	$this->view->headScript()->appendFile('/js/checkStatus.js');
    	$this->view->headTitle('Schedules');
    }
}
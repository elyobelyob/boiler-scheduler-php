<?php
/**
 * Elyob_IndexController
 * 
 * @category   Elyob
 * @package    Elyob_Controller
 * @copyright  Copyright (c) 2010 Cooke IT Ltd
 * @license    tbd
 */
class Elyob_HolidaysController extends Zend_Controller_Action
{ 
    public function indexAction()
    {    	
    	$this->view->headTitle('Schedules');
    	
    	$_model = new Elyob_Model_Configuration();
    	$this->view->holidayFrom = strftime("%A %d %B %H:%M", (int) ($_model->getConfigurationByKey("holidayFrom")->value));
    	$this->view->holidayTo = strftime("%A %d %B %H:%M", (int) ($_model->getConfigurationByKey("holidayTo")->value));
    }
}
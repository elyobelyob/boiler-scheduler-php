<?php
/**
 * Elyob_IndexController
 * 
 * @category   Elyob
 * @package    Elyob_Controller
 * @copyright  Copyright (c) 2010 Cooke IT Ltd
 * @license    tbd
 */
class Elyob_IndexController extends Zend_Controller_Action
{ 
    public function indexAction()
    {
    	$this->view->headScript()->appendFile('/js/pagerefresh.js');
    	$this->view->headScript()->appendFile('/js/clock.js');
    	$this->view->headScript()->appendFile('/js/checkStatus.js');
    	$this->view->headTitle('Index');
    	
/*
    	$_model = new Elyob_Model_Configuration();
    	$this->view->holidayFrom = strftime("%A %d %B %H:%M", (int) ($_model->getConfigurationByKey("holidayFrom")->value / 1000));
    	$this->view->holidayTo = strftime("%A %d %B %H:%M", (int) ($_model->getConfigurationByKey("holidayTo")->value / 1000));
*/
    }
}
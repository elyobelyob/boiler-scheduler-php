<?php
/**
 * Converts Java day of week to a string
 * @author james
 *
 */
class Zend_View_Helper_History extends Zend_View_Helper_Abstract
{       
	private $_model;
	
    public function history($id = null)
    {
    	$this->_model = new Elyob_Model_History();
    	if (is_null($id)) return $this;
    	
		return $this->_model->getHistory();
    }
    
    public function getHistory() {
    	return $this->_model->getHistory();
    }
}

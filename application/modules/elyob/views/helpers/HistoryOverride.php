<?php
/**
 * Converts Java day of week to a string
 * @author james
 *
 */
class Zend_View_Helper_HistoryOverride extends Zend_View_Helper_Abstract
{
	private $_model;
	
    public function history($id = null)
    {
    	$this->_model = new Elyob_Model_HistoryOverride();
    	if (is_null($id)) return $this;
    	
		return $this->_model->getHistoryLastFifty();
    }

    public function getHistory() {
        return $this->_model->getHistory();
    }

    public function getHistoryLastFifty() {
        return $this->_model->getHistoryLastFifty();
    }
}

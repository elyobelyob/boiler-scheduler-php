<?php
class Api_ConfigurationController extends Zend_Controller_Action
{
	protected $_model;
	
	public function init()
	{
		$this->_model = new Elyob_Model_Configuration();
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	}
	
	public function statusAction()
	{
		$output['Result'] = "OK";
		$output['Records'] = array(
			"heating" => $this->_model->getConfigurationByKey("heatingStatus")->value,
			"water" => $this->_model->getConfigurationByKey("waterStatus")->value);
		
		$json = Zend_Json::encode($output);
		$this->getResponse()
			->setHttpResponseCode(200)
			->appendBody($json);
	}
	
	public function holidaytimesAction()
	{
		$from = strftime("%A %d %B %H:%M", (int) ($this->_model->getConfigurationByKey("holidayFrom")->value));
    	$to = strftime("%A %d %B %H:%M", (int) ($this->_model->getConfigurationByKey("holidayTo")->value));
		
		$output['Result'] = "OK";
		$output['Message'] = "From: $from To: $to";
		
	
		$json = Zend_Json::encode($output);
		$this->getResponse()
    		->setHttpResponseCode(200)
    		->appendBody($json);
	}

    public function historyAction()
    {
        $from = strftime("%A %d %B %H:%M", (int) ($this->_model->getConfigurationByKey("holidayFrom")->value));
        $to = strftime("%A %d %B %H:%M", (int) ($this->_model->getConfigurationByKey("holidayTo")->value));

        $output['Result'] = "OK";
        $output['Message'] = "From: $from To: $to";


        $json = Zend_Json::encode($output);
        $this->getResponse()
            ->setHttpResponseCode(200)
            ->appendBody($json);
    }
    public function historyoverrideAction()
    {
        $output['Result'] = "OK";

        $json = Zend_Json::encode($output);
        $this->getResponse()
            ->setHttpResponseCode(200)
            ->appendBody($json);
    }

    public function boostAction()
	{
		
		$data = $this->_getParam('toggle');
		$key = "";
	
		$output = array();
		if (is_null($data)) { //must have data
			$output['Result'] = "ERROR";
			$output['Message'] = "Toggle must be specified";
		} else if ($this->_request->getMethod() != "POST") { //must be post not get
			$output['Result'] = "ERROR";
			$output['Message'] = "Incorrect request method";
		} else if ($data == "heating") {
			$output['Result'] = "OK";
			$this->_model->setConfiguration("toggleHeating", "true", "boolean");
		} else if ($data == "water") {
			$output['Result'] = "OK";
			$this->_model->setConfiguration("toggleWater", "true", "boolean");
		} else {
			$output['Result'] = "ERROR";
			$output['Message'] = "Invalid toggle item";
		}
		$json = Zend_Json::encode($output);
		$this->getResponse()
			->setHttpResponseCode(200)
			->appendBody($json);
	}
	
	public function holidayAction()
	{
		$from = strtotime($this->_getParam("from"));
		$to = strtotime($this->_getParam("to"));
	
		$output = array();
	
		if (empty($from) || empty($to)) {
			$output['Result'] = "ERROR";
			$output['Message'] = "From and To times must both be set";
		} elseif ($from > $to) {
			$output['Result'] = "ERROR";
			$output['Message'] = "The to time must be after the from time";
		} 
		else {
			$output['Result'] = "OK";
			$this->_model->setConfiguration("holidayFrom", $from, "long");
			$this->_model->setConfiguration("holidayTo", $to, "long");
		}
		$json = Zend_Json::encode($output);
		$this->getResponse()
		->setHttpResponseCode(200)
		->appendBody($json);
	}
}

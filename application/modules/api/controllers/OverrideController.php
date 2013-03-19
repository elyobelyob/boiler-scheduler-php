<?php
class Api_OverrideController extends Zend_Controller_Action
{
	protected $_model;
	
	public function init()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$this->_model = new Elyob_Model_Override();
	}

	public function boostAction()
	{
		$data = $this->_getParam('toggle');
		$time = $this->_getParam('time');
		$temp = $this->_getParam('temp');
		$delay = $this->_getParam('delay');
		$key = "";
	
		$output = array();
		if (is_null($data)) { //must have data
			$output['Result'] = "ERROR";
			$output['Message'] = "Toggle must be specified";
/*
		} else if ($this->_request->getMethod() != "POST") { //must be post not get
			$output['Result'] = "ERROR";
			$output['Message'] = "Incorrect request method";
*/
		} else if ($data == "heating") {
		
            $override = new Elyob_Model_Override();

            // remove old entries by dis-enabling them
            $data = array(
                'enabled'      => '0'
            );
            $where = array();
            
            $override->update($data,$where);

	$date = date("Y-m-d H:i:s");
	$currentDate = strtotime($date);
	$futureDate = $currentDate+(60*$delay);
	$formatDate = date("Y-m-d H:i:s", $futureDate);
            
	// Now add the new row
            $date = date("Y-m-d H:i:s");
            $currentDate = strtotime($date);
            $futureDate = $currentDate+(60*$delay);
            $formatDate = date("Y-m-d H:i:s", $futureDate);

            // Now add the new row
            $data = array(
                'type'          => 'heat',
                'date'          => $formatDate,
                'length'        => $time,
                'boost'         => 1,
                'enabled'       => 1,
                'heatingTemp'   => $temp
            );
            $override->insert($data);
            
/*
            // Set column values as appropriate for your application
            $override->date = date('Y-m-d H:i:s');
            $override->length = $temp;
            $override->heatingTemp = $temp;
*/
             
            // INSERT the new row to the database
            //$override->save();   
			//$output = $this->_model->setBoostHeating($time,$temp);
		} else if ($data == "water") {
			$output = $this->_model->setBoostWater($time);
		} else {
			$output['Result'] = "ERROR";
			$output['Message'] = "Invalid toggle item";
		}
		$json = "";
		if (is_array($output)) {
			$json = Zend_Json::encode($output);
		} else {
			$json = $output;
		}
		$this->getResponse()
			->setHttpResponseCode(200)
			->appendBody($json);
	}
	
	public function statusAction()
	{
		$output = $this->_model->getStatus();

		$this->getResponse()
    		->setHttpResponseCode(200)
    		->appendBody($output);
	}
}

<?php
class Api_HistoryOverrideController extends Zend_Controller_Action
{
	private $_model;
	public function init()
	{
		$this->_model = new Elyob_Model_HistoryOverride();
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	}
	
	public function listAction()
	{
		$jTableResult['Result'] = "OK";
		
		$displayas = $this->_getParam("display");
		if($displayas == "options") {
			$groups = $this->_model->getHistoryLastFifty();
			$array = array();
			foreach ($groups as $group) {
				$array[] = array("DisplayText" => $group->name, "Value" => $group->id);
			}
			$jTableResult['Options'] = $array;
		} else {
			$jTableResult['Records'] = $this->_model->getHistoryLastFifty()->toArray();
		}
		
		$json = Zend_Json::encode($jTableResult);
		$this->getResponse()
            ->setHttpResponseCode(200)
            ->appendBody($json);
	}

}
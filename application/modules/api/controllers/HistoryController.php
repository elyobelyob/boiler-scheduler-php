<?php
class Api_HistoryController extends Zend_Controller_Action
{
	private $_model;
	public function init()
	{
		$this->_model = new Elyob_Model_History();
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	}
	
	public function listAction()
	{
		$jTableResult['Result'] = "OK";
		
		$displayas = $this->_getParam("display");
		if($displayas == "options") {
			$groups = $this->_model->getHistory();
			$array = array();
			foreach ($groups as $group) {
				$array[] = array("DisplayText" => $group->name, "Value" => $group->id);
			}
			$jTableResult['Options'] = $array;
		} else {
			$jTableResult['Records'] = $this->_model->getHistory()->toArray();
		}
		
		$json = Zend_Json::encode($jTableResult);
		$this->getResponse()
            ->setHttpResponseCode(200)
            ->appendBody($json);
	}

}
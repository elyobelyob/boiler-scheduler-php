<?php
class Elyob_Model_History extends SF_Model_Abstract
{

	public function getHistory()
	{
		return $this->getResource('History')->getHistory();
	}
	
	public function getHistoryByDate($date)
	{
		return $this->getResource('History')->getHistoryByDate($date);
	}

}
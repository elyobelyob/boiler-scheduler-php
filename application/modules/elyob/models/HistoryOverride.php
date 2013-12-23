<?php
class Elyob_Model_HistoryOverride extends SF_Model_Abstract
{

	public function getHistory()
	{
		return $this->getResource('HistoryOverride')->getHistory();
	}

    public function getHistoryLastFifty()
    {
        return $this->getResource('HistoryOverride')->getHistoryLastFifty();
    }

    public function getHistoryByDate($date)
    {
        return $this->getResource('HistoryOverride')->getHistoryByDate($date);
    }

}
<?php
class Elyob_Model_Historyoverride extends SF_Model_Abstract
{

	public function getHistory()
	{
		return $this->getResource('Historyoverride')->getHistory();
	}

    public function getHistoryLastFifty()
    {
        return $this->getResource('Historyoverride')->getHistoryLastFifty();
    }

    public function getHistoryByDate($date)
    {
        return $this->getResource('Historyoverride')->getHistoryByDate($date);
    }

}
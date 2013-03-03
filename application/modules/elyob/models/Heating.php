<?php
class Elyob_Model_Heating extends SF_Model_Abstract
{	
	public function setBoostHeating($time,$temp)
	{
		return $this->getResource('Heating')->setBoostHeating($time,$temp);
	}
	
	public function setBoostWater($time)
	{
		return $this->getResource('Heating')->setBoostWater($time);
	}
	
	public function getStatus()
	{
		return $this->getResource('Heating')->getStatus();
	}
}
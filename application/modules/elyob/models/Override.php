<?php
class Elyob_Model_Override extends Zend_Db_Table
{	

    protected $_name = 'override';
    
    public function setDate() {
        
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
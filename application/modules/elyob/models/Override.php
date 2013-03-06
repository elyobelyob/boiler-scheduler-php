<?php
class Elyob_Model_Override extends Zend_Db_Table
{	

    protected $_name = 'override';
    
    public function setDate() {
        
    }

/*
	public function addBoostHeating($time,$temp)
	{

        $data = array(
            'type'          => 'type',
            'date'          => date('Y-m-d H:m:s'),
            'length'        => $time,
            'boost'         => 1,
            'enabled'       => 1,
            'heatingTemp'   => $temp
        );
        
        $this->insert($data);
         
//        $this->setBoostHeating($data);
//        print_r($data); exit;

		return "Heating Boosted";
		//return $this->getResource('Heating')->setBoostHeating($time,$temp);
	}
*/
	
/*
	public function createRow($data)
	{
        $data = array(
            'type'          => 'heat',
            'date'          => date('Y-m-d H:i:s'),
            'length'        => $data['length'],
            'boost'         => 1,
            'enabled'       => 1,
            'heatingTemp'   => $data['heatingTemp']
        );
        $this->insert($data);    	
	}
*/
	
	public function setBoostWater($time)
	{
		return $this->getResource('Heating')->setBoostWater($time);
	}
	
	public function getStatus()
	{
		return $this->getResource('Heating')->getStatus();
	}
}
<?php
class Elyob_Resource_Schedules extends SF_Model_Resource_Db_Table_Abstract
{
	protected $_name = 'schedule';
	protected $_primary = 'id';
	protected $_rowClass = 'Elyob_Resource_Schedules_Item';
// 	protected $_attributeFieldName = 'label';
// 	protected $_referenceMap = array(
// 			'Project' => array(
// 					'columns' 		=> 'project',
// 					'refTableClass' => 'Elyob_Resource_Projects',
// 					'refColumns' 	=> 'id'
// 			),
// 	);
	
	public function getSchedules()
	{
		$select = $this->select();
		$select->from(array('schedule'), array(   'id',
		                                          'hourOn' => 'hour(timeOn)',
		                                          'minuteOn' =>'minute(timeOn)',
		                                          'hourOff' => 'hour(timeOff)',
		                                          'minuteOff' => 'minute(timeOff)',
		                                          'group',
		                                          'day',
		                                          'heatingOn',
		                                          'heatingTemp',
		                                          'waterOn',
		                                          'enabled'));
		$select->order(array( 'day ASC', 'timeOn ASC', 'id ASC'));
		return $this->fetchAll($select);
	}
	
	public function getSchedulesByDay($day)
	{
		$select = $this->select();
		$select->where("day=?",$day)
				->order(array('timeOn ASC'));
		return $this->fetchAll($select);
	}
	
	public function getSchedulesByGroup($group)
	{
		$select = $this->select();
		$select->where("`group`=?",$group)
				->order(array('enabled DESC', 'day ASC', 'timeOn ASC'));
		return $this->fetchAll($select);
	}
	
	public function getScheduleById($id)
	{
		$select = $this->select();
		$select->where("id=?",$id);
		return $this->fetchRow($select);
	}
	
	public function deleteSchedule($id)
	{
		$where = $this->getAdapter()->quoteInto("id = ?", $id);
		 
		return $this->delete($where);
	}
	
	public function setGroupEnabled($group, $value) 
	{
		$data = array('enabled' => $value);
		$where = $this->getAdapter()->quoteInto("`group`=?",$group);
		return $this->update($data, $where);
	}
}
<?php
class Elyob_Resource_History extends SF_Model_Resource_Db_Table_Abstract
{
	protected $_name = 'boiler_history';
	protected $_primary = 'id';
	protected $_rowClass = 'Elyob_Resource_History_Item';
	protected $_nameColumn = "datetime";

	public function getHistory()
	{
		$select = $this->select();
		$select->order(array($this->_nameColumn . ' DESC'));
		return $this->fetchAll($select);
	}

}
<?php
class Elyob_Resource_HistoryOverride extends SF_Model_Resource_Db_Table_Abstract
{
	protected $_name = 'override';
	protected $_primary = 'id';
	protected $_rowClass = 'Elyob_Resource_HistoryOverride_Item';
	protected $_nameColumn = "id";

    public function getHistory()
    {
        $select = $this->select();
        $select->order(array($this->_nameColumn . ' DESC'));
        return $this->fetchAll($select);
    }

    public function getHistoryLastFifty()
    {
        $select = $this->select();
        $select->order(array($this->_nameColumn . ' DESC'))
                ->limit(50);
        return $this->fetchAll($select);
    }

}
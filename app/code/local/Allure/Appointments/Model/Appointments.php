<?php

class Allure_Appointments_Model_Appointments extends Mage_Core_Model_Abstract
{
	const STATUS_REQUEST  = '1';
	const STATUS_ASSIGNED  = '2';
	const STATUS_COMPLETED = '3';
	const STATUS_CANCELLED = '4';
    const STATUS_MISSED = '5';
	
    public function _construct()
    {
        parent::_construct();
        $this->_init('appointments/appointments');
    }

    public function getStores($key=null)
    {
        $activeStores=Mage::helper("appointments/data")->getActiveStore();
        if($key)
            return $activeStores[$key];
        return $activeStores;
    }

    public function getStatus($key=null)
    {
    	$status =array('1' => 'Requested', '2' => 'Assigned', '3' => 'Completed', '4' => 'Canceled', '5' => 'Missed');
    	if($key)
    		return $status[$key];
    	return $status;
    }

    public function getReminderType($key=null)
    {
        $status =array('nd' => 'Regular', 'day' => 'Day Reminder', 'week' => 'Week Reminder');
        if($key)
            return $status[$key];
        return $status;
    }

    protected function _beforeSave() {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        $this->setUpdatedAt($now);
        return $this;
    }
    
}
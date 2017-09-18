<?php
class Allure_OrderProcessor_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getConfig($key)
	{
		return Mage::getStoreConfig('allure_orderprocessor/general/'.$key);
	}
	
	public function isEnabled()
	{
		return $this->isModuleEnabled() && $this->getConfig('status');
	}
	
	public function getStores()
	{
		return $this->getConfig('stores');
	}
	
	public function getStatusFilter()
	{
		return $this->getConfig('filter_status');
	}
	
	public function getFromFilter()
	{
		return $this->getConfig('filter_from');
	}
	
	public function isDebugMode()
	{
		return $this->getConfig('debug');
	}
	
}
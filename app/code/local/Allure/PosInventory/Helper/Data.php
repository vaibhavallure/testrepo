<?php
class Allure_PosInventory_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getConfig($key)
	{
		return Mage::getStoreConfig('allure_posinventory/general/'.$key);
	}
	
	public function isEnabled()
	{
		return $this->isModuleEnabled() && $this->getConfig('status');
	}
	
	public function getSkipStockBefore()
	{
		return $this->getConfig('skip_stock_before');
	}
	
	public function getSkipStockAfter()
	{
		return $this->getConfig('skip_stock_after');
	}
	
	public function getSkipStockBeforeDate()
	{
		return $this->getConfig('skip_stock_before_date');
	}
	
	public function getSkipStockAfterDate()
	{
		return $this->getConfig('skip_stock_after_date');
	}
}
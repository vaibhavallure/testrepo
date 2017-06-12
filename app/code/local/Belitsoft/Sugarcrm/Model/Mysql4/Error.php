<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */
 
class Belitsoft_Sugarcrm_Model_Mysql4_Error extends Mage_Core_Model_Mysql4_Abstract
{
	protected function _construct()
	{
		$this->_init('sugarcrm/error', 'error_id');
	}	

	/**
	 * Sets the creation and synch timestamps
	 *
	 * @param Mage_Core_Model_Abstract $object Current error
	 * @return Belitsoft_Sugarcrm_Model_Mysql4_Error
	 */
	protected function _beforeSave(Mage_Core_Model_Abstract $object)
	{
		if(!$id = $object->getId()) {
			$object->setCreationDate(Mage::getSingleton('core/date')->gmtDate());
		} else {
			$object->setUpdateDate(Mage::getSingleton('core/date')->gmtDate());
		}
	}
}

<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Source_Orderyesno extends Belitsoft_Sugarcrm_Model_Source_Abstract
{
	public function toOptionArray()
	{
		return array(
			array('value' => Belitsoft_Sugarcrm_Model_Config::ORDER_SYNCH_ENABLE,					'label' => Mage::helper('cms')->__('No')),
			array('value' => Belitsoft_Sugarcrm_Model_Config::ORDER_SYNCH_DISABLE,					'label' => Mage::helper('cms')->__('Yes')),
			array('value' => Belitsoft_Sugarcrm_Model_Config::ORDER_SYNCH_ENABLE_WITH_CONDITION,	'label' => Mage::helper('cms')->__('Enable with condition')),
		);
	}
}
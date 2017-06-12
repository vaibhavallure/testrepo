<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Source_Objects extends Belitsoft_Sugarcrm_Model_Source_Abstract
{
	public function toOptionArray()
	{
		return array(
			array('value' => Belitsoft_Sugarcrm_Model_Error::TYPE_CUSTOMER,	'label' => Mage::helper('sugarcrm')->__('Customer')),
			array('value' => Belitsoft_Sugarcrm_Model_Error::TYPE_QUOTE,	'label' => Mage::helper('sugarcrm')->__('Quote')),
			array('value' => Belitsoft_Sugarcrm_Model_Error::TYPE_ORDER,	'label' => Mage::helper('sugarcrm')->__('Order')),
		);
	}
}

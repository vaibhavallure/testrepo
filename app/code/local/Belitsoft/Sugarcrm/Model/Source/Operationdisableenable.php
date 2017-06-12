<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Source_Operationdisableenable extends Belitsoft_Sugarcrm_Model_Source_Abstract
{
	public function toOptionArray()
	{
		return array(
			array('value' => Belitsoft_Sugarcrm_Model_Operations::OPERATION_DISABLE,				'label' => Mage::helper('sugarcrm')->__('Disable')),
			array('value' => Belitsoft_Sugarcrm_Model_Operations::OPERATION_ENABLE,					'label' => Mage::helper('sugarcrm')->__('Enable')),
			array('value' => Belitsoft_Sugarcrm_Model_Operations::OPERATION_ENABLE_WITH_CONDITION,	'label' => Mage::helper('sugarcrm')->__("Enable with condition")),
		);
	}
}
<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Source_Mappings extends Belitsoft_Sugarcrm_Model_Source_Abstract
{
	public function toOptionArray()
	{
		return array(
			array('value' => Belitsoft_Sugarcrm_Model_Connection::SYNC_MAGEFIELD,	'label' => Mage::helper('sugarcrm')->__('Magento Customer Field')),
			array('value' => Belitsoft_Sugarcrm_Model_Connection::SYNC_CUSTOM,		'label' => Mage::helper('sugarcrm')->__('Custom Model')),
			array('value' => Belitsoft_Sugarcrm_Model_Connection::SYNC_EVALCODE,	'label' => Mage::helper('sugarcrm')->__("PHP code")),
		);
	}
}
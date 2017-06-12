<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Source_Orderbeans extends Belitsoft_Sugarcrm_Model_Source_Abstract
{
	public function toOptionArray()
	{
		return array(
			array('value' => Belitsoft_Sugarcrm_Model_Connection::OPPORTUNITIES,	'label' => Mage::helper('sugarcrm')->__('Opportunities')),
			array('value' => Belitsoft_Sugarcrm_Model_Connection::CASES,			'label' => Mage::helper('sugarcrm')->__('Cases')),
		);
	}
}
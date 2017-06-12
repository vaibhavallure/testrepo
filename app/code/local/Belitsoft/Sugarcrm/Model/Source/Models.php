<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Source_Models extends Belitsoft_Sugarcrm_Model_Source_Abstract
{
	const QUOTE = 'quote';
	const ORDER = 'order';

	public function toOptionArray()
	{
		return array(
			array('value' => '',			'label' => Mage::helper('sugarcrm')->__('Customers')),
			array('value' => self::ORDER,	'label' => Mage::helper('sugarcrm')->__('Orders')),
			array('value' => self::QUOTE,	'label' => Mage::helper('sugarcrm')->__('Quotes')),
		);
	}
}

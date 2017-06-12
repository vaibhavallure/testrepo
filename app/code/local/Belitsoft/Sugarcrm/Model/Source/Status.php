<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Source_Status extends Belitsoft_Sugarcrm_Model_Source_Abstract
{
	public function toOptionArray()
	{
		return array(
			array('value' => Belitsoft_Sugarcrm_Model_Error::STATUS_NEEDRESYNC,	'label' => Mage::helper('sugarcrm')->__('Need re-sync')),
			array('value' => Belitsoft_Sugarcrm_Model_Error::STATUS_RESYNCED,	'label' => Mage::helper('sugarcrm')->__('Re-synced')),
			array('value' => Belitsoft_Sugarcrm_Model_Error::STATUS_CANTSYNCED,	'label' => Mage::helper('sugarcrm')->__("Can't re-sync")),
		);
	}
}
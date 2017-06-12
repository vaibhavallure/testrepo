<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Source_Customs extends Belitsoft_Sugarcrm_Model_Source_Abstract
{
	public function toOptionArray()
	{
		return Mage::getModel('sugarcrm/custom')->toOptionArray();
	}
}
<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

if(Mage::helper('sugarcrm/version')->isEE()) {
	class Belitsoft_Sugarcrm_Helper_Data extends Belitsoft_Sugarcrm_Helper_Enterprise
	{
	}
} else {
	class Belitsoft_Sugarcrm_Helper_Data extends Belitsoft_Sugarcrm_Helper_Community
	{
	}
}
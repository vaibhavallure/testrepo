<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */
 
if(version_compare(Mage::getVersion(), '1.7.0.0', '>') === true) {
	class Belitsoft_Sugarcrm_Block_Adminhtml_System_Config_Form_Fieldset extends Belitsoft_Sugarcrm_Block_Adminhtml_System_Config_Form_Abstract
	{
		protected function _getFieldsetCss($element = null)
		{
			return Belitsoft_Sugarcrm_Block_Adminhtml_System_Config_Form_Abstract::CLASS_CONFIG_COLLAPSEABLE;
		}
	}
} else {
	class Belitsoft_Sugarcrm_Block_Adminhtml_System_Config_Form_Fieldset extends Belitsoft_Sugarcrm_Block_Adminhtml_System_Config_Form_Abstract
	{
		protected function _getFieldsetCss()
		{
			return Belitsoft_Sugarcrm_Block_Adminhtml_System_Config_Form_Abstract::CLASS_CONFIG_COLLAPSEABLE;
		}
	}
}
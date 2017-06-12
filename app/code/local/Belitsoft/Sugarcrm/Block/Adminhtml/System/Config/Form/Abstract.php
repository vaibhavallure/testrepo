<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_System_Config_Form_Abstract extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
	/**
	 * Full css class name for form fieldset
	 */
	const CLASS_CONFIG_COLLAPSEABLE = 'config collapseable';
	
	/**
	 * Collapsed or expanded fieldset when page loaded?
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return bool
	 */
	protected function _getCollapseState($element)
	{
		$extra = Mage::getSingleton('adminhtml/session')->getSugarcrmData();
		if (isset($extra['operationFieldsetStates'][$element->getId()])) {
			return $extra['operationFieldsetStates'][$element->getId()];
		}

		if ($element->getExpanded() !== null) {
			return 1;
		}
		
		return false;
	}
}

<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Errors_Grid_Column_Renderer_Operation
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/**
	 * Renders grid column
	 *
	 * @param Varien_Object $row
	 * @return string
	 */
	public function render(Varien_Object $row)
	{
		if ($data = $row->getData($this->getColumn()->getIndex())) {
			return preg_replace('/^(process)/is', '', $data);
		}
		
		return $this->getColumn()->getDefault();
	}
}

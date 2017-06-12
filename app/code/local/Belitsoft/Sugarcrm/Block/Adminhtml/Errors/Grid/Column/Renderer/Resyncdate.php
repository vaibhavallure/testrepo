<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Errors_Grid_Column_Renderer_Resyncdate
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Datetime
{
	/**
	 * Renders grid column
	 *
	 * @param Varien_Object $row
	 * @return string
	 */
	public function render(Varien_Object $row)
	{
		if (($data = $row->getData($this->getColumn()->getIndex()))
			&& ($data == '0000-00-00 00:00:00' || $data == '0000-00-00' || $data == ''))
		{
			return $this->getColumn()->getDefault();
		}
		
		return parent::render($row);
	}
}

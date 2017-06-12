<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Operations_Userorder_Stages_Cases extends Belitsoft_Sugarcrm_Block_Adminhtml_Operations_Userorder_Stages_Opportunities
{
	public function getStageType()
	{
		return 'opportunities';
	}
}

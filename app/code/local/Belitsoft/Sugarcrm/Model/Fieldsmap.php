<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Fieldsmap extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
		parent::_construct();

		$this->_init('sugarcrm/fieldsmap');
	}
}

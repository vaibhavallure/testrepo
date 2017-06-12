<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Source_Abstract
{
	public function toOptionArray()
	{
		return array();
	}

	public function toOptionHash()
	{
		$hash = array();
		foreach($this->toOptionArray() as $item) {
			$hash[$item['value']] = $item['label'];
		}
		
		return $hash;
	}
}

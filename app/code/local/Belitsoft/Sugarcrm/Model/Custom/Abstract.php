<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

abstract class Belitsoft_Sugarcrm_Model_Custom_Abstract
{
	abstract public function get($customer, $bean_name, $sugarcrm_field, $params=array());
}
<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Source_Beans extends Belitsoft_Sugarcrm_Model_Source_Abstract
{
	public function toOptionArray()
	{
		$return = array();
		
		$beans = Mage::getSingleton('sugarcrm/config')->getBeans();
		foreach($beans as $name=>$bean) {
			$bean['value'] = $name;
			$return[] = $bean;
		}
		
		$return[] = array('label' => Belitsoft_Sugarcrm_Model_Connection::OPPORTUNITIES, 'value' => Belitsoft_Sugarcrm_Model_Connection::OPPORTUNITIES);
		$return[] = array('label' => Belitsoft_Sugarcrm_Model_Connection::CASES, 'value' => Belitsoft_Sugarcrm_Model_Connection::CASES);
		
		return $return;
	}
}

<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Custom_Companyorname extends Belitsoft_Sugarcrm_Model_Custom_Abstract
{
	public function get($customer, $bean_name, $sugarcrm_field, $params=array())
	{
		$return = $customer->getFirstname() . ' ' . $customer->getLastname();
		
		if($default_billing = $customer->getDefaultBilling()) {
			$address = $customer->getAddressById($default_billing);
		} else if($default_shipping = $customer->getDefaultShipping()) {
			$address = $customer->getAddressById($default_shipping);
		}
		
		if(empty($address) || !$address->getCompany()) {
			return $return;
		}
		
		return $address->getCompany();
	}
}
<?php
/**
 * Apple Pay
 *
 * @category    Allure
 * @package     Allure_ApplePay
 * @copyright   Copyright (c) 2017 Allure Inc
 * @license     http://opensource.org/licenses/Apache-2.0  Apache License, Version 2.0
 */

class Allure_ApplePay_Block_Info extends Mage_Payment_Block_Info
{
	protected function _prepareSpecificInformation($transport = null)
	{
	    $transport	= parent::_prepareSpecificInformation($transport);
	    return $transport;
	}
}

<?php

class Allure_ApplePay_Block_Info extends Mage_Payment_Block_Info
{
	protected function _prepareSpecificInformation($transport = null)
	{
	    $transport	= parent::_prepareSpecificInformation($transport);
	    return $transport;
	}
}

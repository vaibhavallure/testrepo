<?php

class Allure_ApplePay_Block_Form extends Mage_Payment_Block_Form
{
	/**
	 * Instantiate with ACH payment form.
	 */
	protected function _construct()
	{
		parent::_construct();
		
		$this->setTemplate('allure/applepay/form.phtml');
	}
}

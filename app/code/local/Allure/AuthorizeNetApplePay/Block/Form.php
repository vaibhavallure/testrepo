<?php

class Allure_AuthorizeNetApplePay_Block_Form extends Mage_Payment_Block_Form
{
	/**
	 * Instantiate with ACH payment form.
	 */
	protected function _construct()
	{
		parent::_construct();
		
		$this->setTemplate('allure/authnetapplepay/form.phtml');
	}
}

<?php

class Allure_ApplePay_Model_Method extends Mage_Payment_Model_Method_Abstract
{
	protected $_formBlockType			= 'applepay/form';
	protected $_infoBlockType			= 'applepay/info';
	protected $_code						= 'applepay';
	
	protected $_canFetchTransactionInfo	= true;
	
	/**
	 * Update the CC info during the checkout process.
	 */
	public function assignData( $data )
	{
		if (!($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}
		
		parent::assignData( $data );
		
		return $this;
	}

	/**
	 * Validate the transaction inputs.
	 */
	public function validate()
	{
		return parent::validate();
	}
	
	/**
	 * Determine whether Accept.js is configured.
	 */
	public function isAcceptJsEnabled()
	{
	    $authnetcim = Mage::getSingleton('authnetcim/method');
		
	    return $authnetcim->isAcceptJsEnabled();
	}
}

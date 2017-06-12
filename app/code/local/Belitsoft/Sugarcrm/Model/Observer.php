<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Observer
{
	function processSaveCustomerBefore($observer)
	{
		if($this->_disabled()) {
			return;
		}
		
		$customer = $observer->getEvent()->getCustomer();
		if (($customer instanceof Mage_Customer_Model_Customer)) {
			$this->_isNewCustomer($customer, true);
		}

		return;
	}

	function processSaveCustomer($observer)
	{
		if($this->_disabled()) {
			return;
		}

		try {
			$customer = $observer->getEvent()->getCustomer();
			if (($customer instanceof Mage_Customer_Model_Customer)) {
				$isNew = $this->_isNewCustomer($customer);
				$customer->setIsCustomerNew($isNew);
				$operation = $isNew ? Belitsoft_Sugarcrm_Model_Connection::OPERATION_INSERT : Belitsoft_Sugarcrm_Model_Connection::OPERATION_UPDATE;
				
				Mage::getModel('sugarcrm/connection')->synchCustomer($customer, $operation);
			}
		} catch(Exception $e) {
			Mage::logException($e);
			if($this->_errors() && ($session = Mage::getSingleton('core/session'))) {
				$session->addError($e->getMessage());
			}
			Mage::getModel('sugarcrm/error')->addErrorParams(array('operation'=>$operation), Belitsoft_Sugarcrm_Model_Error::TYPE_CUSTOMER);
			Mage::getModel('sugarcrm/error')->addError(Belitsoft_Sugarcrm_Model_Error::TYPE_CUSTOMER, __FUNCTION__, $customer, $e);
		}

		return;
	}

	function processSaveCustomerAddress($observer)
	{
		if($this->_disabled()) {
			return;
		}

		if(Mage::app()->getStore()->isAdmin()) {
			return;
		}

		try {
			$customer = null;
			if ($observer->getEvent()->getCustomerAddress()->getCustomerId()) {
				$customer = Mage::getModel('customer/customer')
					->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
				$customer->load($observer->getEvent()->getCustomerAddress()->getCustomerId());
				$customer->getAddresses();
			}
			if (($customer instanceof Mage_Customer_Model_Customer)) {
				Mage::getModel('sugarcrm/connection')->synchCustomer($customer);
			}
		} catch(Exception $e) {
			Mage::logException($e);
			if($this->_errors() && ($session = Mage::getSingleton('core/session'))) {
				$session->addError($e->getMessage());
			}
			Mage::getModel('sugarcrm/error')->addError(Belitsoft_Sugarcrm_Model_Error::TYPE_CUSTOMER, __FUNCTION__, $customer, $e);
		}

		return;
	}

	function processDeleteBeforeCustomer($observer)
	{
		if($this->_disabled()) {
			return;
		}

		try {
			$customer = $observer->getEvent()->getCustomer();
			if (($customer instanceof Mage_Customer_Model_Customer)) {
				Mage::getModel('sugarcrm/connection')->setSynchDataBeforeDelete($customer);
			}
		} catch(Exception $e) {
			Mage::logException($e);
			if($this->_errors() && ($session = Mage::getSingleton('core/session'))) {
				$session->addError($e->getMessage());
			}
			Mage::getModel('sugarcrm/error')->addErrorParams(array('entity_id'=>$customer->getId()), Belitsoft_Sugarcrm_Model_Error::TYPE_CUSTOMER);
		}

		return;
	}

	function processDeleteAfterCustomer($observer)
	{
		if($this->_disabled()) {
			return;
		}

		try {
			$customer = $observer->getEvent()->getCustomer();
			if (($customer instanceof Mage_Customer_Model_Customer)) {
				Mage::getModel('sugarcrm/connection')->synchCustomer($customer, Belitsoft_Sugarcrm_Model_Connection::OPERATION_DELETE);
			}
		} catch(Exception $e) {
			Mage::logException($e);
			if($this->_errors() && ($session = Mage::getSingleton('core/session'))) {
				$session->addError($e->getMessage());
			}
			Mage::getModel('sugarcrm/error')->addError(Belitsoft_Sugarcrm_Model_Error::TYPE_CUSTOMER, __FUNCTION__, $customer, $e);
		}

		return;
	}

	function processSalesOrderSaveAfter($observer)
	{
		if($this->_disabled()) {
			return;
		}

		if(!Mage::getModel('sugarcrm/config')->isEnabledUserOrdersSynch()) {
			return;
		}

		try {
			$order = $observer->getEvent()->getOrder();
			if ($order instanceof Mage_Sales_Model_Order) {
				$order->setOrderObjectName(Belitsoft_Sugarcrm_Model_Synchmap::ORDER_MODEL);
				Mage::getModel('sugarcrm/connection')->synchOrder($order);
			}
		} catch(Exception $e) {
			Mage::logException($e);
			if($this->_errors() && ($session = Mage::getSingleton('core/session'))) {
				$session->addError($e->getMessage());
			}
			Mage::getModel('sugarcrm/error')->addError(Belitsoft_Sugarcrm_Model_Error::TYPE_ORDER, __FUNCTION__, $order, $e);
		}

		return;
	}

	function processSalesOrderDeleteAfter($observer)
	{
		if($this->_disabled()) {
			return;
		}

		if(!Mage::getModel('sugarcrm/config')->isEnabledUserOrdersSynch()) {
			return;
		}

		try {
			$order = $observer->getEvent()->getOrder();
			if ($order instanceof Mage_Sales_Model_Order) {
				$order->setOrderObjectName(Belitsoft_Sugarcrm_Model_Synchmap::ORDER_MODEL);
				Mage::getModel('sugarcrm/connection')->synchOrder($order, Belitsoft_Sugarcrm_Model_Connection::OPERATION_DELETE);
			}
		} catch(Exception $e) {
			Mage::logException($e);
			if($this->_errors() && ($session = Mage::getSingleton('core/session'))) {
				$session->addError($e->getMessage());
			}
			Mage::getModel('sugarcrm/error')->addError(Belitsoft_Sugarcrm_Model_Error::TYPE_ORDER, __FUNCTION__, $order, $e);
		}

		return;
	}

	function processSalesQuoteMergeAfter($observer)
	{
		if($this->_disabled()) {
			return;
		}

		if(!Mage::getModel('sugarcrm/config')->isEnabledUserOrdersSynch()
			|| (!Mage::helper('sugarcrm')->isCheckoutSynchEnabled()
				&& !Mage::helper('sugarcrm')->isCartSynchEnabled()))
		{
			return;
		}

		try {
			$quoteItem = $observer->getEvent()->getQuote();
			$sourceItem = $observer->getEvent()->getSource();
			if(($quoteItem instanceof Mage_Sales_Model_Quote)
				&& ($sourceItem instanceof Mage_Sales_Model_Quote))
			{
				Mage::getModel('sugarcrm/connection')->setSalesQuoteMergeAfter($quoteItem, $sourceItem);
			}
		} catch(Exception $e) {
			Mage::logException($e);
			if($this->_errors() && ($session = Mage::getSingleton('core/session'))) {
				$session->addError($e->getMessage());
			}
			
			Mage::getModel('sugarcrm/error')->addErrorParams(array('merge'=>array('quote'=>$quoteItem->getId(), 'source'=>$sourceItem->getId())), Belitsoft_Sugarcrm_Model_Error::TYPE_QUOTE);
		}

		return;
	}

	function processSalesQuoteSaveAfter($observer)
	{
		if($this->_disabled()) {
			return;
		}

		$isCheckoutSynchEnabled = Mage::helper('sugarcrm')->isCheckoutSynchEnabled();
		$isCartSynchEnabled = Mage::helper('sugarcrm')->isCartSynchEnabled();
		if(!Mage::getModel('sugarcrm/config')->isEnabledUserOrdersSynch() || (!$isCheckoutSynchEnabled && !$isCartSynchEnabled)) {
			return;
		}

		try {
			$quoteItem = $observer->getEvent()->getQuote();
			if (($quoteItem instanceof Mage_Sales_Model_Quote) && $quoteItem->getIsActive()) {
				$controllerName = Mage::app()->getFrontController()->getRequest()->getControllerName();
			
				$state = null;
				if($controllerName == 'cart') {
					if(!$isCartSynchEnabled) {
						return;
					}
					
					$state = Belitsoft_Sugarcrm_Model_Stages::SAVE_CART_STAGE;
					$quoteItem->setState($state);

				} else {
					if(!$isCheckoutSynchEnabled) {
						return;
					}
					
					$state = Belitsoft_Sugarcrm_Model_Stages::CHECKOUT_STAGE;
					$quoteItem->setState($state);
				}

				$quoteItem->setOrderObjectName(Belitsoft_Sugarcrm_Model_Synchmap::QUOTE_MODEL);

				Mage::getModel('sugarcrm/connection')->synchOrder($quoteItem);
			}
		} catch(Exception $e) {
			Mage::logException($e);
			if($this->_errors() && ($session = Mage::getSingleton('core/session'))) {
				$session->addError($e->getMessage());
			}
			if(!empty($state)) {
				Mage::getModel('sugarcrm/error')->addErrorParams(array('state'=>$state), Belitsoft_Sugarcrm_Model_Error::TYPE_QUOTE);
			}
			Mage::getModel('sugarcrm/error')->addError(Belitsoft_Sugarcrm_Model_Error::TYPE_QUOTE, __FUNCTION__, $quoteItem, $e);
		}

		return;
	}
	
	protected function _isNewCustomer($customer, $set=false)
	{
		static $isNewCustomer = array();
		
		$email = $customer->getData('email');
		if($set && !$customer->getData('entity_id')) {
			$isNewCustomer[$email] = true;
		}
		
		return !empty($isNewCustomer[$email]);
	}
	
	protected function _disabled()
	{
		static $isDisabled;
		
		if(is_null($isDisabled)) {
			$isDisabled = Mage::helper('sugarcrm')->isBridgeDisabled();
		}
		
		return $isDisabled;
	}
	
	protected function _errors()
	{
		static $show;
		
		if(is_null($show)) {
			$show = Mage::helper('sugarcrm')->showErrors();
		}
		
		return $show;
	}
}
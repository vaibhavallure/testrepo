<?php
class Allure_SmartAnalytics_Block_Ga extends Mage_Core_Block_Template
{
	/**
	 * Google Analytics Page Types
	 */
	private $_allowedPageTypes 	= array('category','product','cart','checkout','purchase','other');

	/**
	 * Default pagetype
	 */
	private $_pagetype			= 'other';

	/**
	 * Set current pagetype
	 * @param string
	 */
	public function setPageType($pagetype){
		if(in_array(strtolower($pagetype),$this->_allowedPageTypes)){
			$this->_pagetype = strtolower($pagetype);
		}
	}

	/**
	 * get current pagetype
	 * @param string
	 */
	public function getPageType(){
		return $this->_pagetype;
	}

	/**
     * Retrieve current order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        return Mage::getModel('sales/order')->load($orderId);
    }

	/**
     * Retrieve current page URL
     *
     * @return string
     */
    public function getPageName()
    {
        if (!$this->hasData('page_name')) {
            $this->setPageName(Mage::getSingleton('core/url')->escape($_SERVER['REQUEST_URI']));
        }
        return $this->getData('page_name');
    }

	/**
     * Retrieve domain url without www or subdomain
     *
     * @return string
     */
    public function getMainDomain()
    {
        if (!$this->hasData('main_domain')) {
			$host = $this->getRequest()->getHttpHost();
			if (substr_count($host,'.')>1 && (!Mage::helper('allure_smartanalytics')->isDomainAuto())){
				$this->setMainDomain(substr($host,strpos($host,'.')+1));
			}
			else{
				$this->setMainDomain('auto');
			}
        }
        return $this->getData('main_domain');
    }

	/**
     * Return if it is order confirmation page or not and e-commerce tracking is on
     *
     * @return boolean
     */
    public function isEcommerce()
    {
		if ((strpos($this->getPageName(), 'success')!==false) && (strpos($this->getPageName(), 'checkout')!==false) && (Mage::helper('allure_smartanalytics')->isEcommerceEnabled())){
			return true;
		}
		return false;
    }
}

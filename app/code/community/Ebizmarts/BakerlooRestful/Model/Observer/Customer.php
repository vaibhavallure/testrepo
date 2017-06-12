<?php

class Ebizmarts_BakerlooRestful_Model_Observer_Customer
{

    /**
     * Customer delete handler
     *
     * @param Varien_Object $observer
     * @return Mage_Newsletter_Model_Observer
     */
    public function customerDeleted($observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        if ($customer->getId()) {
            $trash = $this->getCustomerTrash();
            $trash->setCustomerId($customer->getId());
            $trash->save();
        }

        return $this;
    }

    public function customerSaveAfter(Varien_Event_Observer $observer) {
        $cacheAllowed = $this->getApiHelper()->config('customer/allow_customer_caching', Mage::app()->getStore()->getId());

        if ($cacheAllowed) {
            $customer = $observer->getEvent()->getCustomer();
            $this->_resetCacheForCustomer($customer);
        }
    }

    public function customerWishlistSaveAfter(Varien_Event_Observer $observer)
    {
        $cacheAllowed = $this->getApiHelper()->config('customer/allow_customer_caching', Mage::app()->getStore()->getId());

        if ($cacheAllowed) {
            /** @var Mage_Wishlist_Model_Wishlist $wishlist */
            $wishlist = $observer->getEvent()->getObject();

            /** @var Mage_Customer_Model_Customer $customer */
            $customer = $this->getCustomerModel()->load($wishlist->getCustomerId());

            if ($customer->getId()) {
                $this->_resetCacheForCustomer($customer);
            }
        }
    }

    public function getApiHelper()
    {
        return Mage::helper('bakerloo_restful');
    }

    public function getCustomerTrash()
    {
        return Mage::getModel('bakerloo_restful/customertrash');
    }

    public function getCustomerModel()
    {
        return Mage::getModel('customer/customer');
    }


    private function _resetCacheForCustomer(Mage_Customer_Model_Customer $customer) {
        Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($customer->getId()));
    }
}

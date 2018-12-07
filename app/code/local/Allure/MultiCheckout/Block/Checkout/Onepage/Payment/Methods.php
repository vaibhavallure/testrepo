<?php

class Allure_MultiCheckout_Block_Checkout_Onepage_Payment_Methods extends Mage_Checkout_Block_Onepage_Payment_Methods
{

    public function isWholeSaleCustomer ()
    {
        $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $role = Mage::getSingleton('customer/group')->load($roleId)->getData('customer_group_code');
        $role = strtolower($role);
        
        if ('wholesale' == strtolower($role)) {
            return true;
        } else {
            return false;
        }
    }

    private function getPaymentMethodsByCustomerRoles ($method)
    {
        $quote = $this->getQuote();
        $status = false;
        $helper = Mage::helper('allure_multicheckout');
        
        if ($this->isWholeSaleCustomer()) {
            $payment_methods = $helper->getWholeCustomerPaymentMethods();
            if (in_array($method, $payment_methods))
                $status = true;
        } else {
            $payment_methods_retailer = $helper->getRetailerCustomerPaymentMethods();
            if (in_array($method, $payment_methods_retailer))
                $status = true;
        }
        return $status;
    }

    public function getMethods ()
    {
        $methods = $this->getData('methods');
        if ($methods === null) {
            $quote = $this->getQuote();
            $store = $quote ? $quote->getStoreId() : null;
            $methods = array();
            foreach ($this->helper('payment')->getStoreMethods($store, $quote) as $method) {
                if ($this->_canUseMethod($method) && $method->isApplicableToQuote($quote,
                        Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL)) {
                    if ($this->getPaymentMethodsByCustomerRoles($method->getCode())) {

                        /*-----temporary paypal payement method disabled for two ship method  */

                        if($this->isDeleiveryMethodTwoShipment() && $method->getCode()=="paypal_express")
                            continue;

                        $this->_assignMethod($method);
                        $methods[] = $method;
                    }
                }
            }
            $this->setData('methods', $methods);
        }
        return $methods;
    }

    public function getPayNowOptionsMenthods ()
    {
        $helper = Mage::helper('allure_multicheckout');
        return $helper->getWholesaleCustomersPayNowMethods();
    }

    public function getPayAsShipOptionsMethods ()
    {
        $helper = Mage::helper('allure_multicheckout');
        return $helper->getWholesaleCustomersPayAsShipMethods();
    }

    public function isContainsOutofStockProducts ()
    {
        return $this->isQuoteContainsBackorder();
    }

    private function isQuoteContainsBackorder ()
    {
        $isBackorderAvailable = false;
        $quote = $this->getQuote();
        $qouteItems = $quote->getAllVisibleItems(); // getAllItems();
        foreach ($qouteItems as $item) :
            $productInventoryQty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProduct())
                ->getQty();
            
            $stock_qty = intval($item->getProduct()
                ->getStockItem()
                ->getQty());
            if ($stock_qty < $item->getQty() && $item->getProduct()
                ->getStockItem()
                ->getIsInStock()) :
                // if($productInventoryQty<=0):
                $isBackorderAvailable = true;
                break;
	       	endif;
            
        endforeach;
        
        return $isBackorderAvailable;
    }
    public  function isDeleiveryMethodTwoShipment(){
        if($this->getQuote()->getDeliveryMethod()=='two_ship')
            return TRUE;
        else 
            return FALSE;
    }
}

<?php

class Allure_MultiCheckout_Helper_Data extends Mage_Customer_Helper_Data
{

    const ONE_SHIP = 'one_ship';

    const TWO_SHIP = 'two_ship';

    const PAY_NOW = 'pay_now';

    const PAY_AS_SHIP = 'pay_as_ship';

    const SAME = 1;

    const DIFFERENT = 2;

    const SINGLE_ORDER = 'Single';

    const MULTI_MAIN_ORDER = 'Multiple - Main';

    const MULTI_BACK_ORDER = 'Multiple - Backorder';

    const XML_PATH_RETAILER_CUSTOMER_PAYMENT_METHODS = 'allure_multicheckout/retail/payment_methods';

    const XML_PATH_WHOLESALE_CUSTOMER_PAY_NOW_OPTIONS = 'allure_multicheckout/wholesale/payment_methods_pay_now';

    const XML_PATH_WHOLESALE_CUSTOMER_PAY_AS_SHIP_OPTIONS = 'allure_multicheckout/wholesale/payment_methods_pay_as_ship';

    public function getWholeCustomerPaymentMethods ()
    {
        $pay1 = $this->getWholesaleCustomersPayNowMethods();
        $pay2 = $this->getWholesaleCustomersPayAsShipMethods();
        $newArr = array_merge($pay1, $pay2);
        $newArr = array_unique($newArr);
        return $newArr;
    }

    public function getRetailerCustomerPaymentMethods ()
    {
        return $this->getPaymentMethods(self::XML_PATH_RETAILER_CUSTOMER_PAYMENT_METHODS, array());
    }

    public function getWholesaleCustomersPayNowMethods ()
    {
        $payment_methods = array(
                'purchaseorder',
                'ccsave',
                'banktransfer'
        );
        return $this->getPaymentMethods(self::XML_PATH_WHOLESALE_CUSTOMER_PAY_NOW_OPTIONS, $payment_methods);
    }

    public function getWholesaleCustomersPayAsShipMethods ()
    {
        $payment_methods = array(
                'ccsave',
                'checkmo'
        );
        return $this->getPaymentMethods(self::XML_PATH_WHOLESALE_CUSTOMER_PAY_AS_SHIP_OPTIONS, $payment_methods);
    }

    public function getPaymentMethods ($path, $defaultPayment)
    {
        $payment_methods = Mage::getStoreConfig($path);
        if ($payment_methods) {
            return explode(",", $payment_methods);
        }
        return $defaultPayment;
    }

    private function getQuote ()
    {
        return Mage::getSingleton("checkout/session")->getQuote();
    }

    public function isQuoteContainOutOfStockProducts ()
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
            
        endforeach
        ;
        return $isBackorderAvailable;
    }

    public function getFedexFreeShippingMethod ()
    {
        return Mage::getStoreConfig('carriers/fedex/free_method');
    }

    public function checkWholeSaleCustomer ()
    {
        $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $role = Mage::getSingleton('customer/group')->load($roleId)->getData('customer_group_code');
        $role = strtolower($role);
        
        if ('wholesale' == strtolower($role))
            return true;
        else
            return false;
    }
}

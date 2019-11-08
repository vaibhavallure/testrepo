<?php
/**
 * helper class
 * 
 */
class Allure_MultiCheckout_Helper_Data extends Mage_Customer_Helper_Data
{
    const ONE_SHIP          = 'one_ship';
    const TWO_SHIP          = 'two_ship';
    const PAY_NOW           = 'pay_now';
    const PAY_AS_SHIP       = 'pay_as_ship';
    const SAME              = 1;
    const DIFFERENT         = 2;
    const SINGLE_ORDER      = 'Single';
    const MULTI_MAIN_ORDER  = 'Multiple - Main';
    const MULTI_BACK_ORDER  = 'Multiple - Backorder';
    
    const XML_PATH_RETAILER_CUSTOMER_PAYMENT_METHODS = 'allure_multicheckout/retail/payment_methods';
    const XML_PATH_WHOLESALE_CUSTOMER_PAY_NOW_OPTIONS = 'allure_multicheckout/wholesale/payment_methods_pay_now';
    const XML_PATH_WHOLESALE_CUSTOMER_PAY_AS_SHIP_OPTIONS = 'allure_multicheckout/wholesale/payment_methods_pay_as_ship';
    const XML_PATH_ONEPAGE_LOG = 'allure_multicheckout/multi_log/multi_log_status';

    /**
     * get quote object.
     */
    private function getQuote (){
        return Mage::getSingleton("checkout/session")->getQuote();
    }
    
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

    

    public function isQuoteContainOutOfStockProducts ()
    {
        return $this->isQuoteContainsBackorderProduct();
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
    
    /**
     * return true|false if quote contains out of stock product
     */
    public function isQuoteContainsBackorderProduct(){
        $quote = $this->getQuote();
        $qouteItems = $quote->getAllVisibleItems();
        $storeId = Mage::app()->getStore()->getStoreId();
        $isBackOrderProduct = false;
        foreach ($qouteItems as $item){
            $sku = $item->getSku();
            $_product = Mage::getModel('catalog/product')
                            ->setStoreId($storeId)
                            ->loadByAttribute('sku',$sku);
           $stock = Mage::getModel('cataloginventory/stock_item')
            ->loadByProduct($_product);
           $stock_qty=$stock->getQty();
           if ($stock_qty < $item->getQty() && $stock->getManageStock()==1) {
               $isBackOrderProduct = true;
               break;
           }
        }
        return $isBackOrderProduct;
    }
    
    
    /**
     * get is quote contain out of stock item.
     */
    public function getQuoteItemStockStatus(){
        $quote = $this->getQuote();
        $collection = Mage::getModel("sales/quote_item")->getCollection();
        $collection->getSelect()->join(
            array("stock_item" => "cataloginventory_stock_item"),
            "(stock_item.product_id = main_table.product_id )",
            array("sum(if(stock_item.qty >= main_table.qty,1,0)) 'instock'", "sum(if(stock_item.qty >= main_table.qty,0,1)) 'backorder'", "sum(if(stock_item.qty >= main_table.qty, 0, if(stock_item.qty > 0,1,0) )) 'available'")
        );
        $collection->getSelect()->where("main_table.quote_id = {$quote->getId()} AND product_type NOT IN('configurable')");
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array("sum(if(stock_item.qty >= main_table.qty,1,0)) 'instock'", "sum(if(stock_item.qty >= main_table.qty,0,1)) 'backorder'", "sum(if(stock_item.qty >= main_table.qty, 0, if(stock_item.qty > 0,1,0) )) 'available'"));
        $firstRecord = isset($collection->getData()[0]) ? $collection->getData()[0] : array();
        Mage::log($collection->getSelect()->__toString(),Zend_Log::DEBUG,'abc.log',true);
        return $firstRecord;
    }

    public function getOnePagelogStatus(){
        return Mage::getStoreConfig(self::XML_PATH_ONEPAGE_LOG);
    }
    
    public function isTwoShipment(){
        $quote = $this->getQuote();
        $status = false;
        if(strtolower($quote->getDeliveryMethod()) == self::TWO_SHIP)
            $status = true;
            return $status;
    }
    
    public function changeCustomQuoteStatus(){
        $instockSession = Mage::getSingleton("allure_multicheckout/ordered_session");
        $backorderSession = Mage::getSingleton("allure_multicheckout/backordered_session");
        $orederdQuoteId = $instockSession->getOrdered();
        $backOrderdQuoteId = $backorderSession->getBackorder();
        if (isset($orederdQuoteId) && ! empty($orederdQuoteId)) {
            if ($instockSession->getQuote()->getId() != 0 && $instockSession->getQuote()->getId() != $this->getQuote()->getId()) {
                $instockSession->getQuote()
                    ->setIsActive(false)
                    ->save();
            }
            $instockSession->setOrdered(null);
        }
        
        if (isset($backOrderdQuoteId) && ! empty($backOrderdQuoteId)) {
            if ($backorderSession->getQuote()->getId() != 0 &&
                $backorderSession->getQuote()->getId() != $this->getQuote()->getId()) {
                $backorderSession->getQuote()
                    ->setIsActive(false)
                    ->save();
            }
            $backorderSession->setBackorder(null);
        }
    }
}

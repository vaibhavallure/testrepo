<?php

class Allure_MultiCheckout_Block_Checkout_Onepage_Deliveryoption extends Mage_Checkout_Block_Onepage_Abstract
{

    protected function _construct ()
    {
        $this->getCheckout()->setStepData("delivery_option",
            array(
                "label" => Mage::helper("checkout")->__("Delivery Option"),
                "is_show" => $this->isShow()
            ));
        parent::_construct();
    }

    public function getQuote ()
    {
        return Mage::getSingleton("checkout/session")->getQuote();
    }

    private function isQuoteContainsBackorder ()
    {
        $isBackorderAvailable = false;
        $quote = $this->getQuote();
        $qouteItems = $quote->getAllVisibleItems(); // getAllItems();
        $storeId = Mage::app()->getStore()->getStoreId();
        foreach ($qouteItems as $item) :
            $_product = Mage::getModel('catalog/product')->setStoreId($storeId)->loadByAttribute('sku',$item->getSku());
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
            $stock_qty=$stock->getQty();

            /* $stock_qty = intval($item->getProduct()
                ->getStockItem()
                ->getQty()); */
            if ($stock_qty < $item->getQty()&& $stock->getManageStock()==1) :
                // if($productInventoryQty<=0):
                $isBackorderAvailable = true;
                break;
            endif;

        endforeach
        ;
        return $isBackorderAvailable;
    }

    /* this function check if back order product contain any qty greater than one
     * jira number MT-906
     * start-----------------------
     * */
    private function isQuoteContainsBackorderWithInStockQty ()
    {
        $isBackorderWithInStockQtyAvailable = false;
        $quote = $this->getQuote();
        $qouteItems = $quote->getAllVisibleItems(); // getAllItems();
        $storeId = Mage::app()->getStore()->getStoreId();
        foreach ($qouteItems as $item) :
            $_product = Mage::getModel('catalog/product')->setStoreId($storeId)->loadByAttribute('sku',$item->getSku());
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
            $stock_qty=$stock->getQty();

            if ($stock_qty < $item->getQty()&& $stock->getManageStock()==1) :
                // if($productInventoryQty<=0):
                if($stock_qty>0) {
                    $isBackorderWithInStockQtyAvailable = true;
                    break;
                }
            endif;

        endforeach
        ;
        return $isBackorderWithInStockQtyAvailable;
    }
    /*
     * end--------------------
     * */

    private function isQuoteContainsAvailableProducts ()
    {
        $isAvailable = false;
        $quote = $this->getQuote();
        $qouteItems = $quote->getAllVisibleItems(); // getAllItems();
        $storeId = Mage::app()->getStore()->getStoreId();
        foreach ($qouteItems as $item) :
            /* $productInventoryQty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProduct())
                ->getQty();

            $stock_qty = intval($item->getProduct()
                ->getStockItem()
                ->getQty()); */
            $_product = Mage::getModel('catalog/product')->setStoreId($storeId)->loadByAttribute('sku',$item->getSku());
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
            $stock_qty=$stock->getQty();
            if (! ($stock_qty < $item->getQty()) || $stock->getManageStock()==0) :
                // if($productInventoryQty > 0):
                $isAvailable = true;
                break;
            endif;

        endforeach
        ;
        return $isAvailable;
    }

    public function isShowTwoShipment ()
    {
        //$isBackOrder = $this->isQuoteContainsBackorder();
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        $isBackOrder =  $_checkoutHelper->isQuoteContainsBackorderProduct();
        $isAvailable = $this->isQuoteContainsAvailableProducts();
        if ($isBackOrder && $isAvailable)
            return true;
        return true;
    }

    public function isContainsBackorder ()
    {
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        return $_checkoutHelper->isQuoteContainsBackorderProduct();
        //return $this->isQuoteContainsBackorder();
    }

    public function isUSCountry ()
    {
        $countryName = $this->getQuote()
            ->getShippingAddress()
            ->getData('country_id');

        $country = Mage::getModel('directory/country')->load($countryName);

        $isUSCountry = false;

        if ($country->getId() == "US")
            $isUSCountry = true;

        return $isUSCountry;
    }

    public function getStatus ()
    {
        $status = false;
        if ($this->isUSCountry()) {
            if ($this->isShowTwoShipment())
                $status = true;
        } /*
           * else{
           * if($this->isShowTwoShipment())
           * $status = true;
           * }
           */
        return $status;
    }

    public function getShipmentStatus ()
    {
        $is_inorder = $this->isQuoteContainsAvailableProducts();
        $is_backorder = $this->isQuoteContainsBackorder();



        $is_two_ship = $is_inorder && $is_backorder;

        /* this function check if back order product contain any qty greater than one
    * jira number MT-906
    * start-----------------------
    * */
        $is_backorder_with_some_available_qty=$this->isQuoteContainsBackorderWithInStockQty();
        if(!$is_two_ship && $is_backorder)
            $is_two_ship = $is_backorder_with_some_available_qty && $is_backorder;
        /*end----------------------------------------------*/



        $is_us = $this->isUSCountry();
        $is_us_two_Ship = $is_us && $is_two_ship;
        $status = array(
            'in_order' => $is_inorder,
            'back_order' => $is_backorder,
            'two_ship' => $is_two_ship,
            'us' => $is_us,
            'us_two_ship' => $is_us_two_Ship
        );
        return $status;
    }
}

<?php
class Allure_Catalog_Model_Product_Type_Price extends Mage_Catalog_Model_Product_Type_Price
{
    /**
     * override the method
     */
    protected function _getCustomerGroupId($product)
    {
        if ($product->getCustomerGroupId()) {
            return $product->getCustomerGroupId();
        }
        /** for admin order creation */
        if(Mage::app()->getStore()->isAdmin()){
            if(! Mage::getSingleton('customer/session')->getCustomerGroupId()){
                $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
                if($quote){
                    return $quote->getCustomerGroupId();
                }
            }
        }
        return Mage::getSingleton('customer/session')->getCustomerGroupId();
    }
}



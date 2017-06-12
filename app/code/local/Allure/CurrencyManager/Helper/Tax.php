<?php
/**
 * @category   Allure
 * @package    Allure_CurrencyManager
 * @copyright  Copyright (c) 2016 Allure Inc (http://www.allureinc.co)
 * @contacts   support@allureinc.co
 */

class Allure_CurrencyManager_Helper_Tax extends Mage_Tax_Helper_Data
{
    /**
     * Initialize helper instance
     *
     * @param array $args
     */
    public function  __construct(array $args = array())
    {
        $this->_config = Mage::getSingleton('tax/config');
        $this->_app = !empty($args['app']) ? $args['app'] : Mage::app();
    }

    /**
     * Get product price with all tax settings processing CM wrapper
     *
     * @param   Mage_Catalog_Model_Product $product
     * @param   float $price inputed product price
     * @param   bool $includingTax return price include tax flag
     * @param   null|Mage_Customer_Model_Address $shippingAddress
     * @param   null|Mage_Customer_Model_Address $billingAddress
     * @param   null|int $ctc customer tax class
     * @param   null|Mage_Core_Model_Store $store
     * @param   bool $priceIncludesTax flag what price parameter contain tax
     * @return  float
     */
    
    public function getPrice($product, $price, $includingTax = null, $shippingAddress = null, $billingAddress = null,
        $ctc = null, $store = null, $priceIncludesTax = null, $roundPrice = true
    ) {
        if (!$price) {
            return $price;
        }
        $store = Mage::app()->getStore($store);
        if (!$this->needPriceConversion($store)) {
            return $price;
        }
        Mage::app()->getStore($store)->setDoNotRoundIt(true);
        
        if (version_compare(Mage::getVersion(), '1.8.1', '>')) {
        	$result = parent::getPrice($product, $price, $includingTax, $shippingAddress, $billingAddress, $ctc,
            				$store, $priceIncludesTax, $roundPrice);
        } else {
        	$result = parent::getPrice($product, $price, $includingTax, $shippingAddress, $billingAddress, $ctc,
        					$store, $priceIncludesTax);
        }
        
        Mage::app()->getStore($store)->setDoNotRoundIt(false);
        return $result;
    }
}

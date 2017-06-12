<?php
/**
 * @category   Allure
 * @package    Allure_CurrencyManager
 * @copyright  Copyright (c) 2016 Allure Inc (http://www.allureinc.co)
 * @contacts   support@allureinc.co
 */

class Allure_CurrencyManager_Model_Store extends Mage_Core_Model_Store
{

    /**
     * Round price
     *
     * @param mixed $price
     * @return double
     */
    public function roundPrice($price)
    {
        // fixed double rounding for stores, which use non base display currency and product prices include taxes
        // http://support.etwebsolutions.com/issues/984
        if (Mage::app()->getStore()->getDoNotRoundIt()) {
            return $price;
        }


        $options = Mage::helper('currencymanager')->getOptions(array());
        $data = new Varien_Object(array(
            "price" => $price,
            "format" => $options,
        ));

        Mage::dispatchEvent("currency_options_after_get", array("options" => $data));
        $options = $data->getData("format");
        $price = $data->getData("price");

        return round($price, isset($options["precision"]) ? $options["precision"] : 2);
    }
}

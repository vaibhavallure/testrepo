<?php
/**
 * @category   Allure
 * @package    Allure_CurrencyManager
 * @copyright  Copyright (c) 2016 Allure Inc (http://www.allureinc.co)
 * @contacts   support@allureinc.co
 */
class Allure_CurrencyManager_Block_Adminhtml_Catalog_Product_Edit_Tab_Options_Option
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Option
{
    /**
     * Rewrite to use custom precision
     *
     * @param $value
     * @param $type
     * @return string
     */
    public function getPriceValue($value, $type)
    {
        $options = Mage::helper('currencymanager')->getOptions(array());

        if (isset($options["input_admin"]) && isset($options['precision'])) {
            if ($type == 'percent') {
                return number_format($value, $options['precision'], null, '');
            } elseif ($type == 'fixed') {
                return number_format($value, $options['precision'], null, '');
            }
        }

        return parent::getPriceValue($value, $type);
    }

}

<?php
/**
 * @category   Allure
 * @package    Allure_CurrencyManager
 * @copyright  Copyright (c) 2016 Allure Inc (http://www.allureinc.co)
 * @contacts   support@allureinc.co
 */

class Allure_CurrencyManager_Block_Adminhtml_Formprice  extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Price
{
    public function getEscapedValue($index=null)
    {
        $options = Mage::helper('currencymanager')->getOptions(array());
        $value = $this->getValue();

        if (!is_numeric($value)) {
            return null;
        }

        if (isset($options["input_admin"]) && isset($options['precision'])) {
            return number_format($value, $options['precision'], null, '');
        }

        return parent::getEscapedValue($index);
    }
}

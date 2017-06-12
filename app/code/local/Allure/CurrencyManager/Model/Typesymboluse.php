<?php
/**
 * @category   Allure
 * @package    Allure_CurrencyManager
 * @copyright  Copyright (c) 2016 Allure Inc (http://www.allureinc.co)
 * @contacts   support@allureinc.co
 */

class Allure_CurrencyManager_Model_Typesymboluse extends Varien_Object
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('currencymanager')->__('Do not use')),
            array('value' => 2, 'label'=>Mage::helper('currencymanager')->__('Use symbol')),
            array('value' => 3, 'label'=>Mage::helper('currencymanager')->__('Use short name')),
            array('value' => 4, 'label'=>Mage::helper('currencymanager')->__('Use name')),
        );
    }
}

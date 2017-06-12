<?php
/**
 * @category   Allure
 * @package    Allure_CurrencyManager
 * @copyright  Copyright (c) 2016 Allure Inc (http://www.allureinc.co)
 * @contacts   support@allureinc.co
 */

class Allure_CurrencyManager_Model_Typeposition extends Varien_Object
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 8, 'label'=>Mage::helper('currencymanager')->__('Default')),
            array('value' => 16, 'label'=>Mage::helper('currencymanager')->__('Right')),
            array('value' => 32, 'label'=>Mage::helper('currencymanager')->__('Left')),
        );
    }
}

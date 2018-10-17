<?php

class Allure_SmartAnalytics_Model_Source_Step
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Billing Information')),
            array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('Shipping Information')),
            array('value' => 3, 'label'=>Mage::helper('adminhtml')->__('Shipping Method')),
            array('value' => 4, 'label'=>Mage::helper('adminhtml')->__('Payment Information')),
            array('value' => 5, 'label'=>Mage::helper('adminhtml')->__('Order Review')),
        );
    }
}

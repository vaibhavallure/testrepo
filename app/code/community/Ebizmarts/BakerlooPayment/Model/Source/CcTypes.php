<?php

class Ebizmarts_BakerlooPayment_Model_Source_CcTypes extends Mage_Adminhtml_Model_System_Config_Source_Payment_Cctype
{

    public function toOptionArray()
    {
        $options = parent::toOptionArray();

        // add custom credit cards
        $options[] = array('value' => 'UP', 'label' => Mage::helper('bakerloo_payment')->__('UnionPay'));

        return $options;
    }

    public function toOption()
    {
        $options =  array();

        $ccTypes = $this->toOptionArray();

        foreach ($ccTypes as $ccType) {
            $options[$ccType['value']] = $ccType['label'];
        }

        return $options;
    }
}

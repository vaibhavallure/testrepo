<?php

class Ebizmarts_BakerlooRestful_Model_Adminhtml_System_Config_Source_CustomerAttribute
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        $attributes = Mage::getModel('customer/customer')
            ->getAttributes();

        $options = array();

        $options []= array('value' => '','label' => '');

        foreach ($attributes as $attribute) {
            if ($attribute->getFrontendLabel()) {
                $options []= array(
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontendLabel(),
                );
            }
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array();
    }
}

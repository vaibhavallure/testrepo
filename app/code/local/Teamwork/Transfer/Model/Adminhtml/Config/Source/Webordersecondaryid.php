<?php

class Teamwork_Transfer_Model_Adminhtml_Config_Source_Webordersecondaryid
{
    public function toOptionArray()
    {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');
        $collection->addVisibleFilter();
        //note: see all types in Mage_Eav_Model_Adminhtml_System_Config_Source_Inputtype
        $collection->addFieldToFilter("frontend_input", array('text', 'textarea'));
        
        $result = array(
            array(
                'label' => Mage::helper('teamwork_transfer')->__("-- Do not use --"),
                'value' => 0,
            )
        );
        foreach ($collection as $attribute)
        {
            $result[] = array(
                'label' => $attribute->getData('frontend_label'),
                'value' => $attribute->getData('attribute_code'),
            );
        }
        return $result;
    }
}

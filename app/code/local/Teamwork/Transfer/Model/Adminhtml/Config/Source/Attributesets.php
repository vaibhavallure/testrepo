<?php

class Teamwork_Transfer_Model_Adminhtml_Config_Source_Attributesets
{
    public function toOptionArray()
    {

        $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection')->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId());

        $result = array();
        
        $defaultAttributeSetId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getDefaultAttributeSetId();
        
        foreach ($attributeSetCollection as $attributeSet)
        {
            $result[] = array(
                'label' => $attributeSet->getAttributeSetName(),
                'value' => $attributeSet->getId() == $defaultAttributeSetId ? 0 : $attributeSet->getId(),
            );
        }
        
        return $result;
    }
}

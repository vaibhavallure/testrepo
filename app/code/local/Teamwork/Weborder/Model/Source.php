<?php
class Teamwork_Weborder_Model_Source extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const ADMIN_MINIMAL_SETTING_MAGENTO_ATTRIBUTE = 'teamwork_weborder/general/magento_attribute';
    public function getAllOptions()
    {
	    $entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($entityTypeId)
            ->addFieldToFilter('frontend_input', array('in' => 
                array(
                    'select', 'text'
                )
            ));
        
        $return = array();
        foreach ($attributes as $attribute)
        { 
            if( $attribute->getFrontendLabel() )
            {
                $return[] = array(
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $attribute->getAttributeCode()
                );
            }
        }
        return $return;
    }
    
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
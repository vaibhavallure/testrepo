<?php
class Allure_Category_Model_System_Config_Source_Filters extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions($withEmpty = false)
    {
        $productAttrs = Mage::getResourceModel('catalog/product_attribute_collection');
        $productAttrs->addFieldToFilter('is_filterable',1);

        $options[]=array('value'=>'','label'=>'None');

        foreach ($productAttrs as $_attr){
            $options[] = array(
                'value' => $_attr->getAttributeCode(),
                'label' => $_attr->getFrontendLabel()
            );
        }
        return $options;
    }
    
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}
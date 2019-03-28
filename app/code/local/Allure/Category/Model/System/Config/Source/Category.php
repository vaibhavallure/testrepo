<?php
class Allure_Category_Model_System_Config_Source_Category extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions($withEmpty = false)
    {
     
        
        $attribute = Mage::getSingleton('eav/config')
        ->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'category_postlengths');
        
        
        
        if ($attribute->usesSource()) {
            $lengths = $attribute->getSource()->getAllOptions(false);
            foreach ($lengths as $length){
              
                $options[] = array(
                    'label' => $length[label],
                    'value' => $length[value]);
            }
        }
       
        
        
        return $options;
    }
    
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
    
    public function getOptionText($value)
    {
        $options = $this->getAllOptions(false);
        foreach ($options as $item) {
            if ($item['value'] == $value) {
                return $item['label'];
            }
        }
        return false;
    }
}
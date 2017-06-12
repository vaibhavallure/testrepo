<?php

class Ecp_Familycolors_Helper_Data extends Mage_Core_Helper_Abstract {
	public    function IsNullOrEmptyString($question){
    return (!isset($question) || trim($question)==='');
	}

    public function getMultiAttributeValues($attrID, $filterID = false) {
        $attributeId = Mage::getResourceModel('eav/entity_attribute')
                ->getIdByCode('catalog_product', $attrID);

        $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
        $attributeOptions = $attribute->getSource()->getAllOptions();
        if ($filterID) {
            foreach ($attributeOptions as $value) {
                if (in_array($value['value'], $filterID)) {
                    $attributeArray[] = $value;
                }
            }
        } else {
            $attributeArray = $attributeOptions;
        }

        foreach ($attributeArray as $key => $value) {
            if ($key && $value)
                $attributeArray[$key][$value['value']]['status'] = 'active';
        }
        return $attributeArray;
    }

    public function getColorsBlock() {
        return Mage::getSingleton('core/layout')->createBlock(
                                'Ecp_Familycolors_Block_Familycolors', 
                                'familycolor_block', 
                                array('template'=>'ecp/familycolors/familycolors.phtml'))->toHtml();
    }
    public function getColorFamilyOptions(){
        $optionsModel = Mage::getModel('ecp_familycolors/familycolors');
        $optionsCollection = $optionsModel->getCollection();
        foreach ($optionsCollection as $item){
            $color_apparel = unserialize($item->getData('color_apparel'));            
            $options[] = array(
                'value' => $item->getId(),
                'label' => $item->getTitle(),
                'option'=> $color_apparel
            );
        }
        return $options; 
    }
    
    public function getColorFamilyValue($colorId){
        $masterValues  = array();
        $master_color_options = array();
        $color_apparel = $this->getColorFamilyOptions();
        foreach($color_apparel as $master){
            foreach($master['option'] as $opt){
                if($opt == $colorId ){
                    $masterValues[$master['value']] = $master['label'];                  
                    break;
                }
            }
        }
        
        if(count($masterValues)){
             $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','master_color');
             $attribute	 = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
             $attributeOptions = $attribute ->getSource()->getAllOptions();              
             foreach($masterValues as $id => $label){
                  foreach ($attributeOptions as $option) {                    
                    if ($option["label"] == $label) { 
                        $master_color_options[] = $option["value"];
                        break;
                    } 
                }
             }
             
        }
        return $master_color_options;
    }
    
}
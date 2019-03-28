<?php
class Allure_SmartAnalytics_Model_Source_Brand_Dropdown
{
    public function toOptionArray()
    {
        $attributes = Mage::getModel('catalog/product')->getAttributes();
        $attributeArray = array(0 => array('label' => '', 'value' => ''));

        foreach($attributes as $a){
            foreach ($a->getEntityType()->getAttributeCodes() as $attributeName) {
                $attributeArray[] = array(
                    'label' => $attributeName,
                    'value' => $attributeName
                );
            }
            break;
        }

        return $attributeArray;
    }
}

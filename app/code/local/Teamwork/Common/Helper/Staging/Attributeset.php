<?php
class Teamwork_Common_Helper_Staging_Attributeset extends Mage_Core_Helper_Abstract
{
    const PREFIX_FOR_UNSUITABLE_ATTRIBUTE_CODE = 'teamwork_';
    public function isStringInt($string)
    {
        return preg_match("/^\d+$/", $string);
    }
    
    public function isAttributeReserved($string)
    {
        return in_array(
            $string, Mage::getModel('catalog/product')->getReservedAttributes()
        );
    }
    
    public function getSafeAttributeCode($attributeCode)
    {
        $attributeCode = strtolower($attributeCode);
        return ($this->isStringInt($attributeCode) || $this->isAttributeReserved($attributeCode))
            ? self::PREFIX_FOR_UNSUITABLE_ATTRIBUTE_CODE . $attributeCode
        : $attributeCode;
    }
}
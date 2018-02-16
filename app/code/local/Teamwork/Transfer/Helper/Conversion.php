<?php
class Teamwork_Transfer_Helper_Conversion
{
    /**
     *  acceptable product conversions
     *
     * @var array
     */
    protected $_acceptableConversions = array(
        array(
            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
        ),
    );
    public function checkConversion($previousType, $newType)
    {
        foreach($this->_acceptableConversions as $acceptableConversion)
        {
            if(!empty($acceptableConversion[$previousType]) && $acceptableConversion[$previousType] == $newType)
            {
                return true;
            }
        }
        return false;
    }
}
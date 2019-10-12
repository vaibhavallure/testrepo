<?php
class Allure_Category_Model_System_Config_Source_Groups extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions($withEmpty = false)
    {
        $groupCollection = Mage::getModel('customer/group')->getCollection();
        $options = array(
            array('value' => "all" ,  'label' => 'All')
        );
        foreach ($groupCollection as $group){
            $options[] = array(
                'value' => $group->getCustomerGroupId()+1,
                'label' => $group->getCustomerGroupCode()
            );
        }
        return $options;
    }
    
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}
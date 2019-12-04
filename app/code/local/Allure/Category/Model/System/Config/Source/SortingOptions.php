<?php
class Allure_Category_Model_System_Config_Source_SortingOptions extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions($withEmpty = false)
    {

        $options[] = array(
            'label' => "No",
            'value' => 0);
        $options[] = array(
            'label' => "Updated At",
            'value' => "updated_at");
        $options[] = array(
            'label' => "Created At",
            'value' => "created_at");

        return $options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}
<?php
class Allure_HarrodsInventory_Model_Source
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'csv', 'label' =>'CSV'),
            array('value' => 'txt', 'label' => 'TEXT'),
        );
    }
}


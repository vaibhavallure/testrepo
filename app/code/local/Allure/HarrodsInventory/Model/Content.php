<?php
class Allure_HarrodsInventory_Model_Content
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'update', 'label' => 'Updated Only'),
            array('value' => 'full', 'label' =>'Full'),
        );
    }
}


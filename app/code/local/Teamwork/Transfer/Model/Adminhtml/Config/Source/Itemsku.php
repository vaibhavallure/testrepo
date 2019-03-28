<?php

class Teamwork_Transfer_Model_Adminhtml_Config_Source_Itemsku
{
    public function toOptionArray()
    {
        return array(
            array(
                'label' => '<item PLU>',
                'value' => Teamwork_Transfer_Helper_Config::ITEMSKU_PLU
            ),
            array(
                'label' => '<style no> - <item PLU>',
                'value' => Teamwork_Transfer_Helper_Config::ITEMSKU_STYLENO_PLU
            ),
        );
    }
}

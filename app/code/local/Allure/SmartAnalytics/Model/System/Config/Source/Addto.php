<?php
class Allure_SmartaAalytics_Model_System_Config_Source_Addto
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'head', 'label'=>Mage::helper('allure_smartanalytics')->__('Head')),
          //  array('value' => 'before_body_end', 'label'=>Mage::helper('allure_smartanalytics')->__('Before Body End')),
        );
    }
}

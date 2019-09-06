<?php
/*
 * My Render Extension
 */
class Allure_Appointments_Block_Adminhtml_Render_Piercing extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $data = $row->getData();
        if($data['special_store'] != 0) {
            $piercing_count = Mage::helper("appointments/counts")->getPiercingCount($data);
            return $piercing_count;
        }
        return '';
    }
}
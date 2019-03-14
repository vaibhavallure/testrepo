<?php
/*
 * My Render Extension
 */
class Allure_Appointments_Block_Adminhtml_Render_Notified extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
       if($row->getData($this->getColumn()->getIndex())=='0000-00-00 00:00:00') {
           $data = 'No';
       } else {
           $data = $row->getData($this->getColumn()->getIndex());
       }
       return $data;
    }
}
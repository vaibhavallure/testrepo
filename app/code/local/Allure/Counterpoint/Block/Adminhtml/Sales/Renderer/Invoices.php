<?php

class Allure_Counterpoint_Block_Adminhtml_Sales_Renderer_Invoices 
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        $total = $row->getData("base_grand_total");
        if($total < 0){
            return "Returned";
        }
        if($value == 1){
            return "Pending";
        }elseif($value == 2){
            return "Paid";
        }if($value == 3){
            return "Canceled";
        }
        
    }
}

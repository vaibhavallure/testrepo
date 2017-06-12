<?php

class Allure_Appointments_Block_Adminhtml_Servicelocations_Edit_Renderer_Timing extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render (Varien_Object $row)
    {
    	$timing = unserialize($row->getTime());
    	$data = "";
    	foreach ($timing as $time)
    	{
    		$data .= "Qty(".$time['qty'].") - ".$time['timing']." min <br/>";
    	}
    	return $data;
    	
    }
}
?>
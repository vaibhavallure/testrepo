<?php
class Allure_Appointments_Block_Adminhtml_Appointments_Edit_Renderer_Modify
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render (Varien_Object $row)
	{
		$url=Mage::getBaseUrl().'admin_appointments/index/modify/id/'.$row->getId().'/email/'.$row->getEmail();
		if(!empty($row->getId()) && !empty($row->getEmail()))
			echo "<a target='_blank' href='$url'>Modify</a>";
		
	}
}
?>
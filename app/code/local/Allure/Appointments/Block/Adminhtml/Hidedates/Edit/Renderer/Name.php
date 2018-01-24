<?php

class Allure_Appointments_Block_Adminhtml_Appointmentpiercers_Edit_Renderer_Name
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render (Varien_Object $row)
	{
		$value = $row->getFirstname()." ". $row->getLastname();
		return $value;
	}
}
?>
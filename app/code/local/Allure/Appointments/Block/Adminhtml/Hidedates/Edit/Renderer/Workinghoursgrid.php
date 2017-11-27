<?php

class Allure_Appointments_Block_Adminhtml_Appointmentpiercers_Edit_Renderer_Workinghoursgrid
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render (Varien_Object $row)
	{
		$timing = unserialize($row->getWorkingHours());
		$helper=Mage::helper("appointments");
		$data = "";
		foreach ($timing as $time)
		{
			$data .= $time['day']." ".$helper->getTimeByValue($time['start'] )."-".   $helper->getTimeByValue($time['end'] )." <br/>";
		}
		return $data;
	}
}
?>
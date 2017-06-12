<?php

class Allure_Appointments_Block_Adminhtml_Appointmentpiercers_Edit_Renderer_Workinghoursgrid
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render (Varien_Object $row)
	{
		$timing = unserialize($row->getWorkingHours());
		$data = "";
		foreach ($timing as $time)
		{
			$data .= sprintf('%02d', $time['start'] ).":00 - ".sprintf('%02d', $time['end'] ).":00 <br/>";
		}
		return $data;
	}
}
?>
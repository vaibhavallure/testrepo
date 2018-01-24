<?php

class Allure_Appointments_Block_Adminhtml_Appointmentpiercers_Edit_Renderer_Workingdays
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render (Varien_Object $row)
	{
		/* $days = array('', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
		$workdays = array();
		
		$value = $row->getWorkingDays();
		$value=explode(',', $value);
		foreach ($value as $day)
		{
			$workdays[] = $days[$day];
		}
		$workdays = implode(', ', $workdays);
		return $workdays; */
		
		
		//Get the 31 values for Working days value
		/* $wDays_arr = array();
		for($i=0;$i<=31;$i++){
			$wDays_arr[] = $i;			
		}
		
		$days = $wDays_arr;
		$workdays = array();
		
		$value = $row->getWorkingDays();
		$value=explode(',', $value);
		foreach ($value as $day)
		{
			$workdays[] = $days[$day];
		}
		$workdays = implode(', ', $workdays);
		return $workdays; */
	}
}
?>
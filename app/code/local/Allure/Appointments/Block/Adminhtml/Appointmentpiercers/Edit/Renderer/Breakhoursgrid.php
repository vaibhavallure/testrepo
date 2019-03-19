<?php

class Allure_Appointments_Block_Adminhtml_Appointmentpiercers_Edit_Renderer_Breakhoursgrid
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render (Varien_Object $row)
	{
		$timing = unserialize($row->getWorkingHours());
		$helper=Mage::helper("appointments");
		$data = "";

		$specialStoreId = Mage::helper('allure_virtualstore')->getStoreId('nordstrom_la');

		$specialStore = false;

		if ($row->getStoreId() == $specialStoreId) {
			$specialStore = true;
		}

		foreach ($timing as $time) {

			if ($specialStore) {
				$data .= $time['day']." <div style='float:right'>".$helper->getBreakTimeByValue($time['break_start'] )."-".  $helper->getBreakTimeByValue($time['break_end'] )."</div> <br/>";
			} else {
				$data .= $time['day']." <div style='float:right'>".$helper->getTimeByValue($time['break_start'] )."-".  $helper->getTimeByValue($time['break_end'] )."</div> <br/>";
			}
		}
		return $data;
	}
}
?>

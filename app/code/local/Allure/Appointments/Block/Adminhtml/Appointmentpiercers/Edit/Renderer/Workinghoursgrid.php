<?php

class Allure_Appointments_Block_Adminhtml_Appointmentpiercers_Edit_Renderer_Workinghoursgrid
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
				$data .= $time['day']." ".$helper->getBreakTimeByValue($time['start'] )."-".  $helper->getBreakTimeByValue($time['end'] )." <br/>";
			} else {
				$data .= $time['day']." ".$helper->getTimeByValue($time['start'] )."-".  $helper->getTimeByValue($time['end'] )." <br/>";
			}
		}
		return $data;
	}
}
?>

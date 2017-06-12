<?php
class Allure_Appointments_Model_Adminhtml_Source_Stores
{
	public function toOptionArray() {
		return Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false);
	}
}
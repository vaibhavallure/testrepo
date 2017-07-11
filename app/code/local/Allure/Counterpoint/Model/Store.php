<?php
class Allure_Counterpoint_Model_Store extends Mage_Core_Model_Abstract {
	public function toOptionArray() {
		return Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
	}
}
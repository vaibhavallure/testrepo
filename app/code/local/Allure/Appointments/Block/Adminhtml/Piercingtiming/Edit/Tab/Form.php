<?php

class Allure_Appointments_Block_Adminhtml_Piercingtiming_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm ()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset("piercingtiming_form",
				array(
						"legend" => Mage::helper("appointments")->__("Piercing Timing")
				));
	
		$fieldset->addField("qty", "text",
				array(
						"label" => Mage::helper("appointments")->__("No of People in Group"),
						"name" => "qty"
				));
		
		$fieldset->addField("time", "text",
				array(
						"label" => Mage::helper("appointments")->__("Time Required"),
						"name" => "time"
				));
	
		//if (!Mage::app()->isSingleStoreMode()) {
		    if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
		        $storeOptions = Mage::getSingleton('allure_virtualstore/adminhtml_store')->getStoreOptionHash();
		    }else{
		        $storeOptions = Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash();
		    }
    		$fieldset->addField('store_id', 'select', array(
    		      'label'     => Mage::helper("appointments")->__("Store"),
    			  'name'    => 'store_id',
    		      'values'   => $storeOptions,//Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash(),
    		));
		//}
		
		if (Mage::getSingleton("adminhtml/session")->getPiercingtimingData()) {
			$form->setValues(Mage::getSingleton("adminhtml/session")->getPiercingtimingData());
			Mage::getSingleton("adminhtml/session")->getPiercingtimingData(null);
		} elseif (Mage::registry("appointment_piercing_timing_data")) {
			$form->setValues(Mage::registry("appointment_piercing_timing_data")->getData());
		}
		return parent::_prepareForm();
	}
}
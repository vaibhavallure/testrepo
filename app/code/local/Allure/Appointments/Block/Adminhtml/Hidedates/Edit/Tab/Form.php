<?php

class Allure_Appointments_Block_Adminhtml_Hidedates_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm ()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset("hidedates_form",
				array(
						"legend" => Mage::helper("appointments")->__("Appointment Dates")
				));
		
		$fieldset->addField('date', 'date', array(
		    'name'               => 'date',
		    'label'              => Mage::helper('appointments')->__('Date'),
		    'tabindex'           => 1,
		    'image'              => $this->getSkinUrl('images/grid-cal.gif'),
		    "required"           => true,
		    'format'             => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
		    'index'   =>'date'
		    
		));
	
		//if (!Mage::app()->isSingleStoreMode()) {
		    
		    if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
		        $storeOptions = Mage::getSingleton('allure_virtualstore/adminhtml_store')->getStoreOptionHash();
		    }else{
		        $storeOptions = Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash();
		    }
		    
			$fieldset->addField('store_id', 'select', array(
					'name' => 'store_id',
					'label' => Mage::helper('appointments')->__('Store Views'),
					'title' => Mage::helper('appointments')->__('Store Views'),
					'required' => true,
			        'value' => '2',
			        'values' => $storeOptions,//Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash(),
			));
		//} 
		/* else {
			$fieldset->addField('store_id', 'hidden', array(
					'name' => 'store_id',
					'value' => Mage::app()->getStore(true)->getStoreId()
			));
		} */
		
		$fieldset->addField("is_available", "select",
				array(
						"label" => Mage::helper("appointments")->__("Is Availbale"),
						'title' => Mage::helper('appointments')->__('Is Availbale'),
						'required' => true,
						'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
						'value' => '0',
						"name" => "is_available"
				));
		$fieldset->addField("exclude", "select",
		    array(
		        "label" => Mage::helper("appointments")->__("Exclude"),
		        'title' => Mage::helper('appointments')->__('Exclude'),
		        'required' => true,
		        'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
		        'value' => '0',
		        "name" => "exclude"
		    ));
		
		
		
		if (Mage::getSingleton("adminhtml/session")->getPiercingtimingData()) {
		    $form->setValues(Mage::getSingleton("adminhtml/session")->getPiercingtimingData());
		    Mage::getSingleton("adminhtml/session")->getPiercingtimingData(null);
		} elseif (Mage::registry("appointment_hidedates_data")) {
		    $form->setValues(Mage::registry("appointment_hidedates_data")->getData());
		}
		
		return parent::_prepareForm();
	}
}
<?php

class Allure_Appointments_Block_Adminhtml_Appointmentpiercers_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm ()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset("piercers_form",
				array(
						"legend" => Mage::helper("appointments")->__("Appointment Piercers")
				));
	
		$fieldset->addField("firstname", "text",
				array(
						"label" => Mage::helper("appointments")->__("First Name"),
						"name" => "firstname",
						'required'  => true,
				));
		
		$fieldset->addField("lastname", "text",
				array(
						"label" => Mage::helper("appointments")->__("Last Name"),
						"name" => "lastname"
				));
		
		$fieldset->addField("email", "text",
				array(
						"label" => Mage::helper("appointments")->__("Email"),
						"name" => "email"
				));
		
		$fieldset->addField("phone", "text",
				array(
						"label" => Mage::helper("appointments")->__("Phone"),
						"name" => "phone"
				));
		
		$fieldset->addField('working_days', 'text', array(
				'label'     => Mage::helper("appointments")->__("Working Days"),
				'required'  => false,
				'name'      => 'working_days',
		));
		$timing = Mage::helper('appointments')->getTimingSelect();
		/* echo "<pre>";print_r($timing);
		die; */
		$office_days = $form->getElement('working_days');
		
		$office_days->setRenderer(
				$this->getLayout()->createBlock('appointments/adminhtml_appointmentpiercers_edit_renderer_multidatepickercalendar')
				);
				
				
		$fieldset->addField('working_hours', 'text', array(
						'name'      => 'working_hours',
						'label'     => Mage::helper('appointments')->__('Working Hours'),
						'required'  => true,
				));
				
		$office_hours = $form->getElement('working_hours');
				
		$office_hours->setRenderer(
						$this->getLayout()->createBlock('appointments/adminhtml_appointmentpiercers_edit_renderer_workinghours')
						);
				
				
				
				
				
				
				
				
		/* $fieldset->addField("working_hours", "text",
				array(
						"label" => Mage::helper("appointments")->__("Working Hours"),
						"name" => "working_hours"
				)); */
		
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
			        'values' => $storeOptions,//Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash(),
			));
		//} 
		/* else {
			$fieldset->addField('store_id', 'hidden', array(
					'name' => 'store_id',
					'value' => Mage::app()->getStore(true)->getStoreId()
			));
		} */
		
		$fieldset->addField("is_active", "select",
				array(
						"label" => Mage::helper("appointments")->__("Is Active"),
						'title' => Mage::helper('appointments')->__('Is Active'),
						'required' => true,
						'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
						'value' => '1',
						"name" => "is_active"
				));
		
		$fieldset->addField("color", "text",
		    array(
		        "label" => Mage::helper("appointments")->__("Color"),
		        "name" => "color"
		    ));
		
		if (Mage::getSingleton("adminhtml/session")->getAppointmentpiercersData()) {
			$form->setValues(Mage::getSingleton("adminhtml/session")->getAppointmentpiercersData());
			Mage::getSingleton("adminhtml/session")->getAppointmentpiercersData(null);
		} elseif (Mage::registry("appointment_piercers_data")) {
			$data = Mage::registry("appointment_piercers_data")->getData();
			$data['working_days'] = explode(',', $data['working_days']);
			$form->setValues($data);
		}
		return parent::_prepareForm();
	}
}
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
						"label" => Mage::helper("appointments")->__("FirstName"),
						"name" => "firstname"
				));
		
		$fieldset->addField("lastname", "text",
				array(
						"label" => Mage::helper("appointments")->__("LastName"),
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
		
// 		$fieldset->addField("working_days", "text",
// 				array(
// 						"label" => Mage::helper("appointments")->__("Working Days"),
// 						"name" => "working_days"
// 				));
		
		$fieldset->addField('working_days', 'checkboxes', array('label' => Mage::helper("appointments")->__("Working Days"),
				'name' => 'working_days[]',
				'values' => array(
						array('value'=>'1', 'label'=>'Monday'),
						array('value'=>'2', 'label'=>'Tuesday'),
						array('value'=>'3', 'label'=>'Wednesday'),
						array('value'=>'4', 'label'=>'Thursday'),
						array('value'=>'5', 'label'=>'Friday'),
						array('value'=>'6', 'label'=>'Saturday'),
						array('value'=>'7', 'label'=>'Sunday'),
				),
				'value' => array('1','2','3','4', '5')));
		
				
		$fieldset->addField('working_hours', 'text', array(
						'name'      => 'working_hours',
						'label'     => Mage::helper('appointments')->__('Working Hours'),
						'required'  => false,
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
		
		if (!Mage::app()->isSingleStoreMode()) {
			$fieldset->addField('store_id', 'select', array(
					'name' => 'store_id',
					'label' => Mage::helper('appointments')->__('Store Views'),
					'title' => Mage::helper('appointments')->__('Store Views'),
					'required' => true,
					'values' => Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash(),
			));
		} else {
			$fieldset->addField('store_id', 'hidden', array(
					'name' => 'store_id',
					'value' => Mage::app()->getStore(true)->getStoreId()
			));
		}
		
		$fieldset->addField("is_active", "select",
				array(
						"label" => Mage::helper("appointments")->__("Is Active"),
						'title' => Mage::helper('appointments')->__('Is Active'),
						'required' => true,
						'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
						'value' => '1',
						"name" => "is_active"
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
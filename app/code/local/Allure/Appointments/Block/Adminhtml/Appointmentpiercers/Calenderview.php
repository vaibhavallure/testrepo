<?php

class Allure_Appointments_Block_Adminhtml_Appointmentpiercers_Calenderview extends Mage_Adminhtml_Block_Widget_Form_Container
{

	public function __construct ()
	{
		parent::__construct();
		
		$this->setTemplate('appointments/piercercalender.phtml');
	
	}

	public function getHeaderText ()
	{
		return Mage::helper("appointments")->__("Appointment View");
	}
}
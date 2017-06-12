<?php

class Allure_Appointments_Block_Adminhtml_Appointments_View_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct ()
	{
		parent::__construct();
		$this->setId("appointments_tabs");
		$this->setDestElementId("view_form");
		$this->setTitle(Mage::helper("appointments")->__("Piercers"));
	}
	
	protected function _beforeToHtml ()
	{
		$this->addTab("form_section",
				array(
						"label" => Mage::helper("appointments")->__("Manage Piercer"),
						"title" => Mage::helper("appointments")->__("Manage Piercer"),
						"content" => $this->getLayout()
						->createBlock("appointments/adminhtml_appointmentpiercers_edit_tab_form")
						->toHtml()
				));
		return parent::_beforeToHtml();
	}
}
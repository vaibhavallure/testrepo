<?php

class Allure_Appointments_Block_Adminhtml_Appointments_Transfer_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct ()
	{
		parent::__construct();
		$this->setId("appointments_tabs");
		$this->setDestElementId("edit_form");
		$this->setTitle(Mage::helper("appointments")->__("Appointments"));
	}
	
	protected function _beforeToHtml ()
	{
		$this->addTab("form_section",
				array(
						"label" => Mage::helper("appointments")->__("Transfer Appointments"),
						"title" => Mage::helper("appointments")->__("Transfer Appointments"),
						"content" => $this->getLayout()
						->createBlock("appointments/adminhtml_appointments_transfer_tab_form")
						->toHtml()
				));
		return parent::_beforeToHtml();
	}
}
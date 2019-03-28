<?php

class Allure_Appointments_Block_Adminhtml_Hidedates_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct ()
	{
		parent::__construct();
		$this->setId("appointments_tabs");
		$this->setDestElementId("edit_form");
		$this->setTitle(Mage::helper("appointments")->__("Dates"));
	}
	
	protected function _beforeToHtml ()
	{
		$this->addTab("form_section",
				array(
						"label" => Mage::helper("appointments")->__("Manage Dates"),
						"title" => Mage::helper("appointments")->__("Manage Dates"),
						"content" => $this->getLayout()
						->createBlock("appointments/adminhtml_hidedates_edit_tab_form")
						->toHtml()
				));
		return parent::_beforeToHtml();
	}
}
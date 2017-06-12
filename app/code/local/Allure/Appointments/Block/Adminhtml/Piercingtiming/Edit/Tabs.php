<?php

class Allure_Appointments_Block_Adminhtml_Piercingtiming_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct ()
	{
		parent::__construct();
		$this->setId("piercingtiming_tabs");
		$this->setDestElementId("edit_form");
		$this->setTitle(Mage::helper("appointments")->__("Piercing Timing"));
	}
	
	protected function _beforeToHtml ()
	{
		$this->addTab("form_section",
				array(
						"label" => Mage::helper("appointments")->__("Timing"),
						"title" => Mage::helper("appointments")->__("Timing"),
						"content" => $this->getLayout()
						->createBlock("appointments/adminhtml_piercingtiming_edit_tab_form")
						->toHtml()
				));
		return parent::_beforeToHtml();
	}
}
<?php
class Allure_Metadata_Block_Adminhtml_Metadata_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("metadata_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("metadata")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("metadata")->__("Item Information"),
				"title" => Mage::helper("metadata")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("metadata/adminhtml_metadata_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}

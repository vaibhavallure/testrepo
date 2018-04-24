<?php

class Allure_Appointments_Block_Adminhtml_Pricing_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct ()
    {
        parent::__construct();
        $this->setId("pricing_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle(Mage::helper("appointments")->__("Item Information"));
    }

    protected function _beforeToHtml ()
    {
        $this->addTab("form_section", 
                array(
                        "label" => Mage::helper("appointments")->__("Item Information"),
                        "title" => Mage::helper("appointments")->__("Item Information"),
                        "content" => $this->getLayout()
                            ->createBlock("appointments/adminhtml_pricing_edit_tab_form")
                            ->toHtml()
                ));
        return parent::_beforeToHtml();
    }
}

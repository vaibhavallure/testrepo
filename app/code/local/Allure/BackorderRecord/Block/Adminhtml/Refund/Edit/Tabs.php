<?php

class Allure_BackorderRecord_Block_Adminhtml_Refund_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct ()
    {
        parent::__construct();
        $this->setId("backorder_tabs");
        $this->setDestElementId("edit_form");
//        $this->setTitle(Mage::helper("backorderrecord")->__("Refund Report Download"));
    }

    protected function _beforeToHtml ()
    {
        $this->addTab("form_section",
                array(
                        "title" => Mage::helper("backorderrecord")->__("Refund Report Download"),
                        "content" => $this->getLayout()
                            ->createBlock("backorderrecord/adminhtml_refund_edit_tab_form")
                            ->toHtml()
                ));
        return parent::_beforeToHtml();
    }
}

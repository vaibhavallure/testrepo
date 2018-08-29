<?php

class Allure_Virtualstore_Block_Adminhtml_Store_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct ()
    {
        parent::__construct();
        $this->setId("store_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle(Mage::helper("allure_virtualstore")->__("Virtual Store Information"));
    }

    protected function _beforeToHtml ()
    {
        $this->addTab("form_section",
                array(
//                        "label" => Mage::helper("virtualstore")->__("Virtual Store Information"),
                        "title" => Mage::helper("allure_virtualstore")->__("Virtual Store Information"),
                        "content" => $this->getLayout()
                            ->createBlock("allure_virtualstore/adminhtml_store_edit_tab_form")
                            ->toHtml()
                ));
        return parent::_beforeToHtml();
    }
}

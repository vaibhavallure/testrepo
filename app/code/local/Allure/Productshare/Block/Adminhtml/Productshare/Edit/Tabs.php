<?php

class Allure_Productshare_Block_Adminhtml_Productshare_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct ()
    {
        parent::__construct();
        $this->setId("productshare_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle(Mage::helper("productshare")->__("Item Information"));
    }

    protected function _beforeToHtml ()
    {
        $this->addTab("form_section", 
                array(
                        "label" => Mage::helper("productshare")->__("Item Information"),
                        "title" => Mage::helper("productshare")->__("Item Information"),
                        "content" => $this->getLayout()
                            ->createBlock("productshare/adminhtml_productshare_edit_tab_form")
                            ->toHtml()
                ));
        return parent::_beforeToHtml();
    }
}

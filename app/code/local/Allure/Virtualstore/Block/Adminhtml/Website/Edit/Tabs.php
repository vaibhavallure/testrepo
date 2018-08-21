<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/20/18
 * Time: 3:55 PM
 */
class Allure_Virtualstore_Block_Adminhtml_Website_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct ()
    {
        parent::__construct();
        $this->setId("website_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle(Mage::helper("virtualstore")->__("Website Information"));
    }

    protected function _beforeToHtml ()
    {
        $this->addTab("form_section",
            array(
//                        "label" => Mage::helper("virtualstore")->__("Virtual Store Information"),
                "title" => Mage::helper("virtualstore")->__("Website Information"),
                "content" => $this->getLayout()
                    ->createBlock("virtualstore/adminhtml_website_edit_tab_form")
                    ->toHtml()
            ));
        return parent::_beforeToHtml();
    }
}

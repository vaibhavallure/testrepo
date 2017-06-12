<?php

class Allure_Productshare_Block_Adminhtml_Productshare_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm ()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("productshare_form", 
                array(
                        "legend" => Mage::helper("productshare")->__("Item information")
                ));
        
        $fieldset->addField("website_id", "select", 
                array(
                        "label" => Mage::helper("productshare")->__("Website"),
                        "name" => "website_id",
                        "values" => Mage::getModel("adminhtml/system_config_source_website")->toOptionArray(),
                        "disabled" => true
                ));
        
        $fieldset->addField("status", "select", 
                array(
                        "label" => Mage::helper("productshare")->__("Status"),
                        "name" => "status",
                        "options" => array(
                                1 => "Pending",
                                2 => "Processing",
                                3 => "Complete"
                        )
                ));
        
        $fieldset->addField("last_updated_product", "text", 
                array(
                        "label" => Mage::helper("productshare")->__("Last Updated Product (Start From)"),
                        "name" => "last_updated_product"
                ));
        
        $fieldset->addField("last_product", "text", 
                array(
                        "label" => Mage::helper("productshare")->__("Last Product (Target Product)"),
                        "name" => "last_product"
                ));
        
        $fieldset->addField("execution", "select", 
                array(
                        "label" => Mage::helper("productshare")->__("Automatic Product Sharing"),
                        "name" => "execution",
                        "options" => array(
                                0 => "OFF",
                                1 => "ON"
                        )
                ));
        
        if (Mage::getSingleton("adminhtml/session")->getProductshareData()) {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getProductshareData());
            Mage::getSingleton("adminhtml/session")->setProductshareData(null);
        } elseif (Mage::registry("productshare_data")) {
            $form->setValues(Mage::registry("productshare_data")->getData());
        }
        return parent::_prepareForm();
    }
}

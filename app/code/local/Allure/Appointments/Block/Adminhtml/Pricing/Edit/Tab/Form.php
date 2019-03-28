<?php

class Allure_Appointments_Block_Adminhtml_Pricing_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm ()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("appointments_form", 
                array(
                        "legend" => Mage::helper("appointments")->__("Item information")
                ));
        
        $fieldset->addField("type", "text",
            array(
                "label" => Mage::helper("appointments")->__("Type of piercing"),
                "name" => "type"
            ));;
        
        $fieldset->addField("service_cost", "text", 
                array(
                        "label" => Mage::helper("appointments")->__("Service Cost"),
                        "name" => "service_cost"
                ));
        
        $fieldset->addField("jewelry_start_at", "text", 
                array(
                        "label" => Mage::helper("appointments")->__("Jewelry Start At"),
                        "name" => "jewelry_start_at"
                ));
        
        if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
            $storeOptions = Mage::getSingleton('allure_virtualstore/adminhtml_store')->getStoreOptionHash();
        }else{
            $storeOptions = Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash();
        }
        $fieldset->addField('store_id', 'select', array(
            'label'     => Mage::helper("appointments")->__("Store"),
            'name'    => 'store_id',
            'values'   => $storeOptions,
        ));
        
        if (Mage::getSingleton("adminhtml/session")->getPricingData()) {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getPricingData());
            Mage::getSingleton("adminhtml/session")->setPricingData(null);
        } elseif (Mage::registry("pricing_data")) {
            $form->setValues(Mage::registry("pricing_data")->getData());
        }
        return parent::_prepareForm();
    }
}

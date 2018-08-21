<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/20/18
 * Time: 3:55 PM
 */
class Allure_Virtualstore_Block_Adminhtml_Website_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        $this->setForm($form);


        $fieldset = $form->addFieldset('website_form', array(
            'legend'=>Mage::helper('virtualstore')->__('Website information')
        ));

        $fieldset->addField("name", "text", array(
            "label"     => Mage::helper("virtualstore")->__("Name"),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));

        $fieldset->addField("code", "text", array(
            "label"     => Mage::helper("virtualstore")->__("Code"),
            'name'      => 'code',
        ));



        $fieldset->addField("default_group_id", 'text', array(
            "label"     => Mage::helper("virtualstore")->__("Default Group Id"),
            'class'     => 'required-entry validate-digits',
            'name'      => 'default_group_id',
        ));

        $fieldset->addField("sort_order", "text", array(
            "label"     => Mage::helper("virtualstore")->__("Sort Order"),
            'class'     => 'required-entry validate-digits',
            'required'  => true,
            'name'      => 'sort_order',
        ));

        $fieldset->addField("stock_id", "text", array(
            "label"     => Mage::helper("virtualstore")->__("Stock Id"),
            'class'     => 'validate-digits',
            'name'      => 'stock_id',
        ));
        $fieldset->addField("website_price_rule", "text", array(
            "label"     => Mage::helper("virtualstore")->__("Website Price Rule"),
            'name'      => 'website_price_rule',
        ));

        $fieldset->addField("is_default", "Select", array(
            "label"     => Mage::helper("virtualstore")->__("Is default"),
            'class'     => 'required-entry validate-decimal',
            'name'      => 'is_default',
            'options'     => array(
                1 => 'Yes',
                0 => 'No',

            ),
        ));
        if (Mage::getSingleton('adminhtml/session')->getWebsiteData()) {
            $data = Mage::getSingleton('adminhtml/session')->getWebsiteData();
            Mage::getSingleton('adminhtml/session')->setWebsiteData(null);
        } elseif (Mage::registry('website_data')) {
            $data = Mage::registry('website_data')->getData();
        }

        $form->setValues($data);
        return parent::_prepareForm();
    }
}
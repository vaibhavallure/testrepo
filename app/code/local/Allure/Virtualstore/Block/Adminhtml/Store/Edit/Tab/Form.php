<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/14/18
 * Time: 6:22 PM
 */
class Allure_Virtualstore_Block_Adminhtml_Store_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        $this->setForm($form);


        $fieldset = $form->addFieldset('virtualstore_form', array(
            'legend'=>Mage::helper('allure_virtualstore')->__('Virtualstore information')
        ));

        $fieldset->addField("name", "text", array(
            "label"     => Mage::helper("allure_virtualstore")->__("Name"),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));

        $fieldset->addField("code", "text", array(
            "label"     => Mage::helper("allure_virtualstore")->__("Code"),
            'name'      => 'code',
        ));


        $fieldset->addField('website_id', 'select', array(
            'label'     => 'Website Id',
            'class'     => 'required-entry',
            'required'  => true,
            'values'    => Mage::getModel('allure_virtualstore/website')->getWebsiteIds(),
            'value'     =>array(620),
            'name'      => 'website_id',
        ));

        $fieldset->addField("group_id", 'select', array(
            'label'     => 'Group Id',
            'class'     => 'required-entry',
            'required'  => true,
            'values'    => Mage::getModel('allure_virtualstore/group')->getGroupIds(),
            'value'     =>array(620),
            'name'      => 'group_id',
        ));
        $fieldset->addField("sort_order", "text", array(
            "label"     => Mage::helper("allure_virtualstore")->__("Sort Order"),
            'class'     => 'required-entry validate-digits',
            'required'  => true,
            'name'      => 'sort_order',
        ));
        $fieldset->addField("is_active", "select", array(
            "label"     => Mage::helper("allure_virtualstore")->__("Is Active"),
            'class'     => 'required-entry',
            'required'  => true,
            'options'     => array(
                1 => 'Yes',
                0 => 'No',

            ),
            'name'      => 'is_active',
        ));

        $fieldset->addField("is_copy_old_product", "select", array(
            "label"     => Mage::helper("allure_virtualstore")->__("Is Copy Old Product"),
            'class'     => 'required-entry',
            'required'  => true,
            'options'     => array(
                1 => 'Yes',
                0 => 'No',

            ),
            'name'      => 'is_copy_old_product',
        ));

//        $fieldset->addField("currency", "text", array(
//            "label"     => Mage::helper("allure_virtualstore")->__("Currency"),
//            'name'      => 'currency',
//        ));
//
//        $fieldset->addField("timezone", "text", array(
//            "label"     => Mage::helper("allure_virtualstore")->__("Timezone"),
//            'name'      => 'timezone',
//        ));

        if (Mage::getSingleton('adminhtml/session')->getVirtualstoreData()) {
            $data = Mage::getSingleton('adminhtml/session')->getVirtualstoreData();
            Mage::getSingleton('adminhtml/session')->setVirtualstoreData(null);
        } elseif (Mage::registry('virtualstore_data')) {
            $data = Mage::registry('virtualstore_data')->getData();
        }

        $form->setValues($data);
        return parent::_prepareForm();
    }
}
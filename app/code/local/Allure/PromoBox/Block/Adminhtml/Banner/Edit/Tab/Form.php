<?php

class Allure_PromoBox_Block_Adminhtml_Banner_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('promobox_banner_form', array('legend' => Mage::helper('promobox')->__('Banner information')));

        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('promobox')->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));

        $fieldset->addField('image', 'image', array(
            'label' => Mage::helper('promobox')->__('Image'),
            'required' => true,
            'name' => 'image',
        ));


        $fieldset->addField('html_block', 'editor', array(
            'name' => 'html_block',
            'label' => Mage::helper('promobox')->__('Html Content'),
            'title' => Mage::helper('promobox')->__('Html Content'),
        ));


        $fieldset->addField('size', 'select', array(
            'label' => Mage::helper('promobox')->__('Size'),
            'name' => 'size',
            'values' => array(
                array(
                    'value' => "one_by_one",
                    'label' => Mage::helper('promobox')->__('One By One'),
                ),
                array(
                    'value' => "one_by_two",
                    'label' => Mage::helper('promobox')->__('One By Two'),
                ),
            ),
        ));



        if (Mage::getSingleton('adminhtml/session')->getBannerData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBannerData());
            Mage::getSingleton('adminhtml/session')->setBannerData(null);
        } elseif (Mage::registry('banner_data')) {
            $tmp = Mage::registry('banner_data')->getData();
            $tmp['image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'promobox' . DS . $tmp['image'];
            $form->setValues($tmp);
        }

        return parent::_prepareForm();
    }

}
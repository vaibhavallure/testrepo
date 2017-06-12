<?php

class Ecp_Footlinks_Block_Adminhtml_Footlinks_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('footlinks_form', array('legend'=>Mage::helper('footlinks')->__('Link information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('footlinks')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));
      
      $fieldset->addField('link', 'text', array(
          'label'     => Mage::helper('footlinks')->__('Link'),
          'name'      => 'link'
      ));            

      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('footlinks')->__('Status'),
          'name'      => 'status',
          'values'    => Mage::getModel('ecp_footlinks/status')->getOptionArray()
      ));      
            
      $fieldset->addField('use_for_seo_text', 'checkbox', array(
          'label'     => Mage::helper('footlinks')->__('Use for seo text'),
          'name'      => 'use_for_seo_text',
          'onchange'  => "changeForSeo(this)",
          'checked'   => Mage::registry('footlinks_data')->getData('use_for_seo_text'),
          'after_element_html' => '<small>Select if want to use as seo text container</small>'
        ));
      
      $fieldset->addField('block_for_seo_default', 'select', array(
            'label'     => Mage::helper('footlinks')->__('Block for seo default'),
            'name'      => 'block_for_seo_default',
            'values'    => Mage::getModel('cms/block')->getCollection()->toOptionArray()
        ));
      
      $fieldset->addField('block_for_home_seo', 'select', array(
            'label'     => Mage::helper('footlinks')->__('Block for seo home'),
            'name'      => 'block_for_home_seo',
            'values'    => Mage::getModel('cms/block')->getCollection()->toOptionArray()
        ));
      
      $fieldset->addField('type', 'select', array(
          'label'     => Mage::helper('footlinks')->__('Type'),
          'class'     => 'required-entry',
          'name'      => 'type',
          'onchange'  => 'hideSelected()',
          'values'    => Mage::getModel('ecp_footlinks/type')->getOptionArray()
      ));
      
        $fieldset->addField('block_value', 'select', array(
            'label'     => Mage::helper('footlinks')->__('Block'),
            'name'      => 'block_value',
            'values'    => Mage::getModel('cms/block')->getCollection()->toOptionArray()
        ));

        $fieldset->addField('url_value', 'text', array(
            'label'     => Mage::helper('footlinks')->__('Url'),          
            'name'      => 'url_value'
        ));
        
      $form->setValues(Mage::registry('footlinks_data')->getData());
      return parent::_prepareForm();
  }
}
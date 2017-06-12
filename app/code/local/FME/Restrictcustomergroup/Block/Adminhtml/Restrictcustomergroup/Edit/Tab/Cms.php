<?php

class FME_Restrictcustomergroup_Block_Adminhtml_Restrictcustomergroup_Edit_Tab_Cms
  extends Mage_Adminhtml_Block_Widget_Form {
	
  protected function _prepareForm() {
	
      $form = new Varien_Data_Form();
	  $_helper = Mage::helper('restrictcustomergroup');
	  $cmsArr = $_helper->getAllCmsPages();
	  $otherPagesArr = $_helper->getOtherPages();
      $this->setForm($form);
      $fieldset = $form->addFieldset('restrictcustomergroup_form', array('legend' => Mage::helper('restrictcustomergroup')->__('Restrict Cms Page(s)')));
     
      $fieldset->addField('cms_pages','multiselect',array(
			'name'      => 'cms_pages[]',
            'label'     => $_helper->__('CMS Pages'),
            'title'     => $_helper->__('CMS Pages'),
            'required'  => false,
			'values'    => $cmsArr
	  ));
     
	  $fieldset->addField('other_pages','multiselect',array(
			'name'      => 'other_pages[]',
            'label'     => $_helper->__('Other Pages'),
            'title'     => $_helper->__('Other Pages'),
            'required'  => false,
			'values'    => $otherPagesArr,
      ));
	  
      if ( Mage::getSingleton('adminhtml/session')->getRestrictcustomergroupData() ) {
		
          $form->setValues(Mage::getSingleton('adminhtml/session')->getRestrictcustomergroupData());
          Mage::getSingleton('adminhtml/session')->setRestrictcustomergroupData(null);
     
	  } elseif ( Mage::registry('restrictcustomergroup_data') ) {
     
	      $form->setValues(Mage::registry('restrictcustomergroup_data')->getData());
      }
     
	  return parent::_prepareForm();
  }
}
<?php

class FME_Restrictcustomergroup_Block_Adminhtml_Restrictcustomergroup_Edit_Tab_Form
  extends Mage_Adminhtml_Block_Widget_Form {
	
  protected function _prepareForm() {
	
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('restrictcustomergroup_form', array('legend' => Mage::helper('restrictcustomergroup')->__('Basic information')));
      $model = Mage::registry('restrictcustomergroup_data');
	  
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('restrictcustomergroup')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

	  $fieldset->addField('priority', 'text', array(
          'label'     => Mage::helper('restrictcustomergroup')->__('Priority'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'priority',
      ));
	  
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('restrictcustomergroup')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('restrictcustomergroup')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('restrictcustomergroup')->__('Disabled'),
              ),
          ),
      ));
	  
      $fieldset->addField('customer_groups', 'multiselect', array(
        'label'     => Mage::helper('restrictcustomergroup')->__('Customer Groups'),
        'name'      => 'customer_groups',
        'values'    => Mage::getResourceModel('customer/group_collection')->toOptionArray(),
		'class'     => 'required-entry',
		'required' => true
      ));
	 
	  /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode())
		{
            $field = $fieldset->addField('store_id', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => Mage::helper('restrictcustomergroup')->__('Store View'),
                'title'     => Mage::helper('restrictcustomergroup')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        }
        else
		{
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
			
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }
		
		
	  $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array('tab_id' => 'form_section'));
	  $wysiwygConfig["files_browser_window_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index');
	  $wysiwygConfig["directives_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
	  $wysiwygConfig["directives_url_quoted"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
	  $wysiwygConfig["widget_window_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index');
	  $wysiwygConfig["files_browser_window_width"] = (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_width');
	  $wysiwygConfig["files_browser_window_height"] = (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_height');
	  $plugins = $wysiwygConfig->getData("plugins");
	  $plugins[0]["options"]["url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin');
	  $plugins[0]["options"]["onclick"]["subject"] = "MagentovariablePlugin.loadChooser('" . Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin') . "', '{{html_id}}');";
	  $plugins = $wysiwygConfig->setData("plugins", $plugins);
	  
      $fieldset->addField('description', 'editor', array(
          'name'      => 'description',
          'label'     => Mage::helper('restrictcustomergroup')->__('Error Message'),
          'title'     => Mage::helper('restrictcustomergroup')->__('Error Message'),
          'style'     => 'width:600px; height:300px;',
          'config'   => $wysiwygConfig,
          'required'  => false,
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

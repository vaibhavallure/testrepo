<?php

class Entrepids_Backup_Block_Adminhtml_Backup_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('backup_form', array('legend'=>Mage::helper('entrepids_backup')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('entrepids_backup')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('entrepids_backup')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('entrepids_backup')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('entrepids_backup')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('entrepids_backup')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('entrepids_backup')->__('Content'),
          'title'     => Mage::helper('entrepids_backup')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getBackupData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBackupData());
          Mage::getSingleton('adminhtml/session')->setBackupData(null);
      } elseif ( Mage::registry('backup_data') ) {
          $form->setValues(Mage::registry('backup_data')->getData());
      }
      return parent::_prepareForm();
  }
}
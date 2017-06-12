<?php

class Entrepids_Backup_Block_Adminhtml_Backup_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'entrepids_backup';
        $this->_controller = 'adminhtml_backup';
        
        $this->_updateButton('save', 'label', Mage::helper('entrepids_backup')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('entrepids_backup')->__('Delete Item'));
	
//        $this->_addButton('saveandcontinue', array(
//            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
//            'onclick'   => 'saveAndContinueEdit()',
//            'class'     => 'save',
//        ), -100);
        
        print_r(get_class_methods($this));

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('backup_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'backup_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'backup_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText() {
        return Mage::helper('form')->__('My Form Container');
    }
}
<?php
/**
 *
 */
class Allure_Salesforce_Block_Adminhtml_Import_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Add fieldset
     *
     * @return Mage_ImportExport_Block_Adminhtml_Import_Edit_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/uploadsave'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('importexport')->__('Import Settings')));
       
        $fieldset->addField("import_file", 'file', array(
            'name'     => 'import_file',
            'label'    => Mage::helper('importexport')->__('Select File to Import'),
            'title'    => Mage::helper('importexport')->__('Select File to Import'),
            'required' => true
        ));
        
        $fieldset->addField("object_type", 'select', array(
            'name'     => 'object_type',
            'label'    => Mage::helper('importexport')->__('Select Object'),
            'title'    => Mage::helper('importexport')->__('Select Object'),
            'required' => true,
            'values'   => Mage::helper("allure_salesforce/csv")->getObjectOptionArray()
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

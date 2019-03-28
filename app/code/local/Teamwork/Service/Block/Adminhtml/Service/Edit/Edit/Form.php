<?php

class Teamwork_Service_Block_Adminhtml_Service_Edit_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $helper = Mage::helper('teamwork_service');
        
        $model = Mage::registry('model');
               
        $columnNames = array_keys($model[0]);

        $form = new Varien_Data_Form(array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save', array(
                    'entity_id' => $this->getRequest()->getParam('entity_id'),
                    'entity' => $this->getRequest()->getParam('entity')
                    )),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ));
        
        $fieldset = $form->addFieldset('general_form', array(
                    'legend' => $helper->__("Edit information")
                ));
                
        foreach ($columnNames AS $columnName)
        {
            if($columnName != 'entity_id')
            {
                $fieldset->addField("$columnName", 'text', array(
                'label' => $helper->__("$columnName"),
                'name' => "$columnName",
                ));
            }
        }
        
        $form->setUseContainer(true);
        $form->setValues($model[0]);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
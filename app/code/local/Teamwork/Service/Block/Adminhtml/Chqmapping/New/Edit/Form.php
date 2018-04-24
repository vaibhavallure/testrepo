<?php

class Teamwork_Service_Block_Adminhtml_Chqmapping_New_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareLayout()
    {
        $this->setChild('continue_button',
        $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label'     => Mage::helper('teamwork_service')->__('Continue'),
                'class'     => 'save',
                'onclick'   => "document.forms['chqmapping_form'].submit();",
                ))
        );
        return parent::_prepareLayout();
    }
    
    protected function _prepareForm()
    {  
       $helper = Mage::helper('teamwork_service');
       
       $form = new Varien_Data_Form(array(
            'id' => 'chqmapping_form',
            'action' => $this->getUrl('*/*/new'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));
                
        $fieldset = $form->addFieldset('mapping', array('legend'=>Mage::helper('teamwork_service')->__('Create Chq Mapping')));
      

        $fieldset->addField('type', 'select', array(
            'label' => 'Product Type',
            'name' => 'type',
            'values' => array('Style' => 'Configurable Product', 'Item' => 'Simple Product'),
        ));
        
        
        $fieldset->addField('channel_id', 'select', array(
            'label' => 'Channel name',
            'name' => 'channel_id',
            'values' => $helper->getChannelsList(),
        ));

        $fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));
        
        $form->setUseContainer(true);
        $this->setForm($form);
    }
}

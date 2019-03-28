<?php

class Teamwork_Service_Block_Adminhtml_Chqmapping_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {  
       
       $channelId = $this->getRequest()->getParam('channel_id');
       $typeId = $this->getRequest()->getParam('type');
       
       $model = Mage::registry('model');
       
       $model->setChannelId($channelId);
       
       $helper = Mage::helper('teamwork_service');
       
       $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array(
                        'id' => $this->getRequest()->getParam('id')
                    )),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));
                
        if(!empty($model))
        {
            $fieldset = $form->addFieldset('mapping', array('legend'=>Mage::helper('teamwork_service')->__('Edit Chq Mapping')));
        }
        else
        {
            $fieldset = $form->addFieldset('mapping', array('legend'=>Mage::helper('teamwork_service')->__('Create Chq Mapping')));
        }
        
        $fieldset->addField('channel_id', 'hidden', array(
            'name'  => 'channel_id',
            'value' => $channelId
        ));

        $fieldset->addField('attribute_id', 'select', array(
            'label' => 'Attribute Label',
            'name' => 'attribute_id',
            'values' => $helper->getAttributeOptions($channelId, $typeId, $model['attribute_id']),        
        ));
        
        $fieldset->addField('field_id', 'select', array(
            'label' => 'CHQ label',
            'name' => 'field_id',
            'required' => true,
            'values' => $helper->getMappingfieldOptions($typeId),        
        ));
        
        $fieldset->addField('push_once', 'select', array(
            'label' => 'Push Once',
            'name' => 'push_once',
            'required' => true,
            'values' => array(
                0 => $helper->__('No'),
                1 => $helper->__('Yes')),      
        ));
        
        $form->setUseContainer(true);
        $form->setValues($model->getData());
        $this->setForm($form);

    }
}

<?php

class Teamwork_Service_Block_Adminhtml_Richmedia_Mapping_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{   
    protected function _prepareForm()
    {
        $channel_id = $this->getRequest()->getParam('channel');
        
        $helper = Mage::helper('teamwork_service');
        
        $form = new Varien_Data_Form(array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/add', array(
                        'channel' => $channel_id
                    )),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ));
        
        $fieldset = $form->addFieldset('general_form', array(
                    'legend' => $helper->__("Add new mapping")
                ));
                
        $fieldset->addField('channel_id', 'hidden', array(
        'name'  => 'channel_id',
        'value' => $channel_id
        ));
            
        $fieldset->addField('attribute_id', 'select', array(
            'label' => $helper->__('Attribute label'),
            'required' => true,
            'name' => 'attribute_id',
            'values' => $helper->getMappingAttributeOptions($channel_id),
        ));
        
        $fieldset->addField('media_index', 'select', array(
            'label' => $helper->__('Rich media name'),
            'required' => true,
            'name' => 'media_index',
            'values' => $helper->getListTemplateElements($channel_id),
        ));
        
        
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}
<?php

class Teamwork_Service_Block_Adminhtml_Richmedia_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{   
    protected function _prepareForm()
    {
        $channel_id = $this->getRequest()->getParam('channel');
        
        $helper = Mage::helper('teamwork_service');
        
        $form = new Varien_Data_Form(array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ));
        
        $fieldset = $form->addFieldset('general_form', array(
                    'legend' => $helper->__("List mapping")
                ));
                
        $fieldset->addField('channel_id', 'hidden', array(
        'name'  => 'channel_id',
        'value' => $channel_id
        ));
        
        $richmediaCollection = Mage::getModel('teamwork_service/richmedia')->getCollection()
            ->addFieldToFilter('channel_id', $channel_id);
        
        foreach($richmediaCollection as $richmedia)
        {
            $attribute = Mage::getModel('eav/entity_attribute')->load($richmedia['attribute_id']);
           
            $attributeId = $attribute['attribute_id'];
           
            $fieldset->addField($attribute['attribute_code'], 'select', array(
                'label' => $helper->__($attribute['frontend_label']),
                'name' => $attribute['attribute_id'],
                'value' => $richmedia['media_index'],
                'values' => $helper->getListTemplateElements($channel_id),
                'after_element_html' =>'<td class="value"> <input name='.$attributeId.'_delete type="checkbox" /> '. $helper->__('[Delete this mapping]'). '</td>',
            ));            
        }
        
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
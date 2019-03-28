<?php

class Teamwork_Service_Block_Adminhtml_Confattrmap_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {  

       $model = Mage::registry('model');
       
       $helper = Mage::helper('teamwork_service');
       
       $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('_current'=>true)),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $fieldset = $form->addFieldset('mapping', array('legend'=>Mage::helper('teamwork_service')->__('CHQ Attribute Mapping')));

        
        $readOnly = ($this->getAttributeAssignedConfProducts($model->getData('chq_internal_id'))->count() > 0); 
        
        if ($readOnly)
        {
            $fieldset->addField('req_text', 'note', array(
                'text' => '<ul class="messages"><li class="notice-msg"><ul><li>'
                    .  $this->__('Do remove configurable products which are use "%s" magento attribute before applying another one magento attribute. But you can still change values mapping.', $model->getData('magento_attribute_code'))
                    . '</li></ul></li></ul>'
            ));
        }


        $data = array(
            'label' => $helper->__('Is Active'),
            'name' => 'is_active',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            'value' => $model->getData('is_active'),
            'required' => true,
        );

        if ($readOnly)
        {
            $data['disabled'] = 'disabled';
        }
        $fieldset->addField('is_active', 'select', $data);
        
        
        
        $typeInstance = Mage::getModel('catalog/product')->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)->getTypeInstance(true);
        
        $entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
        
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($entityTypeId)
            ->setOrder('attribute_code','ASC');
        
        $options = array(
            array(
                'label' => $helper->__('-- please select --'),
                'value' => 0,
            ),
        );

        foreach ($attributes as $attribute) {
            if ($typeInstance->canUseAttribute($attribute)) {
                $options[] = array(
                    'label' => $attribute->getData('attribute_code') . " (" . $attribute->getData('frontend_label') . ")",
                    'value' => $attribute->getData('attribute_id'),
                );
            }
        }
        
        $data = array(
            'label' => $helper->__('Magento Attribute'),//$model->getData('chq_code'),
            'name' => 'attribute_id',
            'values' => $options,
            'value' => $model->getData('chq_internal_id'),
            'required' => true,
        );

        if ($readOnly)
        {
            $data['disabled'] = 'disabled';
        }

        $fieldset->addField('attribute_id', 'select', $data);
        
        
        $options = array();
        foreach(Teamwork_Service_Model_Confattrmapprop::getValuesMappingOptions() as $value => $label)
        {
            $options[] = array(
                'label' => $label,
                'value' => $value,
            );
        }

        $fieldset->addField('values_mapping', 'select', array(
            'label' => $helper->__('Values Mapping'),
            'name' => 'values_mapping',
            'values' => $options,
            'value' => $model->getData('values_mapping'),
            'required' => true,
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
    }
    
    static public function getAttributeAssignedConfProducts($attributeId)
    {
        $productCollection = Mage::getResourceModel('catalog/product_collection');
        //$productCollection->addAttributeToSelect(array('name'));
        $productCollection->getSelect()->join(array('link_table' => 'catalog_product_super_attribute'),'link_table.product_id = e.entity_id', array('product_id','attribute_id'));
        $productCollection->getSelect()->where('link_table.attribute_id=?', $attributeId);
        return $productCollection; 
    }
    
}

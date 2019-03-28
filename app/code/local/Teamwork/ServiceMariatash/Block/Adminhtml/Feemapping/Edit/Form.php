<?php

class Teamwork_ServiceMariatash_Block_Adminhtml_Feemapping_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {   
       $model = Mage::registry('model');
	   
	   Mage::log($model, null, 'temp.log');
       
       $helper = Mage::helper('teamwork_servicemariatash/feemapping');
	   
       
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
            $fieldset = $form->addFieldset('general_form', array('legend'=>Mage::helper('teamwork_service')->__('Edit')));
        }
        else
        {
            $fieldset = $form->addFieldset('general_form', array('legend'=>Mage::helper('teamwork_service')->__('Create')));
        }

        $fieldset->addField('shipping_id', 'select', array(
            'label' => 'Shipping',
            'name' => 'shipping_id',
			'required' => true,
            'values' => $helper->getServiceFeeMapping(),        
        ));
        
        $fieldset->addField('fee_id', 'select', array(
            'label' => 'Fee',
            'name' => 'fee_id',
            'required' => true,
            'values' => $helper->getServiceFee(),        
        ));
        
        $form->setUseContainer(true);
		if(!empty($model))
		{
			$form->setValues($model->getData());
		}
        $this->setForm($form);
    }
}

<?php 
class Allure_Store_Block_Adminhtml_System_Store_Edit_Form extends Mage_Adminhtml_Block_System_Store_Edit_Form{
    protected function _prepareForm(){
        parent::_prepareForm();
        if (Mage::registry('store_type') == 'store'){
            $storeModel = Mage::registry('store_data');
            $fieldset = $this->getForm()->getElement('store_fieldset');
            $fieldset->addField('is_copy_old_product', 'select', array(
                    'name'      => 'store[is_copy_old_product]',
                    'label'     => Mage::helper('core')->__('Is copy old product'),
                    'value'        => $storeModel->getData('is_copy_old_product') ,
                    'options'   => array(
		                	0 => Mage::helper('adminhtml')->__('No'),
		                	1 => Mage::helper('adminhtml')->__('Yes')
                    	),
                ));
        }
        return $this;
    }
}

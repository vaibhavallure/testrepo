<?php

/*
 *  Magestore_Webpos v2.3.1
 *  Updated by Daniel
 */

class Magestore_Webpos_Model_Source_Adminhtml_Producttaxclass {

    public function toOptionArray() {
        $options = array();
        $options[] = array('value' => '0', 'label' => Mage::helper('webpos')->__('None'));
        $taxClassIds = Mage::getModel('tax/class')->getCollection()->addFieldToFilter('class_type', 'PRODUCT');
        if (count($taxClassIds) > 0):
            foreach ($taxClassIds as $taxClass):
                $title = $taxClass->getData('class_name') ? $taxClass->getData('class_name') : $taxClass->getData('class_id');
                $options[] = array('value' => $taxClass->getData('class_id'), 'label' => $title);
            endforeach;
        endif;
        return $options;
    }

}

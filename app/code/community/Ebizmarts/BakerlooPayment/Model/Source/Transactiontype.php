<?php

class Ebizmarts_BakerlooPayment_Model_Source_Transactiontype {

    private function _getTypes() {
        $helper = Mage::helper('bakerloo_payment');
        
        $types = Mage::getConfig()->getNode('default/payment/bakerloo_pay_at_till/types')->asArray();
        foreach ($types as $k => $v) {
            $types[$k] = $helper->__($v);            
        }
        
        return $types;
    }

    public function toOptionArray() {
        $options =  array();

        $types = $this->_getTypes();

        foreach ($types as $code => $name) {
            $options[] = array(
                'value' => $code,
                'label' => $name
            );
        }

        return $options;
    }

    public function toOption() {
        $options =  array();

        $types = $this->_getTypes();

        foreach ($types as $code => $name) {
            $options[$code] = $name;
        }

        return $options;
    }

}
<?php

class Ebizmarts_BakerlooPayment_Model_Source_Transactiontype {

    private function _getTypes() {
        return array(
            'cash'         => Mage::helper('bakerloo_payment')->__('Cash'),
            'credit_card'  => Mage::helper('bakerloo_payment')->__('Credit Card'),
            'debit_card'   => Mage::helper('bakerloo_payment')->__('Debit Card'),
        );
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
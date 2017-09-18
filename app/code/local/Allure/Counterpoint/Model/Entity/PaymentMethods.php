<?php

class Allure_Counterpoint_Model_Entity_PaymentMethods
{
    public function toOptionArray(){
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();
        $methods = array();
        $methods[''] = array('label' => "",'value' => "");
        foreach ($payments as $paymentCode=>$paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
            $methods[$paymentCode] = array('label'   => $paymentTitle,
                'value' => $paymentCode,
            );
        }
        return $methods;
    }
}
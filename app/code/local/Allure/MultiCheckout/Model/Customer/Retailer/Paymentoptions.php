<?php

class Allure_MultiCheckout_Model_Customer_Retailer_Paymentoptions
{

    /**
     * Provide available options as a value/label array
     *
     * @return array
     */
    public function toOptionArray ()
    {
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();
        // $methods = array(array('value'='',
        // 'label'=>Mage::helper('adminhtml')->__('–Please Select–')));
        
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
            $methods[$paymentCode] = array(
                    'label' => $paymentTitle,
                    'value' => $paymentCode
            );
        }
        return $methods;
    }
}

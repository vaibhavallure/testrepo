<?php

class Ebizmarts_BakerlooPayment_Model_Multiple extends Ebizmarts_BakerlooPayment_Model_Method_Abstract
{

    protected $_code  = "bakerloo_multiple";

    protected $_infoBlockType = 'bakerloo_payment/info_multiple';

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Method_Abstract
     */
    public function assignData($data)
    {

        $allPayments = $data->getData('addedPayments');

        foreach ($allPayments as $k => $payment) {
            /** @var Mage_Payment_Model_Method_Abstract $instance */
            $instance = Mage::helper('payment')->getMethodInstance($payment['method']);
            if ($instance instanceof Ebizmarts_BakerlooPayment_Model_Method_Abstract) {
                $allPayments[$k]['comments'] = $instance->getAdditionalDetails($payment);
            }
        }

        $this->getInfoInstance()->setPosPaymentInfo(serialize($allPayments));
        return $this;
    }
}

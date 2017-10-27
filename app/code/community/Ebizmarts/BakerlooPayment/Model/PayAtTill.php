<?php


class Ebizmarts_BakerlooPayment_Model_PayAtTill extends Ebizmarts_BakerlooPayment_Model_Method_Abstract
{

    protected $_code          = "bakerloo_pay_at_till";
    protected $_infoBlockType = 'bakerloo_payment/info_payAtTill';

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Ebizmarts_BakerlooPayment_Model_PayAtTill
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $reference = array(
            'till_number' => $data->getData('payReference'),
            'transaction_type' => $data->getTransactionType()
        );

        $info = $this->getInfoInstance();
        $info->setPosPaymentInfo(serialize($reference));

        return $this;
    }
}
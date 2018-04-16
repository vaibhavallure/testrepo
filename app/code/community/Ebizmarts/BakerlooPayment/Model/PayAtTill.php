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

    public function getAdditionalDetails($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $helper = Mage::helper('bakerloo_payment');
        $output = parent::getAdditionalDetails($data);

        if ($data->getData('payReference')) {
            $output .= "<br />" . $helper->__("Till reference: %s", $data->getData('payReference'));
        }

        if ($data->getTransactionType()) {
            $typeOptions = Mage::getModel('bakerloo_payment/source_transactiontype')->toOption();

            if(isset($typeOptions[$data->getTransactionType()])) {
                $output .= "<br />" . $helper->__("Payment method: %s", $typeOptions[$data->getTransactionType()]);
            }
        }

        return $output;
    }
}
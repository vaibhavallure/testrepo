<?php

class Ebizmarts_BakerlooPayment_Block_Info_PayAtTill extends Ebizmarts_BakerlooPayment_Block_Info_Default {


    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml() {

        $output = $this->getMethod()->getTitle();

        $posPaymentInfo = unserialize($this->getInfo()->getPosPaymentInfo());

        if(isset($posPaymentInfo['till_number']))
            $output .= "<br />" . $this->__("Till reference: %s", $posPaymentInfo['till_number']);

        if(isset($posPaymentInfo['transaction_type'])) {
            $typeOptions = Mage::getModel('bakerloo_payment/source_transactiontype')->toOption();

            if(isset($typeOptions[$posPaymentInfo['transaction_type']]))
                $output .= "<br />" . $this->__("Payment method: %s", $typeOptions[$posPaymentInfo['transaction_type']]);
        }

        return $output;

    }


}
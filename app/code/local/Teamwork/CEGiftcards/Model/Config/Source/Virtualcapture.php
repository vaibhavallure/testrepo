<?php

class Teamwork_CEGiftcards_Model_Config_Source_Virtualcapture
{
    const CONFIG_PATH_VIRTUAL_CAPTURE = 'teamwork_cegiftcards/general/virtual_capture';
    const FORCE_PAYMENT = 'force_payment';
    const CREATE_INVOICE = 'create_invoice';

    public function toOptionArray()
    {
        return array(
            array(
                'label' => 'Force Payment To Be Captured During Checkout',
                'value' => self::FORCE_PAYMENT
            ),
            array(
                'label' => 'Create Invoice Immediately After Payment',
                'value' => self::CREATE_INVOICE
            ),
        );
    }
}

<?php

class MT_Checkout_Block_Adminhtml_Sales_Order_Create_Signature extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_create_signature_method');
    }

    public function getHeaderText()
    {
        return Mage::helper('allure_mtcheckout')->__('Signature Required ?');
    }

    public function getHeaderCssClass()
    {
        return 'head-signature-method';
    }
}

<?php
class IWD_OrderManager_Block_Adminhtml_Sales_Order_Payment_Authorizenet_Info_Cc extends Mage_Paygate_Block_Authorizenet_Info_Cc{

    const CONFIG_XML_NOTIFY_AUTHORIZENET_CHECKOUT_PROGRESS = 'iwd_ordermanager/edit/authorizenet_checkout_progress';

    public function __construct()
    {
        parent::__construct();

        $this->_isCheckoutProgressBlockFlag = Mage::getStoreConfig(self::CONFIG_XML_NOTIFY_AUTHORIZENET_CHECKOUT_PROGRESS) ? true : false;
    }
}
<?php
class IWD_OrderManager_Block_Adminhtml_Sales_Order_Info_Form extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('iwd/ordermanager/info/form.phtml');
    }

    public function getStatusList()
    {
        return Mage::getModel('sales/order_status')->getResourceCollection()->getData();
    }

    public function getStateList()
    {
        $helper = Mage::helper('iwd_ordermanager');
        return array('new' => $helper->__('New'),
            'pending_payment' => $helper->__('Pending Payment'),
            'processing' => $helper->__('Processing'),
            'complete' => $helper->__('Complete'),
            'closed' => $helper->__('Closed'),
            'canceled' => $helper->__('Canceled'),
            'holded' => $helper->__('Holded'),
            'payment_review' => $helper->__('Payment Review')
        );
    }
}
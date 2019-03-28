<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Sales' . DS . 'OrderController.php';

class Teamwork_Transfer_Teamworktransfer_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    public function resendToChqAction()
    {
        $orderIds        = $this->getRequest()->getPost('order_ids', array());
        $webstagingModel = Mage::getModel('teamwork_transfer/webstaging')->resendOrdersToChq($orderIds);

        $this->_redirect('*/sales_order/');
    }
}
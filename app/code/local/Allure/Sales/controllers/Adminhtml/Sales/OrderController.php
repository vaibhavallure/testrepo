<?php

/**
 * 
 * @author Allure
 * 
 */

require_once("Mage/Adminhtml/controllers/Sales/OrderController.php");
class Allure_Sales_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    /**
     * Print orders for selected orders
     */
    public function pdfordersAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                $flag = true;
                if (!isset($pdf)){
                    $pdf = Mage::getModel('allure_sales/pdf_order')->getPdf($order);
                } else {
                    $pages = Mage::getModel('allure_sales/pdf_order')->getPdf($order);
                    $pdf->pages = array_merge ($pdf->pages, $pages->pages);
               }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'order'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                    'application/pdf'
                    );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }
    
}

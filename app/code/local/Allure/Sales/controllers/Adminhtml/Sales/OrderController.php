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
    public function pdfdocsAction(){
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
                
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->setOrderFilter($orderId)
                ->load();
                if ($invoices->getSize()){
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
                
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->setOrderFilter($orderId)
                ->load();
                if ($shipments->getSize()){
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
                
                $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                ->setOrderFilter($orderId)
                ->load();
                if ($creditmemos->getSize()) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'docs'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf',
                    $pdf->render(), 'application/pdf'
                    );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }
    
    /**
     * Generate gift receipt if order contains gift item.
     */
    public function pdfOrdersGiftReceiptAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                try{
                    foreach ($order->getAllVisibleItems() as $item){
                        if($item->getIsGiftItem()){ 
                            if ($item->getParentItem()) {
                                continue;
                            }
                            
                            if (!isset($pdf)){
                                $flag = true;
                                $pdf = Mage::getModel('allure_sales/pdf_gift')->getPdf($item);
                            }else{
                                $pages = Mage::getModel('allure_sales/pdf_gift')->getPdf($item);
                                $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                            }
                        }
                    }
                }catch (Exception $e){
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'order_gift_receipt'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
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

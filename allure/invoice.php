<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

$from = $_GET['from'];
$to= $_GET['to'];
$store= $_GET['store'];

/* if(empty($from) || empty($to) || empty($store)){
    die('Please add Store Id ,from & to date limit');
} */

if(empty($store)){
 die('Please add Store Id ');
 }

$count=0;
$from= date("Y-m-d 00:00:00",strtotime($from));
$to = date("Y-m-d 23:59:59",strtotime($to));
$orders=Mage::getModel("sales/order")->getCollection()->addAttributeToFilter('old_store_id',$store)
//->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to))
->addAttributeToFilter('status', array('complete','processing','pending'));

foreach ($orders as $order){

    $order=Mage::getModel('sales/order')->load($order->getId());
    if($order->canInvoice())
    {
        $count++;
        echo $count.'.'.$order->getIncrementId();
        echo "<br>";
        Mage::log("ORDER ID::".$order->getIncrementId(),Zend_log::DEBUG,'invoice.log',true);
    }
    try {
        if ($order->canInvoice()) {
            
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            
            if (! $invoice->getTotalQty()) {
                Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
            }
            
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
            $invoice->register();
            $transactionSave = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
          
            Mage::log("INVOICE::".$invoice->getId(), Zend_log::DEBUG, 'invoice.log', true);
        }
        if ($order->canShip()) {
            try {
                
                $convertor = Mage::getModel('sales/convert_order');
                $shipment = $convertor->toShipment($order);
                foreach ($order->getAllItems() as $orderItem) {
                    if ($orderItem->getQtyToShip() && !$orderItem->getIsVirtual()) {
                        $item = $convertor->itemToShipmentItem($orderItem);
                        $item->setQty($orderItem->getQtyToShip());
                        $shipment->addItem($item);
                    }
                }
                $shipment->setEmailSent(TRUE);
                $shipment->register();
                $order->setIsInProcess(true);
                Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($order)
                ->save();
               
                Mage::log("SHIPMENT::".$shipment->getId(), Zend_log::DEBUG, 'invoice.log', true);
                
            } catch (Exception $e) {
                throw $e;
            }
        }
    }
    
    catch (Mage_Core_Exception $e) {
        
    }

}

die("finish");
<?php

require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

$needle='allureinc';
$fromDate='2016-01-01 00:00:00';
$tomDate='2018-05-05 00:00:00';
$cntr=0;
$orders = Mage::getModel('sales/order')->getCollection()
->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$tomDate))
    ->addAttributeToFilter('customer_email', array('like' => '%allureinc%'));


foreach ($orders as $o) {
    //load order object - I know it's not ok to use load in a loop but it should be ok since it's a one time script
    $order = Mage::getModel('sales/order')->load($o->getId());
    
    $invoices = $order->getInvoiceCollection();
    foreach ($invoices as $invoice){
        //delete all invoice items
        $items = $invoice->getAllItems();
        foreach ($items as $item) {
            $item->delete();
        }
        //delete invoice
        $invoice->delete();
    }
    $creditnotes = $order->getCreditmemosCollection();
    foreach ($creditnotes as $creditnote){
        //delete all creditnote items
        $items = $creditnote->getAllItems();
        foreach ($items as $item) {
            $item->delete();
        }
        //delete credit note
        $creditnote->delete();
    }
    $shipments = $order->getShipmentsCollection();
    foreach ($shipments as $shipment){
        //delete all shipment items
        $items = $shipment->getAllItems();
        foreach ($items as $item) {
            $item->delete();
        }
        //delete shipment
        $shipment->delete();
    }
    //delete all order items
    $items = $order->getAllItems();
    foreach ($items as $item) {
        $item->delete();
    }
    //delete payment - not sure about this one
    $order->getPayment()->delete();
    //delete quote - this can be skipped
    if ($order->getQuote()) {
        foreach ($order->getQuote()->getAllItems() as $item) {
            $item->delete();
        }
        $order->getQuote()->delete();
    }
    //delete order
    $cntr++;
    Mage::log($cntr.'-'.$order->getId(),Zend_log::DEBUG,'deleteOrders.log',TRUE);
    $order->delete();
}
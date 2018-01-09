<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

$from = $_GET['from'];
$to= $_GET['to'];
$store= $_GET['store'];

if(empty($from) || empty($to) || empty($store)){
    die('Please add Store Id ,from & to date limit');
}

$count=0;
$from= date("Y-m-d 00:00:00",strtotime($from));
$to = date("Y-m-d 23:59:59",strtotime($to));
$orders=Mage::getModel("sales/order")->getCollection()->addAttributeToFilter('store_id',$store)
->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to))
->addAttributeToFilter('status', array('complete','processing','pending'));
foreach ($orders as $order){

    $order=Mage::getModel('sales/order')->load($order->getId());
    if($order->canInvoice())
    {
        $count++;
        echo $count.'.'.$order->getIncrementId();
        echo "<br>";
        Mage::log($order->getIncrementId(),Zend_log::DEBUG,'invoice.log',true);
    }
    try {
        if(!$order->canInvoice())
        {
            Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
        }
        
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
        
        if (!$invoice->getTotalQty()) {
            Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
        }
        
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $transactionSave = Mage::getModel('core/resource_transaction')
        ->addObject($invoice)
        ->addObject($invoice->getOrder());
        $transactionSave->save();
        Mage::log($invoice->getId(),Zend_log::DEBUG,'invoice.log',true);
    }
    catch (Mage_Core_Exception $e) {
        
    }
}
die("finish");
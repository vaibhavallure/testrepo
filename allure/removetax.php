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
$to = date("Y-m-d 00:00:00",strtotime($to));
$orders=Mage::getModel("sales/order")->getCollection()->addAttributeToFilter('store_id',$store)
->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to));
$orders->addAttributeToFilter('status', array('nin' =>array('closed')));
foreach ($orders as $order){
    try {
        $order=Mage::getModel('sales/order')->load($order->getEntityId());
        if($order->getBaseTaxAmount() > 0){
            $order->setBaseTaxInvoiced($order->getBaseTaxInvoiced()-$order->getBaseTaxAmount());
            $order->setTaxAmount($order->getTaxAmount()-$order->getBaseTaxAmount());
            $order->setGrandTotal($order->getGrandTotal()-$order->getBaseTaxAmount());
            $order->setBaseTotalPaid($order->getBaseTotalPaid()-$order->getBaseTaxAmount());
            $order->setBaseTotalInvoiced($order->getBaseTotalInvoiced()-$order->getBaseTaxAmount());
            $order->setBaseGrandTotal($order->getBaseGrandTotal()-$order->getBaseTaxAmount());
            $order->setTaxInvoiced($order->getTaxInvoiced()-$order->getBaseTaxAmount());
            $order->setTotalInvoiced($order->getTotalInvoiced()-$order->getBaseTaxAmount());
            $order->setTotalPaid($order->getTotalPaid()-$order->getBaseTaxAmount());
            $order->setBaseSubtotalInclTax($order->getBaseSubtotalInclTax()-$order->getBaseTaxAmount());
            $order->setSubtotalInclTax($order->getSubtotalInclTax()-$order->getBaseTaxAmount());
            $order->setBaseTaxAmount($order->getBaseTaxAmount()-$order->getBaseTaxAmount());
            $items=$order->getAllVisibleItems();
            foreach ($items as $item){
                if($item->getTaxPercent()>0)
                    $item->setBaseTaxAmount($item->getBaseTaxAmount()-$item->getTaxAmount());
                    $item->setTaxInvoiced($item->getTaxInvoiced()-$item->getTaxAmount());
                    $item->setBaseTaxInvoiced($item->getBaseTaxInvoiced()-$item->getTaxAmount());
                    $item->setPriceInclTax($item->getPriceInclTax()-$item->getTaxAmount());
                    $item->setBasePriceInclTax($item->getBasePriceInclTax()-$item->getTaxAmount());
                    $item->setRowTotalInclTax($item->getRowTotalInclTax()-$item->getTaxAmount());
                    $item->setBaseRowTotalInclTax($item->getBaseRowTotalInclTax()-$item->getTaxAmount());
                    $item->setTaxPercent(0);
                    $item->setTaxAmount(0);
                    $item->save();
                    
            }
            foreach ($order->getInvoiceCollection() as $invoice){
                
                if($invoice->getTaxAmount() > 0)
                {
                    $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()-$invoice->getTaxAmount());
                    $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount()-$invoice->getTaxAmount());
                    $invoice->setGrandTotal($invoice->getGrandTotal()-$invoice->getTaxAmount());
                    $invoice->setSubtotalInclTax($invoice->getSubtotalInclTax()-$invoice->getTaxAmount());
                    $invoice->setBaseSubtotalInclTax($invoice->getBaseSubtotalInclTax()-$invoice->getTaxAmount());
                    $invoice->setTaxAmount(0);
                    foreach ($invoice->getAllItems() as $item){
                        if($item->getTaxAmount()>0){
                            $item->setPriceInclTax($item->getPriceInclTax()-$item->getTaxAmount());
                            $item->setBaseTaxmount($item->getBaseTaxmount()-$item->getTaxAmount());
                            $item->setBasePriceInclTax($item->getBasePriceInclTax()-$item->getTaxAmount());
                            $item->setBaseRowTotalInclTax($item->getBaseRowTotalInclTax()-$item->getTaxAmount());
                            $item->setRowTotalInclTax($item->getRowTotalInclTax()-$item->getTaxAmount());
                            $item->setTaxAmount(0);
                            $item->save();
                        }
                    }
                    $invoice->save();
                }
            }
            $order->save();
            $count++;
            Mage::log('count:'.$count,Zend_log::DEBUG,'remove_tax',true);
            Mage::log($order->getIncrementId(),Zend_log::DEBUG,'remove_tax',true);
        }
    } catch (Exception $e) {
        Mage::log("Exception For:".$order->getIncrementId(),Zend_log::DEBUG,'remove_tax',true);
        Mage::log($e->getMessage(),Zend_log::DEBUG,'remove_tax',true);
    }
}
echo "Done";
die;
  
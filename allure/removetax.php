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
->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to))
->addAttributeToFilter('status', array('complete','processing','pending'));
$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
$connection->beginTransaction();

foreach ($orders as $order){
    try {
        $order=Mage::getModel('sales/order')->load($order->getEntityId());
        if($order->getBaseTaxAmount() > 0){
            $order->setBaseTaxInvoiced($order->getBaseTaxInvoiced()-$order->getBaseTaxAmount());
         
            
            $order->setBaseTotalPaid($order->getBaseTotalPaid()-$order->getBaseTaxAmount());
            $order->setBaseTotalInvoiced($order->getBaseTotalInvoiced()-$order->getBaseTaxAmount());
            $order->setBaseGrandTotal($order->getBaseGrandTotal()-$order->getBaseTaxAmount());
            $order->setBaseSubtotalInclTax($order->getBaseSubtotalInclTax()-$order->getBaseTaxAmount());
            $order->setBaseTaxAmount($order->getBaseTaxAmount()-$order->getBaseTaxAmount());
            
            $order->setTotalPaid($order->getTotalPaid()-$order->getTaxAmount());
            $order->setTaxInvoiced($order->getTaxInvoiced()-$order->getTaxAmount());
            $order->setTotalInvoiced($order->getTotalInvoiced()-$order->getTaxAmount());
            $order->setSubtotalInclTax($order->getSubtotalInclTax()-$order->getTaxAmount());
            $order->setGrandTotal($order->getGrandTotal()-$order->getTaxAmount());
            $order->setTaxAmount($order->getTaxAmount()-$order->getTaxAmount());
            
            $items=$order->getAllVisibleItems();
            foreach ($items as $item){
                if($item->getTaxPercent()>0)
                    $item->setBasePriceInclTax($item->getBasePriceInclTax()-$item->getBaseTaxAmount());
                    $item->setBaseRowTotalInclTax($item->getBaseRowTotalInclTax()-$item->getBaseTaxAmount());
                    $item->setBaseTaxInvoiced($item->getBaseTaxInvoiced()-$item->getBaseTaxAmount());
                    $item->setBaseTaxAmount($item->getBaseTaxAmount()-$item->getBaseTaxAmount());
                    
                    $item->setTaxInvoiced($item->getTaxInvoiced()-$item->getTaxAmount());
                    $item->setPriceInclTax($item->getPriceInclTax()-$item->getTaxAmount());
                    $item->setRowTotalInclTax($item->getRowTotalInclTax()-$item->getTaxAmount());
                    $item->setTaxPercent(0);
                    $item->setTaxAmount(0);
                    $item->save();
                    
            }
            foreach ($order->getInvoiceCollection() as $invoice){
                
                if($invoice->getTaxAmount() > 0)
                {
                    $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()-$invoice->getBaseTaxAmount());
                    $invoice->setBaseSubtotalInclTax($invoice->getBaseSubtotalInclTax()-$invoice->getBaseTaxAmount());
                    $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount()-$invoice->getBaseTaxAmount());
                    
                    $invoice->setGrandTotal($invoice->getGrandTotal()-$invoice->getTaxAmount());
                    $invoice->setSubtotalInclTax($invoice->getSubtotalInclTax()-$invoice->getTaxAmount());
                 
                    $invoice->setTaxAmount(0);
                    foreach ($invoice->getAllItems() as $item){
                        if($item->getTaxAmount()>0){
                            $item->setBasePriceInclTax($item->getBasePriceInclTax()-$item->getBaseTaxAmount());
                            $item->setBaseRowTotalInclTax($item->getBaseRowTotalInclTax()-$item->getBaseTaxAmount());
                            $item->setBaseTaxAmount($item->getBaseTaxAmount()-$item->getBaseTaxAmount());
                            
                            $item->setPriceInclTax($item->getPriceInclTax()-$item->getTaxAmount());
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
            if($count%10==0){
                $connection->commit();
                $connection->beginTransaction();
            }
            Mage::log('count:'.$count,Zend_log::DEBUG,'remove_tax.log',true);
            Mage::log($order->getIncrementId(),Zend_log::DEBUG,'remove_tax.log',true);
        }
        $connection->commit();
    } catch (Exception $e) {
        Mage::log("Exception For:".$order->getIncrementId(),Zend_log::DEBUG,'remove_tax.log',true);
        Mage::log($e->getMessage(),Zend_log::DEBUG,'remove_tax',true);
    }
}
echo "Done";
die;
  
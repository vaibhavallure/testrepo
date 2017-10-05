<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$products = array() ;

$ordersByPaymentCheckMo = Mage::getResourceModel('sales/order_payment_collection')
->addFieldToSelect('*');
$counter=0;
try {
    foreach($ordersByPaymentCheckMo as $orderByPayment):
    $order = Mage::getModel('sales/order')->load($orderByPayment->getParentId());
    if ($order->getPayment()->getMethod() == "cp1forpos" || $order->getPayment()->getMethod() == "cp2forpos" || $order->getPayment()->getMethod() == "cashforpos" || $order->getPayment()->getMethod() == "multipaymentforpos" || $order->getPayment()->getMethod() == "codforpos" || $order->getPayment()->getMethod() == "ccforpos") {
        $payment = $order->getPayment();
        $order->setCustomerNote($order->getCustomerNote()."Previous Payment Method was ".$payment->getMethod());
        $payment->setMethod('cashondelivery');
        $payment->save();
        
        $order->save();
        $counter++;
        Mage::log('Count:'.$counter,Zend_log::DEBUG,'remove_webpos.log',true);
        Mage::log('ORDER:'.$order->getIncrementId(),Zend_log::DEBUG,'remove_webpos.log',true);
    }
    endforeach;
} catch (Exception $e) {
    Mage::log('Exception occured:'.$e->getMessage(),Zend_log::DEBUG,'remove_webpos.log',true);
}

Mage::log('Final Count:'.$counter,Zend_log::DEBUG,'remove_webpos.log',true);
echo "Done";
die;

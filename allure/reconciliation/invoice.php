<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();

$increment_number=$_GET['id'];
try {
    $websiteId = 1;
    $orderObj = Mage::getModel('sales/order')->loadByIncrementId($increment_number);
    if (!$orderObj->getId()) {
        Mage::log("Order Not Created/Present", Zend_Log::DEBUG, "reconciliation.log", true);
    }


    $orderId = $orderObj->getId();
    $ordered_items = $orderObj->getAllItems();
    $savedQtys = array();
    $isPending = false;
    foreach ($ordered_items as $item) {     //item detail
        $savedQtys[$item->getItemId()] = $item->getQtyOrdered();
        $otherSysQty = $item->getOtherSysQty();

    }

    if ($orderObj->hasInvoices()) {
        Mage::log("Invoice Already Present for this Order" . $orderId, Zend_Log::DEBUG, "reconciliation.log", true);

    } else {
//    START CREATING INVOICE
        $payment = $orderObj->getPayment();

        $payment_method = $payment->getMethodInstance()->getTitle();
        $paymentCode =$payment_method;
        $paidAmt = $orderObj->getBaseGrandTotal();
        $isNegative=false;
        if($paidAmt<0)
        {
            $isNegative=true;
        }
        $paidAmt = ($paidAmt < 0) ? $paidAmt * (-1) : $paidAmt;
        $invoice = Mage::getModel('sales/service_order', $orderObj)
            ->prepareInvoice($savedQtys);
        $invoice->setRequestedCaptureCase("offline");
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);
        $state = 2;
        $invoice->setState($state);
        $invoice->setCanVoidFlag(0);
        $amount=$paidAmt;

        if ($paidAmt) {

            $invoice->setBaseGrandTotal($amount);
            $invoice->setGrandTotal($amount);
            $invoice->setBaseSubtotal($amount);
            $invoice->setSubtotal($amount);
            $invoice->setSubtotalInclTax($amount);



        }


        $createdAt = $orderObj->getCreatedAt();
        $invoice->setCreatedAt($createdAt);
        $invoice->save();
        $invoiceNumber = $invoice->getIncrementId();
        $customerId = $orderObj->getCustomerId();
        echo "<br>Invoice Number".$invoiceNumber;
        Mage::log("Order Id:" . $orderId . " Invoice No.:" . $invoiceNumber, Zend_Log::DEBUG, "reconciliation.log", true);
        $orderPay = $orderObj->getPayment();
        if ($paymentCode ) {
//not need to change payment method

            $orderPay = Mage::getModel("sales/order_payment");
            $orderPay->setParentId($orderObj->getId());
            echo "<br>AMOUNT ".$amount;
            $orderPay->setAmountPaid($amount);
            $orderPay->setBaseAmountPaid($amount);
            $orderPay->setMethod($paymentCode);
            $orderPay->save();

        }

//invoice flag

        $orderObj->setTotalPaid($orderObj->getBaseGrandTotal());
        if($isNegative) {
            $newAmount=$amount-($amount*2);
            $orderObj->setBaseTotalInvoiced($newAmount);
        }
        else
        {
            $orderObj->setBaseTotalInvoiced($amount);
        }
        $orderObj->setData('state', "processing")
            ->setData('status', "processing");
        $orderObj->save();
        return $invoiceNumber;
    }
}
catch (Exception $ex)
{
    echo "ERROR:".$ex->getMessage();
    Mage::log("Error While Creating Invoice :".$ex->getMessage(), Zend_Log::DEBUG, "reconciliation.log", true);
}



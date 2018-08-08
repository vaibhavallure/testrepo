<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$file = $_GET["file"];

if(empty($file)){
    die("Specify file path.");
}


$teamworkLog = "customer_in_teamwork.log";

$folderPath   = Mage::getBaseDir("var") . DS .$file;

$csvData = array();
if(($handle = fopen($folderPath, "r")) != false){
    $max_line_length = defined("MAX_LINE_LENGTH") ? MAX_LINE_LENGTH : 10000;
    $header = fgetcsv($handle, $max_line_length);
    foreach ($header as $c => $_cols){
        $header[$c] = strtolower(str_replace(" ", "_", $_cols));
    }
    
    $header_column_count = count($header);
    
    while (($row = fgetcsv($handle,$max_line_length)) != false){
        $row_column_count = count($row);
        if($row_column_count == $header_column_count){
            $entry = array_combine($header, $row);
            $csvData[] = $entry;
        }
    }
    fclose($handle);
    
    if(count($csvData)){
        $websiteId = 1;
        $existCnt = 0;
        $nonExistCnt = 0;
        
        
        $paymentMethodsArr = array(
            "LIBERTY TILL"  => "tm_pay_liberty_till",
            "WIRE TRANSFER" => "tm_pay_wire_transfer",
            "CC REFUND"     => "tm_pay_cc_refund",
            "STORE CREDIT"  => "tm_pay_store_credit",
            "GENIUS CHARGE" => "tm_pay_genius_charge",
            "GIFT CARD"     => "tm_pay_gift_card",
            "CASH"          => "tm_pay_cash",
            "CASHEURO"      => "tm_pay_casheuro",
            "AUTHORIZE.NET" => "tm_pay_authrize",
            "GENIUS REFUND" => "tm_pay_genius_refund",
            "DEPOSIT"       => "tm_pay_deposit",
            "OFFLINE CREDIT CARD" => "tm_pay_offline_credit_card",
            "CREDIT CARD" => "tm_pay_credit_card",
            "PAYPAL" => "tm_pay_paypal",
            "CASHUK" => "tm_pay_cashuk"
        );
        
        $creditPaymentsArr = array(
            "tm_pay_genius_charge",
            "tm_pay_genius_refund",
            "tm_pay_authrize",
            "tm_pay_credit_card",
            "tm_pay_offline_credit_card"
        );
        
        
        $cardCodeArr = array(
            "Discover" => "DISC",
            "Master" => "MC",
            "Visa" => "VI",
            "Amex" => "AMEX"
        );
        
        $invCnt = 0;
        
        foreach ($csvData as $data){
            
            $invCnt++;
            
            $tData = unserialize($data["order"]);
            
            foreach ($tData as $receiptId => $oData){
                try{
                    
                    $orderObj = Mage::getModel('sales/order')->load($receiptId,'teamwork_receipt_id');
                    if(!$orderObj->getId()){
                        Mage::log($invCnt." - Receipt Id:".$receiptId." Order not created.",Zend_log::DEBUG,$teamworkLog,true);
                        continue;
                    }
                    
                    $orderId = $orderObj->getId();
                    
                    $ordered_items = $orderObj->getAllItems();
                    $savedQtys = array();
                    
                    $isPending = false;
                    
                    foreach($ordered_items as $item){     //item detail
                        $savedQtys[$item->getItemId()] = $item->getQtyOrdered();
                        $otherSysQty = $item->getOtherSysQty(); 
                        if($otherSysQty < 0){
                            $isPending = true;
                        }else{
                            
                        }
                    }
                    
                    if($orderObj->hasInvoices()){
                        Mage::log($invCnt." - Invoice Already present. Order Id:".$orderId,Zend_log::DEBUG,$teamworkLog,true);
                    }else{
                        $paymentDetails = $oData["payment_details"];
                        
                        $cntPayments = count($paymentDetails);
                        
                        $totalPaidAmount = 0;
                        
                        $paymentCnt = 0;
                        $paymentInfo = array();
                        foreach ($paymentDetails as $paymentData){
                            $payment_method = trim($paymentData["PaymentMethodCode"]);
                            
                            $paymentCode = $paymentMethodsArr[$payment_method];
                            
                            $paidAmt    = $paymentData["PaymentAmount"];
                            $changeAmt  = $paymentData["ChangeAmount"];
                            
                            $paidAmt = ($paidAmt < 0 )? $paidAmt * (-1): $paidAmt;
                            
                            $incAmount = ($paidAmt != 0)? $paidAmt : $changeAmt * (-1);
                            
                            $invoice = Mage::getModel('sales/service_order', $orderObj)
                            ->prepareInvoice($savedQtys);
                            
                            $invoice->setRequestedCaptureCase("offline");
                            $invoice->register();
                            $invoice->getOrder()->setIsInProcess(true);
                            
                            $state = 2;
                            /* if($isPending){
                                $state = 1;
                            } */
                            
                            $invoice->setState($state);
                            $invoice->setCanVoidFlag(0);
                            
                            
                            $amount = $incAmount;
                            
                            if($changeAmt != 0){
                                //continue;
                                $invoice->setBaseGrandTotal($incAmount);
                                $invoice->setGrandTotal($incAmount);
                                
                                $invoice->setSubtotalInclTax($incAmount);
                                $invoice->setSubtotal($incAmount);
                                
                                $invoice->setBaseSubtotal($incAmount);
                            }
                                
                            elseif($paidAmt > $orderObj->getGrandTotal()){
                                $amount = $paidAmt;
                                
                                $invoice->setBaseGrandTotal($amount);
                                $invoice->setGrandTotal($amount);
                                
                                $invoice->setSubtotalInclTax($amount);
                                $invoice->setSubtotal($amount);
                                
                                $invoice->setSubtotal($amount);
                                
                                $invoice->setTaxAmount(0);
                                $invoice->setBaseTaxAmount(0);
                                
                            }else{
                                if(!$isPending && $paidAmt < $orderObj->getGrandTotal()){
                                    $amount = $paidAmt;
                                    $invoice->setBaseGrandTotal($amount);
                                    $invoice->setGrandTotal($amount);
                                    
                                    $invoice->setSubtotalInclTax($amount);
                                    $invoice->setSubtotal($amount);
                                    
                                    $invoice->setSubtotal($amount);
                                    
                                    $invoice->setTaxAmount(0);
                                    $invoice->setBaseTaxAmount(0);
                                }else{
                                    $amount = $orderObj->getGrandTotal();
                                }
                            }
                            
                            
                            
                            
                            
                            
                            $isShowPay = true;
                            if($incAmount <= 0){
                                $isShowPay = false;
                            }
                            
                            $invoice->save();
                            
                            $invoiceNumber  = $invoice->getIncrementId();
                            $customerId     = $orderObj->getCustomerId();
                            
                            Mage::log($invCnt." - Receipt Id:".$receiptId." Order Id:".$orderId." Invoice No:".$invoiceNumber,Zend_log::DEBUG,$teamworkLog,true);
                            
                            $orderPay = $orderObj->getPayment();
                            
                            if($paymentCode == "tm_pay_cash"){
                                //not need to change payment method
                                if($paymentCnt > 0){
                                    $orderPay = Mage::getModel("sales/order_payment");
                                }
                                $orderPay->setParentId($orderObj->getId());
                                $orderPay->setAmountPaid($amount);
                                $orderPay->setBaseAmountPaid($amount);
                                $orderPay->setMethod($paymentCode);
                                $orderPay->save();
                                $paymentCnt++;
                            }else{
                                
                                $isCreditTransaction = false;
                                
                                if(in_array($paymentCode,$creditPaymentsArr)){
                                    $isCreditTransaction = true;
                                }
                                
                                
                                if($paymentCnt > 0){
                                    $orderPay = Mage::getModel("sales/order_payment");
                                }
                                
                                $orderPay->setParentId($orderObj->getId());
                                $orderPay->setAmountPaid($amount);
                                $orderPay->setBaseAmountPaid($amount);
                                
                                $orderPay->setMethod($paymentCode);
                                $orderPay->save();
                                $paymentCnt++;
                                
                                //for credit payments.
                                if($isCreditTransaction){
                                    $ccLast4 = $paymentData["AccountNumberSearch"];
                                    $cardExpMonth = $paymentData["CardExpMonth"];
                                    $cardExpYear  = $paymentData["CardExpYear"];
                                    
                                    $cardType = $paymentData["CardTypeDescription"];
                                    
                                    $cardType = ($cardCodeArr[$cardType])?$cardCodeArr[$cardType]:$cardType;
                                    
                                    if($cardType){
                                        $orderPay->setCcType($ccType);
                                    }
                                    
                                    if(($cardExpMonth)){
                                        $orderPay->setCcExpMonth($cardExpMonth);
                                    }
                                    
                                    if(($cardExpYear)){
                                        $orderPay->setCcExpYear($cardExpYear);
                                    }
                                    
                                    $transactionId = $paymentData["CardOrderId"];
                                    
                                    Mage::log("transactionId:".$transactionId,Zend_log::DEBUG,$teamworkLog,true);
                                    
                                    
                                    $orderPay->setLastTransId($cardExpYear);
                                    
                                    $accNumber = $paymentData["AccountNumberSearch"];
                                    if($accNumber){
                                        $accNumber = "XXXX".$accNumber;
                                        $orderPay->setCcLast4($ccLast4);
                                    }
                                    
                                    Mage::log("accNumber:".$accNumber,Zend_log::DEBUG,$teamworkLog,true);
                                    
                                    
                                    //$cardType = $paymentData["CardType"];
                                    
                                    $responseTrn = array(
                                        "save" => "1",
                                        "response_code" => "1",
                                        "response_subcode" =>"",
                                        "response_reason_code" => "0",
                                        "response_reason_text" =>"",
                                        "approval_code" => "000000",
                                        "auth_code" => "000000",
                                        "avs_result_code" => "P",
                                        "transaction_id" => $transactionId,
                                        "reference_transaction_id" =>"",
                                        "invoice_number" => $invoiceNumber,
                                        "description" =>"",
                                        "amount" => $amount,
                                        "method" => "CC",
                                        "transaction_type" => "auth_capture",
                                        "customer_id" => $customerId,
                                        "md5_hash" => "2D28AC7293F21CC59888CFD8B92014EB",
                                        "card_code_response_code" =>"",
                                        "cavv_response_code" =>"",
                                        "acc_number" => $accNumber,
                                        "card_type" =>$cardType ,
                                        "split_tender_id" =>"",
                                        "requested_amount" =>"",
                                        "balance_on_card" =>"",
                                        "profile_id" => "",
                                        "payment_id" => "",
                                        "is_fraud" =>"",
                                        "is_error" =>""
                                    );
                                    
                                    $orderPay->setAdditionalInformation($responseTrn);
                                    $orderPay->save();
                                    
                                    $transaction = Mage::getModel('sales/order_payment_transaction');
                                    $transaction->setOrderId($orderObj->getId());
                                    $transaction->setOrderPaymentObject($orderPay);
                                    $transaction->setTxnType("capture");
                                    $transaction->setTxnId($transactionId);
                                    $transaction->setIsClosed(0);
                                    $additinalInfo = $orderPay->getAdditionalInformation();
                                    if ($additinalInfo) {
                                        foreach ($additinalInfo as $key => $value) {
                                            $transaction->setAdditionalInformation($key, $value);
                                        }
                                    }
                                    $transaction->save();
                                    
                                    Mage::log("Transaction:".$transaction->getId(),Zend_log::DEBUG,$teamworkLog,true);
                                    
                                }
                            }
                            
                            $paymentInfo[$invoice->getId()] = array('is_show'=>$isShowPay,'payment_id'=>$orderPay->getId(),'amt'=>$incAmount);
                            
                        }
                        
                        $orderObj->getPayment()->setAdditionalData(serialize($paymentInfo))->save();
                        /* $counterpointExtraInfo = unserialize($orderObj->getCounterpointExtraInfo());
                        $counterpointExtraInfo['payment_info'] = $paymentDetails;
                        $counterpointExtraInfo = serialize($counterpointExtraInfo);
                        $orderObj->setCounterpointExtraInfo($counterpointExtraInfo); */
                        
                        $orderObj->setTotalPaid($orderObj->getGrandTotal());
                        
                        $orderObj->setData('state',"processing")
                        ->setData('status',"processing");
                        
                        $orderObj->save();
                        
                    }
                    
                 }catch (Exception $e){
                    Mage::log("Exception".$e->getMessage(),Zend_log::DEBUG,$teamworkLog,true);
                 }
            }
        }
    }
}

Mage::log("Finish...",Zend_log::DEBUG,$teamworkLog,true);
die("Finish...");



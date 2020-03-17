<?php


/**
 * @author allure
 */
class Allure_Xero_Model_Xero {
    public $xeroClient;

    /**
     * Allure_Xero_Model_Xero constructor.
     * @param Mage_Core_Helper_Abstract $xeroClient
     */
    public function __construct(){
        $this->xeroClient = Mage::helper('allure_xero/xeroClient');
    }

    public function setInvoicePaymentForOrder($isTw=false) {


            $invoicesResponse = $this->xeroClient->getInvoices();
            $invoiceArr = $invoicesResponse['Invoices'];

            $paymentRequestArgs = array("Payments" => array());
            foreach ($invoiceArr as $invoice) {
                $invoiceNo = $invoice['InvoiceNumber'];
                if($isTw){
                    $incrementId = str_replace("TWI-",'TW-',$invoiceNo);
                }else {
                    $incrementId = str_replace("MTI-",'',$invoiceNo);
                }
                //$incrementId = $orderRef;
                $order = Mage::getModel('sales/order')->load($incrementId,'increment_id');
                //$incrementId = $order->getIncrementId();
                $createdAt = $order->getCreatedAt();
                $payments = $order->getPaymentsCollection();
                //$totalInvoiceAmount = 0;

//            print_r($order->getData());die;
                $paymentReqArgs = $this->getPaymentRequestArgs("Invoice","InvoiceNumber",$payments,$invoiceNo,$createdAt,$order->getBaseGrandTotal());
                //$this->xeroClient->log("Pushed data for {$orderRef}".json_encode($paymentRequestArgs));
                //print_r($paymentReqArgs);die;

                array_push($paymentRequestArgs['Payments'],...$paymentReqArgs);
                //print_r($paymentRequestArgs);
                //print_r(json_encode($paymentRequestArgs,true));die;
            }
            $createPaymentResponse = $this->xeroClient->createPaymentForInvoice($paymentRequestArgs);
            print_r($createPaymentResponse);die;

//        foreach ($arr as $orderRef) {
//            $invoiceRespone = $this->xeroClient->getInvoiceByOrderReference($orderRef);
//
//            $invoice = &$invoiceRespone['Invoices'][0];
//            $invoiceID = $invoice['InvoiceID'];
//            $invoiceNo = $invoice['InvoiceNumber'];
//            $lineItems = $invoice['LineItems'];
//
//            $l = array();
//            foreach ($lineItems as &$lineItem) {
//                array_push($l,array('LineItemID' => $lineItem['LineItemID'],"Description" => "TEST"));
//            }
//
//            $invoice['LineItems'] = $l;
//            array_push($rq['Invoices'],$invoice);
////            print_r($res);die;
//            //$this->xeroClient->deleteInvoice($orderRef,"VOIDED");
//        }


//            if ($invoiceID) {
//                $this->xeroClient->log("Invoice ID exist {$invoiceID} for Order {$orderRef}");
//                $payments = $invoice['Payments'];
//                if (count($payments)) {
//                    $totalPaymentAmount = 0;
//                    foreach ($payments as $payment) {
//                        $paymentId = $payment['PaymentID'];
//                        $amount = $payment['Amount'];
//                        $totalPaymentAmount += $amount;
//                        $deletePaymentResponse = $this->xeroClient->deletePayment($paymentId);
//                        $status = $deletePaymentResponse['Status'];
//                        $this->xeroClient->log("Payment deletion Status = {$status}");
//                    }
//                } else {
//                    $this->xeroClient->log("No payments found for Order #{$orderRef} and Invoice {$invoiceNo}");
//                }
//            }
//

//        print_r(json_encode($paymentRequestArgs));die;
            //$this->xeroClient->log($createPaymentResponse);

//        }
//
//        $batches = array_chunk($paymentRequestArgs, 50);

//        foreach ($batches as $batch) {
//            $paymentRequestArgs = array("Payments" => $batch);
//            print_r($paymentRequestArgs);die;
//            $createPaymentResponse = $this->xeroClient->createPaymentForInvoice($paymentRequestArgs);
//            print_r($createPaymentResponse);die;
//            $this->xeroClient->log($createPaymentResponse);
//        }

    }

    private function getPaymentRequestArgs($type,$refer,$payments,$id,$createdAt,$grandTotal) {
        $paymentRequestArgs = array();
        $bankAccounts = $this->xeroClient->bank_accounts;
        $totalInvoiceAmount = 0;
//        echo count($payments->getData());
        if(count($payments->getData())) {
            foreach ($payments as $payment) {
                $method = $payment->getMethodInstance();
                $paymentTitle = $method->getTitle();
                $amount = $payment->getBaseAmountPaid();
                $totalInvoiceAmount += $amount;
                $mappedBankAccount = $this->xeroClient->getXeroBankAccountNameByPaymentTitle($paymentTitle);
                //TODO LOGS

                if($type == "CreditNote" && ($paymentTitle == "CASH" && $amount > 0)) continue;
                if($amount >0 || $type == "CreditNote") {
                    array_push($paymentRequestArgs ,array(
                        $type => array($refer => $id),
                        "Account" => array("AccountID" => $bankAccounts[$mappedBankAccount]),
                        "Date" => $createdAt,
                        "Amount" => abs($amount)
                    ));
                }
            }
        }
        if(empty($paymentRequestArgs)){
            $mappedBankAccount = $this->xeroClient->getXeroBankAccountNameByPaymentTitle('Cash');
            array_push($paymentRequestArgs,array(
                $type => array($refer => $id),
                "Account" => array("AccountID" => $bankAccounts[$mappedBankAccount]),
                "Date" => $createdAt,
                "Amount" => $grandTotal,
                //"Reference" => ""
            ));
        }

//        print_r($paymentRequestArgs);die;
        return $paymentRequestArgs;
    }

    public function bankTransaction() {
        $bankTransactionResponse = $this->xeroClient->createBankTransaction("RECEIVE-OVERPAYMENT");
        $status = $bankTransactionResponse['Status'];
        if($status == "OK") {
            $bankTransaction = $bankTransactionResponse['BankTransactions'][0];
            $overPaymentId = $bankTransaction['OverpaymentID'];
            $this->xeroClient->allocateOverpaymentToInvoice($overPaymentId,"2ccb33e0-1a24-4317-bfd8-d5b85bd44902");
        }else {
            //TODO LOGS
        }
    }

    public function createCreditNoteForOrder($isTW=false) {
        //$creditNoteArr = array("TW-86862","TW-86864","TW-86869","TW-86873","TW-86875","TW-86906","TW-86940","TW-86947");

        $creditNotesResponse = $this->xeroClient->getCreditNotes();
        $creditNoteArr = $creditNotesResponse['CreditNotes'];

        $paymentRequestArgs = array("Payments" => array());
        foreach ($creditNoteArr as $creditNote) {
            //$crediNoteResponse = $this->xeroClient->getCreditNoteByOrderReference($cr);
            //$creditNote = $cr['CreditNotes'][0];
            $creditNoteID = $creditNote['CreditNoteID'];
            $creditNoteNo = $creditNote['CreditNoteNumber'];
            $totalAmount = $creditNote['Total'];
            $date = $creditNote['DateString'];
            //$bankAccounts = $this->xeroClient->bank_accounts;

            //print_r($crediNoteResponse);die;
            if($isTW) {
                $increment_id = str_replace('TWC-','TW-',$creditNoteNo);
            }else {
                $increment_id = str_replace('MTC-','TW-',$creditNoteNo);
            }

            $order = Mage::getModel('sales/order')->load($increment_id,'increment_id');
            $payments = $order->getPaymentsCollection();
            $paymentReqArgs = $this->getPaymentRequestArgs("CreditNote","CreditNoteNumber",$payments,$creditNoteNo,$date,$totalAmount);
            //print_r($paymentReqArgs);die;
//            foreach ($paymentReqArgs as $payment) {
//                $res = $this->xeroClient->createPaymentForCreditNote(array("Payments" => array($payment)));
//            }

            //print_r($res);
            array_push($paymentRequestArgs['Payments'],...$paymentReqArgs);
        }
        //print_r(json_encode($paymentRequestArgs));die;
        $res = $this->xeroClient->createPaymentForCreditNote($paymentRequestArgs);
        print_r($res);
//        die;
    }

    public function deleteInvoices() {
        $invoicesResponse = $this->xeroClient->getInvoices();
        $invoiceArr = $invoicesResponse['Invoices'];

        $deleteInvoiceReqArgs = array("Invoices" => array());
        foreach ($invoiceArr as $invoice) {
            //$invoiceNo = $invoice['InvoiceNumber'];
            $invoiceId = $invoice['InvoiceID'];
            if($invoiceId) {
                array_push($deleteInvoiceReqArgs['Invoices'],array("InvoiceID" => $invoiceId, "Status" => "VOIDED"));
            }
                //$this->xeroClient->deleteInvoice($invoiceNo,"VOIDED");
        }
        $res = $this->xeroClient->deleteInvoice("","VOIDED",true,$deleteInvoiceReqArgs);
        print_r($res);
        echo "<br>";
        echo "____________________________________________________________________________";
        $invoiceUpdateReqArgs = array('Invoices' => array());
        foreach ($res['Elements'] as $invoice) {
            $lineItems = $invoice['LineItems'];
            $newLineItems = array();
            foreach ($lineItems as $lineItem) {
                array_push($newLineItems,array(
                    "LineItemID" => $lineItem['LineItemID'],
                    "Description" => "DELETED"
                ));
            }

            array_push($invoiceUpdateReqArgs['Invoices'],array(
                "InvoiceID" => $invoice["InvoiceID"],
                "LineItems" => $newLineItems
            ));
        }
        //print_r(json_encode($invoiceUpdateReqArgs));die;
        $res = $this->xeroClient->updateInvoice($invoiceUpdateReqArgs);
        print_r($res);

    }

    public function deleteCreditNotes() {
        //$creditNotesResponse = $this->xeroClient->getCreditNoteByOrderReference();
        //$creditNoteArr = $creditNotesResponse['CreditNotes'];

//        $creditNoteArr = array(
//            "CN-86563"
//        );

        $creditNotesResponse = $this->xeroClient->getCreditNotes();
        $creditNoteArr = $creditNotesResponse['CreditNotes'];

        $deleteCreditNoteReqArgs = array("CreditNotes" => array());
        foreach ($creditNoteArr as $creditNote) {
            //$invoiceNo = $invoice['InvoiceNumber'];
            $invoiceId = $creditNote['CreditNoteID'];
            if($invoiceId) {
                array_push($deleteCreditNoteReqArgs['CreditNotes'],array("CreditNoteID" => $invoiceId, "Status" => "VOIDED"));
            }
            //                $this->xeroClient->deletecreditNote($creditNote,"DELETED");
        }
        //print_r($deleteCreditNoteReqArgs);die;
        $res = $this->xeroClient->deleteCreditNotes("","VOIDED",true,$deleteCreditNoteReqArgs);
        print_r($res);
        echo "<br>";
        echo "____________________________________________________________________________";

    }

}









//https://login.xero.com/identity/connect/authorize?response_type=code&client_id=915692A7613B41C392644638DFC59337&redirect_uri=https://allureinc.co&state=12qwaszx&scope=accounting.transactions offline_access accounting.settings

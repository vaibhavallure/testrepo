<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$startDate = $_GET['start'];
$endDate   = $_GET['end'];

/* if(empty($startDate)){
    die("Please mention data in 'start' field.");
}else{
    $startDate1 = DateTime::createFromFormat('Y-m-d', $startDate);
    $date_errors = DateTime::getLastErrors();
    if ($date_errors['warning_count'] + $date_errors['error_count'] > 0) {
        die("'start' field format wrong. eg:2009-01-30");
    }
}

if(empty($endDate)){
    die("Please mention data in 'end' field.");
}else{
    $endDate1 = DateTime::createFromFormat('Y-m-d', $endDate);
    $date_errors = DateTime::getLastErrors();
    if ($date_errors['warning_count'] + $date_errors['error_count'] > 0) {
        die("'end' field format wrong. eg:2009-01-30");
    }
} */



function getQuery(){
    $query = "SELECT R.RecCreated, R.ReceiptId, R.ReceiptNum, R.StateDate,
              R.TotalAmountWithoutTax,
              R.TotalAmountWithTax, 
              (R.TotalAmountWithTax -R.TotalAmountWithoutTax) AS TAX,
              R.TotalQty, R.EmailAddress,
              R.SellToLastName, R.SellToFirstName, R.SellToAddress1, R.SellToAddress2,
              R.SellToCity, R.SellToState, R.SellToPostalCode, R.SellToPhone1,
              R.SellToPhone2, R.SellToCountryCode,R.SellToCustomerId,
              R.DeviceTransactionNumber,
              CONCAT(EMP.FirstName,' ',EMP.LastName) AS EMPNAME,
              LOC.Name, LOC.LocationCode, LOC.LocationCodeNameSearch,
              CUR.CODE, CUR.Symbol, CUR.CurrencyCode,
              PITM.VALUE,CAT.SKU,CAT.METAL_COLOR,
              RITM.ReceiptItemId, RITM.ITEMID,RITM.ListOrder,
              RITM.Qty, RITM.OriginalPriceWithTax,
              RITM.OriginalPriceWithoutTax, RITM.OriginalExtPrice,
              RITM.OriginalExtPriceWithoutTax, RITM.OriginalExtPriceWithTax,
              (RITM.LineExtDiscountAmount) AS ITM_DISC, 
              (RITM.LineDiscountPercent) AS DISC_PER ,
              RPAY.ReceiptPaymentId,
              RPAY.PaymentMethodCode, RPAY.PaymentAmount, RPAY.ChangeAmount,
              RPAY.AccountNumber, RPAY.CardExpMonth, RPAY.CardExpYear,
              RPAY.CardTransactionId, RPAY.CardType, RPAY.AccountNumberSearch,
              RPAY.CardTypeDescription,RPAY.CardOrderId,
              ITM.StyleNo, ITM.Description4
              FROM RECEIPT AS R 
              JOIN RECEIPTPAYMENT AS RPAY   ON R.ReceiptId = RPAY.ReceiptId
              JOIN Currency AS CUR ON RPAY.CurrencyID = CUR.CurrencyID
              LEFT JOIN Employee EMP ON R.CreateEmployeeId = EMP.EmployeeId
              LEFT JOIN Location LOC ON R.LocationId = LOC.LocationID
              JOIN RECEIPTITEM AS RITM ON R.ReceiptId = RITM.ReceiptId 
              JOIN INVENITEMINFO AS ITM ON RITM.ITEMID = ITM.ITEMID
              LEFT JOIN ApiInvenItemIdentifier AS PITM ON PITM.ITEMID = ITM.ITEMID
              LEFT JOIN _MARISP66_Catalog AS CAT ON CAT.teamwork_id = PITM.VALUE
              WHERE R.WebOrderNo IS NULL and R.EmailAddress is not null 
              and R.StateDate >= '2018-02-02' and R.StateDate < '2018-02-03'
              -- AND r.TotalQty > 0
              -- WHERE R.ReceiptId = '110EDCCE-D9FE-471B-AEDD-2183A50EDC55' -- 'C5F8D895-E72E-49C0-B460-8AD0686FFCD4' -- 'E8741722-B4E5-4374-9CE2-3418952E354E' -- 'D04D9D85-D1DC-4983-A552-4A440F9261E8' -- 'C5F8D895-E72E-49C0-B460-8AD0686FFCD4'  -- 'E8741722-B4E5-4374-9CE2-3418952E354E'  -- 'D3ED5776-F343-4861-925F-A005DE80E724' -- 'BFC9C625-4A8A-47EF-BBA7-001C92C9C9ED' --'FB021240-3808-4067-8A32-001376834437' 
              -- '63F588FB-FAD4-4154-AACD-DF7BC5AA4E4F'
            ; 
            ";
    return $query;
}

function getConnection(){
    $host_name  = "cpsql";
    $db_user    = "root";
    $db_pass    = "12qwaszx";
    
    $conn = odbc_connect($host_name, $db_user,$db_pass);
    if($conn){
        echo "Connection established.<br>";
    }else{
        echo "Connection not established.<br>";
        $conn = null;
    }
    return $conn;
}

//function processQuery(){
    $log_file = "abc.log";
    
    $connection = getConnection();
    if($connection != null){
        $query = getQuery();
        
        
        try{
            
            $folderPath   = Mage::getBaseDir("var") . DS . "teamwork" . DS . "order";
            $filename     = "Order".$startDate."-To-".$endDate.".csv";
            $filepath     = $folderPath . DS . $filename;
            
            
            $io = new Varien_Io_File();
            $io->setAllowCreateFolders(true);
            $io->open(array("path" => $folderPath));
            
            $csv = new Varien_File_Csv();
            
            $result = odbc_exec($connection, $query);
            $rowData = array();
            
            $header = array("order");
            
            $rowData[]  = $header;
            
            $pay_header = array(
                "ReceiptPaymentId","PaymentMethodCode",
                "PaymentAmount","ChangeAmount","AccountNumber","CardExpMonth", 
                "CardExpYear","CardTransactionId","CardType","AccountNumberSearch",
                "CardTypeDescription","CardOrderId"
            );
            
            $product_header = array(
                "ReceiptItemId","ITEMID","ListOrder","Qty","OriginalPriceWithTax",
                "OriginalPriceWithoutTax","OriginalExtPrice",
                "OriginalExtPriceWithoutTax","OriginalExtPriceWithTax",
                "LineExtDiscountAmount","LineDiscountPercent",
                "StyleNo","Description4","VALUE","SKU","METAL_COLOR"
            );
            
            $order_header = array(
                "RecCreated","StateDate","ReceiptId", "TotalAmountWithoutTax",
                "TotalAmountWithTax","TAX",
                "TotalQty", "EmailAddress",
                "SellToLastName", "SellToFirstName", "SellToAddress1", "SellToAddress2",
                "SellToCity", "SellToState", "SellToPostalCode","SellToPhone1",
                "SellToPhone2","SellToCountryCode","SellToCustomerId",
                "CODE","Symbol","CurrencyCode"
            );
            
            $order_extra_header = array(
                "DeviceTransactionNumber","ReceiptNum","EMPNAME","Name",
                "LocationCode","LocationCodeNameSearch"
            );
            
            $orderArr = array();
            $row = array();
            while (odbc_fetch_row($result)){
               
                $receiptId = odbc_result($result, "ReceiptId");
                $paymentId = odbc_result($result, "ReceiptPaymentId");
                $receiptItemId = odbc_result($result, "ReceiptItemId");
                
                $orderDetails   = array();
                $productDetails = array();
                $paymentDetails = array();
                $extraOrderDetails = array();
                
                for ($j = 1; $j <= odbc_num_fields($result); $j++){
                    $field_name  = odbc_field_name($result, $j);
                    $field_value = odbc_result($result, $field_name);
                    
                    //var_dump($field_name." = ".$field_value);
                    
                    if(in_array($field_name, $pay_header)){
                        $paymentDetails[$field_name] = $field_value;
                    }elseif (in_array($field_name, $product_header)){
                        $productDetails[$field_name] = $field_value;
                    }elseif (in_array($field_name, $order_header)){
                        if($field_name == "Symbol"){
                            $field_value = utf8_decode($field_value);
                        }
                        $orderDetails[$field_name] = $field_value;
                    }else{
                        $extraOrderDetails[$field_name] = $field_value;
                    }
                }
                
                if(!array_key_exists($receiptId, $orderArr)){
                    $orderArr[$receiptId] = array(
                        "order_detail"      => $orderDetails,
                        "product_details"   => array(
                            $receiptItemId => $productDetails
                        ),
                        "payment_details"  => array(
                            $paymentId => $paymentDetails
                        ),
                        "extra_details" => $extraOrderDetails
                    );
                }else{
                    $tempPayment = $orderArr[$receiptId]["payment_details"];
                    $orderArr[$receiptId]["payment_details"][$paymentId] = $paymentDetails;
                    
                    $tempProducts = $orderArr[$receiptId]["product_details"];
                    $orderArr[$receiptId]["product_details"][$receiptItemId] = $productDetails;
                }
                
                $rowData[$receiptId] = array(serialize(array($receiptId => $orderArr[$receiptId])));
                
            }
            //$rowData[] = array("order" => $orderArr);
            $csv->saveData($filepath,$rowData);
            
           echo "<pre>";
           print_r($orderArr);
            
        }catch (Exception $e){
            echo $e->getMessage();
            Mage::log("Exception:".$e->getMessage(),Zend_Log::DEBUG,$log_file,true);
        }
    }
//}

//processQuery();

die("Finish...");




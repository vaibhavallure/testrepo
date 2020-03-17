<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 22/2/19
 * Time: 2:22 PM
 */

require_once('../../app/Mage.php');
umask(0);
Mage::app();
echo "<pre>";

$type = $_GET['type'];
$isTw = $_GET['tw'];
if(isset($type)){
    if($isTw == 1){
        $isTw = true;
    } else {
        $isTw = false;
    }

    $xeroClient = Mage::helper('allure_xero/xeroClient');
    $xero = Mage::getModel('allure_xero/xero');
    if($type === 'invoice') {
        $xero->setInvoicePaymentForOrder($isTw);
    }else if($type == 'creditnote') {
        $xero->createCreditNoteForOrder($isTw);
    }
}

//$xero->bankTransaction();
//$xero->deleteInvoices();
//$xero->deleteCreditNotes();
//$xeroClient->deleteInvoice(	"INV-6138");
//$xeroClient->allocateOverpaymentToInvoice(null,null);
print_r($xero);die;


$order = Mage::getModel('sales/order')->load(457857);
$_payment=$order->getPaymentsCollection();
foreach ($_payment as $payment) {
    $method = $payment->getMethodInstance();
    echo $method->getTitle();
    echo "<br>";
}
die;

$payment=$_payment->toArray();
print_r($payment);

die;
$start = $_GET['start'];
$end = $_GET['end'];

if(empty($start) || empty($end)) {
    echo "Please provide date";
}

//$start = '2019-01-01';
//$end = '2019-01-05';

$TM_URL = "/services/allureSalesByLocation";
$helper = Mage::helper("allure_teamwork");
$urlPath = $helper->getTeamworkSyncDataUrl();
$requestURL = $urlPath . $TM_URL;
$token = trim($helper->getTeamworkSyncDataToken());
$sendRequest = curl_init($requestURL);
curl_setopt($sendRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
curl_setopt($sendRequest, CURLOPT_HEADER, false);
curl_setopt($sendRequest, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($sendRequest, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($sendRequest, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($sendRequest, CURLOPT_FOLLOWLOCATION, 0);
curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json",
    "Authorization: Bearer " . $token
));

$requestArgs = array(
    "start_time" => $start,
    "end_time" => $end,
);

if ($requestArgs != null) {
    $json_arguments = json_encode($requestArgs);
    curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
}
$response = curl_exec($sendRequest);
$result = unserialize($response);

$csv = "";
$commonHeader = "Date,Store,TW Total Sale,Magento (Sales Total),TW Net Sales w/o Vat,Net Revenue (Magento)".PHP_EOL;
if($result['status']) {
    $model = Mage::getModel('ecp_reporttoemail/observer');

    foreach ($result['data'] as $date => $byDateResult) {
        $csv .= $commonHeader;
        foreach ($byDateResult as $locationCode => $locationData) {
            if($locationCode === 1){
                $storeObj = Mage::getSingleton("allure_virtualstore/store")->load('653 Broadway','name');
            }else {
                $storeObj = Mage::getSingleton("allure_virtualstore/store")->load($locationCode,'tm_location_code');
            }

            $storesId = $storeObj->getData('store_id');
            $netSalesAmtWOTax = $locationData['NetSalesAmtWOTax'];
            $netSalesAmtWithTax = $locationData['NetSalesAmtWithTax'];

            $dataArray = $model->getDataForReportNew($storesId,$date,"manual",true);
            $data = $dataArray["data"];
            $storeName = $storeObj->getData("name");

            $csv .= "{$date},{$storeName},{$netSalesAmtWithTax},{$data['total_income_amount']},{$netSalesAmtWOTax},{$data['total_revenue_amount']}".PHP_EOL;
        }
        $csv .= PHP_EOL;
    }
}
header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="filename.csv"');
echo $csv; exit();

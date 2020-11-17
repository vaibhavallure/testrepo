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

$dam = Mage::helper('teamworkdam/teamworkDAMClient');
$data = array(
    'plu' => '18575',
    'name' => 'Flag.png',
    'data' => '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'
);
print_r($dam->syncImage($data));


die;
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

<?php

require_once ('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

$TM_URL = "/services/orders";
$TOKEN = "OUtNUUhIV1V2UjgxR0RwejV0Tmk0VllneEljNTRZWHdLNHkwTERwZXlsaz0=";

$start = $_GET["start"];
$end = $_GET["end"];

if(!isset($start) && !isset($end)){
    die("provide date....");
}

//$start = str_replace(" ","%20",$start);
//$end = str_replace(" ","%20",$end);

$helper = Mage::helper("allure_teamwork");
$urlPath = $helper->getTeamworkSyncDataUrl();
$requestURL = $urlPath . $TM_URL;//."?start=".$start."&end=".$end;
var_dump($requestURL);
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
    "Authorization: Bearer ".$token
));

$requestArgs = array(
    "start_time" => $start,
    "end_time"   => $end
);
// convert requestArgs to json
if ($requestArgs != null) {
    $json_arguments = json_encode($requestArgs);
    curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
}
$response  = curl_exec($sendRequest);
//$response1 = json_decode($response,true);
echo "<pre>";
$response1 = unserialize($response);
var_dump(count($response1));
//print_r($response1);
//$response = json_encode($response1);
if(!$response1["status"]){
    Mage::log($response1,Zend_Log::DEBUG,"teamwork_sync_data.log",true);
}else{
    Mage::getModel("allure_teamwork/tmobserver")->addDataIntoSystem($response);
}
die("Finish");

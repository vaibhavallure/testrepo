<?php

require_once ('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

$TM_URL = "/services/oldOrders";
$TOKEN = "OUtNUUhIV1V2UjgxR0RwejV0Tmk0VllneEljNTRZWHdLNHkwTERwZXlsaz0=";

$start = $_GET["start"];
$end = $_GET["end"];

if(!isset($start) && !isset($end)){
    die("provide date....");
}

$start = str_replace(" ","%20",$start);
$end = str_replace(" ","%20",$end);

$helper = Mage::helper("allure_teamwork");
$urlPath = "http://35.237.115.49:9000";////$helper->getTeamworkSyncDataUrl();
$requestURL = $urlPath . $TM_URL."?start=".$start."&end=".$end;
var_dump($requestURL);
$token = $TOKEN;//trim($helper->getTeamworkSyncDataToken());
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

$requestArgs = null;
// convert requestArgs to json
if ($requestArgs != null) {
    $json_arguments = json_encode($requestArgs);
    curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
}
$response  = curl_exec($sendRequest);
$response1 = json_decode($response,true);
echo "<pre>";
var_dump(count($response1));
//print_r($response);
Mage::getModel("allure_teamwork/tmobserver")->addDataIntoSystem($response);
die("Finish");

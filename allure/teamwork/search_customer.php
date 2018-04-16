<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "cp_cust_update.log";

try{
    $_url         = "https://api.teamworksvs.com/externalapi3/customers/listmodified";
    //dev env
    //$_accessToken = "bWFyaWF0dGVzdDIgNTYyOTQ5OTUzNDIxMzEyMCB1ZnlQM3VIM05nN1g1WTJYODdaWk5PSk91SjF1dXEzUw==";
    //prod env
    $_accessToken = "bWFyaWF0YXNoIDU2NTkzMTM1ODY1NjkyMTYgakUyMEJhWU5RVjhyQVVDRUpWV2tXQ3JhaUFJNjYzQ3A=";
    $request = array();
    /* $email = "happyone@gmail.com";
    $request["query"] = $email;
    $request["fields"] = array("primaryEmail"); */
    
    $date = date('Y-m-d');
    $time =strtotime("February 08") * 1000; //strtotime($date);
    $request['pageSize'] = 500;
    $request['modifiedAfter'] = $time;//time();
    echo "time:".$time."<br>";
    
    $sendRequest = curl_init($_url);
    curl_setopt($sendRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($sendRequest, CURLOPT_HEADER, false);
    curl_setopt($sendRequest, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($sendRequest, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($sendRequest, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($sendRequest, CURLOPT_FOLLOWLOCATION, 0);
    
    curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "Access-Token: {$_accessToken}"
    ));
    
    $json_arguments = json_encode($request);
    curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
    $response = curl_exec($sendRequest);
    curl_close($sendRequest);
    $responseObj = json_decode($response);
    echo "<pre>";
    print_r($responseObj);
    
}catch (Exception $e){
    var_dump($e->getMessage());
    //Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
}
Mage::log("Finish...",Zend_log::DEBUG,$logFile,true);

<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();


//remote site wsdl url
$_URL       = "http://mariatash.ws02.allure.inc/api/v2_soap/?wsdl=1";

/**
 * @return array of magento credentials.
 */
function getMagentoSiteCredentials(){
    $_USERNAME  = "allureinc";
    $_APIKEY    = "12qwaszx";
    return array("username"=>$_USERNAME,'apiKey'=>$_APIKEY);
}

/**
 * @return array of soap wsdl options.
 */
function getSoapWSDLOptions(){
    return array('connection_timeout' => 60,'trace' => 1,
        'cache_wsdl' => WSDL_CACHE_NONE);
}


$item_detail = array();
$item_detail[] = array(
    'name'=>'Test Sagar','price'=>10,
    'sku'=>'test-sagar','qty'=>1
);

$customer_detail = array(
    'name'=>'Sagar G','email'=>'sagardada12@allureinc.co',
    'street'=>'Sagar Path','city'=>'Pune','state'=>'Maharashtra',
    'country'=>'India','zip_code'=>'413103','phone'=>'9657293982'
);
$order_detail = array(
    'subtotal'=>'100.00','tax'=>'25.00',
    'order_date'=>'19-08-2017'
);

$_order_data = array();
$_order_data['1004'] = array(
    'item_detail'       => $item_detail,
    'customer_detail'   => $customer_detail,
    'order_detail'      => $order_detail
);


try{
    $_AUTH_DETAILS_ARR = getMagentoSiteCredentials();
    $_WSDL_SOAP_OPTIONS_ARR = getSoapWSDLOptions();
    $client = new SoapClient($_URL, $_WSDL_SOAP_OPTIONS_ARR);
    $session = $client->login($_AUTH_DETAILS_ARR);
    
    $_RequestData = array(
        'sessionId' => $session->result,
        'counterpoint_data' => json_encode($_order_data)
    );
    
    $result  = $client->counterpointOrderList($_RequestData);
    
    echo "<pre>";
    print_r($result);
    $client->endSession(array('sessionId' => $session->result));
}catch (Exception $e){
    echo "<pre>";
    print_r($e);
}



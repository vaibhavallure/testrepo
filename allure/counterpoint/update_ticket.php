<?php
require_once('../../app/Mage.php'); 
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');
//ini_set('post_max_size', '20M');

$startDate = $_GET['start'];
$endDate   = $_GET['end'];
$state     = $_GET['state'];
//die;

if(empty($startDate)){
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
}

ini_set('max_execution_time', -1);
$helper = Mage::helper('allure_counterpoint');
$hostName   = $helper->getHostName();
$dbUsername = $helper->getDBUserName();//"sa";
$dbPassword = $helper->getDBPassword();//"root";
$dbName = "Venus84";

$conn = odbc_connect($hostName, $dbUsername,$dbPassword);
if($conn){
    try{
        echo "Connection established...";
        $query = "SELECT A.TKT_NO order_id, B.orig_tkt_no
                    FROM PS_TKT_HIST A
                    JOIN PS_TKT_HIST_ORIG_DOC B ON(A.DOC_ID=B.DOC_ID)
                    WHERE A.STR_ID IN(1,2) 
                    AND A.TKT_DT >= convert(datetime,'".$startDate."')
                    AND A.TKT_DT <= convert(datetime,'".$endDate."') 
                    -- AND A.TKT_NO = '215849' 
                    ORDER BY B.ORIG_TKT_NO DESC";
        
        $result = odbc_exec($conn, $query);
        $count = 0;
        $i 	   = 0;
        $mainArr = array();
        while(odbc_fetch_row($result)){
            $order_id   = odbc_result($result, 'order_id');
            $extra      = array();
            //parse row data as required format
            for ($j = 1; $j <= odbc_num_fields($result); $j++){
                $field_name  = odbc_field_name($result, $j);
                $field_value = odbc_result($result, $field_name);
                $extra[$field_name] = $field_value;
            }
            
            if(!array_key_exists($order_id, $mainArr)){
                $mainArr[$order_id] = array('order'=>$extra);
            }
            $i++;
        }
        odbc_close($conn);
    }catch (Exception $e){
        print_r($e->getMessage());
    }
}else{
    echo "Connection  not established...";
    die;
}

echo "<pre>";
print_r(count($mainArr));
//print_r(($mainArr));
//die; 

//remote site wsdl url
$_URL       = "http://universal.allurecommerce.com/api/v2_soap/?wsdl=1";

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

try{
    $_AUTH_DETAILS_ARR = getMagentoSiteCredentials();
    $_WSDL_SOAP_OPTIONS_ARR = getSoapWSDLOptions();
    $client = new SoapClient($_URL, $_WSDL_SOAP_OPTIONS_ARR);
    $session = $client->login($_AUTH_DETAILS_ARR);
    
    $reqS = addslashes(serialize($mainArr));
    $reqU = utf8_encode('"'.$reqS.'"');
    
    
    $_RequestData = array(
        'sessionId' => $session->result,
        'order_data' => $reqU
    );
    
    $result  = $client->counterpointOrderUpdateTicketByOrignalTicket($_RequestData);
    //$result = Mage::getModel('allure_counterpoint/order_api')->updateTicketByOrignalTicket($reqU);
    echo "<pre>";
    print_r($result);
    $client->endSession(array('sessionId' => $session->result));
}catch (Exception $e){
    echo "<pre>";
    print_r($e);
}



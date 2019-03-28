<?php
require_once('../../app/Mage.php'); 
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$startDate = $_GET['start'];
$endDate   = $_GET['end'];
$state     = $_GET['state'];
//die;
if(empty($state)){
    die("Please mention data in 'state' field.");
}else{
    if(!($state == 1 || $state == 2)){
        die("Please enter 'state' either in 1 or 2");
    }
}

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
$hostName   = "cpoint";$helper->getHostName();
$dbUsername = "sa";//$helper->getDBUserName();//"sa";
$dbPassword = "12qwaszx";//$helper->getDBPassword();//"root";
$dbName = "CPSQL";

$conn = odbc_connect($hostName, $dbUsername,$dbPassword);
if($conn){
    try{
        echo "Connection established...";
        
        $query = "SELECT MAIN_TABLE.cust_no,MAIN_TABLE.tkt_no order_id,MAIN_TABLE.doc_id,MAIN_TABLE.str_id,
                    CONTACT_TABLE.CONTACT_ID contact_id,CONTACT_TABLE.FST_NAM fst_nam,CONTACT_TABLE.LST_NAM lst_nam,
                    CONTACT_TABLE.EMAIL_ADRS_1 as email, CONTACT_TABLE.NAM name, AR_CUST_TABLE.CUST_NAM_TYP nam_typ, CONTACT_TABLE.ADRS_1 street, CONTACT_TABLE.CITY city,
                    CONTACT_TABLE.STATE state, CONTACT_TABLE.ZIP_COD zip_code , CONTACT_TABLE.CNTRY as country, CONTACT_TABLE.PHONE_1 phone

                    FROM PS_TKT_HIST MAIN_TABLE 
                    JOIN PS_TKT_HIST_CONTACT CONTACT_TABLE ON ( MAIN_TABLE.DOC_ID = CONTACT_TABLE.DOC_ID )
                    
                    JOIN AR_CUST AR_CUST_TABLE ON( AR_CUST_TABLE.CUST_NO = MAIN_TABLE.CUST_NO )
                    
                    WHERE 
                    MAIN_TABLE.STR_ID NOT IN(3,7) AND
                    MAIN_TABLE.TKT_TYP = 'T' AND
                    MAIN_TABLE.TKT_DT >= convert(datetime,'".$startDate."') 
                    AND MAIN_TABLE.TKT_DT <= convert(datetime,'".$endDate."')  
                    -- AND CONTACT_TABLE.CONTACT_ID = ".$state."
                    ORDER BY MAIN_TABLE.TKT_DT DESC";
        
        $result = odbc_exec($conn, $query);
        $count = 0;
        $i 	   = 0;
        $mainArr = array();
        $addressHeader = array('email','name','street','city','state','zip_code','country','phone','fst_nam','lst_nam');
        while(odbc_fetch_row($result)){
            $order_id   = odbc_result($result, 'TKT_NO');
            $contact_id = odbc_result($result, 'contact_id');
            $arr 		= array();
            $address 	= array();
            $extra      = array();
            
            //parse row data as required format
            for ($j = 1; $j <= odbc_num_fields($result); $j++){
                $field_name  = odbc_field_name($result, $j);
                //var_dump($field_name);
                $field_value = odbc_result($result, $field_name);
                
                if(strtolower($field_name) == strtolower("TKT_NO")){
                    $field_name = "order_id";
                }elseif (strtolower($field_name) == strtolower("EMAIL_ADRS_1")){
                    $field_name = "email";
                }elseif (strtolower($field_name) == strtolower("NAM")){
                    $field_name = "name";
                }elseif (strtolower($field_name) == strtolower("CUST_NAM_TYP")){
                    $field_name = "nam_typ";
                }elseif (strtolower($field_name) == strtolower("ADRS_1")){
                    $field_name = "street";
                }elseif (strtolower($field_name) == strtolower("ZIP_COD")){
                    $field_name = "zip_code";
                }elseif (strtolower($field_name) == strtolower("CNTRY")){
                    $field_name = "country";
                }elseif (strtolower($field_name) == strtolower("PHONE_1")){
                    $field_name = "phone";
                }
                
                
                if(in_array($field_name, $addressHeader)){
                    if($field_name == 'email')
                        $field_value = strtolower($field_value);
                     $address[$field_name] = $field_value;
                }else{
                    $extra[$field_name] = $field_value;
                }
            }

            if(!array_key_exists($order_id, $mainArr)){
                $mainArr[$order_id] = array(
                    'customer_detail'=> array($contact_id => $address),
                    'extra' => $extra
                );
            }else{
                $tempItems = $mainArr[$order_id]['customer_detail'];
                $tempItems[$contact_id] = $address;
                $mainArr[$order_id]['customer_detail'] = $tempItems;
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
die; 


//remote site wsdl url
$_URL       = "https://www.mariatash.com/api/v2_soap/?wsdl=1";

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
        'customer_data' => $reqU
    );
    
    $result  = $client->counterpointOrderAddCustomer($_RequestData);
    //$result = Mage::getModel('allure_counterpoint/order_api')->addCustomer($reqU);
    echo "<pre>";
    print_r($result);
    $client->endSession(array('sessionId' => $session->result));
}catch (Exception $e){
    echo "<pre>";
    print_r($e);
}


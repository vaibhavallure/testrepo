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
$hostName   = $helper->getHostName();
$dbUsername = $helper->getDBUserName();//"sa";
$dbPassword = $helper->getDBPassword();//"root";
$dbName = "Venus84";

$conn = odbc_connect($hostName, $dbUsername,$dbPassword);
if($conn){
    try{
        echo "Connection established...";
        $query = "SELECT MAIN_TABLE.doc_id,MAIN_TABLE.str_id,MAIN_TABLE.sta_id,
                    MAIN_TABLE.usr_id,MAIN_TABLE.stk_loc_id,MAIN_TABLE.cust_no,
                    MAIN_TABLE.tkt_no order_id, MAIN_TABLE.event_no , MAIN_TABLE.tkt_dt order_date,
                    MAIN_TABLE.sub_tot subtotal, MAIN_TABLE.tax_amt tax,MAIN_TABLE.tot total,
                    MAIN_TABLE.tot_tnd,MAIN_TABLE.tot_chng,
                    CONCAT(ITEM_TABLE.ITEM_NO,'|',ITEM_TABLE.CELL_DESCR) sku,
                    ITEM_TABLE.ORIG_QTY qty, ITEM_TABLE.PRC prc, ITEM_TABLE.DESCR pname,
                    ITEM_TABLE.lin_seq_no,
                    CONTACT_TABLE.EMAIL_ADRS_1 as email, CONTACT_TABLE.NAM name, CONTACT_TABLE.NAM_TYP nam_typ, CONTACT_TABLE.ADRS_1 street, CONTACT_TABLE.CITY city,
                    CONTACT_TABLE.STATE state, CONTACT_TABLE.ZIP_COD zip_code , CONTACT_TABLE.CNTRY as country, CONTACT_TABLE.PHONE_1 phone,
                    DISC_TABLE.DISC_AMT dis_amount, DISC_TABLE.DISC_PCT dis_pct

                    FROM PS_ORD_HIST MAIN_TABLE 
                    JOIN PS_ORD_HIST_LIN ITEM_TABLE ON ( MAIN_TABLE.TKT_NO = ITEM_TABLE.TKT_NO )
                    JOIN PS_ORD_HIST_CONTACT CONTACT_TABLE ON ( MAIN_TABLE.DOC_ID = CONTACT_TABLE.DOC_ID )
                    LEFT JOIN PS_ORD_HIST_DISC DISC_TABLE ON ( MAIN_TABLE.DOC_ID = DISC_TABLE.DOC_ID AND DISC_TABLE.LIN_SEQ_NO is null)
                    WHERE 
                    MAIN_TABLE.STR_ID NOT IN(3,7) 
                   -- AND MAIN_TABLE.TKT_DT >= convert(datetime,'".$startDate."') 
                   -- AND MAIN_TABLE.TKT_DT <= convert(datetime,'".$endDate."')  
                   -- AND CONTACT_TABLE.CONTACT_ID = ".$state."
                    AND MAIN_TABLE.TKT_NO = '214880' 
                    ORDER BY MAIN_TABLE.TKT_DT DESC";
        
        
        $result = odbc_exec($conn, $query);
        $count = 0;
        $i 	   = 0;
        $mainArr = array();
        $itemHeader = array('qty','sku','prc','pname','lin_seq_no');
        $addressHeader = array('email','name','street','city','state','zip_code','country','phone','nam_typ');
        $extHeader = array('doc_id','str_id','sta_id','tkt_typ','drw_id','event_no','stk_loc_id','cust_no');
        while(odbc_fetch_row($result)){
            $order_id   = odbc_result($result, 'order_id');
            $lin_seq_no = odbc_result($result, 'lin_seq_no');
            $arr 		= array();
            $items 		= array();
            $address 	= array();
            $info		= array();
            $extra      = array();
            
            //parse row data as required format
            for ($j = 1; $j <= odbc_num_fields($result); $j++){
                $field_name  = odbc_field_name($result, $j);
                $field_value = odbc_result($result, $field_name);
                if(in_array($field_name, $itemHeader)){
                    if($field_name == 'sku'){
                        $sku = strtoupper($field_value);
                        $sku = rtrim($sku,'|');
                        $sku = str_replace('/', '|', $sku);
                        $items[$field_name] = $sku;
                    }else{
                        $items[$field_name] = $field_value;
                    }
                }elseif(in_array($field_name, $addressHeader)){
                    if($field_name == 'email')
                        $field_value = strtolower($field_value);
                        $address[$field_name] = $field_value;
                }elseif(in_array($field_name, $extHeader)){
                    $extra[$field_name] = $field_value;
                }else{
                    $info[$field_name] = $field_value;
                }
            }
            
            if(!array_key_exists($order_id, $mainArr)){
                $mainArr[$order_id] = array('item_detail'=>array($lin_seq_no=>$items),
                    'customer_detail'=>$address,'order_detail'=>$info,
                    'extra_data'=>$extra,'order_type'=>'ord'
                );
            }else{
                $tempItems = $mainArr[$order_id]['item_detail'];
                $tempItems[$lin_seq_no] = $items;
                $mainArr[$order_id]['item_detail'] = $tempItems;
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
   // $client = new SoapClient($_URL, $_WSDL_SOAP_OPTIONS_ARR);
   // $session = $client->login($_AUTH_DETAILS_ARR);
    
    $reqS = addslashes(serialize($mainArr));
    $reqU = utf8_encode('"'.$reqS.'"');
    
    
    $_RequestData = array(
        'sessionId' => $session->result,
        'counterpoint_data' => $reqU
    );
    
    //$result  = $client->counterpointOrderList($_RequestData);
    $result = Mage::getModel('allure_counterpoint/order_api')->test($reqU);
    echo "<pre>";
    print_r($result);
    //$client->endSession(array('sessionId' => $session->result));
}catch (Exception $e){
    echo "<pre>";
    print_r($e);
}



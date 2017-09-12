<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();
die;
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');
//ini_set('post_max_size', '20M');

$startDate = $_GET['start'];
$endDate   = $_GET['end'];
$state     = $_GET['state'];

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

/* $from = $_GET['from'];
if(empty($from))
    die('year required'); */

$helper = Mage::helper('allure_counterpoint');

$hostName   = $helper->getHostName();
$dbUsername = $helper->getDBUserName();//"sa";
$dbPassword = $helper->getDBPassword();//"root";
$dbName = "Venus84";


$conn = odbc_connect($hostName, $dbUsername,$dbPassword);
if(0&&$conn){
    try{
        echo "Connection established...";
        $query = " select a.DOC_ID,a.TKT_NO order_id,a.event_no ,a.TKT_DT order_date,
                    a.TAX_OVRD_REAS place,a.SUB_TOT subtotal,a.tax_amt tax,
					a.tot total,concat(b.ITEM_NO,'|',b.CELL_DESCR) sku,
                    b.QTY_SOLD qty,b.prc prc,b.descr pname,
	 				c.EMAIL_ADRS_1 as email,c.nam name,c.adrs_1 street,c.city,
                    c.state,c.zip_cod zip_code , c.cntry as country,c.phone_1 phone,
                    d.disc_amt dis_amount,d.disc_pct dis_pct
					from ps_tkt_hist a join
					ps_tkt_hist_lin b on a.TKT_NO=b.TKT_NO
                    left join
                    PS_TKT_HIST_DISC d on(a.doc_id=d.doc_id and d.lin_seq_no is null)
					join ps_tkt_hist_contact c  on(a.doc_id=c.doc_id)
					where  c.CONTACT_ID=".$state." and (TAX_OVRD_REAS<>'MAGENTO' or TAX_OVRD_REAS is null)
					and a.tkt_dt >= convert(datetime,'".$startDate."') 
                    and a.tkt_dt <= convert(datetime,'".$endDate."')  
                    and a.tkt_typ='T' 
                    order by a.BUS_DAT desc;";
        
        $result = odbc_exec($conn, $query);
        $count = 0;
        $i 	   = 0;
        $mainArr = array();
        $itemHeader = array('qty','sku','prc','pname');
        $addressHeader = array('email','name','street','city','state','zip_code','country','phone');
        while(odbc_fetch_row($result)){
            $order_id = odbc_result($result, 'order_id');
            $arr 		= array();
            $items 		= array();
            $address 	= array();
            $info		= array();
            
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
                }else{
                    $info[$field_name] = $field_value;
                }
            }
            
            if(!array_key_exists($order_id, $mainArr)){
                $mainArr[$order_id] = array('item_detail'=>array($items),
                    'customer_detail'=>$address,'order_detail'=>$info);
            }else{
                $tempItems = $mainArr[$order_id]['item_detail'];
                $tempItems[] = $items;
                $mainArr[$order_id]['item_detail'] = $tempItems;
            }
            $i++;
        }
        odbc_close($conn);
        /* echo "<pre>";
        print_r(count($mainArr));
        echo "<br>";
        $ad = addslashes(json_encode($mainArr));
        print_r($ad);
        echo "<br>";
        $td = stripslashes($ad);
        print_r($td);
        die; */
        
        
    }catch (Exception $e){
        print_r($e->getMessage());
    }
}else{
    echo "Connection  not established...";
    ///die;
}

 echo "<pre>";
// print_r(count($mainArr));
/* print_r(($mainArr));
die;  */


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


try{
    $_AUTH_DETAILS_ARR = getMagentoSiteCredentials();
    $_WSDL_SOAP_OPTIONS_ARR = getSoapWSDLOptions();
    $client = new SoapClient($_URL, $_WSDL_SOAP_OPTIONS_ARR);
    $session = $client->login($_AUTH_DETAILS_ARR);
    
    $reqS = addslashes(serialize($mainArr));
    $reqU = utf8_encode('"'.$reqS.'"');
    
    
    $_RequestData = array(
        'sessionId' => $session->result,
        'counterpoint_data' => $reqU
    );
    
    $result  = $client->counterpointOrderList($_RequestData);
    //$result = Mage::getModel('allure_counterpoint/order_api')->test($reqU);
    echo "<pre>";
    print_r($result);
    $client->endSession(array('sessionId' => $session->result));
}catch (Exception $e){
    echo "<pre>";
    print_r($e);
}



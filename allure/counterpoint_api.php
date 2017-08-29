<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();

Mage::app()->setCurrentStore(0);

$from = $_GET['from'];
if(empty($from))
    die('year required');

$helper = Mage::helper('allure_counterpoint');

$hostName   = $helper->getHostName();
$dbUsername = $helper->getDBUserName();//"sa";
$dbPassword = $helper->getDBPassword();//"root";
$dbName = "Venus84";


$conn = odbc_connect($hostName, $dbUsername,$dbPassword);
if($conn){
    try{
        echo "Connection established...";
        
        $query = "SELECT a.doc_id,a.tkt_no order_id,a.tkt_dt order_date,concat(b.item_no,'|',cell_descr) sku,b.DESCR pname,
                          b.orig_qty qty,b.prc,a.sub_tot subtotal,a.tot_ext_cost,a.tax_amt tax,a.tot total, c.nam name,
                          c.EMAIL_ADRS_1 as email,c.adrs_1 street,c.city,c.state,c.zip_cod zip_code ,c.phone_1 phone,
                          c.cntry as country FROM ps_ord_hist a JOIN ps_ord_hist_lin b on(a.tkt_no=b.tkt_no)
                          join ps_ord_hist_contact c on(a.doc_id=c.doc_id) WHERE (a.TAX_OVRD_REAS<>'MAGENTO' or a.TAX_OVRD_REAS is null)
                          and a.tkt_dt like '%2008%' order by a.BUS_DAT desc;";

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
        echo "<pre>";
        /* $str = json_encode($mainArr,true);
        $ad = addslashes($str);
        echo ($ad);
        $tempD = stripslashes($ad);
        echo "<br>";
        print_r(($tempD));
        die; */
        
    }catch (Exception $e){
        print_r($e->getMessage());
    }
}else{
    echo "Connection  not established...";
    die;
}


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


$item_detail = array();
$item_detail[] = array(
    'name'=>'Test Sagar','price'=>10,
    'sku'=>'test-sagar','qty'=>1
);


$order_detail = array(
    'subtotal'=>'100.00','tax'=>'25.00',
    'order_date'=>'19-08-2017'
);

$_order_data = array();
for($i=0;$i<1;$i++){
    $id = 2250+$i;
    $customer_detail = array(
        'name'=>'Sagar G','email'=>'sagardada122145678'.$i.'@allureinc.co',
        'street'=>'Sagar Path','city'=>'Pune','state'=>'Maharashtra',
        'country'=>'India','zip_code'=>'413103','phone'=>'9657293982'
    );
    $_order_data[$id] = array(
        'item_detail'       => $item_detail,
        'customer_detail'   => $customer_detail,
        'order_detail'      => $order_detail
    );
}



try{
    $_AUTH_DETAILS_ARR = getMagentoSiteCredentials();
    $_WSDL_SOAP_OPTIONS_ARR = getSoapWSDLOptions();
    $client = new SoapClient($_URL, $_WSDL_SOAP_OPTIONS_ARR);
    $session = $client->login($_AUTH_DETAILS_ARR);
    
    $_RequestData = array(
        'sessionId' => $session->result,
        'counterpoint_data' => addslashes(json_encode($mainArr,true))
    );
    
    $result  = $client->counterpointOrderList($_RequestData);
    
    echo "<pre>";
    print_r($result);
    $client->endSession(array('sessionId' => $session->result));
}catch (Exception $e){
    echo "<pre>";
    print_r($e);
}



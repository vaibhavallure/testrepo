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
$hostName   = "cpoint";//$helper->getHostName();
$dbUsername = "sa";//$helper->getDBUserName();//"sa";
$dbPassword = "12qwaszx";//$helper->getDBPassword();//"root";
$dbName = "CPSQL";

$conn = odbc_connect($hostName, $dbUsername,$dbPassword);
if($conn){
    try{
        echo "Connection established...";
        
        $query1 = "SELECT a.doc_id,a.tkt_no order_id,a.tkt_dt order_date,concat(b.item_no,'|',cell_descr) sku,b.DESCR pname,
                          b.orig_qty qty,b.prc,a.sub_tot subtotal,a.tot_ext_cost,a.tax_amt tax,a.tot total, c.nam name,
                          c.EMAIL_ADRS_1 as email,c.adrs_1 street,c.city,c.state,c.zip_cod ,c.phone_1 phone,
                          c.cntry as country FROM ps_ord_hist a JOIN ps_ord_hist_lin b on(a.tkt_no=b.tkt_no)
                          join ps_ord_hist_contact c on(a.doc_id=c.doc_id) WHERE (a.TAX_OVRD_REAS<>'MAGENTO' or a.TAX_OVRD_REAS is null)
                          and a.tkt_dt like '%2008%' order by a.BUS_DAT desc;";
        
        $query2 = " select a.DOC_ID,a.TKT_NO order_id,a.event_no ,a.TKT_DT order_date,
                    a.TAX_OVRD_REAS place,a.SUB_TOT subtotal,a.tax_amt tax,
					a.tot total,concat(b.ITEM_NO,'|',b.CELL_DESCR) sku,
                    b.QTY_SOLD qty,b.prc prc,b.descr pname,
	 				c.EMAIL_ADRS_1 as email,c.nam name,c.nam_typ,c.adrs_1 street,c.city,
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
        
        
        $query = "SELECT MAIN_TABLE.doc_id,MAIN_TABLE.str_id,MAIN_TABLE.sta_id,
                    MAIN_TABLE.tkt_typ,MAIN_TABLE.drw_id,MAIN_TABLE.usr_id,MAIN_TABLE.stk_loc_id,MAIN_TABLE.cust_no,
                    MAIN_TABLE.tkt_no order_id, MAIN_TABLE.event_no , MAIN_TABLE.tkt_dt order_date,
                    MAIN_TABLE.tax_ovrd_reas place, MAIN_TABLE.sub_tot subtotal, MAIN_TABLE.tax_amt tax,MAIN_TABLE.tot total,
                    MAIN_TABLE.tot_tnd,MAIN_TABLE.tot_chng,
                    CONCAT(ITEM_TABLE.ITEM_NO,'|',ITEM_TABLE.CELL_DESCR) sku,
                    ITEM_TABLE.QTY_SOLD qty, ITEM_TABLE.PRC prc, ITEM_TABLE.DESCR pname,
                    ITEM_TABLE.lin_seq_no,
                    CONTACT_TABLE.EMAIL_ADRS_1 as email, CONTACT_TABLE.NAM name, AR_CUST_TABLE.CUST_NAM_TYP nam_typ, CONTACT_TABLE.ADRS_1 street, CONTACT_TABLE.CITY city,
                    CONTACT_TABLE.STATE state, CONTACT_TABLE.ZIP_COD zip_code , CONTACT_TABLE.CNTRY as country, CONTACT_TABLE.PHONE_1 phone,
                    DISC_TABLE.DISC_AMT dis_amount, DISC_TABLE.DISC_PCT dis_pct

                    FROM PS_TKT_HIST MAIN_TABLE 
                    JOIN PS_TKT_HIST_LIN ITEM_TABLE ON ( MAIN_TABLE.TKT_NO = ITEM_TABLE.TKT_NO )
                    JOIN PS_TKT_HIST_CONTACT CONTACT_TABLE ON ( MAIN_TABLE.DOC_ID = CONTACT_TABLE.DOC_ID )
                    
                    JOIN AR_CUST AR_CUST_TABLE ON( AR_CUST_TABLE.CUST_NO = MAIN_TABLE.CUST_NO )

                    LEFT JOIN PS_TKT_HIST_DISC DISC_TABLE ON ( MAIN_TABLE.DOC_ID = DISC_TABLE.DOC_ID AND DISC_TABLE.LIN_SEQ_NO is null)
                    
                    WHERE 
                    MAIN_TABLE.STR_ID NOT IN(3,7) 
                    AND MAIN_TABLE.TKT_TYP = 'T' 
                    AND MAIN_TABLE.TKT_DT >= convert(datetime,'".$startDate."') 
                    AND MAIN_TABLE.TKT_DT <= convert(datetime,'".$endDate."')  
                    AND CONTACT_TABLE.CONTACT_ID = ".$state."
                    -- AND MAIN_TABLE.DOC_ID NOT IN(SELECT DOC_ID FROM PS_TKT_HIST_ORIG_DOC)
                    -- MAIN_TABLE.TKT_NO = '285570' 
                    ORDER BY MAIN_TABLE.TKT_DT DESC";
        
        $result = odbc_exec($conn, $query);
        $count = 0;
        $i 	   = 0;
        $mainArr = array();
        $itemHeader = array('qty','sku','prc','pname','lin_seq_no');
        $addressHeader = array('email','name','street','city','state','zip_code','country','phone','nam_typ');
        $extHeader = array('doc_id','str_id','sta_id','tkt_typ','drw_id','event_no','stk_loc_id','cust_no');
        while(odbc_fetch_row($result)){
            $order_id   = odbc_result($result, 'TKT_NO');
            $lin_seq_no = odbc_result($result, 'lin_seq_no');
            $arr 		= array();
            $items 		= array();
            $address 	= array();
            $info		= array();
            $extra      = array();
            
            //parse row data as required format
            for ($j = 1; $j <= odbc_num_fields($result); $j++){
                $field_name  = odbc_field_name($result, $j);
                //$field_value = odbc_result($result, $field_name);
                
                $field_value = odbc_result($result, $field_name);
                
                if(strtolower($field_name) == strtolower("TKT_NO")){
                    $field_name = "order_id";
                }elseif (strtolower($field_name) == strtolower("TKT_DT")){
                    $field_name = "order_date";
                }elseif (strtolower($field_name) == strtolower("TAX_OVRD_REAS")){
                    $field_name = "place";
                }elseif (strtolower($field_name) == strtolower("SUB_TOT")){
                    $field_name = "subtotal";
                }elseif (strtolower($field_name) == strtolower("TAX_AMT")){
                    $field_name = "tax";
                }elseif (strtolower($field_name) == strtolower("TOT")){
                    $field_name = "total";
                }elseif (strtolower($field_name) == strtolower("QTY_SOLD")){
                    $field_name = "qty";
                }elseif (strtolower($field_name) == strtolower("DESCR")){
                    $field_name = "pname";
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
                }elseif (strtolower($field_name) == strtolower("DISC_AMT")){
                    $field_name = "dis_amount";
                }elseif (strtolower($field_name) == strtolower("DISC_PCT")){
                    $field_name = "dis_pct";
                }
                
                
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
                    'extra_data'=>$extra,'order_type'=>'tkt'
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
        'counterpoint_data' => $reqU,
        'memory_limit' => '-1',
        'is_memory_limit' => 1
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



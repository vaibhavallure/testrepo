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
                    CONCAT(ITEM_TABLE.ITEM_NO,'|',ITEM_TABLE.CELL_DESCR) sku,
                    ITEM_TABLE.QTY_SOLD qty, ITEM_TABLE.PRC prc, ITEM_TABLE.DESCR pname,
                    ITEM_TABLE.lin_seq_no,
                    CONTACT_TABLE.EMAIL_ADRS_1 as email, CONTACT_TABLE.NAM name, CONTACT_TABLE.NAM_TYP nam_typ, CONTACT_TABLE.ADRS_1 street, CONTACT_TABLE.CITY city,
                    CONTACT_TABLE.STATE state, CONTACT_TABLE.ZIP_COD zip_code , CONTACT_TABLE.CNTRY as country, CONTACT_TABLE.PHONE_1 phone,
                    DISC_TABLE.DISC_AMT dis_amount, DISC_TABLE.DISC_PCT dis_pct,
                    PMT_TABLE.pay_cod, PMT_TABLE.pay_cod_typ, PMT_TABLE.descr,PMT_TABLE.pmt_lin_typ,PMT_TABLE.amt,
                    PMT_TABLE.pmt_seq_no,
                    PMT_CARD_TABLE.cr_card_no, PMT_CARD_TABLE.cr_card_no_msk, PMT_CARD_TABLE.cr_card_nam, PMT_CARD_TABLE.cr_card_exp_dat,
                    PMT_RCPT_TABLE.trans_typ, PMT_RCPT_TABLE.unique_trans_id, PMT_RCPT_TABLE.trans_stat, PMT_RCPT_TABLE.trans_approved,
                    PMT_RCPT_TABLE.processor_trans_id, PMT_RCPT_TABLE.rcpt_card_no_msk, PMT_RCPT_TABLE.rcpt_card_typ,
                    PMT_RCPT_TABLE.rcpt_amt, PMT_RCPT_TABLE.processor_msg, PMT_RCPT_TABLE.rcpt_msg, PMT_RCPT_TABLE.entry_meth,
                    PMT_RCPT_TABLE.processor_client_rcpt, PMT_RCPT_TABLE.processor_merch_rcpt

                    FROM PS_TKT_HIST MAIN_TABLE 
                    JOIN PS_TKT_HIST_LIN ITEM_TABLE ON ( MAIN_TABLE.TKT_NO = ITEM_TABLE.TKT_NO )
                    JOIN PS_TKT_HIST_PMT PMT_TABLE ON ( MAIN_TABLE.TKT_NO = PMT_TABLE.TKT_NO ) 
                    JOIN PS_TKT_HIST_CONTACT CONTACT_TABLE ON ( MAIN_TABLE.DOC_ID = CONTACT_TABLE.DOC_ID )
                    LEFT JOIN PS_TKT_HIST_DISC DISC_TABLE ON ( MAIN_TABLE.DOC_ID = DISC_TABLE.DOC_ID AND DISC_TABLE.LIN_SEQ_NO is null)
                    LEFT JOIN PS_TKT_HIST_PMT_CHK PMT_CHECK_TABLE ON ( PMT_TABLE.DOC_ID = PMT_CHECK_TABLE.DOC_ID )
                    LEFT JOIN PS_TKT_HIST_PMT_CR_CARD PMT_CARD_TABLE ON( PMT_TABLE.DOC_ID = PMT_CARD_TABLE.DOC_ID )
                    LEFT JOIN PS_TKT_HIST_PMT_RCPT PMT_RCPT_TABLE on(PMT_TABLE.DOC_ID = PMT_RCPT_TABLE.DOC_ID)
                    WHERE 
                    MAIN_TABLE.STR_ID NOT IN(3 , 7)
                    AND MAIN_TABLE.TKT_TYP = 'T'
                    AND MAIN_TABLE.TKT_DT >= convert(datetime,'".$startDate."') 
                    AND MAIN_TABLE.TKT_DT <= convert(datetime,'".$endDate."')  
                    AND CONTACT_TABLE.CONTACT_ID = ".$state."
                    -- AND MAIN_TABLE.TKT_NO = '285086' 
                    ORDER BY MAIN_TABLE.TKT_DT DESC";
        
        $result = odbc_exec($conn, $query);
        $count = 0;
        $i 	   = 0;
        $mainArr = array();
        $itemHeader = array('qty','sku','prc','pname','lin_seq_no');
        $addressHeader = array('email','name','street','city','state','zip_code','country','phone','nam_typ');
        $paymentHeader = array('pmt_seq_no','pay_cod','pay_cod_typ','descr','pmt_lin_typ',
            'amt','cr_card_no','cr_card_no_msk','cr_card_nam','cr_card_exp_dat',
            'trans_typ','unique_trans_id','trans_stat','trans_approved','processor_trans_id',
            'rcpt_card_no_msk','rcpt_card_typ','rcpt_amt','processor_msg','rcpt_msg',
            'entry_meth','processor_client_rcpt','processor_merch_rcpt'
            );
        $extHeader = array('doc_id','str_id','sta_id','tkt_typ','drw_id','event_no','stk_loc_id','cust_no');
        while(odbc_fetch_row($result)){
            $order_id   = odbc_result($result, 'order_id');
            $lin_seq_no = odbc_result($result, 'lin_seq_no');
            $pmt_seq_no = odbc_result($result, 'pmt_seq_no');
            $arr 		= array();
            $items 		= array();
            $address 	= array();
            $info		= array();
            $payment    = array();
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
                        $field_value = "sagar@allureinc.co";//strtolower($field_value);
                     $address[$field_name] = $field_value;
                }elseif(in_array($field_name, $paymentHeader)){
                    $payment[$field_name] = $field_value;
                }elseif(in_array($field_name, $extHeader)){
                    $extra[$field_name] = $field_value;
                }else{
                    $info[$field_name] = $field_value;
                }
            }
            
            if(!array_key_exists($order_id, $mainArr)){
                $mainArr[$order_id] = array('item_detail'=>array($lin_seq_no=>$items),
                    'customer_detail'=>$address,'order_detail'=>$info,
                    'payment'=>array($pmt_seq_no=>$payment),'extra_data'=>$extra
                );
            }else{
                $tempItems = $mainArr[$order_id]['item_detail'];
                $tempItems[$lin_seq_no] = $items;
                $mainArr[$order_id]['item_detail'] = $tempItems;
                
                $tempPayment = $mainArr[$order_id]['payment'];
                $tempPayment[$pmt_seq_no] = $payment;
                $mainArr[$order_id]['payment'] = $tempPayment;
                
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
    die;
}

 echo "<pre>";
 print_r(count($mainArr));
/* print_r(($mainArr));
die; */ 


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
    'pname'=>'Test Sagar','prc'=>795,
    'sku'=>'test-sagar','qty'=>-1
);

 $item_detail[] = array(
    'pname'=>'Test Sagar 1 ','prc'=>175,
    'sku'=>'test-sagar-1','qty'=>-1
);
  $item_detail[] = array(
    'pname'=>'Test Sagar 2 ','prc'=>385,
    'sku'=>'test-sagar-2','qty'=>1
);
 $item_detail[] = array(
    'pname'=>'Test Sagar 3 ','prc'=>430,
    'sku'=>'test-sagar-3','qty'=>1
); 
/*$item_detail[] = array(
    'pname'=>'Test Sagar 1 ','prc'=>28.68,
    'sku'=>'test-sagar1','qty'=>1
);
 */

$order_detail = array(
    'subtotal'=>'100.00','tax'=>'-2.98',
    'order_date'=>'19-08-2017',
    'lins'=>'3',
    'sal_lins'=>'1',
    'ret_sal_lins'=>'2',
    'sal_lin_tot'=>'120',
    'ret_lin_tot'=>'-155'
);

$_order_data = array();
for($i=0;$i<1;$i++){
    $id = 5001;
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
    //$client = new SoapClient($_URL, $_WSDL_SOAP_OPTIONS_ARR);
    //$session = $client->login($_AUTH_DETAILS_ARR);
    
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



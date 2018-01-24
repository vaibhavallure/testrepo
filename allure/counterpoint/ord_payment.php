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
                    PMT_TABLE.pay_cod, PMT_TABLE.pay_cod_typ, PMT_TABLE.descr,PMT_TABLE.pmt_lin_typ,PMT_TABLE.amt,
                    PMT_TABLE.pmt_seq_no,PMT_TABLE.home_curncy_amt,
                    PMT_CARD_TABLE.cr_card_no, PMT_CARD_TABLE.cr_card_no_msk, PMT_CARD_TABLE.cr_card_nam, PMT_CARD_TABLE.cr_card_exp_dat,
                    PMT_RCPT_TABLE.trans_typ, PMT_RCPT_TABLE.unique_trans_id, PMT_RCPT_TABLE.trans_stat, PMT_RCPT_TABLE.trans_approved,
                    PMT_RCPT_TABLE.processor_trans_id, PMT_RCPT_TABLE.rcpt_card_no_msk, PMT_RCPT_TABLE.rcpt_card_typ,
                    PMT_RCPT_TABLE.rcpt_amt, PMT_RCPT_TABLE.processor_msg, PMT_RCPT_TABLE.rcpt_msg, PMT_RCPT_TABLE.entry_meth,
                    PMT_RCPT_TABLE.processor_client_rcpt, PMT_RCPT_TABLE.processor_merch_rcpt

                    FROM PS_ORD_HIST MAIN_TABLE 
                    INNER JOIN PS_ORD_HIST_PMT PMT_TABLE ON ( MAIN_TABLE.TKT_NO = PMT_TABLE.TKT_NO) 
                    LEFT JOIN PS_ORD_HIST_PMT_CHK PMT_CHECK_TABLE ON ( PMT_TABLE.DOC_ID = PMT_CHECK_TABLE.DOC_ID AND PMT_TABLE.PMT_SEQ_NO = PMT_CHECK_TABLE.PMT_SEQ_NO)
                    Left JOIN PS_ORD_HIST_PMT_CR_CARD PMT_CARD_TABLE ON( PMT_TABLE.DOC_ID = PMT_CARD_TABLE.DOC_ID AND PMT_TABLE.PMT_SEQ_NO = PMT_CARD_TABLE.PMT_SEQ_NO)
                    LEFT JOIN PS_ORD_HIST_PMT_RCPT PMT_RCPT_TABLE on(PMT_TABLE.DOC_ID = PMT_RCPT_TABLE.DOC_ID AND PMT_TABLE.PMT_SEQ_NO = PMT_RCPT_TABLE.PMT_SEQ_NO)
                    WHERE 
                    MAIN_TABLE.STR_ID NOT IN(3,7)
                    -- AND MAIN_TABLE.TKT_DT >= convert(datetime,'".$startDate."') 
                    -- AND MAIN_TABLE.TKT_DT <= convert(datetime,'".$endDate."')  
                    AND MAIN_TABLE.TKT_NO = '214880' 
                    ORDER BY MAIN_TABLE.TKT_DT DESC";
        
        $result = odbc_exec($conn, $query);
        $count = 0;
        $i 	   = 0;
        $mainArr = array();
        $paymentHeader = array('pmt_seq_no','pay_cod','pay_cod_typ','descr','pmt_lin_typ',
            'amt','home_curncy_amt','cr_card_no','cr_card_no_msk','cr_card_nam','cr_card_exp_dat',
            'trans_typ','unique_trans_id','trans_stat','trans_approved','processor_trans_id',
            'rcpt_card_no_msk','rcpt_card_typ','rcpt_amt','processor_msg','rcpt_msg',
            'entry_meth','processor_client_rcpt','processor_merch_rcpt'
            );
        $extHeader = array('doc_id','str_id','sta_id','tkt_typ','drw_id','event_no','stk_loc_id','cust_no');
        while(odbc_fetch_row($result)){
            $order_id   = odbc_result($result, 'order_id');
            $pmt_seq_no = odbc_result($result, 'pmt_seq_no');
            $arr 		= array();
            $info		= array();
            $payment    = array();
            $extra      = array();
            
            //parse row data as required format
            for ($j = 1; $j <= odbc_num_fields($result); $j++){
                $field_name  = odbc_field_name($result, $j);
                $field_value = odbc_result($result, $field_name);
                if(in_array($field_name, $paymentHeader)){
                    $payment[$field_name] = $field_value;
                }elseif(in_array($field_name, $extHeader)){
                    $extra[$field_name] = $field_value;
                }else{
                    $info[$field_name] = $field_value;
                }
            }
            
            if(!array_key_exists($order_id, $mainArr)){
                $mainArr[$order_id] = array(
                    'order_detail'=>$info,
                    'payment'=>array($pmt_seq_no=>$payment),
                    'extra_data'=>$extra,
                    'order_type'=>'ord'
                );
            }else{
                $tempPayment = $mainArr[$order_id]['payment'];
                $tempPayment[$pmt_seq_no] = $payment;
                $mainArr[$order_id]['payment'] = $tempPayment;
                
            }
            $i++;
        }
        odbc_close($conn);
    }catch (Exception $e){
        print_r($e->getMessage());
    }
}else{
    echo "Connection  not established...";
    //die;
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
    //$client = new SoapClient($_URL, $_WSDL_SOAP_OPTIONS_ARR);
    //$session = $client->login($_AUTH_DETAILS_ARR);
    
    $reqS = addslashes(serialize($mainArr));
    $reqU = utf8_encode('"'.$reqS.'"');
    
    
    $_RequestData = array(
        'sessionId' => $session->result,
        'payment_data' => $reqU
    );
    
    //$result  = $client->counterpointOrderAddPayment($_RequestData);
    $result = Mage::getModel('allure_counterpoint/order_api')
        ->addPayment($reqU);
    echo "<pre>";
    print_r($result);
    //$client->endSession(array('sessionId' => $session->result));
}catch (Exception $e){
    echo "<pre>";
    print_r($e);
}



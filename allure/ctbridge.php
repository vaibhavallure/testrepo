<?php
 require_once('../app/Mage.php'); 
umask(0);
Mage::app(); 

Mage::app()->setCurrentStore(0);

$fromYear = $_GET['from'];
$toYear = $_GET['to'];

/* if(empty($fromYear) && empty($toYear))
    die("Please Provide correct data!!!");
    
Mage::getModel('allure_counterpoint/data')->synkCounterpointOrders($fromYear,$toYear);
    
die("Finish!!!"); */

$helper = Mage::helper('allure_counterpoint');

$hostName   = $helper->getHostName();
$dbUsername = $helper->getDBUserName();//"sa";
$dbPassword = $helper->getDBPassword();//"root";
$dbName = "Venus84";


$conn = odbc_connect($hostName, $dbUsername,$dbPassword);
if($conn){
    try{
        echo "Connection established...";
        $from = $fromYear;//'2017-05-30 23:59:59';
        $to = $toYear;//'2017-05-30 00:00:00';
        
        $query2 = "select a.DOC_ID,a.TKT_NO order_id,a.TKT_DT order_date,a.TAX_OVRD_REAS place,a.SUB_TOT subtotal,a.tax_amt tax,
					a.tot total,concat(b.ITEM_NO,'|',b.CELL_DESCR) sku,b.QTY_SOLD qty,b.prc,b.descr pname,
	 				c.EMAIL_ADRS_1 as email,c.nam name,c.adrs_1 street,c.city,c.state,c.zip_cod , c.cntry as country,c.phone_1 phone
					from ps_tkt_hist a join
					ps_tkt_hist_lin b on a.TKT_NO=b.TKT_NO
					join ps_tkt_hist_contact c  on(a.doc_id=c.doc_id)
					where c.CONTACT_ID=1 and b.QTY_SOLD>0 and (TAX_OVRD_REAS<>'MAGENTO' or TAX_OVRD_REAS is null)
					and a.tkt_dt <='".$from."' and a.tkt_dt >='".$to."' order by a.BUS_DAT desc;";
        //and tkt_dt >='2017-05-30'
        $query1 = "select * from dbo.ps_ord_hist where tkt_no='2017003176'";
        
        $query = "SELECT a.doc_id,a.tkt_no order_id,a.tkt_dt order_date,concat(b.item_no,'|',cell_descr) sku,b.DESCR pname,
                          b.orig_qty qty,b.prc,a.sub_tot subtotal,a.tot_ext_cost,a.tax_amt tax,a.tot total, c.nam name,
                          c.EMAIL_ADRS_1 as email,c.adrs_1 street,c.city,c.state,c.zip_cod ,c.phone_1 phone,
                          c.cntry as country FROM ps_ord_hist a JOIN ps_ord_hist_lin b on(a.tkt_no=b.tkt_no)
                          join ps_ord_hist_contact c on(a.doc_id=c.doc_id) WHERE (a.TAX_OVRD_REAS<>'MAGENTO' or a.TAX_OVRD_REAS is null)
                          and a.tkt_dt like '%2009%' or a.tkt_dt like '%2010%' or a.tkt_dt like '%2011%' order by a.BUS_DAT desc;";
                              
        
        
        $result = odbc_exec($conn, $query);
        $count = 0;
        $i 	   = 0;
        $mainArr = array();
        $itemHeader = array('qty','sku','prc','pname');
        $addressHeader = array('email','name','street','city','state','zip_cod','country','phone');
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
                $mainArr[$order_id] = array('items'=>array($items),
                    'address'=>$address,'info'=>$info);
            }else{
                $tempItems = $mainArr[$order_id]['items'];
                $tempItems[] = $items;
                $mainArr[$order_id]['items'] = $tempItems;
            }
            $i++;
        }
        odbc_close($conn);
        echo "<pre>";
        print_r(count($mainArr));
        
    }catch (Exception $e){
        print_r($e->getMessage());
    }
}else{
   echo "Connection  not established...";
}

die;




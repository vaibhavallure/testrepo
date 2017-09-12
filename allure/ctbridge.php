<?php
 require_once('../app/Mage.php'); 
umask(0);
Mage::app(); 

$helper = Mage::helper('allure_counterpoint');

$hostName   = $helper->getHostName();
$dbUsername = $helper->getDBUserName();//"sa";
$dbPassword = $helper->getDBPassword();//"root";
$dbName = "Venus84";


$conn = odbc_connect($hostName, $dbUsername,$dbPassword);
if($conn){
    try{
        $query = "select  a.bus_dat,a.doc_id,a.event_no,a.tkt_no,a.tkt_dt,a.sal_lin_tot,
                  a.ret_lin_tot,a.lins,a.sal_lins,a.ret_lins,a.sub_tot,
                  a.tax_amt,a.tot_chng,a.tot,
                  b.lin_typ,b.item_no,b.descr,b.ret_reas,b.qty_sold,
                  b.prc,b.ext_prc,b.gross_ext_prc,b.disp_ext_prc,
                  c.disc_typ,c.disc_amt,c.disc_pct
                  FROM PS_TKT_HIST a 
                  join PS_TKT_HIST_LIN b on(a.tkt_no=b.tkt_no)
                  left join PS_TKT_HIST_DISC c on(b.doc_id = c.doc_id 
                    AND b.lin_seq_no=c.lin_seq_no)
                  where (TAX_OVRD_REAS<>'MAGENTO' or TAX_OVRD_REAS is null) 
                        and a.tkt_typ='T' 
                    and a.tkt_dt >= convert(datetime,'2015-01-01')
                    and a.tkt_dt <= convert(datetime,'2015-03-31');";
        $result = odbc_exec($conn, $query);
        $count = 0;
        $i 	   = 0;
        $mainArr = array();
        $itemHeader = array('lin_typ','item_no','descr','ret_reas','qty_sold','prc','ext_prc',
        'gross_ext_prc','disp_ext_prc','disc_typ','disc_amt','disc_pct');
        while(odbc_fetch_row($result)){
            $order_id = odbc_result($result, 'tkt_no');
            $arr 		= array();
            $items 		= array();
            $address 	= array();
            $info		= array();
            
            //parse row data as required format
            for ($j = 1; $j <= odbc_num_fields($result); $j++){
                $field_name  = odbc_field_name($result, $j);
                $field_value = odbc_result($result, $field_name);
                if(in_array($field_name, $itemHeader)){
                    $items[$field_name] = $field_value;
                }else{
                    $info[$field_name] = $field_value;
                }
            }
            
            if(!array_key_exists($order_id, $mainArr)){
                $mainArr[$order_id] = array('items'=>array($items),
                   'info'=>$info);
            }else{
                $tempItems = $mainArr[$order_id]['items'];
                $tempItems[] = $items;
                $mainArr[$order_id]['items'] = $tempItems;
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
/* echo "<pre>";
print_r($mainArr);
die; */

$csv = Mage::getBaseDir('var').DS."import".DS."magento_order_2.csv";
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');
$arrCsv = array();
while($csvData = $io->streamReadCsv()){
    $arrCsv[$csvData[1]] = $csvData[0];
}

$str = "<table border='1'>
  <tr>
    <th>MAGENTO_ID</th>
    <th>TKT_NO</th>
    <th>BUST_DAT</th>
    <th>DOC_ID</th>
    <th>EVENT_NO</th>
    <th>ORD_DT</th>
    <th>SAL_LIN_TOT</th>
    <th>RET_LIN_TOT</th>
    <th>LINS</th>
    <th>SAL_LINS</th>
    <th>RET_LINS</th>
    <th>SUB_TOT</th>
    <th>TAX_AMT</th>
    <th>TOT_CHNG</th>
    <th>TOT</th>
    <th>
        ITEMS
        <table>
            		<tr>
            			<th>LIN_TYP</th>
            			<th>ITEM_NO</th>
            			<th>DESCR</th>
            			<th>RET_REAS</th>
                        <th>QTY_SOLD</th>
            			<th>PRC</th>
            			<th>EXT_PRC</th>
            			<th>GROSS_EXT_PRC</th>
                        <th>DISP_EXT_PRC</th>
            			<th>DISC_TYP</th>
                        <th>DISC_AMT</th>
            			<th>DISC_PCT</th>
            		</tr>
            </table>
    </th>
  </tr>";
foreach ($mainArr as $key=>$data){
    $orderDetail = $data['info'];
    $str .= "<tr style='border-bottom: solid 1px black;'>
    <td>{$arrCsv[$key]}</td>
    <td>$key</td>
    <td>{$orderDetail['bus_dat']}</td>
    <td>{$orderDetail['doc_id']}</td>
	<td>{$orderDetail['event_no']}</td>
    <td>{$orderDetail['tkt_dt']}</td>
    <td>{$orderDetail['sal_lin_tot']}</td>
    <td>{$orderDetail['ret_lin_tot']}</td>
    <td>{$orderDetail['lins']}</td>
    <td>{$orderDetail['sal_lins']}</td>
    <td>{$orderDetail['ret_lins']}</td>
    <td>{$orderDetail['sub_tot']}</td>
    <td>{$orderDetail['tax_amt']}</td>
    <td>{$orderDetail['tot_chng']}</td>
    <td>{$orderDetail['tot']}</td>
    
    <td>
    	<table>
    		";
    foreach($data['items'] as $items){
        $str .= "<tr >
    				<td>{$items['lin_typ']}</td>
    				<td>{$items['item_no']}</td>
    				<td>{$items['descr']}</td>
    				<td>{$items['ret_reas']}</td>

                    <td>{$items['qty_sold']}</td>
    				<td>{$items['prc']}</td>
    				<td>{$items['ext_prc']}</td>
    				<td>{$items['gross_ext_prc']}</td>

                    <td>{$items['disp_ext_prc']}</td>
    				<td>{$items['disc_typ']}</td>
    				<td>{$items['disc_amt']}</td>
    				<td>{$items['disc_pct']}</td>

    			</tr>";
    }
    $str .= "</table>
    </td>
    
  </tr>";
}
$str .="</table>";

header ( "Content-type: application/vnd.ms-excel" );
header ( "Content-Disposition: attachment; filename=cp_order_2015.xls" );

echo $str;





die;

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
       // echo "Connection established...";
        $from = $fromYear;//'2017-05-30 23:59:59';
        $to = $toYear;//'2017-05-30 00:00:00';
        
        $query = " select a.DOC_ID,a.TKT_NO order_id,a.TKT_DT order_date,
                    a.TAX_OVRD_REAS place,a.SUB_TOT subtotal,a.tax_amt tax,
					a.tot total,concat(b.ITEM_NO,'|',b.CELL_DESCR) sku,
                    b.QTY_SOLD qty,b.prc,b.descr pname,
	 				c.EMAIL_ADRS_1 as email,c.nam name,c.adrs_1 street,c.city,
                    c.state,c.zip_cod zip_code, c.cntry as country,c.phone_1 phone,
                    d.disc_amt dis_amount,d.disc_pct dis_pct
					from ps_tkt_hist a left join
                    PS_TKT_HIST_DISC d on(a.doc_id=d.doc_id) join
					ps_tkt_hist_lin b on a.TKT_NO=b.TKT_NO
					join ps_tkt_hist_contact c  on(a.doc_id=c.doc_id)
					where c.CONTACT_ID=1 and b.QTY_SOLD>0 and (TAX_OVRD_REAS<>'MAGENTO' or TAX_OVRD_REAS is null)
					and a.tkt_no ='285094' order by a.BUS_DAT desc;";
        //and a.tkt_dt <='".$from."' and a.tkt_dt >='".$to."'
        //and tkt_dt >='2017-05-30'
        $query1 = "select * from dbo.ps_ord_hist where tkt_no='2017003176'";
        
        $query1 = "SELECT a.doc_id,a.tkt_no order_id,a.tkt_dt order_date,concat(b.item_no,'|',cell_descr) sku,b.DESCR pname,
                          b.orig_qty qty,b.prc,a.sub_tot subtotal,a.tot_ext_cost,a.tax_amt tax,a.tot total, c.nam name,
                          c.EMAIL_ADRS_1 as email,c.adrs_1 street,c.city,c.state,c.zip_cod ,c.phone_1 phone,
                          c.cntry as country FROM ps_ord_hist a JOIN ps_ord_hist_lin b on(a.tkt_no=b.tkt_no)
                          join ps_ord_hist_contact c on(a.doc_id=c.doc_id) WHERE (a.TAX_OVRD_REAS<>'MAGENTO' or a.TAX_OVRD_REAS is null)
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
        //$st = addslashes('"'.serialize($mainArr).'"');
       // print_r($st);
       // echo "<br>";
       // $st = trim(stripslashes($st),'"');
       // print_r(count(unserialize($st)));
       print_r(($mainArr));
        
    }catch (Exception $e){
        print_r($e->getMessage());
    }
}else{
   echo "Connection  not established...";
}

die;
die;
$csv = Mage::getBaseDir('var').DS."import".DS."magento_order_1.csv";
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');
$arrCsv = array();
while($csvData = $io->streamReadCsv()){
    $arrCsv[$csvData[1]] = $csvData[0];
}
//echo "<pre>";
//print_r($arrCsv);

//die;

header ( "Content-type: application/vnd.ms-excel" );
header ( "Content-Disposition: attachment; filename=counterpoint_order.xls" );


$str = "<table>
  <tr>
    <th>magento_order_id</th>
    <th>ct_order_id</th>
    <th>
        items
    </th>
    <th>customer name</th>
    <th>email</th>
    <th>street</th>
    <th>city</th>
    <th>state</th>
    <th>zip</th>
    <th>country</th>
    <th>phone</th>
    <th>subtotal</th>
    <th>tax</th>
    <th>order date</th>
  </tr>";
   foreach ($mainArr as $key=>$data){
 $str .= "<tr>
    <td>$arrCsv[$key]</td>
    <td>$key</td>
    <td>
    	<table>
    		<tr>
    			<th>sku</th>
    			<th>name</th>
    			<th>price</th>
    			<th>qty</th>
    		</tr>";
    	 foreach($data['items'] as $items){
    	   $str .= "<tr>
    				<td>{$items['sku']}</td>
    				<td>{$items['pname']}</td>
    				<td>{$items['prc']}</td>
    				<td>{$items['qty']}</td>
    			</tr>";
    	   }
    		
    $customerInfo = $data['address'];
    $orderDetail = $data['info'];
    		
    $str .= "</table>
    </td>
    <td>{$customerInfo['name']}</td>
    <td>{$customerInfo['email']}</td>
    <td>{$customerInfo['street']}</td>
    <td>{$customerInfo['city']}</td>
    <td>{$customerInfo['state']}</td>
    <td>{$customerInfo['zip_cod']}</td>
    <td>{$customerInfo['country']}</td>
    <td>{$customerInfo['phone']}</td>
    
     
    <td>{$orderDetail['subtotal']}</td>
    <td>{$orderDetail['tax']}</td>
	<td>{$orderDetail['order_date']}</td>
  </tr>";
   }
  $str .="</table>";

  echo $str;

    


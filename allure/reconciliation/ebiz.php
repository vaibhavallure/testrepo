
<html>

<body>

<form method="POST" action="">
    <label for="from">Date From (e.g. 1)</label>
    <input type="number" name="from" id="from" required><br><br>
    <label for="to">Date To (e.g. 30)</label>
    <input type="number" name="to" id="to" required><br><br>
    <label for="month">Month (e.g. for march use 3)</label>
    <input type="number" name="month" id="month" required><br><br>
    <label for="year">Year (e.g. for 2017 use 17)</label>
    <input type="number" name="year" id="year" required><br><br>
    <input type="submit" value="Submit" name="submit_btn">

</form>

</body>
<?php
if(isset($_REQUEST['submit_btn']))
{
ebiz();
}
function ebiz()
{
    $fileName = "./ebiz/";
    $files = array(
        "16-01-2018 Employee Transactions With Comments By RG - Employee Transactions With Comm.csv",
    );

    $from = intval($_POST['from']);
    $month = intval($_POST['month']);
    $to = intval($_POST['to']);
    $year = $_POST['year'];
//Liberty Comparisons - 170901_Employee Transactions With Comm
//liberty comparisons - 171201 - Employee Transactions With Comm.csv 
//liberty comparisons - 171031 - Employee Transactions With Comm
    for ($i = $from; $i <= $to; $i++) {
        $f = $i >= 10 ? $i : "0" . $i;
        $m = $month >= 10 ? $month : "0" . $month;
        $fileName = $f.$m.$year."_Employee Transactions With Comm.csv";
        // 020217 - Employee Transactions With Comm
        // $fileName = "Employee Transactions With Comments By RG ".$f.".".$m.".".$year." - Employee Transactions With Comm.csv";   
// $fp = fopen("summary.txt","a");
//     fwrite($fp,'Working On'.$fileName.PHP_EOL);
//     fclose($fp); 
        //$fileName = "Liberty Comparisons - ".$year.$m.$f."_Employee Transactions With Comm.csv";
        //  var_dump("20".$year."-".$m."-".$f."\n");
//   var_dump($fileName);

        $date = "20" . $year . "-" . $m . "-" . $f;
//    var_dump($date);
        searchByGtAndQty($fileName, $date);
    }
}

//die;


//searchByGtAndQtyAndMinusPrice($fileName);


/**
 * SOAP request to Magento to fetch orders
 */
function getIncrementId($date,$base_grand_total,$total_qty_ordered,$client,$sessionId,$isMinus) {
    /* create filter where we can search in array of increment id's*/
    // var_dump($date);
    // var_dump(implode(',',array(
    //     $base_grand_total,              
    //     $base_grand_total-1,            
    //     $base_grand_total+20            
    // )));
    // var_dump(implode(',',array($total_qty_ordered,$total_qty_ordered+1)));die;
    if($isMinus){
        $gt = array(
            'key' => 'base_grand_total',
            'value' => array(
                'key' => 'eq',
                'value' => $base_grand_total
            )
        );
    }else{
        $gt = array(
            'key' => 'base_grand_total',
            'value' => array(
                'key' => 'in',
                'value' => implode(',',array(
                    $base_grand_total,              
                    $base_grand_total-1,            
                    $base_grand_total+20,            
                    $base_grand_total-15            
                ))
            )
        );
    }

    $filters = array(

        'complex_filter' => array(
            array(
                'key' => 'created_at',
                'value' => array(
                    'key' => 'from',
                    'value' => $date.' 00:00:00'
                ),
            ),
            array(
                'key' => 'old_store_id',
                'value' => array(
                    'key' => 'eq',
                    'value' => 2
                ),
            ),
            array(
                'key' => 'created_at',
                'value' => array(
                    'key' => 'to',
                    'value' => $date.' 23:59:59'
                ),
            ),
            array(
                'key' => 'total_qty_ordered',
                'value' => array(
                    'key' => 'in',
                    'value' => implode(',',array($total_qty_ordered,$total_qty_ordered+1))
                ),
            ),
            $gt
        )
    );

    /* call salesOrderList service with given filter*/
    $salesOrderResult = $client->salesOrderList(array('sessionId' => $sessionId, 'filters' => $filters));
    //print_r(json_encode($salesOrderResult,true));die;
    $foundArr = [];
    foreach($salesOrderResult as $orderArray) {
        foreach($orderArray as $order) {
            //var_dump($order);die;
            if(is_array($order)){
                foreach ($order as $val) {
                    array_push($foundArr,$val->increment_id);  
                }
            }else{
                array_push($foundArr,$order->increment_id);                  
            }
        }
    }    
    return $foundArr;
}


/**
 * Try to match the G.T and Total Quantity of products
 * @param fileName
 */
function searchByGtAndQty($fileName,$date) {

    // Create the SoapClient instance
    $url         = "https://www.mariatash.com/api/v2_soap?wsdl=1";
    $client     = new SoapClient($url, array("trace" => 1, "exception" => 0,'cache_wsdl'=>WSDL_CACHE_NONE));

    /* get seesion id by calling login service*/
    $tokenResult = $client->login(array(
        "username" => "allureinc",
        "apiKey" => "12qwaszx"
    ));
    

    //print_r($filterIncrementIds);
    $sessionId = $tokenResult->result; 
    $lines = file("./ebiz/".$fileName);
    $i = 0;
    foreach ($lines as $lineNumber => $line) {      
       // echo "<pre>";      
            if (strpos($line, "Total") !== false) {
              $myvalue = $lines[$lineNumber];
              $lineArray = explode(',',trim($myvalue));
              //var_dump($lineArray);
              $grandTotal= floatval(str_replace('"','',$lineArray[9]));
              $totalQty=intval(str_replace('"','',$lineArray[13]));     
              $promotionAmount = floatval($lineArray[10]);                  
              //var_dump($grandTotal);var_dump($totalQty);
              if(!empty($promotionAmount))
                $grandTotal+=abs($promotionAmount);
            
              $incrementIDS = getIncrementId($date,$grandTotal,$totalQty,$client,$sessionId,false);
              $inc = "";

//              print_r('Data  '.$grandTotal." - ".$totalQty."\n");

              // TO DO - CHECK ORDER ITEMS AND SKU            
              // If there are multiple check there SKU and set appropriate Increment ID
              if(!empty($incrementIDS)){
                  foreach ($incrementIDS as $val) {
                      $inc.=$val." ";
                  }
                  $i+=1;
                  //print_r('Added '.$inc."\n");
              }                            
              //var_dump($inc);die;

              $lines[$lineNumber] = trim($lines[$lineNumber]).",".$inc.PHP_EOL;
              file_put_contents('./ebizoutput/'.$fileName,$lines);
            } 
    }

    $fp = fopen("summary.txt","a");
    fwrite($fp,'Total added for '.$date." : - ".$i.PHP_EOL);
    fclose($fp);    
}

 ?>
</html>

<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();

/**
 * Enabling CORS
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
  header('Access-Control-Allow-Headers: token, Content-Type');
  header('Access-Control-Max-Age: 1728000');
  header('Content-Length: 0');
  die();
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: *');


/**
 * To get date from request
 * Have minor problem with Axios so it's inserting name as key
 */
if(empty($_POST['date'])){
  $arr = array_keys($_POST);
  $dateArr = json_decode($arr[0]);
  $selected_date = $dateArr->date;
}else{                              //this is for w/o axios request
  $selected_date = $_POST['date'];
}

$dir = "";
//print_r($dir);die;
$fp = fopen($dir.$selected_date.'.csv', 'a');


/**
 * Called 3times manually so as to avoid gateway timeout
 */
writeCSV($selected_date."%2000:00:00",$selected_date."%2015:00:00",$fp);
writeCSV($selected_date."%2015:00:00",$selected_date."%2017:00:00",$fp);
writeCSV($selected_date."%2017:00:00",$selected_date."%2023:59:59",$fp);
fclose($fp);

print $dir.$selected_date.'.csv';
die;

/**
 * Makes CSV using TW api for given Start and End time
 * @author Indrajeet Latthe
 * @param $from_date_time = String $to_date_time = String
 */
function writeCSV($from_date_time,$to_date_time,$fp) {
  $ch = curl_init();
  $headers = array(
    'Accept: application/json',
    'Content-Type: application/json', 
  );
  curl_setopt($ch, CURLOPT_URL, "http://35.237.115.49/indra.php?start=".$from_date_time."&end=".$to_date_time);
  //echo "http://35.237.115.49/indra.php?start=".$from_date_time."&end=".$to_date_time;
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  // Timeout in seconds
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);

  $result = curl_exec($ch);

  $result = unserialize($result);

  foreach($result as $key => $value){
    $orderDetails = $value['order_detail'];
   // print_r($orderDetails);die;
    $receiptNum = $orderDetails['ReceiptNum'];
    $createAtOtherStr = explode(".", trim($orderDetails["StateDate"]));
    $timeDate = strtotime($createAtOtherStr[0]);
    $orderDate = strtotime("5 hour", $timeDate);
    $newCreateAt = date('Y-m-d H:i:s', $orderDate);
    // print_r($orderDetails["StateDate"]);
    // print_r($newCreateAt);die;
    $stateDate = $orderDetails["StateDate"];
    $incrementId = "TW-".$receiptNum;
    $webOrderNo = $orderDetails['WebOrderNo'];
    $TotalAmountWithTax = $orderDetails['TotalAmountWithTax'];
    $TWFirstName = $orderDetails['FirstName'];
    $TWLastName = $orderDetails['LastName'];
    $TWEmail1 = $orderDetails['EMail1'];
    $TWEmail2 = $orderDetails['EMail2'];
    $name = $orderDetails['Name'];
    //echo $receiptNum.",".$webOrderNo;
    fwrite($fp, $receiptNum.",".$incrementId.",".$webOrderNo.",".$name.",".$TotalAmountWithTax.",".$stateDate.",".$TWFirstName." ".$TWLastName.",".$TWEmail1.",".$TWEmail2.PHP_EOL);
  }
}
?>
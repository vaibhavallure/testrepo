<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
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

$fileName = $_GET['filename'];
//var_dump($fileName);die;

//$dir = Mage::getBaseDir("var") . DS ."tw-mag-report/";
$dir = "";
/* get all increment id's from file for filtering*/
$filterIncrementIds = rtrim(buildIncrentId($dir.$fileName), ',');

// Create the SoapClient instance
$url = "https://www.mariatash.com/api/v2_soap?wsdl=1";
$client = new SoapClient($url, array("trace" => 1, "exception" => 0, 'cache_wsdl' => WSDL_CACHE_NONE));

/* get seesion id by calling login service*/
$tokenResult = $client->login(array(
    "username" => "allureinc",
    "apiKey" => "12qwaszx"
));

//print_r($filterIncrementIds);die;
$sessionId = $tokenResult->result;

/* create filter where we can search in array of increment id's*/
$filters = array('complex_filter' => array(
    array(
        'key' => 'increment_id',
        'value' => array(
            'key' => 'in',
            'value' => $filterIncrementIds
        ),
    )));

/* call salesOrderList service with given filter*/
$salesOrderResult = $client->salesOrderList(array('sessionId' => $sessionId, 'filters' => $filters));
//print_r($salesOrderResult);die;
foreach ($salesOrderResult as $orderArray) {
    foreach ($orderArray as $order) {
        foreach ($order as $o) {
            //print_r($o);die;
//      print_r($o->increment_id." = ".$o->grand_total);die;
            $date = new DateTime($o->created_at);
            $date->setTimezone(new DateTimeZone('America/New_York')); // +04

            searchWord($fileName, $o->increment_id, $o->base_grand_total, $o->order_id, $o->order_currency_code, $date->format('Y-m-d H:i:s'));
        }
    }
}

/**
 * Print out the file for response
 */
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . basename($dir.$fileName) . "\"");
readfile($dir.$fileName);


/**
 * @param fileName,incrementId,grantTotal
 * @desc Add PASS or FAIL at end of the line if the increment id is present
 */
function searchWord($fileName, $increment_id, $grand_total, $order_id, $currency, $date)
{
    // $addthis = ",".$grand_total.",PASS";
    $addthis = "," . $grand_total . "," . $order_id . "," . $currency . "," . $date;
    $lines = file($fileName);
    foreach ($lines as $lineNumber => $line) {
        if (strpos($line, $increment_id) !== false) {
            $lines[$lineNumber] = trim($lines[$lineNumber]) . $addthis . PHP_EOL;
            file_put_contents($fileName, $lines);
        }
    }
}

/**
 * @param fileName
 * @desc Returns increment ids in String comma seperated
 */
function buildIncrentId($fileName)
{
    $lines = file($fileName);
    $incrementIds = "";
    foreach ($lines as $lineNumber => $line) {
        $myvalue = $lines[$lineNumber];
        $lineArray = explode(',', trim($myvalue));
        $incrementIds .= "TW-" . $lineArray[0] . ",";
    }
    return $incrementIds;
}

/**
 * @param fileName
 * @desc Add FAIL and 0 grand  if PASS string not found in line
 */
function addFailStatus($fileName)
{
    $addthis = ",0,FAIL";
    //$addthis = ",0";
    $lines = file($fileName);
    foreach ($lines as $lineNumber => $line) {
        if ((strpos($line, ",PASS") == false) || (strpos($line, ",FAIL") == false)) {
            $lines[$lineNumber] = trim($lines[$lineNumber]) . $addthis . PHP_EOL;
            file_put_contents($fileName, $lines);
        } else {

        }
    }
}

?>

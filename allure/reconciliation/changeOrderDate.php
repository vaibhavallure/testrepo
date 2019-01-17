<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();

$file=$_GET['file'];

$fileName= "./changeorder/".$file;
$lines = file($fileName);
$order_count = 0; //user for total order count
$order = array();
if(!file_exists($fileName))
die("File Not Found...!");
Mage::log("In Change Order Date", Zend_Log::DEBUG, "reconciliation.log", true);

if($file ) {

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection = $resource->getConnection('core_write');

    foreach ($lines as $lineNumber => $line) {
        if ($lineNumber > 0) {
     $readConnection = $resource->getConnection('core_read');

    $query = "SELECT created_at,onestepcheckout_order_comment FROM sales_flat_order where increment_id='" . trim($lines[$lineNumber])."'";

    $results = $readConnection->fetchAll($query);
    $originalDate = $results[0]['created_at'];
    $originalComment = $results[0]['onestepcheckout_order_comment'];
    $newComment = $originalComment." ".$originalDate;
    $newDate = substr_replace($originalDate,"2008",0,4);
    //var_dump($results);

    try {

        //$writeConnection = $resource->getConnection('core_write');



        //$query = "UPDATE sales_flat_order SET created_at='". $newDate ."'"." and onestepcheckout_order_comment='" . $newComment . "' WHERE increment_id = '" . trim($lines[$lineNumber]) . "'";

        $query = "UPDATE sales_flat_order SET created_at = '".$newDate."' , onestepcheckout_order_comment = '".$newComment."' WHERE increment_id = '".trim($lines[$lineNumber])."'";


        $writeConnection->query($query);
        echo trim($lines[$lineNumber])." updated.</br>";
        Mage::log("Order Id:" . $lines[$lineNumber] . " New Date:" . $newDate." With Comment:".$newComment, Zend_Log::DEBUG, "reconciliation.log", true);

    }
    catch(Exception $ex)
    {
        echo $ex->getMessage();
    }
           // echo trim($lines[$lineNumber]);
            //break;
        }

    }

}




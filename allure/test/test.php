<?php


require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');


die();

/*
var_dump(json_decode(Mage::getStoreConfig("reclaim/allure/coupon_prefix")));


die();*/


$data[0]=array("qty"=>1,"length"=>12,"format"=>"alphanum");

Mage::getModel('klaviyo_reclaim/api2_coupon_rest_admin_v1')->_multiCreate($data);




die();

$product = Mage::getModel('catalog/product')->loadByAttribute('sku','ZXMARK');
var_dump($product->getId());
die();

var_dump(Mage::helper("appointments/data")->isPopupStore(1));


die();



var_dump(Mage::helper("appointments/notification")->getDate("2019/09/23",'fr'));
//var_dump(Mage::helper("appointments/notification")->getDateTime("2019/09/23 14:33:11","fr"));

die();
$resource = Mage::getSingleton('core/resource');
$writeAdapter = $resource->getConnection('core_write');
$readConnection = $resource->getConnection('core_read');




$file = 'app.csv';
$csv = new Varien_File_Csv();
$data = $csv->getData($file);

for($i=1; $i<count($data); $i++)
{


    var_dump($data[$i][0],$data[$i][13]);

    $app_start=$data[$i][13];
    $id=$data[$i][0];


    $updateQuery = "UPDATE `allure_piercing_appointments` SET `appointment_start`='{$app_start}' WHERE `id`={$id}";

    try {
        $writeAdapter->query($updateQuery);
        $writeAdapter->commit();


    } catch (Exception $e) {
        echo $e->getMessage();
    }



    die();
}
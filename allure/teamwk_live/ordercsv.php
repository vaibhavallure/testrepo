<?php
require_once ('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

$orderCollection = Mage::getModel("sales/order")->getCollection()
->addFieldToFilter("create_order_method",2);
$orderCollection->getSelect()->where("salesforce_order_id is not null and created_at < '2018-11-20'");

$header = array(
    "ID"                => "ID",
    "Created_At__c"     => "Created_At__c"
);

$io           = new Varien_Io_File();
$folderPath   = Mage::getBaseDir("var") . DS . "teamwork_live" ;
$filename    = "teamwork_order_date_update.csv";
$filepath     = $folderPath . DS . $filename;

$io = new Varien_Io_File();
$io->setAllowCreateFolders(true);
$io->open(array("path" => $folderPath));

$csv = new Varien_File_Csv();

$rows = array();
$rows[] = $header;


foreach ($orderCollection as $object){
    $salesforceId = $object->getSalesforceOrderId();
    $createdAt = $object->getCreatedAt();
    $createdAt = date("Y-m-d",strtotime($createdAt))."T".date("H:i:s",strtotime($createdAt));
    $rows[] = array(
        "ID"            => $salesforceId,
        "Created_At__c" => $createdAt
    );
}

$csv->saveData($filepath,$rows);

die("Finish");

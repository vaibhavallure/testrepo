<?php
require_once ('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

$orderCollection = Mage::getResourceModel('sales/order_creditmemo_collection');

$orderCollection->getSelect()->join(array("sales"=>"sales_flat_order"),"sales.entity_id=main_table.order_id",
   array("sales.created_at as order_date") );

$orderCollection->getSelect()->where("sales.create_order_method = 2 and sales.salesforce_order_id is not null and sales.created_at < '2018-11-20'");

//echo $orderCollection->getSelect()->__toString();
//die;
//->addFieldToFilter("create_order_method",2);

$orderCollection->load();

$header = array(
    "ID"                => "ID",
    "Order_Date__c"     => "Order_Date__c",
    "Created_At__c"     => "Created_At__c"
);

$io           = new Varien_Io_File();
$folderPath   = Mage::getBaseDir("var") . DS . "teamwork_live" ;
$filename    = "teamwork_creditmemo_date_update.csv";
$filepath     = $folderPath . DS . $filename;

$io = new Varien_Io_File();
$io->setAllowCreateFolders(true);
$io->open(array("path" => $folderPath));

$csv = new Varien_File_Csv();

$rows = array();
$rows[] = $header;


foreach ($orderCollection as $object){
    $createdAt = $object->getOrderDate();
    $createdAt = date("Y-m-d",strtotime($createdAt))."T".date("H:i:s",strtotime($createdAt))."+00:00";
    $salesforceId = $object->getSalesforceCreditmemoId();
        if(!$salesforceId){
          return;  
        }
        $rows[] = array(
            "ID"                => $salesforceId,
            "Order_Date__c"     => $createdAt,
            "Created_At__c"     => $createdAt
        );
}

$csv->saveData($filepath,$rows);

die("Finish");

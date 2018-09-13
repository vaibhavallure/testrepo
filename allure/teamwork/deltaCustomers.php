<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$csv = Mage::getBaseDir('var').DS."teamwork".DS.'customer Hashes Production.csv';
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');
$idIndex = 0;
$hashIndex = 2;


$csvFile = 'orders';

$ioo = new Varien_Io_File();
$path = Mage::getBaseDir('var') . DS . 'teamwork' ;
$name = "customer_hash_delta";
$file = $path . DS . $name . '.csv';
$ioo->setAllowCreateFolders(true);
$ioo->open(array('path' => $path));
$ioo->streamOpen($file, 'w+');
$ioo->streamLock(true);


$header = array('id'=>'id','email'=>'eamil','hash'=>'password_hash');
$data=array();
$io->streamWriteCsv($header);
while($csvData = $io->streamReadCsv()){
    $id = trim($csvData[$idIndex]);
    $hash=trim($csvData[$hashIndex]);
    $customer = Mage::getModel('customer/customer')->load($id);
    if ($customer->getId()){
        if(!empty($customer->getPasswordHash()) && $hash!=$customer->getPasswordHash()){
            $data['id']=$customer->getId();
            $data['email']=$customer->getEmail();
            $data['hash']=$customer->getPasswordHash();
            $ioo->streamWriteCsv($data);
            $data = array();
        }
        
    }
}

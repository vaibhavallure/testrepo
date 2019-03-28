<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

$dbname = 'mariatash_v1';

$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');


$query = "SHOW TABLES FROM $dbname";
$rows       = $connection->fetchAll($query);
foreach ($rows as $key=>$table){
    foreach ($table as $keyArray=>$tab){
        //echo $tab;
        $query1 = "SHOW COLUMNS FROM ".$tab;
        $rows1       = $connection->fetchAll($query1);
        foreach ($rows1 as $key1=>$value){
           // print_r($value['Field']);
            $coloumn=$value['Field'];
            $query2= 'UPDATE '.$tab.' SET '.$coloumn.' = REPLACE('.$coloumn.' , "venusbymariatash.com","mariatash.com");';
            try {
                $writeConnection->query($query2);
            } catch (Exception $e) {
                echo $query2;
                echo "<br>";
            }
           
            Mage::log($query2,Zend_log::DEBUG,'ajay.log',true);
           
          
        }
       
    }
   
}
//print_r($rows);
<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');
$logFile='spam_customers.log';


$collection=Mage::getModel('customer/customer')->getCollection();
$count=0;
foreach ($collection as $customer){
    $email = $customer->getEmail();
    try {
        if (strpos($email, '@') !== false) {
            if(substr_count($email, '@')>2){
                $count++;
                $customer->delete();
                Mage::log($count."::Email with Multiple @::".$email,Zend_log::DEBUG,$logFile,true);
            }
        }else{
            
            $count++;
            Mage::log($count."::Email without  @::".$email,Zend_log::DEBUG,$logFile,true);
            $customer->delete();
        }
    } catch (Exception $e) {
    }
}

die("Finished");